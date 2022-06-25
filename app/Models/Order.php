<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

	public function orderDetails(){
		return $this->hasMany(OrderDetail::class, 'order_id','id');
	}
	
	public function userd(){
		return $this->hasOne(User::class,'id', 'user_id');
	}
	
	public function userdd(){
		return $this->hasOne(UserDetail::class,'user_id', 'user_id');
	}
	
	public function getstatname(){
		return $this->hasOne(State::class, 'id', 'shipping_state');
	}
	public function getcityname(){
		return $this->hasOne(City::class, 'id', 'shipping_city');
	}
	
}
