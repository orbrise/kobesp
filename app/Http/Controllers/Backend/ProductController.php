<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Attribute;
use Auth;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Category;


class ProductController extends Controller
{
    public function products()
	{
		$prods = Product::where('user_id', Auth::user()->id)->get();
		return view('backend.products.products', compact('prods'));
	}

    public function addNewProuct($sid = '', $cid = '')
    {
        if(empty($sid) and empty($cid)){  return back();}
         $school = School::find($sid);
        $class = SchoolClass::find($cid);
        $categories  = Category::get();
        $products =  Product::where(['school_id'=>$sid, 'class_id'=>$cid])->get();
    	return view('backend.products.add', compact('school', 'class', 'products', 'categories'));
    }

    public function addNewProuctPost(Request $req)
    {    	
    	$title = $req->title;
    	$slug = str_replace(array('/',' ','!','@','#','$','%','^','&','*','(',')'), '-', $title);
    	$desc = $req->desc;
    	$price = $req->price;
    	
    	$newfile = '';
    	if(!empty($req->productimg)){
    	$target_dir = "public/uploads/";
		$target_file = $target_dir . basename($_FILES["productimg"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$newfile = $target_dir.date("YmdHis").'1.'.$imageFileType;
		if (move_uploaded_file($_FILES["productimg"]["tmp_name"], $newfile)) {
  		} else {echo 'error';  return false;}
    	}

    	$add = new Product;
    	$add->user_id = Auth::user()->id;
    	$add->title = $title;
    	$add->slug = $slug;
    	$add->description = $desc;
    	$add->price = $price;
    	$add->picture = $newfile;
		$add->attributes = $req->totalattr;
        $add->school_id = $req->schoolid;
        $add->class_id = $req->classid;
        $add->category_id = $req->category;
        $add->product_cost = $req->pcost;
        $add->stockinput = $req->stock;
        $add->minqty = $req->minqty;
		$add->minorderqty = $req->minorderqty;
		
		if($req->has('public_view')){
			$add->public_view = 1;
		} else {$add->public_view = 0;}
		
		if($req->has('parent_view')){
			$add->parent_view = 1;
		} else {$add->parent_view = 0;}

    	if($add->save()){

    	if($req->totalattr > 0){
    		for($n=1; $n<=$req->totalattr; $n++){

    		$attr_name = $req['attrname'.$n];
    		$attr_price = $req['attrprice'.$n];
    		$attr_value = $req['attrvalue'.$n];

    		$data[] = [
    			'product_id' => $add->id,
    			'attr_name' => $attr_name,
    			'attr_price' => $attr_price,
    			'attr_value' => $attr_value,
    		];

    		

    		}
    		
    		Attribute::insert($data);
    	}
    	return back()->with('successmsg', 'Your product has been adedd successfully');

    	
    } else {
    	return back()->with('errormsg', 'Error! Please try again');
    }

    }

    public function editProuct($id='', $sid, $cid )
    {
    	$prod = Product::find($id);
         $school = School::find($sid);
        $class = SchoolClass::find($cid);
    	$attrs  = Attribute::where('product_id', $id)->get();
        $categories  = Category::get();
    	return view('backend.products.edit', compact('prod', 'attrs', 'school', 'class', 'categories'));
    }

    public function attrDelete(Request $req)
    {	
    	Attribute::find($req->delid)->delete();
    }

    public function editProuctPost(Request $req)
    {

    	$title = $req->title;
		$slug = str_replace(array('/',' ','!','@','#','$','%','^','&','*','(',')'), '-', $title);
    	$desc = $req->desc;
    	$price = $req->price;
    	$pcost = $req->product_cost;

    	$add = Product::find($req->prodid);
    	$add->title = $title;
    	$add->slug = $slug;
    	$add->description = $desc;
    	$add->price = $price;
		$add->product_cost = $req->pcost;
		$add->stockinput = $req->stock;
		$add->minqty = $req->minqty;
		$add->minorderqty = $req->minorderqty;
		
		if($req->has('public_view')){
			$add->public_view = 1;
		} else {$add->public_view = 0;}
		
		if($req->has('parent_view')){
			$add->parent_view = 1;
		} else {$add->parent_view = 0;}
		
    	if(!empty($req->productimg)){
    	if(file_exists(public_path('upload/bio.png'))){
		unlink(public_path('upload/bio.png'));
			}
    	$target_dir = "public/uploads/";
		$target_file = $target_dir . basename($_FILES["productimg"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$newfile = $target_dir.date("YmdHis").'1.'.$imageFileType;
		if (move_uploaded_file($_FILES["productimg"]["tmp_name"], $newfile)) {
  		} else {echo 'error';  return false;}
  		$add->picture = $newfile;
    	}
		$add->attributes = $req->totalattr;
        $add->category_id = $req->category;
    	if($add->save()){

    	if($req->totalattr > 0){

    		Attribute::where('product_id', $req->prodid)->delete();

    		for($n=1; $n<=$req->totalattr; $n++){

    		$attr_name = $req['attrname'.$n];
    		$attr_price = $req['attrprice'.$n];
    		$attr_value = $req['attrvalue'.$n];

    		$data[] = [
    			'product_id' => $add->id,
    			'attr_name' => $attr_name,
    			'attr_price' => $attr_price,
    			'attr_value' => $attr_value,
    		];

    		

    		}
    		
    		Attribute::insert($data);
    	}
    	return back()->with('successmsg', 'Your product has been updated successfully');

    	
    } else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function deleteProduct($id='')
    {
    	if(Product::where('id', $id)->delete()){
    		Attribute::where('product_id', $id)->delete();
    		return back()->with('successmsg', 'Your product has been deleted successfully');
    	}else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }
}
