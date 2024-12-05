<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use App\Models\Inventory\Inventory;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesDetail;
use App\Models\Master\Warehouse;
use App\Models\Master\Item\ItemMaterial;


class CartController extends Controller
{
    private $cartId, $customerId, $request;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customerId)
    {
        try {
            //cari yang belum di proses ke order
            $order = sales::where('customer_id', $customerId)
                        ->whereNull('order_number')
                        ->where('quotation_status', sales::QUOTATION_PENDING)
                        ->first();

            if(empty($order))  return response()->json(['message' => 'cart is empty'], 500);

            $orders = SalesDetail::with([
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
                    'sales_details.price',
                    'sales_details.total_price',
                    'sales_details.summary'
                )->paginate(10);

            foreach ($orders as $orderD) {
                $itemImages = $orderD->item_material->item->images;
                //set default value if image empty
                if(count($itemImages) <= 0) {
                    $orderD->item_material->item->images[] = [
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

        } catch (\Throwable $th) {
            return response()->json(['message' => 'server sedang dalam perbaikan'], 500);
        }

        return response()->json($orders, 200);
    }

    private function _validateRequest ()
    {
        $res = ['isFail' => false, 'message' => ''];
        
        // params: item_material_id, length, tilt, color_id, thick_id, qty, summary
        $validator = Validator::make($this->request->all(), [
            'item_material_id' => ['required', 'numeric'],
            'is_custom_length' => ['required'],
            'length' => ['required', 'numeric', 'min:1'],
            'sheet' => ['required', 'numeric', 'min:1'],
            'summary' => ['string']
        ]);
        
        if ($validator->fails()) {
            $errorMessages = [];
            foreach ($validator->errors()->get('*') as $key => $value) {
                $errorMessages[$key] = implode(', ', $value);
            }
            
            $res = ['isFail' => true, 'message' => $errorMessages];
        }
            
        return (object) $res;
    }

    private function _storeSales ()
    {
        $customerId = $this->customerId;
        // customerId, quotation_status=0 order_number is null, transactionType mobile
        // if empty new Sales or update
        $order = sales::where('customer_id', $customerId)
                        ->whereNull('order_number')
                        ->where('quotation_status', sales::QUOTATION_PENDING)
                        ->first();

        if (empty($order)) {
            $order = new Sales();
            $order->quotation_date = date('Y-m-d H:i:s');
            $order->quotation_number = \RunningNumber::generate('sales', 'quotation_number', \Config::get('transactions.sales_quotation.code'));
            $order->quotation_status = sales::QUOTATION_PENDING;
            $order->order_status = sales::DEFAULT_ORDER_STATUS;
            $order->warehouse_id = $this->_getWarehouseByRegionUser();
            $order->customer_id = (int) $customerId;
            $order->created_by = (int) $customerId;
            $order->created_at = date('Y-m-d H:i:s');
            $order->discount = 0;
            $order->total_price = 0;
            $order->transaction_channel = sales::TRANSACTION_CHANNEL_MOBILE;
        }
        
        $order->updated_at = date('Y-m-d H:i:s');
        $order->save();

        return $order;
    }

    protected function _getWarehouseByRegionUser()
    {
        // get warehouse terdekat by region, jika district, pakai id citynya
        $regionType = $this->activeUser->region_type;
        $regionId = $this->activeUser->region_id;
        
        if($regionType === $this->activeUser::REGION_TYPE_DISTRICT){
            $regionType = $this->activeUser::REGION_TYPE_CITY;
            $regionId = $this->activeUser->region_district->city_id; 
        }
        
        $warehouse = Warehouse::where([
            'region_type' => $regionType,
            'region_id' => $regionId
        ])->first();

        return !empty($warehouse) ? $warehouse->id : null;
    }
    protected function _countDiscount($price, $discount)
    {
        return $price*($discount/100);
    }
    protected function _countTax($price, $tax)
    {
        return $price*($tax/100);
    }
    protected function _countQuantity()
    {
        return $this->request->length * $this->request->sheet;
    }
    protected function _countTotalPrice($params, $chargeCustomLength)
    {
        // $tax = $this->_countTax($params->price, $params->tax);
        $discount = $this->_countDiscount($params->price, $params->discount);
        // return (($params->price + $tax + $chargeCustomLength) * $params->quantity) - $discount;
        return (($params->price + $chargeCustomLength) * $params->quantity) - $discount;
    }
    protected function _countChargeLength($charge)
    {
        $request = $this->request;
        return (int) $request->is_custom_length === 1 ? $charge : 0;
    }

    protected function _storeSalesDetail ($order, $cartId = NULL)
    {
        $defaultDiscount = 0;
        $defaultTax = 10;
        $request = $this->request;
        
        // SalesDetail find by item_material_id, if empty insert new, else update qty++
        $itemMaterial = ItemMaterial::find($request->item_material_id);
        $itemMaterial->price = $this->_getPriceItem($itemMaterial->item_id);
        $item = $itemMaterial->item;
        $chargeCustomLength = $this->_countChargeLength($item->charge_custom_length);
        
        // prepare input detail
        $detailParams = [];
        $detailParams['item_material_id'] = $itemMaterial->id; 
        $detailParams['width'] = $item->width;
        $detailParams['height'] = $item->height;
        $detailParams['weight'] = $item->weight*$request->length;
        $detailParams['is_custom_length'] = $request->is_custom_length;
        $detailParams['length'] = $request->length;
        $detailParams['sheet'] = $request->sheet;
        $detailParams['quantity'] = $this->_countQuantity();
        $detailParams['price'] = $itemMaterial->price;
        $detailParams['tax'] = $defaultTax;
        $detailParams['discount'] = $defaultDiscount;
        $detailParams['total_price'] = $this->_countTotalPrice((object) $detailParams, $chargeCustomLength);
        $detailParams['summary'] = $request->summary ? $request->summary : '-';
        
        $detail = $order->sales_details()->where('item_material_id', $itemMaterial->id)->first();

        //jika belum ada, insert baru
        if(!empty($detail)) {
            if($detail->length != $detailParams['length']) {
                $order->sales_details()->create($detailParams);
                return;
            }else if($detail->sheet != $detailParams['sheet']) {
                $detailParams['sheet'] = $detail->sheet + $detailParams['sheet'];
                $detailParams['quantity'] = $this->_countQuantity();
                $detailParams['total_price'] = $this->_countTotalPrice((object) $detailParams, $chargeCustomLength);          
            }
            
            $detail->update($detailParams);
            return;        
        }
        
        $order->sales_details()->create($detailParams);
        return;
    }

    public function _getPriceItem ($itemId)
    {
      // get warehouse terdekat by region, jika district, pakai id citynya
      $regionType = $this->activeUser->region_type;
      $regionId = $this->activeUser->region_id;

      if($regionType === $this->activeUser::REGION_TYPE_DISTRICT){
        $regionType = $this->activeUser::REGION_TYPE_CITY;
        $regionId = $this->activeUser->region_district->city_id; 
      }

      $warehouse = Warehouse::where([
        'region_type' => $regionType,
        'region_id' => $regionId
      ])->first();

      if(empty($warehouse)) return 0;

      // get inventory by item
      $inventory = Inventory::where([
        'type_inventory' => Inventory::TYPE_INVENTORY_FINISH,
        'reference_id' => $itemId
      ])->first();

      if(empty($inventory)) return 0;

      /** 
       * item di inventory warehouse bisa lebih dari 1, 
       * karena tiap purchase receipt di catat menjadi inv warehouse baru
       * pakai harga paling terbaru 
       * */
      $selectedInventory = $inventory->inventory_warehouses()
        ->where('warehouse_id', $warehouse->id)
        ->where('selling_price', '>', 0)
        ->orderBy('created_at', 'DESC')
        ->first();

      return $selectedInventory->selling_price;
    }

    /**
     * Store a newly created resource / update one cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $customerId, $cartId = NULL)
    {
        $this->activeUser = $request->user;
        $this->customerId = $this->activeUser->id;
        $this->cartId = $cartId ? $cartId : NULL;
        $this->request = $request;

        if ($this->_validateRequest()->isFail) {            
            return response()->json(['message' => $this->_validateRequest()->message], 400);
        }
        
        DB::beginTransaction();
        try {
            $order = $this->_storeSales();
            $this->_storeSalesDetail($order, $cartId);

            $orderTotalPrice = $order->sales_details()->sum('total_price');
            $order->update([ 
                'total_price' => $orderTotalPrice,
                'grand_total_price' => $orderTotalPrice
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => 'gagal menyimpan cart' . $th->getMessage()], 500);
        }

        return response()->json(['message' => 'cart berhasil tersimpan'], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($customerId, $cartId = NULL)
    {
        try {
            //cari yang belum di proses ke order
            $order = Sales::where('customer_id', $customerId)
                        ->whereNull('order_number')
                        ->where('quotation_status', sales::QUOTATION_PENDING)
                        ->first();

            if(empty($order))  return response()->json(['message' => 'cart is empty'], 500);

            $orders = SalesDetail::with([
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
                    'sales_details.length',
                    'sales_details.is_custom_length',
                    'sales_details.width', 
                    'sales_details.height',
                    'sales_details.sheet',
                    'sales_details.quantity',
                    'sales_details.price',
                    'sales_details.total_price',
                    'sales_details.summary'
                )->first();

            //set default value if image empty
            $itemImages = $orders->item_material->item->images;
            if(count($itemImages) <= 0) {
                $orders->item_material->item->images[] = [
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

        } catch (\Throwable $th) {
            return response()->json(['message' => 'server sedang dalam perbaikan'], 500);
        }

        return response()->json($orders, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Update the multiple cart resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $customerId)
    {
        $this->activeUser = $request->user;
        
        $order = Sales::where('customer_id', $customerId)
            ->whereNull('order_number')
            ->where('quotation_status', sales::QUOTATION_PENDING)
            ->first();

        if(empty($order)) return response()->json(['message' => 'cart is empty'], 500);

        DB::beginTransaction();
        try {
            foreach ($request->item as $item)
            {
                $item = (object) $item;
                $this->request = $item;
                
                $this->_storeSalesDetail($order, $item->id);
            }

            $orderTotalPrice = $order->sales_details()->sum('total_price');
            $order->update([
                'total_price' => $orderTotalPrice,
                'grand_total_price' => $orderTotalPrice
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            return response()->json(['message' => 'server dalam perbaikan'], 500);
        }

        DB::commit();
        return response()->json(['message' => 'cart berhasil di perbarui'], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($customerId, $cartId)
    {
        try {
            $orderDetail = SalesDetail::find($cartId);
            $order = $orderDetail->sales;
            if(empty($orderDetail)) return response()->json(['message' => 'cart tidak ditemukan'], 500);

            $orderDetail->forceDelete();
            if(count($order->sales_details) == 0) $order->forceDelete();

        } catch (\Throwable $th) {
            return response()->json(['message' => 'server dalam perbaikan'], 500);
        }

        return response()->json(['message' => 'cart berhasil di hapus'], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
