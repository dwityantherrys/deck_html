<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
	const TYPE_DP = 0;
	const TYPE_BILL = 1;
	
	protected $fillable = ['purchase_id', 'amount', 'balance', 'note', 'type'];
	
	public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_id');
    }
}
