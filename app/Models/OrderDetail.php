<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{

	protected $fillable= [
		'id','order_id','product_id','product_name','qty','price','total','attachment'
	];
	
	public function orderPic(){
		return $this->hasOne(Product::class, 'id', 'product_id');
	}
	
	public function prodd(){
		return $this->hasOne(Product::class, 'id', 'product_id');
	}
	
	
	
	
}
