<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class ItemNew extends Model
{
    protected $table = 'items';
    protected $fillable = [
      'name',
      'price',
      'item_category_id',
      'is_active'
    ];
    protected $appends = ['length_options', 'review_rate', 'review_total'];
    /** append attribut */
    public function getLengthOptionsAttribute ()
    {
      $optFromLengthField = [];
      $optFromLengthField[] = [
        'id' => 99,
        'name' => 'standart',
        'length' => $this->attributes['length'],
        'is_active' => 1
      ];

      $options = ($this->attributes['has_length_options'] == 0) ? $optFromLengthField : ItemLength::active()->get();
      return $options;
    }
    public function getReviewRateAttribute ()
    {
      return round($this->item_reviews()->avg('rate'), 1);
    }
    public function getReviewTotalAttribute ()
    {
      return $this->item_reviews()->count();
    }

    /** scoped */
    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }

    /** relations */
    public function images ()
    {
      return $this->hasMany('App\Models\Master\Item\ItemImage', 'item_id');
    }
    public function item_category ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemCategory', 'item_category_id');
    }
    public function item_reviews ()
    {
      return $this->hasMany('App\Models\Master\Item\ItemReview', 'item_id')->orderBy('created_at', 'DESC');
    }
    public function item_materials ()
    {
      return $this->hasMany('App\Models\Master\Item\ItemMaterial', 'item_id');
    }
}
