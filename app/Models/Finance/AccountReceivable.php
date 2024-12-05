<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
	const TYPE_DP = 0;
	const TYPE_BILL = 1;

	protected $fillable = ['sales_id', 'amount', 'balance', 'note', 'type'];

	public function sales()
	{
		return $this->belongsTo('App\Models\Sales\Sales', 'sales_id');
	}

	public function sales_transactions()
	{
		return $this->hasMany('App\Models\Sales\SalesTransaction', 'account_receivable_id');
	}

}
