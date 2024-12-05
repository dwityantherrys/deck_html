<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodReceiptConsume extends Model
{
    // use SoftDeletes;
    protected $fillable = [
      'good_receipt_detail_id',
      'good_issued_detail_id',
      'quantity',
      'created_at',
      'updated_at'
    ];

    public function good_receipt_detail ()
    {
      return $this->belongsTo('App\Models\Production\GoodReceiptDetail', 'good_receipt_detail_id');
    }
    public function good_issued_detail ()
    {
      return $this->belongsTo('App\Models\Production\GoodIssuedDetail', 'good_issued_detail_id');
    }
}
