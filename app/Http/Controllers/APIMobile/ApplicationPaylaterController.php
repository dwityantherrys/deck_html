<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\Profile\ApplicationPaylater;
use App\Models\Finance\AccountReceivable;

class ApplicationPaylaterController extends Controller
{
    // check paylater limit balance
    private function _checkLimitUsed ($profile)
    {
        $accountPayables = AccountReceivable::join('sales', 'sales.id', '=', 'account_receivables.sales_id')
                                ->join('shipping_instructions', 'shipping_instructions.sales_id', '=', 'sales.id')
                                ->join('delivery_notes', 'delivery_notes.shipping_instruction_id', '=', 'shipping_instructions.id')
                                ->join('sales_invoices', 'sales_invoices.delivery_note_id', '=', 'delivery_notes.id')
                                ->where('sales_invoices.payment_method_id', 4) 
                                ->where('sales.customer_id', $profile->user_id)
                                ->where('balance', '>', 0)
                                ->select('account_receivables.*')
                                ->distinct()
                                ->get();

        if(empty($accountPayables)) return 0;

        return $accountPayables->sum('balance');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activeUser = $request->user;
        $profile = $activeUser->profile;

        // tidak ditemukan
        if(empty($profile->application_paylater)) return response()->json([
            'profile_id' => null,
            'limit' => 0,
            'limit_used' => 0,
            'status_in_text' => 'belum mendaftar',
            'status' => ApplicationPaylater::APPLICATION_EMPTY,
            'date_application' => date('Y-m-d H:i:s'),
            'date_validation' => date('Y-m-d H:i:s')
        ], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);

        $profile->application_paylater->limit = $profile->transaction_setting->limit;
        $profile->application_paylater->limit_used = $this->_checkLimitUsed($profile);

        return response()->json($profile->application_paylater, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requiredData = ['name', 'email', 'phone', 'image', 'identity_number', 'identity_image'];
        $errorMessages = [];

        $activeUser = $request->user;
        $profile = $activeUser->profile;
        $billingAddress = $profile->billing_address;

        $userData = array_merge($activeUser->toArray(), $profile->toArray());

        foreach ($requiredData as $fieldName) {
            if(empty($userData[$fieldName])) $errorMessages[$fieldName] = $fieldName . ' tidak boleh kosong';
        }

        if(empty($billingAddress)) $errorMessages['billing_address'] = 'alamat penagihan belum disetting';

        if(count($errorMessages) > 0) return response()->json(['message' => $errorMessages], 400);

        ApplicationPaylater::create([
            'profile_id' => $profile->id,
            'status' => ApplicationPaylater::APPLICATION_PENDING,
            'date_application' => date('Y-m-d H:i:s')
        ]);

        return response()->json([], 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
