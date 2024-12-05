<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class ItemReview extends Model
{
    protected $fillable = [ 'comment', 'rate', 'item_id', 'sales_detail_id' ];

    public function item ()
    {
      return $this->belongsTo('App\Models\Master\Item\Item', 'item_id');
    }
    public function sales_detail ()
    {
      return $this->belongsTo('App\Models\Sales\SalesDetail', 'sales_detail_id');
    }
}
