<?php

namespace App\Http\Controllers\Front;

use App\DefaultPage;
use App\Demo_page;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Model\Common\Setting;
use App\Model\Common\TemplateType;
use App\Model\Front\FrontendPage;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Config;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use Throwable;

class PageController extends Controller
{
    /**
     * @var FrontendPage
     */
    public $page;

    public function __construct()
    {
        $this->middleware(['auth', 'admin'], ['except' => ['postDemoReq', 'postContactUs', 'pageBySlug', 'contactUsInfo']]);
        $this->middleware('recaptcha:contact')->only('postContactUs');
        $this->middleware('recaptcha:demo')->only('postDemoReq');
        $page = new FrontendPage;
        $this->page = $page;
    }

    /**
     * Public: fetch a single published page by slug for the SPA page view.
     * Returns null data (200) when not found so the client can show a
     * "page not found" state instead of being redirected.
     */
    public function pageBySlug(string $slug): JsonResponse
    {
        try {
            $page = FrontendPage::where('slug', $slug)
                ->where('publish', 1)
                ->select('id', 'name', 'slug', 'content', 'type')
                ->first();

            return successResponse('', $page);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function transform(string $type, string $data, array $trasform = []): string // @phpstan-ignore missingType.iterableValue
    {
        $config = Config::get('transform.'.$type);
        $result = '';
        $array = [];
        foreach ($trasform as $trans) {
            $array[] = $this->checkConfigKey($config, $trans);
        }

        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            $array1 = $this->keyArray($array[$i]);
            $array2 = $this->valueArray($array[$i]);
            $result .= str_replace($array1, $array2, $data);
        }

        return $result;
    }

    public function getPriceDescription(int $productId): string
    {
        try {
            $product = Product::find($productId);
            if (! $product instanceof Product) {
                return '';
            }

            if ($product->add_to_contact == 1) {
                return '';
            }

            $plans = Plan::where('product', $productId)
                ->where('status', 1)
                ->with('planPrice')
                ->cursor();

            foreach ($plans as $plan) {
                if (in_array($plan->days, [365, 366])) {
                    $description = $plan->planPrice->first();
                    if ($description) {
                        if (is_null($description->add_price) || $description->add_price === '' || $description->add_price == 0) { // @phpstan-ignore function.impossibleType
                            return 'free';
                        }

                        if ($product->status) {
                            return $description->no_of_agents
                                ? 'per month for <strong>'.$description->no_of_agents.' agent</strong>'
                                : 'per month';
                        }

                        return (string) $description->price_description;
                    }
                }
            }

            if (! $product->status) {
                $plan = $plans->first();
                if ($plan && $plan->planPrice->isNotEmpty()) {
                    return (string) $plan->planPrice->first()->price_description;
                }
            }

            return '';
        } catch (Exception $exception) {
            Logger::exception($exception);

            return '';
        }
    }

    /**
     * @param  array<mixed>  $transform
     * @return mixed[]
     */
    public function checkConfigKey(mixed $config, array $transform): array
    {
        $result = [];
        if ($config) {
            foreach ($config as $key => $value) {
                if (array_key_exists($key, $transform)) {
                    $result[$value] = $transform[$key];
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function keyArray(mixed $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $key;
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function valueArray(mixed $array): array
    {
        $result = [];
        foreach ($array as $value) {
            $result[] = $value;
        }

        return $result;
    }

    public function postContactUs(ContactRequest $request): JsonResponse
    {
        try {
            $contact = getContactData();

            $isSpam = $this->detectSpam($request->input('message'));

            if ($isSpam) {
                return errorResponse(__('message.spam_detected'));
            }

            $set = new Setting;
            $set = $set->findOrFail(1);

            $template = TemplateType::getSelectedTemplate('contact_us');
            if (! $template) {
                throw new Exception('Template not found');
            }
            $replace = [
                'name' => $request->input('conName'),
                'email' => $request->input('email'),
                'message' => $request->input('conmessage'),
                'mobile' => $request->input('country_code').' '.$request->input('Mobile'),
                'ip_address' => $request->ip(),
                'title' => $set->title,
                'request_url' => request()->fullUrl(),
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'reply_email' => $request->input('email'),

            ];
            $type = (string) ($template->type()->value('name') ?? '');

            if (emailSendingStatus()) {
                $mail = new PhpMailController;
                $mail->SendEmail((string) $set->email, (string) $set->company_email, (string) $template->data, (string) $template->name, $type, $replace, $type);
            }

            return successResponse(__('message.message_sent_successfully_400'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function detectSpam(string $message): bool
    {
        if ($this->containsExcessivePunctuation($message)) {
            return true;
        }

        if ($this->containsExcessiveCaps($message)) {
            return true;
        }

        return $this->containsSpamKeywords($message);
    }

    private function containsExcessivePunctuation(string $text): bool
    {
        return (bool) preg_match('/!{5,}/', $text);
    }

    private function containsExcessiveCaps(string $text): bool
    {
        $uppercaseCount = preg_match_all('/[A-Z]/', $text);
        $lowercaseCount = preg_match_all('/[a-z]/', $text);
        $totalCharacters = $uppercaseCount + $lowercaseCount;
        if ($totalCharacters > 0) {
            $percentageCaps = ($uppercaseCount / $totalCharacters) * 100;
            if ($percentageCaps > 50) {
                return true;
            }
        }

        return false;
    }

    private function containsSpamKeywords(string $text): bool
    {
        $spamKeywords = ['viagra', 'casino', 'lottery', 'free money', 'enlargement', 'promotions'];

        return array_any($spamKeywords, fn ($keyword): bool => stripos($text, (string) $keyword) !== false);
    }

    public function postDemoReq(ContactRequest $request): JsonResponse
    {
        try {
            $contact = getContactData();
            $isSpam = $this->detectSpam($request->input('demomessage'));

            if ($isSpam) {
                return errorResponse(__('message.spam_detected'));
            }

            $set = new Setting;
            $set = $set->findOrFail(1);

            $template = TemplateType::getSelectedTemplate('demo_request');
            if (! $template) {
                throw new Exception('Template not found');
            }
            $replace = [
                'name' => $request->input('demoname'),
                'email' => $request->input('demoemail'),
                'message' => $request->input('demomessage'),
                'mobile' => $request->input('country_code').' '.$request->input('Mobile'),
                'ip_address' => $request->ip(),
                'title' => $set->title,
                'request_url' => request()->fullUrl(),
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'reply_email' => $request->input('demoemail'),

            ];
            $type = (string) ($template->type()->value('name') ?? '');
            $product = $request->input('product') != 'online' ? $request->input('product') : 'our product ';
            $templatename = (string) $template->name.' '.'for'.' '.$product;

            if (emailSendingStatus()) {
                $mail = new PhpMailController;
                $mail->SendEmail((string) $set->email, (string) $set->company_email, (string) $template->data, $templatename, $type, $replace, $type);
            }

            return successResponse(__('message.message_sent_successfully_400'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getDemoStatus(): JsonResponse
    {
        $demo = Demo_page::first();

        return successResponse('', [
            'status' => $demo && (bool) $demo->status,
        ]);
    }

    public function saveDemoPage(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        Demo_page::updateOrCreate([],
            ['status' => $request->boolean('status')]
        );

        return successResponse(__('message.data_updated_successfully'));
    }

    public function getAllPages(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $pages = FrontendPage::select('id', 'name', 'url', 'created_at')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->where('name', 'like', sprintf('%%%s%%', $searchQuery))
                        ->orWhere('url', 'like', sprintf('%%%s%%', $searchQuery));
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $pages);
    }

    public function deleteBulkPages(Request $request): JsonResponse
    {
        $ids = $request->input('page_ids', []);

        $defaultPageId = DefaultPage::value('page_id');

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        if (in_array($defaultPageId, $ids)) {
            return errorResponse(__('message.can-not-delete-default-page'));
        }

        FrontendPage::whereIn('id', $ids)->where('id', '!=', $defaultPageId)->delete();

        return successResponse(__('message.deleted-successfully'));
    }

    public function currencyFormatWithSpan(float|int $amount, string $currency, ?int $id = null): string
    {
        // number only
        $formatted = currencyFormat($amount, $currency, includeSymbol: false);

        // formatted with symbol (actual placement)
        $withSymbol = currencyFormat($amount, $currency);

        // extract symbol by removing number part
        $symbol = trim(str_replace($formatted, '', $withSymbol));

        // prepare span
        $span = '<span class="price-unit"'.($id ? ' id="'.$id.'"' : '').'>'.$symbol.'</span>';

        // rebuild keeping correct placement
        if (str_starts_with((string) $withSymbol, $symbol)) {
            // symbol is in front
            return $span.$formatted;
        }

        // symbol at the end
        return $formatted.$span;
    }

    public function createPage(Request $request): JsonResponse
    {
        try {
            $pagesCount = FrontendPage::count();
            if ($pagesCount >= 3) {
                return errorResponse(__('message.limit_exceed'));
            }

            $url = $request->input('url');
            if ($request->input('type') === 'contactus') {
                $url = url('/contact-us');
            }

            $page = FrontendPage::create([
                'name' => $request->input('name'),
                'publish' => $request->input('publish', 0),
                'slug' => $request->input('slug'),
                'url' => $url,
                'parent_page_id' => $request->input('parent_page_id') ?? 0,
                'type' => $request->input('type'),
                'content' => $request->input('content'),
            ]);

            return successResponse(__('message.saved-successfully'), $page);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getPage(Request $request, int $pageId): JsonResponse
    {
        try {
            $page = FrontendPage::with('parent:id,name')->findOrFail($pageId);
            $defaultPageId = DefaultPage::value('page_id');
            $data = $page->toArray();
            $data['is_default'] = (int) $page->id === (int) $defaultPageId;

            return successResponse('', $data);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatePage(Request $request, int $pageId): JsonResponse
    {
        try {
            $page = FrontendPage::findOrFail($pageId);

            // Fill except created_at
            $page->fill($request->except('created_at'));

            // parent_page_id is NOT NULL in the schema; default to 0 (no parent)
            if ($page->parent_page_id === null) { // @phpstan-ignore identical.alwaysFalse
                $page->parent_page_id = 0;
            }

            // Handle created_at if provided and valid
            if ($request->filled('created_at')) {
                $date = DateTime::createFromFormat('m/d/Y', $request->input('created_at'));
                if ($date) {
                    $page->created_at = Date::instance($date);
                }
            }

            $page->save();

            $defaultPageId = $request->input('default_page_id');
            $defaultUrl = $defaultPageId
                ? FrontendPage::where('id', $defaultPageId)->value('url')
                : url('my-invoices');

            DefaultPage::findOrFail(1)->update([
                'page_id' => $defaultPageId ?? 1,
                'page_url' => $defaultUrl,
            ]);

            return successResponse(__('message.updated-successfully'), $page);
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }
}
