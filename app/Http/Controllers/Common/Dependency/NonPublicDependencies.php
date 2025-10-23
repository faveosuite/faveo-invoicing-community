<?php

namespace App\Http\Controllers\Common\Dependency;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\User;

class NonPublicDependencies extends BaseDependencyController
{
    protected function handleNonPublicDependencies($type)
    {
        switch ($type) {
            case 'managers':
                return $this->managers();
            case 'products':
                return $this->products();
            case 'product-plans':
                return $this->productPlans();
        }
    }

    private function managers()
    {
        $this->sortField = 'created_at';
        $this->sortOrder = 'asc';

        $role = $this->request->input('role', 'manager');

        $baseQuery = $this->baseQuery(new User)
            ->select('id', 'first_name', 'last_name', 'email')
                ->where('position', $role)
                ->when($this->searchQuery, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    });
                });

        return $this->get('managers', $baseQuery);
    }

    private function products()
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Product)
            ->where('invoice_hidden', 0)
                ->when($this->searchQuery, function ($query, $searchQuery) {
                    $query->where('name', 'like', "%{$searchQuery}%");
                });

        return $this->get('products', $baseQuery, function ($item) {
            return ['id' => $item->id, 'name' => $item->name];
        });
    }

    private function productPlans()
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $productId = $this->request->input('product_id');

        $baseQuery = $this->baseQuery(new Plan)
            ->where('product', $productId)
            ->when($this->searchQuery, function ($query, $searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            });

        return $this->get('plans', $baseQuery, function ($item) {
            return ['id' => $item->id, 'name' => $item->name];
        });
    }
}
