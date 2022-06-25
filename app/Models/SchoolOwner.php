<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolOwner extends Model
{
	protected $fillable= ['id', 'school_id', 'owner', 'created_at', 'updated_at'];
	
	public function userd(){
		return $this->hasOne(User::class, 'id', 'owner');
	}
	
	public function schoold(){
		return $this->hasOne(School::class, 'id', 'school_id');
	}
	
	
	
}
