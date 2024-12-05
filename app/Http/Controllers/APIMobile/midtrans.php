<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Veritrans\Veritrans;

class OrderBackController extends Controller
{
    public function __construct ()
    {
        Veritrans::$serverKey = env('MIDTRANS_SERVER_KEY', NULL);
        Veritrans::$isProduction = false;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 
    }

    public function redirectTest (Request $request)
    {
        return 'success redirect ' . $request->transaction_status;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $vt = new Veritrans;

        /* 
            customer detail ambil dari table customer relasi address, 
            item detail ambil dari table salesorder, 
            total item
            payment

            update hsalesorder
            insert shipping
            insert invoice
            insert account_receivable
        */
        $transaction_details = array(
            'order_id'          => uniqid(),
            'gross_amount'  => 300000
        );
        // Populate items
        $items = [
            array(
                'id' => 'item1',
                'price' => 100000,
                'quantity' => 1,
                'name' => 'Adidas f50'
            ),
            array(
                'id' => 'item2',
                'price' => 100000,
                'quantity' => 2,
                'name' => 'Nike N90'
            )
        ];
        // Populate customer's billing address
        $billing_address = array(
            'first_name' => "Andri",
            'last_name' => "Setiawan",
            'address' => "Karet Belakang 15A, Setiabudi.",
            'city' => "Jakarta",
            'postal_code' => "51161",
            'phone' => "081322311801",
            'country_code' => 'IDN'
        );
        // Populate customer's shipping address
        $shipping_address = array(
            'first_name' => "John",
            'last_name'     => "Watson",
            'address'       => "Bakerstreet 221B.",
            'city'              => "Jakarta",
            'postal_code' => "51162",
            'phone'             => "081322311801",
            'country_code'=> 'IDN'
            );
        // Populate customer's Info
        $customer_details = array(
            'first_name'            => "Andri",
            'last_name'             => "Setiawan",
            'email'                     => "andrisetiawan@asdasd.com",
            'phone'                     => "081322311801",
            'billing_address' => $billing_address,
            'shipping_address'=> $shipping_address
            );
        // Data yang akan dikirim untuk request redirect_url.
        // Uncomment 'credit_card_3d_secure' => true jika transaksi ingin diproses dengan 3DSecure.
        // $transaction_data = array(
        //     'payment_type'          => 'vtweb', 
        //     'vtweb'                         => array(
        //         //'enabled_payments'    => [],
        //         'credit_card_3d_secure' => true
        //     ),
        //     'transaction_details'=> $transaction_details,
        //     'item_details'           => $items,
        //     'customer_details'   => $customer_details
        // );
        
        $transaction_data = array(
            "payment_type" => "bank_transfer",
            "bank_transfer" => [
                "bank" => "permata",
                "permata" => [
                    "recipient_name" => "SUDARSONO"
                ]
            ],
            'item_details' => $items,
            "transaction_details" => $transaction_details,
            'customer_details'   => $customer_details
        );

        try
        {
            $vtweb_url = $vt->vtweb_charge($transaction_data);
            // return redirect($vtweb_url);
            return 'success' . $vtweb_url;
        } 
        catch (Exception $e) 
        {   
            return $e->getMessage();
        }
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
