<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    public function getClasses()
    {
    	return $this->hasMany(SchoolClass::class, 'school_id', 'id');
    }

    public function getSchoolInfo()
    {
    	return $this->hasOne(School::class, 'id', 'id');
    }
}
