<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attribute;
use App\Models\Category;

class Product extends Model
{
    use HasFactory;
	
	public function getCat(){
		return $this->hasOne(Category::class, 'id','category_id');
	}
	
	public function getAttr(){
		return $this->hasMany(Attribute::class, 'product_id', 'id');
	}
	
	public function schoold(){
		return $this->hasOne(School::class, 'id', 'school_id');
	}
	
	public function classd(){
		return $this->hasOne(SchoolClass::class, 'id', 'class_id');
	}
	
	public static function getminmaxp($id){
		$minp = Attribute::where('product_id',$id)->selectRaw(' min(attr_price) as minimum')->first();
		
		$maxp = Attribute::where('product_id',$id)->selectRaw('max(attr_price) as maximum')->first();
		if($minp->minimum == $maxp->maximum){
			return 'RM'.number_format($minp->minimum,2);
		}
		return 'RM'.number_format($minp->minimum,2).' - RM'.number_format($maxp->maximum,2);
	} 

	public static function getAttrByProdId($id){
			return $attributes = Attribute::where('product_id', $id)->select('attr_name')->groupBy('attr_name')->get();
		}
		
		public function getprodattr(){
			return $this->hasOne(Attribute::class, 'product_id', 'product_id');
		}
	
}
