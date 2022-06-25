<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\OrderDetail;
use App\Models\School;
use Auth;
use App\Models\SchoolClass;
use App\Models\Category;
use App\Models\Product;
use App\Models\Commission;
use Mail;

class BackendController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$from=date("Y-m-01");
		$to = date("Y-m-t");
		$torders = Order::get();
		$tschools = School::get();
		$parents = User::where('role','school')->get();
		$office = User::where('role','office')->get();
        return view('backend.index', compact('torders','tschools','parents', 'office'));
    }
	
	public function OrdersList(){
	    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('orders', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		if(!empty($_GET['optradio'])){
			$type = $_GET['optradio'];
	 $orders = Order::where('role', $type)->get();
		} else {
			$orders = Order::get();
			$type = '';
		}
	 return view('backend.orders.orderlist', compact('orders','type'));
 }
 
 
 
 
	public function OrderView($orderid = ''){
	 if(empty($orderid)) {return back();}
	 $order = Order::find($orderid);
	 if(empty($order)) {return back();}
	 return view('backend.orders.orderview', compact('order'));
 }
 
 public function PendingReq(){
     if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('pendingusers', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
	 $users = User::where(['status' => 0, 'role' => 'office'])->get();
	 return view('backend.pendingreq', compact('users'));
 }
 
 public function PendingAct($id, $action){
	 $user = User::find($id);
	 if($action == 'approve'){
		 User::where('id', $id)->update(['status' => 1]);
		 $name = $user->name;
		 $email = $user->email;
		 
		 Mail::send('frontend.mails.officeuseralert', ['email' => $email, 'name' => $name], function ($message) use ($email)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('Your account is activated - Kobesp');
                $message->to($email);
            });
			
	 }
	 
	 if($action == 'reject'){
		 User::where('id', $id)->delete();
	 }
	 return back()->with('successmsg', 'User successfully updated');
 }
 
	public function ChangeOrderStatus(Request $req){
		$ord = Order::find($req->orderid);
		$ord->order_status = $req->status;
		if(!empty($req->shippername)){
			$ord->shiper_name = $req->shippername;
		}
		if(!empty($req->trackingnumber)){
			$ord->tracking_num = $req->trackingnumber;
		}
		
		if($ord->save()){
			return back();
		}
	}
	
	public function ChangeOrdertracking(Request $req){
		$ord = Order::find($req->orderid);
		$email = $ord->email;
		$company = $ord->shiper_name;
		$trackingnumber = $ord->tracking_num;
		$url = $ord->tracking_url;
		$name = $ord->userd->name;
		
		if(!empty($req->shippername)){
			$ord->shiper_name = $req->shippername;
		}
		if(!empty($req->trackingnumber)){
			$ord->tracking_num = $req->trackingnumber;
		}
		
		if(!empty($req->url)){
			
			$ord->tracking_url = $req->url;
		}
		
		if($ord->save()){
			
			Mail::send('frontend.mails.shippinginfo', ['name' => $name, 'company' => $company, 'url' => $url, 'trackingnumber' => $trackingnumber], function ($message) use ($email)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('Order has been shipped | Kobesp.com.my');
                $message->to($email);
            });
			
			return back()->with('successmsg', 'Information has been update');
		}
		
	}
	
		public function Stockreports(){
		    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('stock', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		    
			$schools = School::get();
			$products = Product::where('stockinput', '<=', '5')->get();
			return view('backend.reports.stock', compact('schools','products'));
		}
		
		public function getClasses(Request $req){
		    $schoolid = $req->schoolid;
			$classes = SchoolClass::where('school_id', $schoolid)->get();
			return view('backend.reports.getclasses', compact('classes','schoolid'));
		}
		
		public function getCats(Request $req){
			$classid = $req->classid;
			$school_id= $req->schoolid;
			$cats = Product::where(['school_id'=> $school_id, 'class_id' => $classid])->select('category_id')->groupBy('category_id')->get();
			return view('backend.reports.getclasses', compact('classid','cats','school_id'));
		}
		
		public function getProds(Request $req){
			$classid = $req->classid;
			$school_id= $req->schoolid;
			$catid = $req->catid;
			$prods = Product::where(['school_id'=> $school_id, 'class_id' => $classid, 'category_id' => $catid])->get();
			return view('backend.reports.getclasses', compact('classid','prods','school_id','catid'));
		}
		
		public function Salereports(){
		    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('sales', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
			$categories = Category::where('parent_id', '!=', 0)->orderBy('name')->get();
			$from = date('Y-m-01');
			$to = date('Y-m-t');
			$lfrom = date('Y-m-01', strtotime('-1 month'));
			$lto = date('Y-m-t', strtotime('-1 month'));
			
			$currentmonthsalereport = Order::selectRaw('sum(total_amount) as totals')
										->whereBetween('created_at', [$from, $to])
										->first();
			$lastmonthsalereport = Order::selectRaw('sum(total_amount) as totals')
										->whereBetween('created_at', [$lfrom, $lto])
										->first();
										
			$currentmonthtotalitems = Order::selectRaw('sum(items) as totalis')
										->whereBetween('created_at', [$from, $to])
										->first();
			$lastmonthtotalitems = Order::selectRaw('sum(items) as totali')
										->whereBetween('created_at', [$lfrom, $lto])
										->first();
			$orders = OrderDetail::whereBetween('created_at', [$from, $to])
			->select('product_id')
			->groupBy('product_id')
			->selectRaw('sum(qty) as totalqty')
			->selectRaw('sum(total) as totalsale')
			->orderBy('totalqty', 'desc')
			->get();
			return view('backend.reports.salereport', compact('orders','currentmonthsalereport', 'lastmonthsalereport','currentmonthtotalitems','lastmonthtotalitems','categories'));
		}
		
		public function Salereportscat(Request $req){
			$fdate = date('Y-m-d', strtotime($req->fdate)); 
			$tdate = date('Y-m-d', strtotime($req->tdate));
			$cat = $req->cat;
			if(!empty($cat)){
				$prodsids = Product::where('category_id', $cat)->pluck('id');
				$orders = OrderDetail::whereBetween('created_at', [$fdate, $tdate])
				->whereIn('product_id', $prodsids)
			->select('product_id')
			->groupBy('product_id')
			->selectRaw('sum(qty) as totalqty')
			->selectRaw('sum(total) as totalsale')
			->orderBy('totalqty', 'desc')
			->get();
			
				
			} else {
			$orders = OrderDetail::whereBetween('created_at', [$fdate, $tdate])
			->select('product_id')
			->groupBy('product_id')
			->selectRaw('sum(qty) as totalqty')
			->selectRaw('sum(total) as totalsale')
			->orderBy('totalqty', 'desc')
			->get();
			}
			
			return view('backend.reports.getcatprods', compact('orders'));
			
		}
		
		public function Salemanreport(){
		    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('saleman', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
			$ref= '';
			$data = [];
			return view('backend.reports.saleman', compact('ref','data'));
		}
		
		public function Salemanreportpost(Request $req){
			$data= [];
			$ref = $req->ref;
			if(!empty($req->ref)){
			$users = User::where('ref', $req->ref)->get();
			
		if(count($users) > 0){
			foreach($users as $user){
				$order = Order::where('user_id',$user->id)->selectRaw('sum(total_amount) as total')->first();
				
			$data[] = [
			'user_id' => $user->id,
			'user_email' => $user->email,
			'total' => $order->total,
			];	
			}
		}
			}
			
			return view('backend.reports.saleman', compact('data','ref'));
		}
		
		public function Comissionreports(){
			$categories = Category::where('parent_id', '!=', 0)->orderBy('name')->get();
			$from = date('Y-m-01');
			$to = date('Y-m-t');
			$totalp = 0;
			$totalc = 0;
			
			$totala = Commission::whereBetween('created_at', [$from, $to])
										->get();
			$totalearnings = Commission::whereBetween('created_at', [$from, $to])
										->sum('total');
			$totalqty = Commission::whereBetween('created_at', [$from, $to])
										->sum('qty');	

			$totalcost = Commission::whereBetween('created_at', [$from, $to])
										->sum('product_cost');										
			foreach($totala as $val){
				$totalp += $val['price'];
				$totalc += $val['product_cost'];
			}
			
			$total = $totalp-$totalc;
										
			$schoolshop = ($total/100)*45;
			$headmaster = 0;
			$mgbstate = ($total/100)*1;
			$mgbdistrict = ($total/100)*1;
			$kobesp = ($total/100)*3;
			$p_mondedex = ($total/100)*50;
			
			
			return view('backend.reports.comission', compact('categories','schoolshop','headmaster','mgbstate','mgbdistrict','kobesp','p_mondedex','totala','totalearnings','totalqty','totalcost'));
		}
		
		public function Comissionreportbyrole(Request $req){
			
			
			$categories = Category::where('parent_id', '!=', 0)->orderBy('name')->get();
			$from = date('Y-m-01', strtotime($req->fdate));
			$to = date('Y-m-t', strtotime($req->tdate));
			$role = $req->role;
			$totalp = 0;
			$totalc = 0;
			
			if(!empty($role)){
			$totala = Commission::where('role', $role)->whereBetween('created_at', [$from, $to])->get();
			$totalearnings = Commission::where('role', $role)->whereBetween('created_at', [$from, $to])
										->sum('total');
			$totalqty = Commission::where('role', $role)->whereBetween('created_at', [$from, $to])
										->sum('qty');	

			$totalcost = Commission::where('role', $role)->whereBetween('created_at', [$from, $to])
										->sum('product_cost');
										
			}else {
			$totala = Commission::whereBetween('created_at', [$from, $to])
										->get();

			$totalearnings = Commission::whereBetween('created_at', [$from, $to])
										->sum('total');
			$totalqty = Commission::whereBetween('created_at', [$from, $to])
										->sum('qty');	

			$totalcost = Commission::whereBetween('created_at', [$from, $to])
										->sum('product_cost');
										
			}
			
			
													
			foreach($totala as $val){
				$totalp += $val['price'];
				$totalc += $val['product_cost'];
			}
			
			$total = $totalp-$totalc;
										
			$schoolshop = ($total/100)*45;
			$headmaster = 0;
			$mgbstate = ($total/100)*1;
			$mgbdistrict = ($total/100)*1;
			$kobesp = ($total/100)*3;
			$p_mondedex = ($total/100)*50;
			
			
			return view('backend.reports.comissionbyrole', compact('categories','schoolshop','headmaster','mgbstate','mgbdistrict','kobesp','p_mondedex','totala','totalearnings','totalqty','totalcost'));
		}
		
		public function AllUsers(){
		    
		     if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('users', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		    
			$users = User::get();
		
        return view(
            "backend.users.index",
            compact('users')
        );
		}
		
		
		public function delUser($userid){
			
			User::where('id', $userid)->delete();
			UserDetail::where('user_id',$userid)->delete();
			return back()->with('smsg', 'User has been deleted');
		}
		
		public function OrderDelete($orderid){
			Order::where('id', $orderid)->delete();
			OrderDetail::where('order_id', $orderid)->delete();
			return back()->with('successmsg', 'Order has been deleted');
		}
		
		
		 public function OrdersofSingle($userid){
		if(!empty($userid)){
	 $orders = Order::where('user_id', $userid)->get();
		} else {
			$orders = '';
		}
	 return view('backend.orders.single', compact('orders'));
 }
 
 
}
