<?php

namespace App\License\Controllers\Admin;

use App\Model\Product\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClientController extends Controller
{
    public function viewClients(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->input('search-query', $request->input('search_query', ''));

        $query = User::select('id', 'first_name', 'last_name', 'email')
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($q2) use ($search): void {
                    $q2->where('first_name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('last_name', 'like', sprintf('%%%s%%', $search))
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [sprintf('%%%s%%', $search)])
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->orderBy('first_name');

        $paginated = $query->paginate(15, ['*'], 'page', $request->input('page', 1));

        $paginated->getCollection()->transform(fn ($u): array => [
            'client_id' => $u->id,
            'full_name' => trim($u->first_name.' '.$u->last_name),
            'email' => $u->email,
        ]);

        return response()->json(['data' => $paginated]);
    }

    public function viewProducts(): \Illuminate\Http\JsonResponse
    {
        $products = Product::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($p): array => [
                'product_id' => $p->id,
                'product_title' => $p->name,
            ]);

        return response()->json(['data' => ['data' => $products]]);
    }
}
