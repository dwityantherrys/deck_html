<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Sales\Sales;
use App\Models\Mobile\Voucher\Voucher;
use App\Models\Mobile\Voucher\VoucherUsage;

class VoucherUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        $currentDate = date('Y-m-d');
        $activeUser = $request->user;
        $voucher = Voucher::where('code', $request->voucher_code)->first();
        $sales = Sales::find($request->order_id);

        $limitUsage = !empty($voucher->limit_usage) ? $voucher->limit_usage : 0;
        $limitCustomer = !empty($voucher->limit_customer) ? $voucher->limit_customer : 0;

        if(empty($voucher)) return response()->json(['message' => 'voucher tidak ditemukan'], 500);

        if(!$voucher->is_active) return response()->json(['message' => 'voucher tidak tersedia'], 500);
        
        if($currentDate < $voucher->start_date) return response()->json(['message' => 'voucher tersedia tanggal ' . $voucher->start_date->format('d/m/Y') ], 500);
        
        if(($voucher->minimum_sales != 0) && ($sales->total_price < $voucher->minimum_sales)) 
            return response()->json(['message' => 'voucher berlaku untuk minimum pembelian ' . \Rupiah::format($voucher->minimum_sales) ], 500);

        if(!empty($voucher->expiration_date) && ($currentDate > $voucher->expiration_date)) {
            return response()->json(['message' => 'voucher telah expired'], 500);
        }
        
        switch ($voucher->limit_type) {
            case Voucher::LIMIT_TYPE_ONCE:
                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'user_id' => $activeUser->id
                ])->count() > $limitUsage) {
                    return response()->json(['message' => 'voucher hanya berlaku 1x'], 500);
                }

                if($voucher->voucher_usages()->where('status_usage', VoucherUsage::STATUS_USED)->count() > $limitCustomer)
                    return response()->json(['message' => 'voucher hanya berlaku 1x, telah di gunakan pengguna lain'], 500);

                break;
            case Voucher::LIMIT_TYPE_DAILY:
                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'user_id' => $activeUser->id,
                    'updated_at' => CURDATE()
                ])->count() > $limitUsage) {
                    return response()->json(['message' => 'voucher hanya berlaku ' . $limitUsage . 'x dalam sehari.'], 500);
                }

                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'updated_at' => CURDATE()
                ])->count() > $limitCustomer) {
                    return response()->json(['message' => 'voucher hanya berlaku untuk ' . $limitCustomer . ' customer dalam sehari.'], 500);
                }

                break;
            case Voucher::LIMIT_TYPE_WEEKLY:
                $firstDayInWeek = date("Y-m-d 00:00:00", strtotime('sunday last week'));  
                $lastDayInWeek = date("Y-m-d 23:59:59", strtotime('sunday this week'));

                $hasUsingVoucher = $voucher->voucher_usages()->where([
                        'status_usage' => VoucherUsage::STATUS_USED,
                        'user_id' => $activeUser->id
                    ])
                    ->where('updated_at', '>=', $firstDayInWeek)
                    ->where('updated_at', '<=', $lastDayInWeek)
                    ->count();

                if($hasUsingVoucher > $limitUsage) {
                    return response()->json(['message' => 'voucher hanya berlaku ' . $limitUsage . 'x dalam seminggu.'], 500);
                }

                break;
            case Voucher::LIMIT_TYPE_MONTHLY:
                $firstDayInMonth = new DateTime('first day of this month');
                $lastDayInMonth = new DateTime('last day of this month');

                $hasUsingVoucher = $voucher->voucher_usages()->where([
                        'status_usage' => VoucherUsage::STATUS_USED,
                        'user_id' => $activeUser->id
                    ])
                    ->where('updated_at', '>=', $firstDayInMonth)
                    ->where('updated_at', '<=', $lastDayInMonth)
                    ->count();

                if($hasUsingVoucher > $limitUsage) {
                    return response()->json(['message' => 'voucher hanya berlaku ' . $limitUsage . 'x dalam sebulan.'], 500);
                }
                break;
        }

        // \DB::beginTransaction();

        try {
            // $voucherUsage = VoucherUsage::where('voucher_id', $voucher->id)
            //                     ->where('user_id', $activeUser->id)
            //                     ->where('status_usage', VoucherUsage::STATUS_AVAILABLE)
            //                     ->first();

            // if(empty($voucherUsage)) $voucherUsage = new VoucherUsage();                    

            // $voucherUsage->voucher_id = $voucher->id;
            // $voucherUsage->user_id = $activeUser->id;
            // $voucherUsage->sales_id = $sales->id;
            // $voucherUsage->status_usage = VoucherUsage::STATUS_AVAILABLE;
            // $voucherUsage->save();

            $discount = $voucher->value;
            $totalBill = $sales->total_price;
            $totalBillDiscounted = $totalBill - ($totalBill*($discount/100));

            // $response['message'] = 'Voucher ' . $voucher->code . ' masih tersedia, segera lakukan pembayaran.';
            $response = [
                'voucher_id' => $voucher->id,
                'voucher_code' => $voucher->code,
                'discount' => (int) $voucher->value . '%',
                'order_id' => $sales->id,
                'total_bill' => $totalBill,
                'total_bill_discounted' => $totalBillDiscounted
            ];

            // \DB::commit();

            return response()->json($response, 200);
        } catch (Exception $e) {
            // \DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
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
