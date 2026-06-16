<?php

namespace App\License\Controllers\Admin;

use App\Model\Product\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClientController extends Controller
{
    public function viewClients(Request $request)
    {
        $search = $request->input('search-query', $request->input('search_query', ''));

        $query = User::select('id', 'first_name', 'last_name', 'email')
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($q2) use ($search): void {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name');

        $paginated = $query->paginate(15, ['*'], 'page', $request->input('page', 1));

        $paginated->getCollection()->transform(fn ($u) => [
            'client_id' => $u->id,
            'full_name' => trim($u->first_name.' '.$u->last_name),
            'email' => $u->email,
        ]);

        return response()->json(['data' => $paginated]);
    }

    public function viewProducts()
    {
        $products = Product::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'product_id' => $p->id,
                'product_title' => $p->name,
            ]);

        return response()->json(['data' => ['data' => $products]]);
    }
}
