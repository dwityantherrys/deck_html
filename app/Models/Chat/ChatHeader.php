<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatHeader extends Model
{
  protected $fillable = [
    'chat_type_id',
    'customer_id',
    'employee_id',
    'refference_id'
  ];

  /**
 * add custom attribute.
 *
 */
  protected $appends = ['all_messages', 'unread_messages'];

  public function getAllMessagesAttribute ()
  {
    return $this->chat_messages()->count();
  }
  public function getUnreadMessagesAttribute ()
  {
    return $this->chat_messages()->where('is_read', false)->count();
  }

  /**
 * add relationships.
 *
 */
  public function chat_type ()
  {
    return $this->belongsTo('App\Models\Chat\ChatType', 'chat_type_id');
  }
  public function chat_messages ()
  {
    return $this->hasMany('App\Models\Chat\ChatMessage', 'chat_header_id');
  }
  public function customer ()
  {
    return $this->belongsTo('App\User', 'customer_id');
  }
}
