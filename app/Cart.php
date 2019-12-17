<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function cartitems() {
      return $this->hasOne('App\Order', 'id', 'order_id');
    }
}
