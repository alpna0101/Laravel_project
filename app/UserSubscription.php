<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('title','channel_id', 'description', 'plan' , 'amount', 'unique_id', 'total_subscription', 'status');

    /**
	 * Save the unique ID 
	 *
	 *
	 */
    public function setUniqueIdAttribute($value){

		$this->attributes['unique_id'] = uniqid(str_replace(' ', '-', $value));

	}

	public function userSubscriptionPayments() {

        return $this->hasMany('App\UserSubscriptionPayment', 'user_subscription_id');

    }

    public function channelUserDetails() {

        return $this->belongsTo('App\User', 'user_id', 'id');

    }

    public function channelDetails() {

        return $this->belongsTo('App\Channel', 'channel_id');

    }


}
