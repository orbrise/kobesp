<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\State;
use App\Models\SchoolClass;
use App\Models\Category;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\UserDetail;
use App\Models\User;
use App\Models\SchoolOwner;
use App\Models\Payment;
use App\Models\CommissionReport;
use Auth;
use Session;
use Mail;

class StaffController extends Controller
{
	 public function __construct()
    {
		if(empty(Session::get('SessionId'))){
		Session::put('sessionId',csrf_token());
		}
		
		$this->pids = Category::where('parent_view', 1)->pluck('id');
		$this->cpids = Category::whereIn('parent_id', $this->pids)->pluck('id');
		
		
	}
		public function  index(){
			
			if(Auth::check()){
				if(Auth::user()->role == 'public'){
				return redirect('user-area');
				} else {
					if(Auth::user()->is_commission_role == 'commission'){
				return redirect('staff/commission-report');
				}

				$cats = Category::where('parent_id', '!=', 0)->where('parent_view', 1)->get();
$sessionid = Session::get('sessionId');
$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
				$schools = SchoolOwner::where('owner', Auth::user()->id)->get();
				
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
			
			
				return view('backend.staff.dashboard', compact('schools','cats','shipping', 'carttotal'));
				}
			} else {
				return redirect('my-account');
			}
		}
		
		public function PlceOrder(){
			$cats = Category::where('parent_id', '!=', 0)->where('parent_view', 1)->get();
			$schools = SchoolOwner::where('owner', Auth::user()->id)->get();
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
			$sessionid = Session::get('sessionId');
$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
			return view('backend.staff.placeorder', compact('schools','cats','shipping','carttotal'));
		}
		
		public function getClasses(Request $req){
			$schoolid = $req->schoolid;
			$classes = SchoolClass::where('school_id', $schoolid)->get();
			return view('backend.staff.getclasses', compact('classes','schoolid'));
		}
		
		public function getCats(Request $req){
			$classid = $req->classid;
			$school_id= $req->schoolid;
			$classd = SchoolClass::find($classid);
			$booklist = $classd->booklist;
			$cats = Product::whereIn('category_id', $this->cpids)->where('parent_view',1)->where(['school_id'=> $school_id, 'class_id' => $classid])->select('category_id')->groupBy('category_id')->get();
			
			return view('backend.staff.getclasses', compact('classid','cats','school_id','booklist'));
		}
		
		public function getProds(Request $req){
			$classid = $req->classid;
			$school_id= $req->schoolid;
			$catid = $req->catid;
			$prods = Product::where('parent_view',1)->where(['school_id'=> $school_id, 'class_id' => $classid, 'category_id' => $catid])->get();
			
			return view('backend.staff.getclasses', compact('classid','prods','school_id','catid'));
		}
		
		public function getCatProds(Request $req){
			$catprods = Product::where('parent_view',1)->where('category_id', $req->catid)->get();
			return view('backend.staff.getclasses', compact('catprods'));
		}
		
		public function cart(){
			$sessionid = Session::get('sessionId');
			$items = CartItem::where('session_id', $sessionid)->get();
			$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
			$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
			return view('backend.staff.cart', compact('items', 'carttotal','shipping'));
		}
		
		public function checkout(){
			$sessionid = Session::get('sessionId');
			$items = CartItem::where('session_id', $sessionid)->get();
			$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
						$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
			return view('backend.staff.checkout', compact('items', 'carttotal','shipping'));
		}
		
		public function checkoutPost(Request $req){
			$sessionid = Session::get('sessionId');
			$carttotal = CartItem::where('session_id', $sessionid)->sum('total');
	$cartitmes = CartItem::where('session_id', $sessionid)->get();
	$items = count($cartitmes);
		$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
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
	
	$ord->address = Auth::user()->userd->address;
	$ord->city = Auth::user()->userd->city;
	$ord->state = Auth::user()->userd->state;
	$ord->zip = Auth::user()->userd->zip;
	$ord->order_notes = '';
	$ord->phone = Auth::user()->userd->phone;
	$ord->status = 1;
	$ord->delete_status = 0;
	$ord->role = Auth::user()->role;
	$ord->pending_payment = 0;
	$ord->shipping_charges = $shipping;
	
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
		CartItem::where('id', $item->id)->delete();
		}
		if(Auth::user()->role == 'school'){
		return redirect('staff/payment/'.$orderid);
		} else {
			return redirect('staff/orderpending/'.$orderid);
		}
		
		
	} else {
		return back();
	}
			
		}
		
		public function orderPending($orderid){
			$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
	
			$order = Order::find($orderid);
			$billing = [
	 'name' => Auth::user()->school_name,
	 'address' => $order->userdd->address,
	 'city' => $order->userdd->city,
	 'state' => $order->userdd->state,
	 'zip' => $order->userdd->zip,
	 ];
	 
	 $from = Auth::user()->name;
			$buyeremail = Auth::user()->email;
			 Mail::send('frontend.mails.requestform', ['buyeremail' => $buyeremail,'billing' => $billing, 'order' => $order, 'from' => $from, 'shipping' => $shipping], function ($message) use ($buyeremail)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('New order has been placed');
                $message->to($buyeremail);
                $message->cc('pustaka.mindadexsb@gmail.com');
            });
			
			return view('backend.staff.orderpending', compact('order','shipping'));
		}
		
		public function Reqform($orderid){
			$userd = UserDetail::where('user_id', Auth::user()->id)->first();
	$usercity = $userd->city;
	$city = City::where('name', $usercity)->first();
	if(!empty($city)){
	$shipping = $city->charges;
	} else {
		$shipping = 0.00;
	}
	
	 $order = Order::find($orderid);
	 $billing = [
	 'name' => Auth::user()->school_name,
	 'address' => $order->userdd->address,
	 'city' => $order->userdd->city,
	 'state' => $order->userdd->state,
	 'zip' => $order->userdd->zip,
	 ];
	 
		$from = Auth::user()->name;
			$buyeremail = Auth::user()->email;
			return view('backend.staff.reqformview', ['buyeremail' => $buyeremail,'billing' => $billing, 'order' => $order, 'from' => $from, 'shipping' => $shipping]);
		}
		
		public function doPayment($orderid){
		$order = Order::find($orderid);
			if($order->pending_payment == 0){
			return view('backend.staff.payment', compact('order'));
			} else {
				return redirect('staff/order-complete');
			}
		}
		
		public function orderCompletePost(){
		
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
	}
	
			return view('backend.staff.order-complete', compact('rescode','resmsg'));
		}
		
		public function UserProfileStaff(){
			$cities = City::get();
			return view('backend.staff.user-profile',compact('cities'));
		}
		
		public function UserProfileStaffPost(Request $req){
		if(!empty($req->password) and !empty($req->rpassword)){
		if($req->password == $req->rpassword){
			$newpassword = bcrypt($req->password);
			User::where('id', Auth::user()->id)->update(['first_name' => $req->fname, 'last_name'=> $req->lname, 'password' => $newpassword, 'mobile' => $req->phone]);
			return back()->with('successmsg', 'Profile Updated Successfully');
		} else {
			return back()->with('errormsg', 'Password did not matched');
		}
		
	}
	
	User::where('id', Auth::user()->id)->update(['first_name' => $req->fname, 'last_name'=> $req->lname, 'mobile' => $req->phone]);
	
	UserDetail::where('user_id', Auth::user()->id)->update(['address' => $req->address, 'city'=> $req->city, 'state' => $req->state, 'zip' => $req->zip]);
	
	return back()->with('successmsg', 'Profile Updated Successfully');
}
 public function OrdersList(){
	 $orders = Order::where('user_id', Auth::user()->id)->get();
	 return view('backend.staff.orderlist', compact('orders'));
 }
 
 public function OrderView($orderid = ''){
	 if(empty($orderid)) {return back();}
	 $order = Order::find($orderid);
	 return view('backend.staff.orderview', compact('order'));
 }
 
 public function UploadAttach(Request $req){
	 if($req->hasFile('file')){
		 $fileName = time().'.'.$req->file->extension();  
		 $req->file->move(public_path('upload_attachments'), $fileName);
		 
		 $orderid = $req->orderid;
		 Order::where('id', $orderid)->update(['attachment' => $fileName]);
		 return back()->with('attachmsg','File has been uploaded');
	 }
 }
 public function CommissionReport(){
	 if(!empty(request()->input('datef')) and !empty(request()->input('datef'))){
		 $datef = date("Y-m-d", strtotime(request()->input('datef')));
		 $datet = date("Y-m-d", strtotime(request()->input('datet')));
	 } else {
		 $datef = date("Y-m-01");
		 $datet = date("Y-m-d");
	 }
	 
	 if(Auth::check()){
		 if(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'school' and Auth::user()->subrole == 'headmaster'){
			 $schoolid = Auth::user()->school_id;
			 $schools = CommissionReport::where(['school_id' => Auth::user()->school_id, 'role' => 'school'])->whereBetween('created_at', [$datef, $datet])->select('email')->groupBy('email')->get();
			  return view('backend.staff.commission', compact('schools','schoolid','datef','datet'));
		 } 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'school' and Auth::user()->subrole == 'state'){
			 $states= CommissionReport::where(['role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('state_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('state_id')->get();
			  return view('backend.staff.commission', compact('states','datef','datet'));
		 }
		 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'school' and Auth::user()->subrole == 'city'){
			 $cities= CommissionReport::where(['role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('city_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('city_id')->get();
			  return view('backend.staff.commission', compact('cities','datef','datet'));
		 }
		 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'school' and Auth::user()->subrole == 'kobesp'){
			 $scs= CommissionReport::where(['role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('school_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('school_id')->get();
			  return view('backend.staff.commission', compact('scs','datef','datet'));
		 }
		 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'school' and Auth::user()->subrole == 'mind'){
			 $minds= CommissionReport::where(['role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('school_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('school_id')->get();
			 $states = State::get(); 
			  return view('backend.staff.commission', compact('minds','datef','datet','states'));
		 }
		 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'office' and Auth::user()->subrole == 'headmaster'){
			 $schoolid = Auth::user()->school_id;
			 $schools = CommissionReport::where(['school_id' => Auth::user()->school_id, 'role' => 'office'])->whereBetween('created_at', [$datef, $datet])->select('email')->groupBy('email')->get();
			  return view('backend.staff.commission', compact('schools','schoolid','datef','datet')); 
		 } 
		 
		  elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'office' and Auth::user()->subrole == 'state'){
			 $states= CommissionReport::where(['role' => 'office'])->whereBetween('created_at', [$datef, $datet])->selectRaw('state_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('state_id')->get();
			  return view('backend.staff.commission', compact('states','datef','datet'));
		 }
		 
		 elseif(Auth::user()->is_commission_role == 'commission' and Auth::user()->role == 'office' and Auth::user()->subrole == 'city'){
			 $cities= CommissionReport::where(['role' => 'office'])->whereBetween('created_at', [$datef, $datet])->selectRaw('city_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('city_id')->get();
			  return view('backend.staff.commission', compact('cities','datef','datet'));
		 }
		 
		   
	 } else {
		 return redirect('user-login');
	 }
	
 }
 
 public function CommissionSearch(){
	 $states = State::get();
$allschools = [];	 
	  if(!empty(request()->input('datef')) and !empty(request()->input('datef'))){
		 $datef = date("Y-m-d", strtotime(request()->input('datef')));
		 $datet = date("Y-m-d", strtotime(request()->input('datet')));
	 } else {
		 $datef = date("Y-m-01");
		 $datet = date("Y-m-d");
	 }
	 //$type = request()->input('type');

 if(!empty(request()->input('state')) or !empty(request()->input('city')) or !empty(request()->input('school'))){
 if(!empty(request()->input('state')) and empty(request()->input('city')) and empty(request()->input('school'))){
	 $state = request()->input('state');
	 $cities= CommissionReport::where(['role' => 'school', 'state_id' => $state])->whereBetween('created_at', [$datef, $datet])->selectRaw('city_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('city_id')->get();
	 $allcities = City::where('state_id', $state)->get();
	return view('backend.staff.commissionfilter', compact('cities','datef','datet','allcities', 'states', 'allschools'));
 }
 elseif(!empty(request()->input('state')) and !empty(request()->input('city')) and empty(request()->input('school'))){
	 $cityid = request()->input('city');
	 $state = request()->input('state');
	 $schools = CommissionReport::where(['city_id' => $cityid, 'role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('school_id, sum(qty) as totalqty,sum(product_cost) as totalcost, sum(total) as total')->groupBy('school_id')->get();
	 $allschools = School::where(['state_id' => $state, 'city_id' => $cityid])->get();
	 $allcities = City::where('state_id', $state)->get();
			  return view('backend.staff.commissionfilter', compact('schools','datef','datet', 'cityid', 'states', 'allcities', 'allschools'));
 
 }
 
 elseif(!empty(request()->input('state')) and !empty(request()->input('city')) and 
 !empty(request()->input('school'))){
	  $state = request()->input('state');
	  $cityid = request()->input('city');
	 $allcities = City::where('state_id', $state)->get();
	$allschools = School::where(['state_id' => $state, 'city_id' => $cityid])->get();
			 $schoolid = request()->input('school');
			 $schools = CommissionReport::where(['school_id' => $schoolid, 'role' => 'school'])->whereBetween('created_at', [$datef, $datet])->select('email')->groupBy('email')->get();
			  return view('backend.staff.commissionfilter', compact('schools','schoolid','datef','datet', 'states', 'allcities', 'allschools'));
 }
	 
  
 } else {
	 
	  $minds= CommissionReport::where(['role' => 'school'])->whereBetween('created_at', [$datef, $datet])->selectRaw('school_id, sum(product_cost) as totalcost, sum(total) as total')->groupBy('school_id')->get();
	$allcities = [];
			  return view('backend.staff.commissionfilter', compact('minds','datef','datet','states','allcities', 'allschools'));
	 
 }
	 
	 
 }
 
 public function OrderDelete($orderid){
			Order::where('id', $orderid)->delete();
			OrderDetail::where('order_id', $orderid)->delete();
			return back()->with('successmsg', 'Order has been deleted');
		}
	
}