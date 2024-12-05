<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\User;
use App\Mail\CustomerForgetPassword;

class ForgetPasswordController extends Controller
{
  public function index($verification_code = null)
  {
    $customer = New User();
    if (!empty($verification_code)) {
      
      $customer = User::where('token_email_verification', $verification_code)
      // ->whereNotNull('email_verified_at')
      ->first();
      
      if (!empty($customer)) {
        return view('auth.forgot-password')->with([ 'customer' => $customer ]);
      }

      abort(404);
    }

    return view('auth.forgot-password')->with([ 'customer' => $customer ]);
  }

  public function forgotPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'email' => ['required', 'string', 'email']
    ]);

    if ($validator->fails()) {
      $errorMessages = [];
      foreach ($validator->errors()->get('*') as $key => $value) {
        $errorMessages[$key] = implode(', ', $value);
      }
      return response()->json(['message' => $errorMessages], 400);
    }

    try {
      $customer = User::where('email', $request['email'])->first();

      $customer->token_email_verification = Str::random(12);
      $customer->save();

      $customer->email = $customer->email;

      Mail::to($customer)->send(new CustomerForgetPassword($customer));

      return response()->json([], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'email tidak ditemukan'], 500);
    }

  }

  public function ProcessForgotPassword(Request $request)
  {
    if(empty($request['id'])) return redirect('customer/forget-password');
    
    $validate = $request->validate([
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $customer = User::find($request['id']);
    $customer->password = Hash::make($request['password']);
    $customer->token_email_verification = null;
    $customer->save();

    $request->session()->flash('notif', [
      'type' => 'success',
      'message' => 'berhasil ubah password, silahkan login pada aplikasi!'
    ]);

    
  }
}
