<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable =   ['title', 'description', 'time'];

    public function users()
    {
        //membuat relasi ke model User
        return $this->belongsToMany(User::class);
    }
}
