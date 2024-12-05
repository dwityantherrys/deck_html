<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;

use App\Models\Customer;
use App\Models\Chat\ChatMessage;
use App\Notifications\NewMessage;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customerId, $headerId)
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $customerId, $headerId)
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

      DB::beginTransaction();

      $chatMessage = ChatMessage::create([
        'chat_header_id' => $headerId,
        'sender' => ChatMessage::SEND_BY_CUSTOMER,
        'sender_id' => null,
        'message'   => $request->message
      ]);

      //ditargetkan ke admin
      if($customerId !== 'all') {
        // $roleAsSuperAdmin = App\Models\Role::where('name', 'super_admin')->first();
        // $chatReceivers = User::whereIn('role_id', [$roleAsSuperAdmin->id])->pluck('notification_channel_id');
        event(new \App\Events\NewChatCustomer());
      } else { //ditargetkan ke customer
        $chatHeader = ChatHeader::find($headerId);
        $this->_sendOnesignalNotification([$chatHeader->notification_channel_id], $headerId, $chatMessage->message);
      }

      DB::commit();
      return response()->json($chatMessage, 200);
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

    function _sendOnesignalNotification($player_ids, $id, $message){
      $content = array(
        "en" => strlen($message) > 12 ? substr($message, 0, 12)."..." : $message
        );

      $fields = array(
        'app_id' => env("ONESIGNAL_APP_ID"),
        'include_player_ids' => $player_ids,
        'data' => array("chat_header_id" => $id),
        'contents' => $content
      );

      $fields = json_encode($fields);
        print("\nJSON sent:\n");
        print($fields);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

      $response = curl_exec($ch);
      curl_close($ch);

      // return $response;
    }
}
