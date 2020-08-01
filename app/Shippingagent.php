<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shippingagent extends Model
{
  public function user(){
  	return $this->belongsTo(User::class);
  }

  public function payments(){
  	return $this->hasMany(Payment::class);
  }
}
