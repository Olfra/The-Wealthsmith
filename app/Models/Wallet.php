<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Wallet extends Model 
{
    
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
  
    protected $fillable = [
        'risk_level', 'start_amount', 'frequency', 'timeframe'
    ];
  

  public function user()
  {
      return $this->belongsTo(User::class);
  }
}