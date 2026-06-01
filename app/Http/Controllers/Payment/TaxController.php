<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxRate;
use App\Model\Payment\TaxRateLocation;
use Illuminate\Http\Request;

/**
 * Admin CRUD for the generic tax engine. Operates on `tax_rates` (+ optional
 * postcode/city locations) and `tax_classes`. No GST/CGST/SGST special-casing.
 */
class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'getState']);
        $this->middleware('admin', ['except' => 'getState']);
    }

    /** Options + tax classes + countries for the settings/forms screens. */
    public function getTaxOptionsApi()
    {
        try {
            return successResponse('', [
                'options' => TaxOption::find(1) ?: '',
                'classes' => $this->taxClassList(),
                'additional_tax_classes' => TaxClass::where('slug', '!=', '')
                    ->orderBy('name')->pluck('name')->implode("\n"),
                'countries' => getSupportedCountriesForIntlInput(),
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    /** Paginated list of tax rates (TaxIndex table). */
    public function getTax(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $classNames = TaxClass::pluck('name', 'slug');

            $rates = TaxRate::query()
                ->when($request->has('tax_class'), function ($query) use ($request) {
                    $query->where('tax_class', (string) $request->input('tax_class'));
                })
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where(function ($q) use ($searchString) {
                        $q->where('name', 'like', "%{$searchString}%")
                            ->orWhere('country', 'like', "%{$searchString}%")
                            ->orWhere('state', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $rates->getCollection()->transform(function ($rate) use ($classNames) {
                return [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'country' => $rate->country ?: 'All',
                    'state' => $rate->state ?: 'All',
                    'rate' => $rate->rate,
                    'priority' => $rate->priority,
                    'compound' => $rate->compound ? __('message.yes') : __('message.no'),
                    'tax_class_name' => $classNames[$rate->tax_class] ?? ($rate->tax_class ?: 'Standard'),
                    'active' => $rate->active ? __('message.active') : __('message.inactive'),
                ];
            });

            return successResponse(__('message.tax_fetched'), $rates);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /** Single rate for the edit form (with its postcode/city locations). */
    public function editTaxApi($id)
    {
        try {
            $rate = TaxRate::with('locations')->find($id);
            if (! $rate) {
                return errorResponse(__('message.tax_record_not_found'), 404);
            }

            $postcodes = $rate->locations->where('location_type', 'postcode')
                ->pluck('location_code')->implode(', ');
            $cities = $rate->locations->where('location_type', 'city')
                ->pluck('location_code')->implode(', ');

            return successResponse('', [
                'tax' => $rate,
                'postcode' => $postcodes,
                'city' => $cities,
                'classes' => $this->taxClassList(),
                'states' => $rate->country ? findStateByRegionId($rate->country) : [],
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /** Create a tax rate. */
    public function saveTaxClassSettingApi(Request $request)
    {
        try {
            if ($error = $this->validateRate($request)) {
                return errorResponse($error, 422);
            }

            $rate = TaxRate::create($this->rateAttributes($request));
            $this->syncLocations($rate, $request);

            return successResponse(__('message.created-successfully'), ['tax' => $rate]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    /** Update a tax rate. */
    public function updateTaxApi($id, Request $request)
    {
        try {
            $rate = TaxRate::find($id);
            if (! $rate) {
                return errorResponse(__('message.tax_not_found'), 404);
            }
            if ($error = $this->validateRate($request)) {
                return errorResponse($error, 422);
            }

            $rate->update($this->rateAttributes($request));
            $this->syncLocations($rate, $request);

            return successResponse(__('message.tax_updated_successfully'), ['tax' => $rate]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /** Bulk-delete tax rates (locations cascade via FK). */
    public function deleteTax(Request $request)
    {
        try {
            $ids = array_filter(array_unique(array_map('intval', (array) $request->input('select', []))));
            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $deleted = TaxRate::whereIn('id', $ids)->get();
            if ($deleted->isEmpty()) {
                return errorResponse(__('message.no-record'), 404);
            }

            TaxRate::whereIn('id', $ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /** States for a country (used by the rate form). */
    public function getState(Request $request, $stateid)
    {
        try {
            $states = \App\Model\Common\State::where('country_code', $stateid)
                ->orderBy('state_subdivision_name', 'asc')
                ->get(['iso2', 'state_subdivision_name']);

            return successResponse('', ['states' => $states]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /** Save global tax settings. */
    public function saveTaxOptionSetting(Request $request)
    {
        try {
            $taxOption = TaxOption::find(1);
            if (! $taxOption) {
                return errorResponse(__('message.tax_option_not_found'), 404);
            }

            $taxOption->fill($request->only([
                'tax_enable', 'inclusive', 'tax_based_on', 'rounding', 'Gst_no', 'cif_no',
            ]))->save();

            if ($request->has('additional_tax_classes')) {
                $this->syncAdditionalClasses((string) $request->input('additional_tax_classes'));
            }

            return successResponse(__('message.tax_settings_saved_successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    // --- helpers ---

    private function taxClassList(): array
    {
        return TaxClass::orderBy('name')->get(['name', 'slug'])
            ->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name])->all();
    }

    /**
     * Reconcile the additional tax classes from the settings textarea
     * (newline-separated, one class per line — Standard excluded). New names
     * are created; removed ones are deleted along with their rates, and any
     * products on a removed class fall back to Standard.
     */
    private function syncAdditionalClasses(string $raw): void
    {
        $desired = collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->mapWithKeys(fn ($n) => [\Illuminate\Support\Str::slug($n) => $n])
            ->forget('');

        $standardId = TaxClass::where('slug', '')->value('id');

        foreach (TaxClass::where('slug', '!=', '')->get() as $class) {
            if (! $desired->has($class->slug)) {
                TaxRate::where('tax_class', $class->slug)->delete();
                \App\Model\Payment\TaxProductRelation::where('tax_class_id', $class->id)
                    ->update(['tax_class_id' => $standardId]);
                $class->delete();
            }
        }

        foreach ($desired as $slug => $name) {
            if (! TaxClass::where('slug', $slug)->exists()) {
                TaxClass::create(['name' => $name, 'slug' => $slug]);
            }
        }
    }

    private function validateRate(Request $request): ?string
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'rate' => 'required|numeric|min:0',
            'priority' => 'nullable|numeric|min:1',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    private function rateAttributes(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'rate' => $request->input('rate'),
            'country' => strtoupper((string) $request->input('country', '')),
            'state' => (string) $request->input('state', ''),
            'priority' => (int) ($request->input('priority') ?: 1),
            'compound' => (bool) $request->input('compound', false),
            'tax_class' => (string) $request->input('tax_class', ''),
            'active' => $request->has('active') ? (bool) $request->input('active') : true,
        ];
    }

    private function syncLocations(TaxRate $rate, Request $request): void
    {
        TaxRateLocation::where('tax_rate_id', $rate->id)->delete();

        foreach (['postcode', 'city'] as $type) {
            $codes = array_filter(array_map('trim', explode(',', (string) $request->input($type, ''))));
            foreach ($codes as $code) {
                TaxRateLocation::create([
                    'tax_rate_id' => $rate->id,
                    'location_code' => $code,
                    'location_type' => $type,
                ]);
            }
        }
    }
}
