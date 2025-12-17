<?php

namespace App\Http\Middleware;

use App\DefaultPage;
//use Illuminate\Routing\Middleware;
use Cart;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class Admin
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    protected $cart;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cart = new Cart();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $defaulturl = DefaultPage::pluck('page_url')->first();
        if (\Auth::user()->role == 'admin') {
            return $next($request);
        } elseif (\Auth::user()->role == 'user') {
            $url = \Session::get('session-url');
            if ($url) {
                $content = \Cart::getContent();
                $currency = \Session::get('currency');
                if (\Auth::user()->currency != $currency) {//If user currency is not equal to the cart currency then redirect to default url and clear his cart items and let the customer add the Product again so that the tax could be calculated properly
                    foreach ($content as $key => $item) {
                        $id = $item->id;
                        $this->cart->remove($id);
                    }
                    \Session::forget('content');

                    return redirect($defaulturl);
                }
                $domain = \Session::get('domain');

                return redirect($url);
            }

            return redirect($defaulturl);
        } else {
            \Auth::logout();
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('login')->with('fails', 'Unauthorized');
            }
        }
    }
}
