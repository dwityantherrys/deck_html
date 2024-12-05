<?php
namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Carbon\Carbon;
use \Midtrans\Config as MidtransConfig;
use \Midtrans\Snap as MidtransSnap;
use \Midtrans\Notification as MidtransNotification;

use App\Models\Master\Profile\ApplicationPaylater;
use App\Models\Master\Profile\ProfileAddress as CustomerAddress;
use App\Models\Sales\LogSalesTransactionNotification;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesDetail;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesTransaction;
use App\Models\Master\Payment\PaymentMethod;
use App\Models\Finance\AccountReceivable;
use App\Models\Shipping\ShippingInstruction;
use App\Models\Shipping\DeliveryNote;
use App\Models\Mobile\Voucher\VoucherUsage;
use App\Models\Mobile\Voucher\Voucher;

class OrderController extends Controller
{
    public function __construct ()
    {
        // Set midtrans configuration
        MidtransConfig::$serverKey = config('services.midtrans.serverKey');
        MidtransConfig::$isProduction = config('services.midtrans.isProduction');
        MidtransConfig::$isSanitized = config('services.midtrans.isSanitized');
        MidtransConfig::$is3ds = config('services.midtrans.is3ds');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $user = $request->user;
        $customerId = $user->id;
        $profileId = $user->profile->id;
        $profileTransactionSetting = $user->profile->transaction_setting;

        $listOrders = [];
        $discount = 0;
        $totalBill = 0;
        $totalBillDiscounted = 0;
        $listPaymentMethods = PaymentMethod::active()
                                ->select('id', 'name', 'image')
                                ->where('name', '<>', 'Cash')
                                ->get();
        $listShippingMethods = [
            ['id' => ShippingInstruction::METHOD_IS_PICKUP, 'name' => 'pickup'],
            ['id' => ShippingInstruction::METHOD_IS_PICKUP_POINT, 'name' => 'pickup pada pickup point'],
            ['id' => ShippingInstruction::METHOD_IS_DELIVERY, 'name' => 'kirim']
        ];
        $isAllowedPaylater = $profileTransactionSetting->is_allowed_paylater;
        $isAllowedInstallment = $profileTransactionSetting->is_allowed_installment;
        $minimumDownpayment = !empty($profileTransactionSetting->minimum_downpayment) ? $profileTransactionSetting->minimum_downpayment : 0;

        //cari yang belum di proses ke order
        $order = Sales::where('customer_id', $customerId)
                    ->whereNull('order_number')
                    ->where('quotation_status', Sales::QUOTATION_PENDING)
                    ->first();

        if(empty($order)) return response()->json(['message' => 'tidak ada cart yang dapat di checkout.'], 400);

        $listOrders = SalesDetail::with([
            'item_material:id,name,thick,item_id,material_id,color_id',
            'item_material.item',
            'item_material.item.images',
            'item_material.material',
            'item_material.color',
            ])
            ->where('sales_id', $order->id)
            ->select(
                'sales_details.id',
                'sales_details.item_material_id', 
                'sales_details.is_custom_length', 
                'sales_details.length', 
                'sales_details.width', 
                'sales_details.height',
                'sales_details.sheet',
                'sales_details.quantity',
                'sales_details.tax',
                'sales_details.discount',
                'sales_details.price',
                'sales_details.total_price'
            )->get();

        foreach ($listOrders as $listOrder) {
            $listOrder->charge_custom_length = $listOrder->is_custom_length  == 1 ? 10 : 0; 

            $itemImages = $listOrder->item_material->item->images;
            //set default value if image empty
            if(count($itemImages) <= 0) {
                $listOrder->item_material->item->images[] = [
                    "id" => 1,
                    "image" => "/img/no-image.png",
                    "item_id" => 1,
                    "is_thumbnail" => 1,
                    "is_active" => 1,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "image_url" => asset("/img/no-image.png")
                ];
            }
        }

        $totalBill = $listOrders->sum('total_price');
        // $totalBillDiscounted = $totalBill;
        if($minimumDownpayment > 0) $minimumDownpayment = $totalBill - ($totalBill*($minimumDownpayment/100));

        // $voucherUsage = $order->voucher_usage;
        // if(!empty($voucherUsage)) {
        //     $discount = $voucherUsage->voucher->value;
        //     $totalBillDiscounted = $totalBill - ($totalBill*($voucherUsage->voucher->value/100));
        // }  

        return response()->json([
            'order_id' => $order->id,
            'list_orders' => $listOrders,
            // 'discount' => $discount,
            'total_bill' => $totalBill,
            // 'total_bill_discounted' => $totalBillDiscounted,
            'list_shipping_method' => $listShippingMethods,
            'list_payment_method' => $listPaymentMethods,
            'is_allowed_paylater' => $isAllowedPaylater,
            'is_allowed_installment' => $isAllowedInstallment,
            'minimum_downpayment' => $minimumDownpayment
        ], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function storeCheckoutRestBill(Request $request, $customerId, $orderId)
    {
        $user = $request->user;
        $customerId = $user->id;
        $transactionStatus = SalesTransaction::TRANSACTION_PENDING;

        $paidBill = 0;
        $restBill = 0;

        //cari yang belum di proses ke order
        $order = Sales::where('id', $orderId)
                    ->where('quotation_status', Sales::QUOTATION_ACCEPT)
                    ->first();

        if(empty($order)) return response()->json(['message' => 'tidak ada order yang dapat di checkout.'], 400);

        $conditionPaymentMethod = $order->payment_method_id == 4 ? 'payment_method_id = 4' : 'payment_method_id <> 4';

        $paidAR = $order->account_receivables()
                        ->join('sales', 'sales.id', 'account_receivables.sales_id')
                        ->whereRaw($conditionPaymentMethod)
                        ->where('balance', 0)
                        ->select('account_receivables.*')
                        ->distinct()
                        ->get();

        $paidBill = $paidAR->sum('amount');

        $unpaidAR = $order->account_receivables()
                        ->join('sales', 'sales.id', 'account_receivables.sales_id')
                        ->whereRaw($conditionPaymentMethod)
                        ->where('balance', '>', 0)
                        ->select('account_receivables.*')
                        ->distinct()
                        ->first();
                        
        $restBill = $unpaidAR->balance;

        try{
            \DB::beginTransaction();

            $transaction = SalesTransaction::create([
                'code' => Str::random(12),
                'account_receivable_id' => $unpaidAR->id,
                // 'sales_invoice_id' => $invoice->id,
                'amount' => (int) $restBill,
                'status' => $transactionStatus,
                'note' => ''
            ]);
    
            // midtrans snap_token.
            $payload = [
                "credit_card" => [
                    "secure" => true,
                    "channel" => "migs",
                    "bank" => "bca",
                    "installment" => [
                        "required" => false,
                        "terms" => [
                        "bni" => [3, 6, 12],
                        "mandiri" => [3, 6, 12],
                        "cimb" => [3, 6, 12],
                        "bca" => [3, 6, 12]
                        ]
                    ]
                ],
                'transaction_details' => [
                    'order_id'      => $transaction->code,
                    'gross_amount'  => $restBill,
                ],
                'customer_details' => [
                    'first_name'    => $request->user->name,
                    'email'         => $request->user->email,
                    'phone'         => $request->user->profile->phone
                ],
                'item_details' => [
                    [
                        'id'       => $transaction->id,
                        'price'    => (int) $restBill,
                        'quantity' => 1,
                        'name'     => 'Sisa tagihan order ' . $order->order_number
                    ]
                ]
            ];
    
            $snapToken = MidtransSnap::getSnapToken($payload);
            $transaction->update([ 'snap_token' => $snapToken ]);

            \DB::commit();
    
            return response()->json([
                'order_id' => $order->id,
                'snap_token' => $snapToken
            ], 200);

        } catch (\Throwable $th) {
            \DB::rollback();
            return response()->json(['message' => $th->getMessage() . $th->getLine()], 500);
        }

    }

    public function checkoutRestBill(Request $request, $customerId, $orderId)
    {
        $user = $request->user;
        $customerId = $user->id;

        $listOrders = [];
        $paidBill = 0;
        $restBill = 0;

        //cari yang belum di proses ke order
        $order = Sales::where('id', $orderId)
                    ->where('quotation_status', Sales::QUOTATION_ACCEPT)
                    ->first();

        if(empty($order)) return response()->json(['message' => 'tidak ada order yang dapat di checkout.'], 400);

        $listOrders = SalesDetail::with([
            'item_material:id,name,thick,item_id,material_id,color_id',
            'item_material.item',
            'item_material.item.images',
            'item_material.material',
            'item_material.color',
            ])
            ->where('sales_id', $order->id)
            ->select(
                'sales_details.id',
                'sales_details.item_material_id', 
                'sales_details.is_custom_length', 
                'sales_details.length', 
                'sales_details.width', 
                'sales_details.height',
                'sales_details.sheet',
                'sales_details.quantity',
                'sales_details.tax',
                'sales_details.discount',
                'sales_details.total_price'
            )->get();

        foreach ($listOrders as $listOrder) {
            $listOrder->charge_custom_length = $listOrder->is_custom_length  == 1 ? 10 : 0; 

            $itemImages = $listOrder->item_material->item->images;
            //set default value if image empty
            if(count($itemImages) <= 0) {
                $listOrder->item_material->item->images[] = [
                    "id" => 1,
                    "image" => "/img/no-image.png",
                    "item_id" => 1,
                    "is_thumbnail" => 1,
                    "is_active" => 1,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "image_url" => asset("/img/no-image.png")
                ];
            }
        }

        $paidAR = $order->account_receivables()
                        ->where('balance', 0)
                        ->select('account_receivables.*')
                        ->distinct()
                        ->get();

        $paidBill = $paidAR->sum('amount');

        $unpaidAR = $order->account_receivables()
                        ->where('balance', '>', 0)
                        ->select('account_receivables.*')
                        ->distinct()
                        ->get();
                        
        $restBill = $unpaidAR->sum('balance');

        return response()->json([
            'order_id' => $order->id,
            'list_orders' => $listOrders,
            'paid_bill' => $paidBill,
            'rest_bill' => $restBill,
        ], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    private function _setDueDate ($orderDate, $profileTransactionSetting)
    {
        $orderDate = new Carbon($orderDate);
        if(!empty($profileTransactionSetting->tempo_charge_month)) $orderDate = $orderDate->addMonths($profileTransactionSetting->tempo_charge_month);
        if(!empty($profileTransactionSetting->tempo_charge_week)) $orderDate = $orderDate->addWeeks($profileTransactionSetting->tempo_charge_week);
        if(!empty($profileTransactionSetting->tempo_charge_day)) $orderDate = $orderDate->addDays($profileTransactionSetting->tempo_charge_day);

        $orderDate;
    }

    private function _checkCustomerHasUnpaidOrder ($customerId, $customerLimit)
    {
        $listAR = AccountReceivable::join('sales', 'sales.id', '=', 'account_receivables.sales_id')
                ->join('shipping_instructions', 'shipping_instructions.sales_id', '=', 'sales.id')
                ->join('delivery_notes', 'delivery_notes.shipping_instruction_id', '=', 'shipping_instructions.id')
                ->join('sales_invoices', 'sales_invoices.delivery_note_id', '=', 'delivery_notes.id')
                ->where('sales_invoices.payment_method_id', '<>', 4) 
                ->where('balance', '>', 0)
                ->where('sales.customer_id', $customerId)
                ->select('account_receivables.*')
                ->distinct()
                ->get();

        $unpaidAR = $listAR->sum('balance');
        if($unpaidAR > 0) return true;

        $listARPayables = AccountReceivable::join('sales', 'sales.id', '=', 'account_receivables.sales_id')
                                ->join('shipping_instructions', 'shipping_instructions.sales_id', '=', 'sales.id')
                                ->join('delivery_notes', 'delivery_notes.shipping_instruction_id', '=', 'shipping_instructions.id')
                                ->join('sales_invoices', 'sales_invoices.delivery_note_id', '=', 'delivery_notes.id')
                                ->where('sales_invoices.payment_method_id', 4) 
                                ->where('sales.customer_id', $customerId)
                                ->where('balance', '>', 0)
                                ->select('account_receivables.*')
                                ->distinct()
                                ->get();

        $unpaidARPayables = $listARPayables->sum('balance');
        $customerLimit = $customerLimit ? $customerLimit : 0;
        $customerLimitAvailable = $customerLimit-$unpaidARPayables;

        return $unpaidARPayables > $customerLimitAvailable;
    }

    private function _isVoucherAvailable($voucher, $totalBill)
    {
        $currentDate = Carbon::now();

        $activeUser = $this->activeUser;
        $limitUsage = !empty($voucher->limit_usage) ? $voucher->limit_usage : 0;
        $limitCustomer = !empty($voucher->limit_customer) ? $voucher->limit_customer : 0;

        if(!$voucher->is_active) return ['code' => 500, 'message' => 'voucher tidak tersedia'];
        
        if($currentDate < $voucher->start_date) return ['code' => 500, 'message' => 'voucher tersedia tanggal ' . $voucher->start_date->format('d/m/Y') ];
        
        if(($voucher->minimum_sales != 0) && ($totalBill < $voucher->minimum_sales)) 
            return ['code' => 500, 'message' => 'voucher berlaku untuk minimum pembelian ' . \Rupiah::format($voucher->minimum_sales) ];

        if(!empty($voucher->expiration_date) && ($currentDate > $voucher->expiration_date)) {
            return ['code' => 500, 'message' => 'voucher telah expired'];
        }
        
        switch ($voucher->limit_type) {
            case Voucher::LIMIT_TYPE_ONCE:
                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'user_id' => $activeUser->id
                ])->count() > $limitUsage) {
                    return ['code' => 500, 'message' => 'voucher hanya berlaku 1x'];
                }

                if($voucher->voucher_usages()->where('status_usage', VoucherUsage::STATUS_USED)->count() > $limitCustomer)
                    return ['code' => 500, 'message' => 'voucher hanya berlaku 1x, telah di gunakan pengguna lain'];

                break;
            case Voucher::LIMIT_TYPE_DAILY:
                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'user_id' => $activeUser->id,
                    'updated_at' => Carbon::now()
                ])->count() > $limitUsage) {
                    return ['code' => 500, 'message' => 'voucher hanya berlaku ' . $limitUsage . 'x dalam sehari.'];
                }

                if($voucher->voucher_usages()->where([
                    'status_usage' => VoucherUsage::STATUS_USED,
                    'updated_at' => Carbon::now()
                ])->count() > $limitCustomer) {
                    return ['code' => 500, 'message' => 'voucher hanya berlaku untuk ' . $limitCustomer . ' customer dalam sehari.'];
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
                    return ['code' => 500, 'message' => 'voucher hanya berlaku ' . $limitUsage . 'x dalam seminggu.'];
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
                    return ['code' => 500, 'message' => 'Voucher hanya berlaku ' . $limitUsage . 'Kali dalam sebulan.'];
                }
                break;
        }

        return ['code' => 200, 'message' => 'voucher availabe'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->activeUser = $request->user;

        $now = Carbon::now();
        $snapToken = null;
        $midtransItemDetails = [];
        $customerId = $request->user->id;
        $profileTransactionSetting = $request->user->profile->transaction_setting;

        \DB::beginTransaction();
        try {
            $orderStatus = Sales::ORDER_PENDING;
            $invoiceStatus = SalesInvoice::BILLED;
            $transactionStatus = SalesTransaction::TRANSACTION_PENDING;
            $invoicePaidDate = null;
            $crossCheckBill = 0;
            $totalBill = $request->total_bill;
            $grandTotalBill = $totalBill;

            $order = Sales::where('customer_id', $customerId)
                        ->whereNull('order_number')
                        ->where('quotation_status', Sales::QUOTATION_PENDING)
                        ->first();

            $crossCheckBill = $order->sales_details()->sum('total_price');

            if(empty($order)) return response()->json(['message' => 'cart tidak ditemukan, tidak dapat melakukan checkout.'], 500);

            if($request->payment_method_id == 4) { //paylater
                $orderStatus = Sales::ORDER_PROCESS;
                $invoiceStatus = SalesInvoice::PAID_OFF;
                $invoicePaidDate = date('Y-m-d');
                // $transactionStatus = SalesTransaction::TRANSACTION_SUCCESS;
                $appliedPaylater = $request->user->profile->application_paylater;

                if(empty($appliedPaylater)) {
                    return response()->json([
                        'message' => 'Tidak dapat menggunakan metode paylater, silahkan mengajukan permohonan terlebih dahulu, melalui menu pengajuan paylater.'
                    ], 500);
                }

                if(($appliedPaylater->status != ApplicationPaylater::APPLICATION_ACCEPT) || !$profileTransactionSetting->is_allowed_paylater) {
                    return response()->json(['message' => 'Tidak diizinkan melakukan pembayaran menggunakan metode paylater'], 500);
                }
            }

            if($this->_checkCustomerHasUnpaidOrder($customerId, $profileTransactionSetting->limit)) {
                return response()->json(['message' => 'Tidak dapat melakukan checkout, terdapat order yang belum dibayar / belum lunas.'], 500);
            }
            
            $order->quotation_status = Sales::QUOTATION_ACCEPT;
            $order->order_number = \RunningNumber::generate('sales', 'order_number', \Config::get('transactions.sales_order.code'));
            $order->order_date = $now;
            $order->order_status = $orderStatus;
            $order->shipping_method_id = $request->shipping_method_id;
            $order->shipping_cost = $request->shipping_cost;
            $order->shipping_address_id = $request->address_id;
            $order->downpayment = $request->downpayment;
            $order->payment_method_id = $request->payment_method_id;
            $order->updated_at = $now;
            $order->save();

            $order->sales_details()->update([
                'quotation_status' => Sales::QUOTATION_ACCEPT,
                'order_status' => $orderStatus
            ]);
            
            foreach ($order->sales_details as $detail) {
                $itemName = $detail->item_material->item->name . ' ' . $detail->item_material->material->name . ' ' . $detail->item_material->thick . 'mm ' .$detail->item_material->color->name;

                $midtransItemDetails[] = [
                    'id'       => $detail->item_material_id,
                    'price'    => $detail->price,
                    'quantity' => (int) $detail->quantity,
                    'name'     => $itemName
                ];

                //cek punya biaya custom length atau tidak
                if($detail->is_custom_length == 1) {
                    $chargeCustomLength = $detail->item_material->item->charge_custom_length; 

                    $midtransItemDetails[] = [
                        'id'       => 'cl' . $detail->item_material_id,
                        'price'    => $chargeCustomLength,
                        'quantity' => (int) $detail->quantity,
                        'name'     => 'cost custom length ' . $itemName
                    ];

                    $grandTotalBill = $grandTotalBill + $chargeCustomLength;
                }
            }

            //cek punya voucher atau enggk
            if(!empty($request->voucher_id)) {
                $voucher = Voucher::find($request->voucher_id);

                $isAllowedUsedVoucher = $this->_isVoucherAvailable($voucher, $totalBill);
                if($isAllowedUsedVoucher['code'] != 200) return response()->json(['message' => $isAllowedUsedVoucher['message']], 500);

                $voucherUsage = new VoucherUsage();                    
                $voucherUsage->voucher_id = $voucher->id;
                $voucherUsage->user_id = $request->user->id;
                $voucherUsage->sales_id = $order->id;
                $voucherUsage->status_usage = VoucherUsage::STATUS_AVAILABLE;
                $voucherUsage->save();

            }else if(!empty($order->voucher_usage)) {
                $voucher = $order->voucher_usage->voucher;

                $isAllowedUsedVoucher = $this->_isVoucherAvailable($voucher);
                if(!$isAllowedUsedVoucher['code']) return response()->json(['message' => $isAllowedUsedVoucher['message']], 500);

                $voucherUsage = $order->voucher_usage;
            }

            if(!empty($voucherUsage)) {
                $voucherDiscount = (int) $totalBill*($voucherUsage->voucher->value/100);
                $grandTotalBill = $grandTotalBill-$voucherDiscount;

                $order->update([
                    'discount' => $voucherUsage->voucher->value,
                    'grand_total_price' => $grandTotalBill
                ]);

                $voucherUsage->update(['status_usage' => VoucherUsage::STATUS_USED]);

                $midtransItemDetails[] = [
                    'id'       => $voucherUsage->id,
                    'price'    => -1*$voucherDiscount,
                    'quantity' => 1,
                    'name'     => 'voucher discount ' . $voucherUsage->voucher->name
                ];
            }

            $shippingInstruction = ShippingInstruction::create([
                'sales_id' => $order->id,
                'status' => ShippingInstruction::NOT_RELEASE,
                'shipping_method_id' => $request->shipping_method_id,
                'shipping_cost' => (int) $request->shipping_cost,
                'address_id' => $request->address_id,
                'created_at' => $now
            ]);

            $deliveryNote = DeliveryNote::create([
                'shipping_instruction_id' => $shippingInstruction->id,
                'status' => DeliveryNote::DEFAULT_DELIVERY_STATUS,
                'shipping_method_id' => $request->shipping_method_id,
                'shipping_cost' => (int) $request->shipping_cost,
                'address_id' => $request->address_id,
                'created_at' => $now
            ]);

            //cek punya biaya ongkir atau enggk
            if ($request->shipping_method_id == ShippingInstruction::METHOD_IS_DELIVERY) {
                $grandTotalBill = $grandTotalBill + (int) $request->shipping_cost;

                $midtransItemDetails[] = [
                    'id'       => $shippingInstruction->id,
                    'price'    => (int) $request->shipping_cost,
                    'quantity' => 1,
                    'name'     => 'Shipping cost'
                ];
            }

            $invoice = SalesInvoice::create([
                'delivery_note_id' => $deliveryNote->id,
                'payment_method_id' => $request->payment_method_id,
                'due_date' => $this->_setDueDate($order->OrderDate, $profileTransactionSetting),
                'status' => $invoiceStatus,
                'paid_of_date' => $invoicePaidDate,
                'total_bill' => $grandTotalBill,
                'created_at' => $now
            ]);
           
            //check jika menggunakan dp
            if($request->payment_method_id == 3) {
                if($request->downpayment < $profileTransactionSetting->minimum_downpayment) {
                    return response()->json(['message' => 'minimum downpayment: ' . $profileTransactionSetting->minimum_downpayment], 500);
                }

                $arDP = AccountReceivable::create([
                    'sales_id' => $order->id, 
                    'amount' => $request->downpayment,
                    'balance' => $request->downpayment,
                    'note' => '',
                    'type' => AccountReceivable::TYPE_DP
                ]);
             
                $midtransItemDetails = [];
                $midtransItemDetails[] = [
                    'id'       => $arDP->id,
                    'price'    => (int) $arDP->balance,
                    'quantity' => (int) 1,
                    'name'     => 'Downpayment Order'
                ];
            }

            //check is paylater
            // $arBalance = ($request->payment_method_id == 4) ? 0 : ($grandTotalBill - $request->downpayment);
            $arBalance = $grandTotalBill - $request->downpayment;

            $arBill = AccountReceivable::create([
                'sales_id' => $order->id, 
                'amount' => $grandTotalBill - $request->downpayment,
                'balance' => $arBalance,
                'note' => '',
                'type' => AccountReceivable::TYPE_BILL
            ]);

            //check ada dp atau tidak
            $ar = ($request->payment_method_id == 3) ? $arDP : $arBill;

            //jika bukan paylater, buat salestransaction
            if($request->payment_method_id != 4) {
                $transaction = SalesTransaction::create([
                    'code' => Str::random(12),
                    'account_receivable_id' => $ar->id,
                    'sales_invoice_id' => $invoice->id,
                    'amount' => (int) $ar->amount,
                    'status' => $transactionStatus,
                    'note' => ''
                ]);
            }

            //jika bukan paylater, generate snaptoken
            if($request->payment_method_id != 4) {
                // midtrans snap_token.
                $payload = [
                    "credit_card" => [
                        "secure" => true,
                        "channel" => "migs",
                        "bank" => "bca",
                        "installment" => [
                        "required" => false,
                        "terms" => [
                            "bni" => [3, 6, 12],
                            "mandiri" => [3, 6, 12],
                            "cimb" => [3, 6, 12],
                            "bca" => [3, 6, 12]
                        ]
                        ]
                    ],
                    'transaction_details' => [
                        'order_id'      => $transaction->code,
                        'gross_amount'  => $transaction->amount,
                    ],
                    'customer_details' => [
                        'first_name'    => $request->user->name,
                        'email'         => $request->user->email,
                        'phone'         => $request->user->profile->phone
                        // 'address'       => $request->user->profile->default_address->
                    ],
                    'item_details' => $midtransItemDetails
                ];

                $snapToken = MidtransSnap::getSnapToken($payload);
                $transaction->update([ 'snap_token' => $snapToken ]);
            }
            
            \DB::commit();
            return response()->json([
                'order_id' => $order->id,
                'payment_method_id' => $invoice->payment_method_id,
                'snap_token' => $snapToken
            ], 200);
        } catch (\Throwable $th) {
            \DB::rollback();

            return response()->json(['message' => $th->getMessage() . ' ' . $th->getLine()], 500);
        }
    }

    // midtrans redirect snap test
    public function snaptest()
    {
        return view('midtrans.snaptest', ['snapToken' => '9ab74632-24b6-420c-b934-dfa654f18c62']);
    }

    // midtrans snap
    public function snaptoken($snapToken)
    {
        return view('midtrans.snaptoken', ['snapToken' => $snapToken]);
    }
    public function finish()
    {
        return view('midtrans.finish');
    }
    public function unfinish()
    {
        return view('midtrans.unfinish');
    }
    public function error()
    {
        return view('midtrans.error');
    }

    /**
     * Midtrans notification handler.
     *
     * @param Request $request
     * 
     * @return void
     */
    public function notificationHandler(Request $request)
    {
        $notif = new MidtransNotification();
        
        $paymentType = $notif->payment_type;
        $paymentfraud = $notif->fraud_status;
        
        $transactionStatus = $notif->transaction_status;
        $transactionCode = $notif->order_id;
        $transactionVANumber = '';
        
        switch ($paymentType) {
            case 'echannel':
                $transactionVANumber = $notif->bill_key;
            break;

            case 'bank_transfer':
                $transactionVANumber = $notif->va_numbers[0]->va_number;
            break;
            
            case 'credit_card':
                $transactionVANumber = $notif->approval_code;
                break;

            default:
                $transactionVANumber = '';
                break;
        }

        DB::beginTransaction();
        
        try {
            $transaction = SalesTransaction::where('code', $transactionCode)->first();
            $transactionAmount = $transaction->amount;

            LogSalesTransactionNotification::create([
                'sales_transaction_id' => $transaction->id,
                'status' => $transactionStatus,
                'notification' => json_encode($request->all())
            ]);

            $salesId = AccountReceivable::find($transaction->account_receivable_id)->sales_id;

            switch ($transactionStatus) {
                case 'capture':
                    // For credit card transaction, we need to check whether transaction is challenge by FDS or not
                    if ($paymentType == 'credit_card') {
    
                        if($fraud == 'challenge') {
                            // TODO set payment status in merchant's database to 'Challenge by FDS'
                            // TODO merchant should decide whether this transaction is authorized or not in MAP
                            $transaction->update([
                                'status' => SalesTransaction::TRANSACTION_PENDING,
                                'va_number' => $transactionVANumber,
                                'note' => "Transaction code: " . $transactionCode ." is challenged by FDS, decide whether this transaction is authorized or not in MAP"
                            ]);
                        } else {
                            $transaction->update([
                                'status' => SalesTransaction::TRANSACTION_SUCCESS,
                                'va_number' => $transactionVANumber,
                                'note' => "Transaction code: " . $transactionCode ." successfully captured using " . $paymentType
                            ]);
                        }
    
                    }
                    break;
    
                case 'settlement':
                                        
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_SUCCESS,
                        'va_number' => $transactionVANumber,
                        'note' => "Transaction code: " . $transactionCode ." successfully transfered using " . $paymentType
                    ]);

                    $accountReceivable = AccountReceivable::find($transaction->account_receivable_id);
                    $accountReceivable->balance = $accountReceivable->balance - $transactionAmount;
                    $accountReceivable->save();

                    $sales = $accountReceivable->sales;

                    $sales->sales_details()
                                ->where(['order_status' => Sales::ORDER_PENDING])
                                ->update(['order_status' => Sales::ORDER_PROCESS]);

                    $sales->order_status = Sales::ORDER_PROCESS;
                    $sales->save();
        
                    if(!empty($transaction->sales_invoice_id)) {
                        $transaction->sales_invoice()->update([
                            'status' => SalesInvoice::PAID_OFF,
                            'paid_of_date' => date('Y-m-d')
                        ]);
                    }
                    break;
    
                case 'pending':
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_PENDING,
                        'va_number' => $transactionVANumber,
                        'note' => "Waiting customer to finish transaction code: " . $transactionCode . " using " . $paymentType
                    ]);
                    break;
    
                case 'deny':
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_FAILED,
                        'note' => "Payment using " . $paymentType . " for transaction code: " . $transactionCode . " is Failed."
                    ]);        
                    break;
    
                case 'expire':
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_EXPIRED,
                        'note' => "Payment using " . $paymentType . " for transaction code: " . $transactionCode . " is expired."
                    ]);

                    // handle cancel checkout
                    $this->_cancelOrder($salesId);
                    break;
    
                case 'cancel':
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_FAILED,
                        'note' => "Payment using " . $paymentType . " for transaction code: " . $transactionCode . " is canceled."
                    ]);
    
                    // handle cancel checkout
                    $this->_cancelOrder($salesId);
                    break;                       
                
                default:
                    $transaction->update([
                        'status' => SalesTransaction::TRANSACTION_PENDING,
                        'va_number' => $transactionVANumber,
                        'note' => "Waiting customer to finish transaction code: " . $transactionCode . " using " . $paymentType
                    ]);
                    break;
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' on line : ' . $th->getLine()], 400);
        }

        DB::commit();
        return response()->json([], 200);
    }

    private function _getSalesProgressStatus ($sales)
    {
        if($sales->order_status == Sales::ORDER_PENDING) {
            $currentProgress = 'Menunggu Pembayaran';
        }else if($sales->order_status == Sales::ORDER_CANCEL){
			$currentProgress = 'Transaksi Dibatalkan';
		}else if($sales->order_status == Sales::ORDER_FINISH){
			$currentProgress = 'Transaksi Selesai';
		}else if($sales->order_status == Sales::ORDER_PROCESS) {
            $currentProgress = 'Dalam Antrian Produksi';
            $hasJoSales = $sales->sales_details()->whereRaw('(sales_details.quantity - (
                                select ifnull(sum(job_order_details.quantity), 0) 
                                from job_order_details  
                                where sales_detail_id = sales_details.id
                                and status <> 0
                                and deleted_at is null
                            )) <> sales_details.quantity')
                            ->get();

            if($hasJoSales->count() > 0) {
                $currentProgress = 'Dalam Proses Produksi';
                $shippingInstructionBySales = $sales->shipping_instructions()->pluck('id');
                $hasShippingSalesNotRelease = $sales->shipping_instructions()
                                        ->where('status', ShippingInstruction::NOT_RELEASE)
                                        ->limit(1)
                                        ->first();

                $hasShippingSalesRelease = $sales->shipping_instructions()
                                        ->where('status', ShippingInstruction::RELEASE)
                                        ->limit(1)
                                        ->first();

                if(!empty($hasShippingSalesNotRelease) || !empty($hasShippingSalesRelease)) {
                    $currentProgress = 'Dalam Antrian Pengiriman';

                    //cek sisa pembayaran
                    $listAR = $sales->account_receivables()
                                ->where('balance', '>', 0)
                                ->select('account_receivables.*')
                                ->distinct()
                                ->get();
                        
                    $unpaidAR = $listAR->sum('balance');

                    if($unpaidAR > 0) $currentProgress = 'siap kirim lunasi sisa pembayaran';

                    $hasDeliverySales = DeliveryNote::whereIn('shipping_instruction_id', $shippingInstructionBySales)
                                            ->where('status', DeliveryNote::DELIVERY_PROCESS)
                                            ->get();

                    $hasDeliverySalesFinish = DeliveryNote::whereIn('shipping_instruction_id', $shippingInstructionBySales)
                                                ->where('status', DeliveryNote::DELIVERY_FINISH)
                                                ->get();

                    if($hasDeliverySales->count() > 0) {
                        $currentProgress = 'Dalam Pengiriman';
                    }else if($hasDeliverySalesFinish->count() > 0) {
                        $currentProgress = 'Pengiriman Selesai';
                    }
                }

            }
        }

        return $currentProgress;
    }

    public function invoiceById ($customerId, $salesId)
    {
        /**
         *  - sales order number
         *  - tanggal pembayaran
         *  - customer name
         *  - customer address

         *  - list item

         *  - shipping cost
         *  - total
         * 
         *  - metode bayar
         *  - va
         * 
         */

         $sales = Sales::where('id', $salesId)
                    ->whereNotNull('order_number')
                    ->where('quotation_status', Sales::QUOTATION_ACCEPT)
                    ->with([
                        'customer:id,name,email',
                        'payment_method',
                        'customer_address',
                        'warehouse_pickup_point',
                        'sales_details',
                        'sales_details.item_review',
                        'sales_details.item_material',
                        'sales_details.item_material.item',
                        'sales_details.item_material.item.images',
                        'sales_details.item_material.material',
                        'sales_details.item_material.color',
                    ])->first();

        foreach ($sales->sales_details as $order) {
            $order->can_review_item = ($order->order_status == Sales::ORDER_FINISH);

            $itemImages = $order->item_material->item->images;
            //set default value if image empty
            if(count($itemImages) <= 0) {
                $order->item_material->item->images[] = [
                    "id" => 1,
                    "image" => "/img/no-image.png",
                    "item_id" => 1,
                    "is_thumbnail" => 1,
                    "is_active" => 1,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "image_url" => asset("/img/no-image.png")
                ];
            }
        }

        $ar = $sales->account_receivables()
                ->join('sales', 'sales.id', 'account_receivables.sales_id')
                // ->where('payment_method_id', '<>', 4) 
                ->where('balance', '>', 0)
                ->where('customer_id', $customerId)
                ->distinct()
                ->get();

        $unpaidAR = $ar->sum('balance');

        $salesTransaction = SalesTransaction::whereIn('account_receivable_id', $ar->pluck('id'))
                                ->whereNull('paid_of_date')
                                ->first();

        $sales->status_in_text = $this->_getSalesProgressStatus($sales);
        $sales->has_unpaid_bill = $unpaidAR > 0;
        $sales->snap_token = optional($salesTransaction)->snap_token;
        $sales->va_number = optional($salesTransaction)->va_number;
        return response()->json($sales, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function history($customerId)
    {
        try {
            //cari yang sudah dicheckout
            $histories = Sales::where('customer_id', $customerId)
                        ->whereNotNull('order_number')
                        ->where('quotation_status', Sales::QUOTATION_ACCEPT)
                        ->paginate(10);
                        
            foreach($histories as $history) {
                $ar = $history->account_receivables()
                            ->join('sales', 'sales.id', 'account_receivables.sales_id')
                            ->where('payment_method_id', '<>', 4) 
                            ->where('balance', '>', 0)
                            ->where('customer_id', $customerId)
                            ->distinct()
                            ->get();
                
                $unpaidAR = $ar->sum('balance');

                $history->status_in_text = $this->_getSalesProgressStatus($history);
                $history->has_unpaid_bill = $unpaidAR > 0;
            }

            if(empty($histories)) return response()->json(['message' => 'history is empty'], 500);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'server sedang dalam perbaikan : ' . $th->getMessage()], 500);
        }

        return response()->json($histories, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function historyPaylater($customerId)
    {
        try {
            //cari yang sudah dicheckout
            $histories = Sales::where('customer_id', $customerId)
                        ->where('payment_method_id', 4) //paylater
                        ->whereNotNull('order_number')
                        ->where('quotation_status', Sales::QUOTATION_ACCEPT)
                        ->paginate(10);
                        
            foreach($histories as $history) {
                $ar = $history->account_receivables()
                            ->join('sales', 'sales.id', 'account_receivables.sales_id')
                            ->where('balance', '>', 0)
                            ->where('customer_id', $customerId)
                            ->distinct()
                            ->get();
                
                $unpaidAR = $ar->sum('balance');

                $history->status_in_text = $this->_getSalesProgressStatus($history);
                $history->has_unpaid_bill = $unpaidAR > 0;
            }

            if(empty($histories)) return response()->json(['message' => 'history is empty'], 500);

        } catch (\Throwable $th) {

            return response()->json(['message' => 'server sedang dalam perbaikan : ' . $th->getMessage()], 500);
        }

        return response()->json($histories, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    //ganti pakai invoicebyid
    // public function historyById($customerId, $salesId)
    // {
    //     try {
    //         //cari yang sudah dicheckout
    //         $orders = SalesDetail::where('sales_id', $salesId)
    //             ->where('quotation_status', Sales::QUOTATION_ACCEPT)
    //             ->with([
    //                 'item_review',
    //                 'item_material:id,name,thick,item_id,material_id,color_id',
    //                 'item_material.item',
    //                 'item_material.item.images',
    //                 'item_material.material',
    //                 'item_material.color',
    //             ])
    //             ->select(
    //                 'sales_details.id', 
    //                 'sales_details.item_material_id', 
    //                 'sales_details.length', 
    //                 'sales_details.width', 
    //                 'sales_details.height',
    //                 'sales_details.sheet',
    //                 'sales_details.quantity',
    //                 'sales_details.price'
    //             )->paginate(10);

    //         if(empty($orders)) return response()->json(['message' => 'history is empty'], 500);

    //         foreach ($orders as $order) {
    //             $order->can_review_item = ($order->order_status == Sales::ORDER_PROCESS);
    //         }

    //     } catch (\Throwable $th) {
    //         return response()->json(['message' => 'server sedang dalam perbaikan'], 500);
    //     }

    //     return response()->json($orders, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    // }

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
    protected function _getPriceBeforeDiscount ($currentPrice, $discount)
    {
        return $currentPrice/($discount/100);
    }
    protected function _cancelOrder ($salesId)
    {
        /**
         * tidak jadi ditagihkan, tidak jadi ada pemasukan
         */

         $salesCheckouted = Sales::where('quotation_status', SALES::QUOTATION_ACCEPT)
                             ->where('order_status', SALES::ORDER_PENDING)
			 				 ->orWhere('order_status', SALES::ORDER_CANCEL)
                             ->where('id', $salesId)
                             ->first();

        if(empty($salesCheckouted)) return false;

        $salesCheckouted->update(['order_status' => SALES::ORDER_CANCEL]);

        $salesCheckouted->sales_details()
            ->where('quotation_status', SALES::QUOTATION_ACCEPT)
            ->where('order_status', SALES::ORDER_PENDING)
            ->update(['order_status' => SALES::ORDER_CANCEL]);


        /** 
         * pembelian online dianggap tidak boleh menyicil pengiriman 
         * karena invoice sudah dibuat diawal
         * */
        $accountReceivables = $salesCheckouted->account_receivables;
        $salesShipping = $salesCheckouted->shipping;
        $salesInvoice = $salesShipping->sales_invoice;

        try {

            \DB::beginTransaction();
            // delete transaction (va midtrans), ar (tagihan yang akan diterima)
            foreach ($accountReceivables as $keyAr => $ar) {
                $ar = AccountReceivable::where('sales_id', $ar->sales_id)->first();
                $ar->sales_transaction()->delete();
                $ar->delete();
            }
    
            $salesInvoice->delete();
            $salesShipping->delete();

            $voucherUsage = VoucherUsage::where('sales_id', $salesCheckouted->id)
                                ->where('status_usage', VoucherUsage::STATUS_USED)
                                ->first();

            if(!empty($voucherUsage)) {
                $voucherUsage->update(['status_usage' => VoucherUsage::STATUS_AVAILABLE]);
    
                $salesCheckouted->update([
                    'discount' => 0,
                    'grand_total_price' => $this->_getPriceBeforeDiscount($salesCheckouted->grand_total_price, $voucherUsage->voucher->value)
                ]);
            }
            \DB::commit();
            return true;

        } catch (\Throwable $th) {
            \DB::rollback();
            return false;
        }
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
