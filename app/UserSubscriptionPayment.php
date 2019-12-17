<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSubscriptionPayment extends Model
{
    public function UserSubscriptionDetails() {

        return $this->belongsTo('App\UserSubscription', 'user_subscription_id');

    }

    public function UserDetails() {

        return $this->belongsTo('App\User', 'user_id');

    }

    public function channelDetails() {

        return $this->belongsTo('App\Channel', 'channel_id');

    }
}
