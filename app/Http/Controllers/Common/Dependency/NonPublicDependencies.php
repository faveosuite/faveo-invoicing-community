<?php

namespace App\Http\Controllers\Common\Dependency;

use App\Model\Common\Bussiness;
use App\Model\Common\PricingTemplate;
use App\Model\License\LicenseType;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PromotionType;
use App\Model\Payment\TaxClass;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Model\Product\Subscription;
use App\User;

class NonPublicDependencies extends BaseDependencyController
{
    protected function handleNonPublicDependencies(mixed $type): mixed
    {
        switch ($type) {
            case 'managers':
                return $this->managers();
            case 'products':
                return $this->products();
            case 'product-plans':
                return $this->productPlans();
            case 'industries':
                return $this->industries();
            case 'order-versions':
                return $this->orderVersions();
            case 'currencies':
                return $this->currencies();
            case 'license-types':
                return $this->licenseTypes();
            case 'product-groups':
                return $this->productGroups();
            case 'tax-classes':
                return $this->taxClasses();
            case 'periods':
                return $this->periods();
            case 'promotion-types':
                return $this->promotionTypes();
            case 'pricing-templates':
                return $this->pricingTemplates();
            case 'all-products':
                return $this->allProducts();
            case 'plugin-products':
                return $this->pluginProducts();
            case 'users':
                return $this->allSystemUsers();
        }

        return null;
    }

    private function currencies(): mixed
    {
        $this->sortField = 'code';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Currency)
            ->where('status', 1)
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('code', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('currencies', $baseQuery, fn ($item): array => ['id' => $item->code, 'name' => $item->code]);
    }

    private function managers(): mixed
    {
        $this->sortField = 'created_at';
        $this->sortOrder = 'asc';

        $role = $this->request->input('role', 'manager');

        $baseQuery = $this->baseQuery(new User)
            ->select('id', 'first_name', 'last_name', 'email')
            ->where('position', $role)
            ->when($this->searchQuery, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [sprintf('%%%s%%', $search)]);
                });
            });

        return $this->get('managers', $baseQuery, fn ($u): array => [
            'id' => $u->id,
            'name' => trim($u->first_name.' '.$u->last_name),
            'email' => $u->email,
        ]);
    }

    private function products(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Product)
            ->where('invoice_hidden', 0)
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('products', $baseQuery, fn ($item): array => ['id' => $item->id, 'name' => $item->name]);
    }

    private function industries(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Bussiness)
            ->where('name', 'LIKE', '%'.$this->searchQuery.'%')
            ->select('name', 'short');

        return $this->get('industries', $baseQuery, fn ($item): array => [
            'id' => $item->short,
            'name' => $item->name,
        ]);
    }

    /**
     * @return array<mixed>
     */
    private function orderVersions(): array
    {
        $versions = Subscription::where('version', '!=', '')
            ->whereNotNull('version')
            ->orderByDesc('version')
            ->distinct()
            ->pluck('version')
            ->map(fn ($v): array => ['id' => $v, 'name' => $v]);

        $special = collect([
            ['id' => 'Latest', 'name' => 'Latest'],
            ['id' => 'Outdated', 'name' => 'Outdated'],
        ]);

        return ['versions' => $special->merge($versions)->values(), 'next_page_url' => null];
    }

    private function productPlans(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $productId = $this->request->input('product_id');

        $baseQuery = $this->baseQuery(new Plan)
            ->where('product', $productId)
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('plans', $baseQuery, fn ($item): array => ['id' => $item->id, 'name' => $item->name, 'days' => $item->days]);
    }

    private function licenseTypes(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new LicenseType)
            ->select('id', 'name')
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('license_types', $baseQuery);
    }

    private function productGroups(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new ProductGroup)
            ->select('id', 'name')
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('product_groups', $baseQuery);
    }

    private function taxClasses(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new TaxClass)
            ->select('id', 'name')
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('tax_classes', $baseQuery);
    }

    /**
     * @return array<mixed>
     */
    private function periods(): array
    {
        $items = Period::orderByRaw('CAST(days AS UNSIGNED)')->get(['id', 'name', 'days']);

        return [
            'periods' => $items->map(fn ($p): array => ['id' => $p->days, 'name' => $p->name]),
            'next_page_url' => null,
        ];
    }

    /**
     * @return array<mixed>
     */
    private function promotionTypes(): array
    {
        $items = PromotionType::orderBy('id')->get(['id', 'name']);

        return ['promotion_types' => $items, 'next_page_url' => null];
    }

    private function pricingTemplates(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new PricingTemplate)
            ->select('id', 'name', 'image')
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('pricing_templates', $baseQuery);
    }

    private function allSystemUsers(): mixed
    {
        $this->sortField = 'first_name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new User)
            ->select('id', 'first_name', 'last_name', 'email')
            ->when($this->searchQuery, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [sprintf('%%%s%%', $search)]);
                });
            });

        return $this->get('managers', $baseQuery, fn ($u): array => [
            'id' => $u->id,
            'name' => trim($u->first_name.' '.$u->last_name),
            'email' => $u->email,
        ]);
    }

    private function allProducts(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Product)
            ->select('id', 'name')
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('products', $baseQuery, fn ($item): array => ['id' => $item->id, 'name' => $item->name]);
    }

    private function pluginProducts(): mixed
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $pluginTypeId = LicenseType::where('name', 'plugin')->value('id');

        $excludeId = (int) $this->request->input('exclude');

        $baseQuery = $this->baseQuery(new Product)
            ->select('id', 'name')
            ->where('type', $pluginTypeId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->when($this->searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            });

        return $this->get('products', $baseQuery, fn ($item): array => ['id' => $item->id, 'name' => $item->name]);
    }
}
