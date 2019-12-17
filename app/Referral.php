<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    public function userDetails() {

        return $this->belongsTo('App\User', 'user_id');
    }
}
