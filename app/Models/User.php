<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'password', 'team_id', 'coins', 'coins_bonus', 'tickets', 'referal_code', 'coins_from_referals', 'confirmed_email', 'banned'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'coins', 'coins_bonus', 'tickets', 'referal_code', 'coins_from_referals', 'confirmed_email'
    ];

    protected $guarded = [
        'banned'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /* protected $casts = [
        'email_verified_at' => 'datetime',
    ]; */
}
