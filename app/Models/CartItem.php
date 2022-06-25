<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
	protected $fillable = [
		'id', 'user_id', 'product_id','product_name','price', 'qty', 'total','status','session_id',
	];
	
	public function getPic(){
		return $this->hasOne(Product::class, 'id', 'product_id');
	}
    
}
