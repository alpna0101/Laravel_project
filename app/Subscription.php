<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	 /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('title', 'description', 'plan' , 'amount', 'picture', 'unique_id', 'total_subscription', 'status','gift_token');

    /**
	 * Save the unique ID 
	 *
	 *
	 */
    public function setUniqueIdAttribute($value){

		$this->attributes['unique_id'] = uniqid(str_replace(' ', '-', $value));

	}

	public function getUserPayments() {

        return $this->hasMany('App\UserPayment', 'subscription_id', 'id');

    }

}
