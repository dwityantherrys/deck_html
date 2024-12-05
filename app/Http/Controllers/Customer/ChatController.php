<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

use App\Models\Chat\ChatType;
use App\Models\Chat\ChatHeader;
use App\Models\Chat\ChatMessage;

class ChatController extends Controller
{
    public function index () {
        return view('customer.chat');
    }

    public function ajaxGetAll(Request $request)
    {
      $where = '1=1';

      try {
        $chats = ChatHeader::join('chat_messages', 'chat_messages.chat_header_id', 'chat_headers.id')
        ->orderBy('chat_headers.created_at', 'DESC')
        ->select('chat_headers.*', DB::raw('count(chat_headers.id) as msgs'))
        ->with(['customer:id,name,email', 'chat_type:id,name,code'])
        ->whereRaw($where)
        ->groupBy(
          'chat_headers.id',
          'chat_headers.chat_type_id', 
          'chat_headers.employee_id', 
          'chat_headers.customer_id', 
          'chat_headers.refference_id', 
          'chat_headers.created_at', 
          'chat_headers.updated_at'
         )
        ->paginate(20);

        foreach ($chats as $chat) {
          $chat->last_messages = ChatMessage::where('chat_header_id', $chat->id)
                                  ->orderBy('created_at', 'DESC')->first();
        }

        return response()->json($chats, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }

    public function ajaxGetMessageByHeader($headerId)
    {
      try {
        $messages = ChatMessage::where('chat_header_id', $headerId)
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return response()->json($messages, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }

    public function ajaxSendMessageByHeader(Request $request, $headerId)
    {
      $validator = Validator::make($request->all(), [
          'message' => ['required'],
      ]);

      if ($validator->fails()) {
        $errorMessages = [];
        foreach ($validator->errors()->get('*') as $key => $value) {
          $errorMessages[$key] = implode(', ', $value);
        }
        return response()->json(['message' => $errorMessages], 400);
      }

      $chatMessage = ChatMessage::create([
        'chat_header_id' => $headerId,
        'sender' => ChatMessage::SEND_BY_EMPLOYEE,
        'sender_id' => $request->user()->id,
        'message'   => $request->message
      ]);

      // $target = Customer::find($chatMessage->chat_header->customer_id);
      // Notification::send($target, new NewMessage($chatMessage));

      return response()->json($chatMessage, 200);
    }

    public function ajaxGetType()
    {
      try {
        return response()->json(ChatType::all(), 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }
}
