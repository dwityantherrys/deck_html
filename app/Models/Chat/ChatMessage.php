<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
  const SEND_BY_EMPLOYEE = 0;
  const SEND_BY_CUSTOMER = 1;

  protected $fillable = [
    'chat_header_id',
    'sender',
    'sender_id', 
    'message',
    'file',
    'is_read',
    'read_at'
  ];

  public function chat_header ()
  {
    return $this->belongsTo('App\Models\Chat\ChatHeader', 'chat_header_id');
  }
  public function employee ()
  {
    return $this->belongsTo('App\User', 'sender_id');
  }
}
