<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $fillable = [
		'id','name','charges','created_at','updated_at',
	];
	
	public static function getCity($id){
		$getc = City::find($id);
		return $getc->name;
	}
	
}
