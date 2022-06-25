<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','attr_name', 'attr_price','created_at', 'updated_at','attr_value'];
	
	
	
	public static function getattrs($attrname, $prodid){
		return  Attribute::where(['attr_name' => $attrname, 'product_id' => $prodid])->get();
	}
}
