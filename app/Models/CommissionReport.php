<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CommissionReport;
use App\Models\School;
use App\Models\State;
use App\Models\City;

class CommissionReport extends Model
{
   public static function getprods($email, $schoolid){
	 $prods = CommissionReport::where(['email' => $email, 'school_id' => $schoolid, 'role' => 'school'])->orderBy('created_at')->get();
	   return $prods;
   }
	
	public function getstate(){
	 return $this->hasOne(State::class,'id', 'state_id');
   }
   
   public function getcity(){
	 return $this->hasOne(City::class,'id', 'city_id');
   }
   
   public function getschool(){
	 return $this->hasOne(School::class,'id', 'school_id');
   }
   
   public static function getschools($cityid){
	   return $schools = School::where('city_id', $cityid)->get();
   }
   
   
  public static function getstatename($stateid){
	  $state = State::find($stateid);
	  if(!empty($state->name)){return $state->name;} else {
		  return 'No Name';
	  }
  }
  
  public static function getcityname($cityid){
	  $city = City::find($cityid);
	  if(!empty($city->name)){return $city->name;} else {
		  return 'No Name';
	  }
	  
  }
  
   public static function getschoolname($schoolid){
	  $school = School::find($schoolid);
	  if(!empty($school->name)){return $school->name;} else {
		  return 'No Name';
	  }
	  
  }
  

	
}
