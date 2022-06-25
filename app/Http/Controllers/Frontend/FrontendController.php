<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use Validator;
use App\Events\Frontend\UserRegistered;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\CustomerRegister;
use Mail;
use Illuminate\Support\Str;
use Auth;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\UserDetail;
use App\Models\City;
use App\Models\School;
use App\Models\State;
use App\Models\SchoolOwner;
use App\Models\Payment;
use Session;

class FrontendController extends Controller
{

    function __construct(){
		$this->pids = Category::where('public_view', 1)->pluck('id');
		$this->cpids = Category::whereIn('parent_id', $this->pids)->pluck('id');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $body_class = '';
        $news = Product::where('public_view', 1)->whereIn('category_id', $this->cpids)->orderBy('id', 'desc')->limit(10)->get();
        return view('frontend.index', compact('body_class', 'news'));
    }

    /**
     * Privacy Policy Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        $body_class = '';

        return view('frontend.privacy', compact('body_class'));
    }

    /**
     * Terms & Conditions Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function terms()
    {
        $body_class = '';

        return view('frontend.terms', compact('body_class'));
    }
	
	public function Returnp()
    {

        return view('frontend.return');
    }
	
	
	public function about(){
		return view('frontend.about');
	}
	
	public function contact(){
		 
		return view('frontend.contact');
	}
	
	public function product($slug, $id){
		$product = Product::find($id);
		if(empty($product)){
			return back();
		}
		$pid = $product->getCat->parent_id;
		if($pid == 0) {$ispublic = $product->getCat->public_view;}
		else {
			$check = Category::where('id', $pid)->first();
			$ispublic = $check->public_view;
		}
		if($ispublic == 0){return back();}
		$attributes = [];
		$minp = 0;
		$maxp = 0;
		if($product->attributes > 0){
			$attributes = Attribute::where('product_id', $id)->select('attr_name')->groupBy('attr_name')->get();
		
		$minp = Attribute::where('product_id',$id)->selectRaw(' min(attr_price) as minimum')->first();
		
		$maxp = Attribute::where('product_id',$id)->selectRaw(' max(attr_price) as maximum')->first();
		}
			
			
		$relateds = Product::where('category_id', $product->category_id)
		->where('id', '!=', $product->id)
		->where('public_view', 1)
		->limit('6')->get();
		return view('frontend.product', compact('product', 'relateds', 'attributes','minp', 'maxp'));
	}
	
	public function shop($category = ''){
		$catname = '';
		$products = Product::where('public_view',1)
		->whereIn('category_id', $this->cpids)
		->orderBy('id', 'desc')->paginate(21)->onEachSide(2);
		if(!empty($category)){
			$find = Category::where('public_view', 1)->where('slug', $category)->first();
			$catname = $find->name;
			$products = Product::where('public_view',1)
			->where('category_id', $find->id)
			->orderBy('id', 'desc')->paginate(21)->onEachSide(2);
		}
		$categories = Category::where('public_view', 1)->where('parent_id', 0)->get();
		
		 
		return view('frontend.shop', compact('categories', 'products','catname'));
	}
	
	public function shopAll($category = ''){
		
		$products = Product::where('public_view',1)
		->whereIn('category_id', $this->cpids)
		->orderBy('id', 'desc')->paginate(21)->onEachSide(2);
		if(!empty($category)){
			$find = Category::where('slug', $category)->first();
			$getallcats = Category::where('parent_id', $find->id)->pluck('id');
			if(count($getallcats)){
			$products = Product::where('public_view',1)->whereIn('category_id', $getallcats)->orderBy('id', 'desc')->paginate(21)->onEachSide(2);} else {
				$products = Product::where('public_view',1)->where('category_id', $find->id)->orderBy('id', 'desc')->paginate(21)->onEachSide(2);
			}
		}
		$categories = Category::where('public_view', 1)->where('parent_id', 0)->get();
		
		 
		return view('frontend.shop', compact('categories', 'products'));
	}
	
	public function register(){
		 if(Auth::check()){
		 if(Auth::user()->role == 'public'){
			return redirect('user-area'); 
			}
			if(Auth::user()->role == 'office' or Auth::user()->role == 'school'){
				if(Auth::user()->is_commission_role == ''){
				return redirect('staff/dashboard');
				} else {
					return redirect('staff/commission-report');
				}
			}
			if(Auth::user()->role == 'admin'){
			return redirect('admin/dashboard'); 
			}
			
		 }
		 else {
		return view('frontend.register');
		 }
	}
	
	public function SchoolReg(){
		if(Auth::check()){
			if(Auth::user()->role == 'school'){
				if(Auth::user()->is_commission_role == ''){
				return redirect('staff/dashboard');
				} else {
					return redirect('staff/commission-report');
				}
		} else if(Auth::user()->role == 'admin'){
			
			return redirect('admin/dashboard');
		}
		else if (Auth::user()->role == 'public'){
			return redirect('my-account');
		}
		} else {
			$states = State::get();
		return view('frontend.registerasschool', compact('states'));
		 }
		
	}
	
	public function OfficeReg(){
		if(Auth::check()){
			if(Auth::user()->role == 'office'){
				if(Auth::user()->is_commission_role == ''){
				return redirect('staff/dashboard');
				} else {
					return redirect('staff/commission-report');
				}
			}else if(Auth::user()->role == 'admin'){
			
			return redirect('admin/dashboard');
		} else if (Auth::user()->role == 'public'){
			return redirect('my-account');
		}
		} else {
			$states = State::get();
		return view('frontend.registerasoffice', compact('states'));
		 }
		
	}

	
	 public function registerPost(CustomerRegister $req)
    {
        $key = Str::random(60);
        $email = $req->email;
        $fname = $req->first_name;
		$lname = $req->last_name;
		$schools = $req->school;
		$ref = '';
		if(isset($req->ref) and !empty($req->ref) ){
			$ref = $req->ref;
		}
		
		if(!empty($schools) and count($schools)> 0){
			$schoolids = implode(',', $schools);
		} else {$schoolids = 0;}
		
        $new =  new User;
		$new->name = $fname.' '.$lname;
        $new->first_name = $fname;
		 $new->last_name = $lname;
        $new->email = $email;
        $new->mobile = $req->mobile;
        $new->password =  bcrypt($req->password);
        $new->remember_token = $key ;
        $new->status = 0;
		$new->token_status = 0;
		$new->role = $req->type;
		$new->school_id = $schoolids;
		$new->school_name = '';
		$new->ref = $ref;
        if($new->save())
        {
			if($req->type == 'school' or $req->type == 'public'){
            Mail::send('frontend.mails.publicuserreg', ['key' => $key, 'email' => $email, 'first_name' => $fname], function ($message) use ($email)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('KOBESP User Email Verification');
                $message->to($email);
            });
			}
			
			if($req->type == 'school' or $req->type == 'office'){
				$newu = new UserDetail;
				$newu->user_id = $new->id;
				$newu->gender = $req->gender;
				$newu->dob = $req->dob;
				$newu->address = $req->address;
				$newu->city = \App\Models\City::getCity($req->city);
				$newu->state = \App\Models\State::getState($req->state);
				$newu->zip = $req->zip;
				$newu->save();
				
				if(count($schools)> 0){
				foreach($schools as $school){
					$ownerdata = [
					'school_id' => $school,
					'owner' => $new->id,
					];
					SchoolOwner::create($ownerdata);
					}
					
				}
			} else {
				$newu = new UserDetail;
				$newu->user_id = $new->id;
				$newu->save();
			}
			
			$resend = url('resend-activation/'.$email.'/'.$fname);
			if($req->type == 'school' or $req->type == 'public'){
            return back()->with(['regsuccessmsg'=> 'We sent you an email, pelase verify your email!', 'resend' => $resend]); 
			}
			
			else {
				return back()->with('regsuccessmsg', 'We have received your request for registration, After review we will approve with in 24 hours.'); 
			}
        }
    }
	
	public function ResendActivation($email = '' ,$fname = ''){
		if(empty($email) or empty($fname)){
			return back();
		}
		$key = Str::random(60);
        $email = $email;
        $fname = $fname;
		$resend = url('resend-activation/'.$email.'/'.$fname);
		Mail::send('frontend.mails.publicuserreg', ['key' => $key, 'email' => $email, 'first_name' => $fname], function ($message) use ($email)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('KOBESP User Email Verification');
                $message->to($email);
            });
			
		return back()->with(['regsuccessmsg'=> 'Activation link resent please check your inbox', 'resend' => $resend]); 
		
	}
	
	 public function AccountVerify($token = '', $email = '')
    {
        $act = User::where(['email' => $email,'remember_token' => $token, 'status' => 0, 'token_status' => 0])->update(['status' => 1, 'token_status' => 1]);
        if($act){
            return  view('frontend.accountverify', [ 'error' => 0]);
        } else { return  view('frontend.accountverify', [ 'error' => 1]); }
        
    }
	
	public function forgetPassword(){
		return view('frontend.forgetpass');
	}
	
	public function forgetPasswordPost(Request $req){
		$email = $req->email;
		$key = Str::random(60);
		$check = User::where('email', $email)->first();
		if(!empty($check)){
		Mail::send('frontend.mails.forgetpass', ['key' => $key, 'email' => $email, 'first_name' => $check->first_name], function ($message) use ($email)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('KOBESP Verify Your Email Address');
                $message->to($email);
            });
		User::where('email', $email)->update(['remember_token' => $key]);
		return back()->with('successmsg', 'Verification email sent successfully. Please check your inbox to process reset password');
		} else {
			return back()->with('errorsmsg', 'Your email did not find! please try again.');
		}
	}
	
	public function resetPassword($token = '', $email = ''){
		 
		if(empty($token) or empty($email)){
			$error = 1;
			 
			return view('frontend.resetpass', compact('error'));
		} else {
			
		$check = User::where(['email' => $email, 'remember_token' => $token ])->first();
		if(empty($check)){
			$error = 1;
			return view('frontend.resetpass', compact( 'error'));
		}	else {
			$error = 0;
			return view('frontend.resetpass', compact( 'error', 'email'));
		}
			
		}
	}
	
	public function resetPasswordPost(Request $req){
		$email = $req->email;
		 $pass = $req->password;
		$rpass = $req->password_confirmation;

		if($pass =! $rpass){
			return back()->with('errorsmsg', 'Your password does not match try again.');
		} else {
			$newpass = bcrypt($req->password);
			User::where('email', $email)->update(['password' => $newpass]);
			return back()->with('successmsg', 'Your password has beed updated successfully.');
		}
	}
  
  
  public function Login()
    {
        return view('frontend.register');
    }
	
	public function UserLoginCustom(){
		if(Auth::check()){
		 if(Auth::user()->role == 'public'){
			return redirect('user-area'); 
			}
			if(Auth::user()->role == 'office' or Auth::user()->role == 'school'){
				if(Auth::user()->is_commission_role == ''){
				return redirect('staff/dashboard');
				} else {
					return redirect('staff/commission-report');
				}
			}
			if(Auth::user()->role == 'admin'){
			return redirect('admin/dashboard'); 
			}
			
		 }
		 else {
		return view('frontend.login');
		 }
	}
	
	 public function UserLogin(Request $req)
    {
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password, 'status' => 1 ]))
        {
			if(Auth::user()->role == 'school' or Auth::user()->role == 'office'){
				if(Auth::user()->is_commission_role == ''){
				return redirect('staff/dashboard');
				} else {
					return redirect('staff/commission-report');
				}
			}
			
			if(Auth::user()->role == 'admin'){
				return redirect()->route('backend.dashboard');
			}
            return redirect('user-area');

        } else {
            return back()->with('errormsg', 'username / password not matched');
        }
    } 
	
	public function UserArea(){
		$orders = Order::where('user_id', Auth::user()->id)->get();
		return view('frontend.userarea.dashboard',compact('orders'));
	}
	
	public function addToCart(Request $req){
		if(Auth::check()){$userid = Auth::user()->id;} else{$userid = 0; }
	$qty = $req->qty;
	$userid = $userid;
	$price = $req->price;
	$pname = $req->pname;
	$pid = $req->pid;
	$total = $price*$qty;
	$sessionid = $req->sessionid;
	
	$check = CartItem::where(['session_id' => $sessionid, 'product_id' => $pid])->first();
	
	if(!empty($check)) {
		$newqty = $qty+$check->qty;
		$newtotal = $price*$newqty;
		CartItem::where(['session_id' => $sessionid, 'product_id' => $pid])->update(['qty' => $newqty, 'total' => $newtotal]);
		echo 'updated';
	} else {
		$new = new CartItem;
		$new->user_id = $userid;
		$new->product_id = $pid;
		$new->product_name = $pname;
		$new->price = $price;
		$new->qty = $qty;
		$new->total = $total;
		$new->session_id = $sessionid;

		if($new->save()) {echo 'added';}
	}	
}

public function cartNumber(Request $req){
	$check = CartItem::where('session_id', $req->sessionid)->get();
	echo count($check);
}

public function carttTotal(Request $req){
	$carttotal = CartItem::where('session_id', $req->sessionid)->sum('total');
	echo  number_format($carttotal,2);
}

public function cartList(Request $req){
	$list= CartItem::where('session_id', $req->sessionid)->get();
	return view('frontend.getlist', compact('list'));
}

public function cartRemove($id){
	CartItem::where('id', $id)->delete();
	return back();
}

public function cart(){
	$sessionid = Session::get('sessionId');
	$cartitmes = CartItem::where('session_id', $sessionid)->get();
	$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
	$shipping = 0.00;
	if(Auth::check()){
	$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	if(isset($userd->city) and !empty($userd->city)){
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}  
	}else {$shipping = 0.00;}
	}
		
	return view('frontend.cart', compact('cartitmes','carttotal','shipping'));
}

public function updateCart(Request $req){
	
	foreach($req->pid as $id){
		$qty = $req->qty[$id];
		$price = $req->price[$id];
		$total = $qty*$price;
		CartItem::where('id', $id)->update(['qty' => $qty, 'price' => $price, 'total' => $total]);
		
	}
	return back()->with('successmsg', 'Update has been successfully Update');
}

public function checkout(){
	if(Auth::check()){
		$sessionid = Session::get('sessionId');
		$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
	$cartitmes = CartItem::where('session_id', $sessionid)->get();
	$states = State::get();
	if(!empty(Auth::user()->userd->state)){
		$st = State::where('name', Auth::user()->userd->state)->first();
		$cities = City::where('state_id', $st->id)->get();
	}else{
	$cities = City::get();}
	if(count($cartitmes) > 0){
		$userd = UserDetail::where('user_id', Auth::user()->id)->first();
		if(!empty($userd->city)){
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
		} else {$shipping = 0.00;}
	
	return view('frontend.checkout', compact('cartitmes','carttotal','states','shipping','cities'));
	} else {
			return redirect('shop');
		}
	} else {
		return redirect('login-user');
	}
}

public function checkoutpost(Request $req){
	$sessionid = Session::get('sessionId');
	$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
	$cartitmes = CartItem::where('session_id', $sessionid)->get();
	$items = count($cartitmes);
	
	$usercity = $req->city;
	$city = City::find($usercity);
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
	
	
	$ord = new Order;
	$ord->user_id = Auth::user()->id;
	$ord->email = Auth::user()->email;
	$ord->items = $items;
	$ord->total_amount = $carttotal+$shipping;
	$ord->address = $req->address;
	$ord->city = $req->city;
	$ord->state = $req->state;
	$ord->zip = $req->zip;
	$ord->order_notes = $req->order_notes;
	$ord->phone = $req->phone;
	$ord->status = 1;
	$ord->delete_status = 0;
	$ord->role = Auth::user()->role;
	$ord->order_status = 'Pending';
	$ord->shipping_charges = $shipping;
	
	if($req->has('is_shipping')){
		$ord->is_delivery_address = 1;
		$ord->shipping_address = $req->shipping_address;
		$ord->shipping_state = $req->shipping_state;
		$ord->shipping_city = $req->shipping_city;
		$ord->shipping_zipcode = $req->shipping_zipcode;
		$ord->shipping_fname = $req->shipping_fname;
		$ord->shipping_lname = $req->shipping_lname;
		$ord->shipping_phone = $req->shipping_phone;
	}
	 
	if($ord->save()){
		
		$orderid = $ord->id;
		foreach($cartitmes as $item){
		$data = [
		'order_id' => $ord->id,
		'product_id' => $item->product_id,
		'product_name' => $item->product_name,
		'qty' => $item->qty,
		'price' => $item->price,
		'total' => $item->total,
		];
		OrderDetail::create($data);
		
		Product::where('id', $item->product_id)->decrement('stockinput', $item->qty);
		Product::where('id', $item->product_id)->update(['stock_used' => $item->qty]);
		CartItem::where('id', $item->id)->delete();
		
		}
		
		
		return redirect('payment/'.$orderid);
		
	} else {
		return back();
	}
	
}

public function orderComplete($orderid) {
	$order = Order::find($orderid);
	
	return view('frontend.ordercomplete', compact('order'));
}

public function orderCompletePost() {
	$rescode = request()->input('rescode');
	$resmsg = request()->input('resmsg');
	if($rescode == 0000){
	$buyeremail = request()->input('email');
	
	$transid = request()->input('transid');
	
	$orderid = request()->input('product');
	
	$cardno= request()->input('cardno');
	
	$cardco= request()->input('cardco');

	 $pay = new Payment;
	 $pay->user_email = $buyeremail;
	 $pay->order_id = $orderid;
	 $pay->transid = $transid;
	 $pay->cardno = $cardno;
	 $pay->cardco = $cardco;
	 $pay->status = 'Approved';
	 $pay->save();
	 $order = Order::find($orderid);
	 $order->pending_payment = 1;
	 $order->save();
	 
	 $order = Order::find($orderid);
	 $billing = [
	 'name' => $order->userd->name,
	 'email' => $order->userd->email,
	 'phone' => $order->userd->phone,
	 'address' => $order->userdd->address,
	 'city' => $order->userdd->city,
	 'state' => $order->userdd->state,
	 'zip' => $order->userdd->zip,
	 ];
	 if($order->is_delivery_address	== 1){
		 
	  $shipping = [
	 'name' => $order->shipping_fname.' '.$order->shipping_lname,
	 'phone' => $order->shipping_phone,
	 'address' => $order->shipping_address,
	 'city' => $order->getcityname->name,
	 'state' => $order->getstatname->name,
	 'zip' =>$order->shipping_zipcode,
	 ];
	 
	 } else {$shipping = '';}
	 
	 
	 Mail::send('frontend.mails.ordernotification', ['buyeremail' => $buyeremail,'billing' => $billing, 'shipping' => $shipping, 'order' => $order], function ($message) use ($buyeremail)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('New order has been placed');
                $message->to($buyeremail);
                $message->cc('pustaka.mindadexsb@gmail.com');
            });
	}
	
	return view('frontend.order-complete1', compact('rescode','resmsg'));
}


public function PendingPayment($orderid){
	if(!Auth::check()){return redirect('login-user');}
	$order = Order::find($orderid);
	Session::put('orderid', $orderid);
	$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	if(!empty($userd->city)){
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}} else {
		$shipping = 0.00;
	}
	if($order->pending_payment == 0){
	return view('frontend.payment', compact('order'));}
	else {
		return redirect('order-complete/'.$orderid)->with('shipping', $shipping);
	}
} 

public function orderView($orderid) {
	$order = Order::find($orderid);
	return view('frontend.userarea.orderview', compact('order'));
}

public function logOut(){
	if(Auth::check()){
		Auth::logout();
		return redirect('/');
	} else {
		return redirect('/');
	}
}

public function UserUpdate(Request $req){
	
	
	if(!empty($req->password) and !empty($req->rpassword)){
		if($req->password == $req->rpassword){
			$newpassword = bcrypt($req->password);
			User::where('id', Auth::user()->id)->update(['first_name' => $req->fname, 'last_name'=> $req->lname, 'password' => $newpassword]);
			return back()->with('successmsg', 'Prifle Updated Successfully');
		} else {
			return back()->with('errormsg', 'Password did not matched');
		}
		
	}
	
	User::where('id', Auth::user()->id)->update(['first_name' => $req->fname, 'last_name'=> $req->lname]);
	
	UserDetail::where('user_id', Auth::user()->id)->update(['state' => $req->state, 'city'=> $req->city, 'address' => $req->address]);
	
	return back()->with('successmsg', 'Prifle Updated Successfully');
	
	
}

public function Search(){
	$pids = Category::where('public_view', 1)->pluck('id');
	$cpids = Category::whereIn('parent_id', $pids)->pluck('id');
	$keyword = empty($_GET['keyword'])? '' : $_GET['keyword'];
	$products =  Product::where('title', 'like', "%$keyword%")
						->whereIn('category_id', $cpids)
						->orWhere('description', 'like', "%$keyword%")
						->paginate(15);
						

	return view('frontend.search', compact('products'));
}

public function getCitiesbyid(Request $req){
	$stateid = $req->state;
	$step = $req->step;
	$total = $req->total;
	$cities = City::where('state_id', $stateid)->get();
	return view('frontend.ajax.ajax', compact('cities', 'step','total'));
}

public function getShippingCharges(Request $req){
	$city =$req->city;
	$subtotal = $req->subtotal;
	$city = City::find($city);
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
	return view('frontend.ajax.ajax',compact('city','subtotal','shipping'));
}

public function getSchoolbyCityid(Request $req){
	
	$cityid = $req->city;
	$step = $req->step;
	$stateid = $req->stateid;
	$schools = School::where(['state_id' => $stateid, 'city_id' => $cityid])->where('id', '!=', 2)->get();
	//$schools = School::get();
	if(count($schools) == 0){
		return 0;
	} else {
	return view('frontend.ajax.ajax', compact('schools', 'step'));
	}
}

public function SendMsg(Request $req){
	$email = $req->con_email;
	$name = $req->con_name;
	$msg = $req->con_message;
	Mail::send('frontend.mails.contact', ['email' => $email, 'name' => $name, 'msg' => $msg], function ($message)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('Mail from Contact us Page');
                $message->to('pustaka.mindadexsb@gmail.com');
            });
			
			return back()->with('successmsg', 'Email has been sent!');
}

public function Tutorial(){
	return view('frontend.tutorial');
}

public function newReg(){
	return view('frontend.newreg');
}


	 
} 
