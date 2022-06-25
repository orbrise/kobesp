<?php

namespace App\Models;

use App\Models\Presenters\UserPresenter;
use App\Models\Traits\HasHashedMediaTrait;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Permissions;

class User extends Authenticatable implements  MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $guarded = [
        'id',
        'updated_at',
        '_token',
        '_method',
        'password_confirmation',
    ];

    protected $dates = [
        'deleted_at',
        'date_of_birth',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
   
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Userprofile');
    }
	
	public function userd(){
		return $this->hasOne(UserDetail::class, 'user_id', 'id');
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userprofile()
    {
        return $this->hasOne('App\Models\Userprofile');
    }
	
	public function getschool(){
		return $this->hasOne(School::class, 'id', 'school_id');
	}
	
    public function getorders(){
		return $this->hasMany(Order::class, 'user_id', 'id');
	}
    
    public static function getpermission($name, $userid){
        return Permission::where('name', $name)->where('user_id', $userid)->first();
    }
    // /**
    //  * Send the password reset notification.
    //  *
    //  * @param string $token
    //  *
    //  * @return void
    //  */
    // public function sendPasswordResetNotification($token)
    // {
    //     $this->notify(new ResetPasswordNotification($token));
    // }

    /**
     * Get the list of users related to the current User.
     *
     * @return [array] roels
     */
    
}
