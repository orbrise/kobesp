<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
  public function getCities()
    {
    	return $this->hasMany(City::class, 'state_id', 'id');
    }

   public static function getState($id){
		$gets = State::find($id);
		return $gets->name;
	}
	
}
