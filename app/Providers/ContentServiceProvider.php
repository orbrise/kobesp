<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use App\Models\Category;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Session;

class ContentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public $menuItems;
	public $cartItems;
	public $sum;

    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {	
        view()->composer('frontend.layouts.master', function($view) {
		
		$sessionid = csrf_token();
		if(empty(Session::get('sessionId'))){
		Session::put('sessionId', $sessionid);
		}
		$cats = Category::where('public_view', 1)->where('parent_id', 0)->get();
		$userid = 0;

		$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
		$cartitems = CartItem::where('session_id', $sessionid)->get();
        
		$this->cartItems = $cartitems;
        $this->menuItems = $cats;
		$this->sum = $carttotal;
		
        $view->with(['cats' => $this->menuItems, 'cartitems' => $this->cartItems, 
			'cart_total' => $this->sum]);
        });
    }
}
