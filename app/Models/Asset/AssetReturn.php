<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetReturn extends Model
{
  protected $table = 'asset_loans_return';
    use SoftDeletes;

    protected $fillable = [
        'loan_id',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function loan ()
    {
      return $this->belongsTo('App\Models\Asset\AssetLoan', 'loans_id');
    }
}
