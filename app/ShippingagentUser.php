<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingagentUser extends Model
{
    //
    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function shippingagent_payments()
    {
      return $this->hasMany(ShippingagentPayment::class)->orderBy('created_at', 'desc')->paginate(12);
    }
}
