<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use App\Models\Chat\ChatType;
use App\Models\Chat\ChatHeader;
use App\Models\Chat\ChatMessage;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id)
    {
      $where = '1=1';
      $where .= " and chat_headers.customer_id = $customer_id";

      try {
        $chats = ChatHeader::join('chat_messages', 'chat_messages.chat_header_id', 'chat_headers.id')
        ->orderBy('chat_headers.created_at', 'DESC')
        ->select('chat_headers.*', DB::raw('count(chat_headers.id) as msgs'))
        ->with(['chat_type:id,name,code'])
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

    public function type()
    {
      try {
        return response()->json(ChatType::all(), 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($customer_id, Request $request)
    {
      $validator = Validator::make($request->all(), [
          'refference_id' => ['required'],
      ]);

      if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()], 400);
      }

      $chatHeader = ChatHeader::where([
        'chat_type_id' => 1, //product
        'customer_id' => $customer_id,
        'refference_id'   => $request["refference_id"] //item_id
      ])->first();

      if (empty($chatHeader)) {
        $chatHeader = ChatHeader::create([
          'chat_type_id' => 1, //product
          'customer_id' => $customer_id,
          'refference_id'   => $request["refference_id"] //item_id
        ]);
      }

      return response()->json($chatHeader, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function show($customer_id, $header_id)
     {

     }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
