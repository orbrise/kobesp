<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Models\State;
use App\Models\SchoolOwner;
use App\Models\Product;
use Auth;

class SchoolController extends Controller
{
    public function schools()
	{
	    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('schools', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		    
		$schools = School::get();
		return view('backend.school.schools', compact('schools'));
	}

	public function addNewSchool()
    {
		$states = State::get();
		$users = User::whereIn('role', ['office','school'])->get();
    	return view('backend.school.add', compact('users', 'states'));
    }

	public function addNewSchoolPost(Request $req)
    {    	
    	$title = $req->title;
    	$slug = str_replace(' ', '-', $title);
    	$desc = $req->desc;
    	$address = $req->address;
		
		$newfile = '';
    	if(!empty($req->logo)){
    	$target_dir = "public/uploads/";
		$target_file = $target_dir . basename($_FILES["logo"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$newfile = $target_dir.date("YmdHis").'1.'.$imageFileType;
		if (move_uploaded_file($_FILES["logo"]["tmp_name"], $newfile)) {
  		} else {echo 'error';  return false;}
    	}

    	$add = new School;
    	$add->name = $title;
    	$add->slug = $slug;
    	$add->school_desc = $desc;
    	$add->address = $address;
    	$add->owner = $req->owner;
    	$add->state_id = $req->state;
    	$add->city_id = $req->city;
    	$add->logo = $newfile;

    	if($add->save()){
			$news = new SchoolOwner;
			$news->school_id = $add->id;
			$news->owner = $req->owner;
			$news->save();
    	return back()->with('successmsg', 'Your school has been adedd successfully');
    } else {
    	return back()->with('errormsg', 'Error! Please try again');
    }

    }

     public function editSchool($id='')
    {	
		$school = School::find($id);
		$states = State::get();
		$owners = SchoolOwner::where('school_id', $id)->get();
    	$cities = City::where('state_id', $school->state_id)->get();
		$users = User::whereIn('role', ['office','school'])->get();
    	
    	return view('backend.school.edit', compact('school','users','cities', 'states','owners'));
    }

     public function editSchoolPost(Request $req)
    {
    	$title = $req->title;
    	$slug = str_replace(' ', '-', $title);
    	$desc = $req->desc;
    	$address = $req->address;
		$add = School::find($req->prodid);
		
		$newfile = $add->logo;
    	if(!empty($req->logo)){
    	$target_dir = "public/uploads/";
		$target_file = $target_dir . basename($_FILES["logo"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$newfile = $target_dir.date("YmdHis").'1.'.$imageFileType;
		if (move_uploaded_file($_FILES["logo"]["tmp_name"], $newfile)) {
  		} else {echo 'error';  return false;}
    	}
    	
    	$add->name = $title;
    	$add->slug = $slug;
    	$add->school_desc = $desc;
    	$add->address = $address;
		
		if(!empty($req->owner)){
		$add->owner = $req->owner;
		}
    	$add->state_id = $req->state;
    	$add->city_id = $req->city;
		$add->logo = $newfile;
	
    if($add->save()){
		
		if(!empty($req->owner)){
			$news = new SchoolOwner;
			$news->school_id = $add->id;
			if(!empty($req->owner)){
			$news->owner = $req->owner;
			}
			
			$news->save();
		}
			
    return back()->with('successmsg', 'Your school has been updated successfully');
	} else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function deleteSchool($id='')
    {
		SchoolClass::where('school_id',$id)->delete();
		SchoolOwner::where('school_id', $id)->delete();
		Product::where('school_id', $id)->delete();
    	if(School::where('id', $id)->delete()){
    		return back()->with('successmsg', 'Your school has been deleted successfully');
    	}else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	 public function deleteOwner($id='')
    {
    	if(SchoolOwner::where('id', $id)->delete()){
    		return back()->with('successmsg', 'Your school Owner has been deleted successfully');
    	}else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	public function states()
	{
	    if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('states', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		    
		$states = State::get();
		return view('backend.cities.states', compact('states'));
	}
	
	public function addNewState()
    {
		
    	return view('backend.cities.add');
    }
	
	public function addNewStatePost(Request $req)
    {    	
    	$title = $req->title;
    	

    	$add = new State;
    	$add->name = $title;

    	if($add->save()){
    	return back()->with('successmsg', 'Your state has been adedd successfully');
    } else {
    	return back()->with('errormsg', 'Error! Please try again');
    }

    }
	
	 public function editState($id='')
    {
    	$state = State::find($id);
    	return view('backend.cities.edit', compact('state'));
    }
	
	 public function editStatePost(Request $req)
    {
    	$title = $req->title;
    	
    	$add = State::find($req->prodid);
    	$add->name = $title;

    if($add->save()){
    return back()->with('successmsg', 'Your state has been updated successfully');
	} else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	
	public function deleteState($id='')
    {
    	if(State::where('id', $id)->delete()){
    		return back()->with('successmsg', 'Your state has been deleted successfully');
    	}else {
    	return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	public function addNewStateCity($id='')
    {
        if(empty($id)){
            return back();
        }

        $state = State::find($id);
        return view('backend.cities.addcity', compact('state'));

    }
	
	  public function addNewCityPost(Request $req)
    {
        $add = new City;
        
        $add->name = $req->classname;
        $add->charges = $req->charges;
		$add->state_id= $req->schoolid;

    if($add->save()){
    return back()->with('successmsg', 'City has been updated successfully');
    } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	public function EditCity($sid = '', $cid = '')
    {
        $state = State::find($sid);
        $city = City::find($cid);
        return view('backend.cities.editcity', compact('state', 'city'));
    }
	
	
	public function editCityPost(Request $req)
    {
        if(City::where('id', $req->classid)->update(['name' => $req->classname, 'charges' => $req->charges])){
            return back()->with('successmsg', 'City has been updated successfully');
        } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	public function deleteCity($id='')
    {
        if(City::where('id', $id)->delete()){
            return back()->with('successmsg', ' City has been deleted successfully');
        }else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }
	
	
	

    public function addNewSchoolClass($id='')
    {
        if(empty($id)){
            return back();
        }

        $school = School::find($id);
        return view('backend.school.addclass', compact('school'));

    }

    public function addNewClassPost(Request $req)
    {
        $slug = str_replace(' ', '-', $req->classname);
        $add = new SchoolClass;
        $add->school_id = $req->schoolid;
        $add->name = $req->classname;
        $add->slug = $slug;
        $add->class_desc = $req->desc;
		if($req->hasFile('booklist')){
		$fileName = time().'.'.$req->booklist->extension();  
		$req->booklist->move(public_path('booklist'), $fileName);
		$add->booklist = $fileName;
		 }

    if($add->save()){
    return back()->with('successmsg', 'Class has been updated successfully');
    } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function EditClass($sid = '', $cid = '')
    {
        $school = School::find($sid);
        $class = SchoolClass::find($cid);
        return view('backend.school.editclass', compact('school', 'class'));
    }

    public function editClassPost(Request $req)
    {
		$c = SchoolClass::find($req->classid)->first();
		$oldimage = $c->booklist;
		
		if($req->hasFile('booklist')){
			unlink(public_path('booklist/'.$oldimage));
		$fileName = time().'.'.$req->booklist->extension();  
		$req->booklist->move(public_path('booklist'), $fileName);
		$oldimage = $fileName;
		}
		 
        if(SchoolClass::where('id', $req->classid)->update(['name' => $req->classname, 'class_desc' => $req->desc, 'booklist' => $oldimage])){
            return back()->with('successmsg', 'Class has been updated successfully');
        } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function deleteClass($id='')
    {
        if(SchoolClass::where('id', $id)->delete()){
            return back()->with('successmsg', 'Your Class has been deleted successfully');
        }else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function Categories()
    {
        if(Auth::user()->id != 1){
		    $check = \App\Models\User::getpermission('cats', Auth::user()->id);
		    if(empty($check)){
		        return view('errors.block');
		    }
		    }
		    
        $categories = Category::where('parent_id', '0')->get();
        return view('backend.categories.categories', compact('categories'));
    }

    public function addNewCategoryPost(Request $req)
    {
        $slug = str_replace(' ', '-', $req->category);
        $add = new Category;
        if(isset($req->pcat) and !empty($req->pcat)){
            $add->parent_id = $req->pcat;
        } else {
        $add->parent_id = 0;
        }
        $add->name = $req->category;
        $add->slug = $slug;
		
		if($req->has('public_view')){
			$add->public_view = 1;
		} else {$add->public_view = 0;}
		
		if($req->has('parent_view')){
			$add->parent_view = 1;
		} else {$add->parent_view = 0;}
        

    if($add->save()){
    return back()->with('successmsg', 'Category has been Added successfully');
    } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
    }

    public function editCategory($id , $pid= '')
    {
        if(empty($id)){
            return back();
        }

        $category = Category::find($id);
        if(empty($pid)){$pcat = '';}else {
        $pcat = Category::where('id', $pid)->first();
        }
        return view('backend.categories.edit', compact('category', 'id', 'pcat', 'pid'));

    }

        public function editCatPost(Request $req)
        {
			if($req->has('public_view')){
			$public_view = 1;
		} else {$public_view = 0;}
		
		if($req->has('parent_view')){
			$parent_view = 1;
		} else {$parent_view = 0;}
		
            if(Category::where('id', $req->category_id)->update(['name' => $req->category, 'slug' => str_replace(" ", "-", $req->category), 'public_view' => $public_view, 'parent_view' => $parent_view])){
            return back()->with('successmsg', 'Category has been updated successfully');
        } else {
        return back()->with('errormsg', 'Error! Please try again');
    }
        }


        public function deleteCategory($id)
        {
            if(Category::where('id', $id)->delete()){
				Category::where('parent_id', $id)->delete();
            return back()->with('successmsg', 'Category has been deleted successfully');
        }else {
        return back()->with('errormsg', 'Error! Please try again');
    }
        }


        public function addSubCat($id)
        {
            $cat  = Category::find($id);
            $categories = Category::where("parent_id", $id)->get();
            return view('backend.categories.subcat', compact('categories', 'id', 'cat'));
        }
		
		public function Shipping(){
			$cities = City::get();
			return view('backend.school.shipping', compact('cities'));
		}
		
		public function ShippingPost(Request $req){
			$cities = City::get();
			foreach($cities as $city){
				City::where('id',$city->id)->update(['charges' => $req->city[$city->id]]);
			}
			return back()->with('successmsg', 'Shipping Table Updated');
		}
		
		
}
