<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function meetings()
    {
        //melakukan relasi many to many antar model melalui perantara pivot table (terhubung ke model meeting))
        return $this->belongsToMany(Meeting::class);
        // return $this->belongsToMany(Meeting::class);
    }
}
