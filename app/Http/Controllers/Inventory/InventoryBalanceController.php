<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\LogPrint;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Master\Material\RawMaterial;


class InventoryBalanceController extends Controller
{
    private $route = 'inventory/balance';
    private $routeView = 'inventory.balance';
    private $params = [];
    private $inventoryType = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new Inventory();
      $this->datatablesBuilder = $datatablesBuilder;
      $this->inventoryType = [
        $this->model::TYPE_INVENTORY_RAW => ['label' => 'raw material', 'label-color' => 'yellow'],
        $this->model::TYPE_INVENTORY_FINISH => ['label' => 'finish good', 'label-color' => 'blue']
      ];

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['typeInventoryOptions'] = [
        $this->model::TYPE_INVENTORY_RAW => 'raw material',
        $this->model::TYPE_INVENTORY_FINISH => 'item material'
      ];
    }

    // di pakai di purchase order, untuk cari purchase request yang belum di order
    public function search(Request $request)
    {
      $where = "order_status = " . $this->model::ORDER_PENDING;
      $where .= " and (order_number IS NULL and order_date IS NULL)";
      $where .= " and request_status = " . $this->model::REQUEST_ACCEPT;
      $response = [];

      if ($request->searchKey) {
        $where .= " and request_number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $result) {
            $result->name = $result->request_number;
        }

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById ($id)
    {
        $result = $this->model->where('id', $id)->with([
            'purchase_details',
            'request_log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        $result->name = $result->request_number;
        return response()->json($result, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $inventoryType = $this->inventoryType;

        if ($request->ajax()) {
            return Datatables::of($this->model::with(['inventory_warehouses', 'raw_material', 'item_material']))
                        ->addColumn('item_name', function (Inventory $inventory) {
                            if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
                                $rawMaterial = $inventory->raw_material;
                                return $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . ' ' . $rawMaterial->color->name;
                            }

                            $itemMaterial = $inventory->item_material;
                            return $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . ' ' . $itemMaterial->color->name;
                        })
                        ->addColumn('total_warehouse', function (Inventory $inventory) {
                            $groupByWarehouse = $inventory->inventory_warehouses()
                                                    ->select('warehouse_id')
                                                    ->distinct()
                                                    ->get();

                            return count($groupByWarehouse);
                        })
                        ->editColumn('type_inventory', function (Inventory $inventory) use ($inventoryType) {
                            return '<small class="label bg-'. $inventoryType[$inventory->type_inventory]['label-color'] . '">' . $inventoryType[$inventory->type_inventory]['label'] . '</small>';
                        })
                        ->editColumn('updated_at', function (Inventory $inventory) {
                            return $inventory->updated_at->format('m/d/Y');
                        })
                        ->addColumn('action', function (Inventory $inventory) {
                            return '<div class="btn-group">
                                <a
                                    href="'. url($this->route . '/' . $inventory->id) .'"
                                    class="btn btn-default"
                                    title="detail inventory"
                                    data-toggle="tooltip">
                                    <i class="fa fa-th-list" aria-hidden="true"></i>
                                </a>
                            </div>';
                        })
                        ->rawColumns(['type_inventory', 'action'])
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'reference_id', 'name' => 'reference_id', 'title' => 'Item code' ])
                                        ->addColumn([ 'data' => 'item_name', 'name' => 'item_name', 'title' => 'Item Name' ])
                                        ->addColumn([ 'data' => 'type_inventory', 'name' => 'type_inventory', 'title' => 'Inv. type' ])
                                        ->addColumn([ 'data' => 'total_warehouse', 'name' => 'total_warehouse', 'title' => 'Warehouse' ])
                                        ->addColumn([ 'data' => 'stock', 'name' => 'stock', 'title' => 'Stock' ])
                                        ->addColumn([ 'data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Last update' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ]);
                                        // ->parameters([
                                        //     'initComplete' => 'function() {
                                        //         $.getScript("'. asset("js/utomodeck.js") .'");
                                        //         $.getScript("'. asset("js/purchase/request-index.js") .'");
                                        //     }',
                                        // ]);

        return view($this->routeView . '.index', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     * add adjustment inventory
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['model'] = new InventoryWarehouse();

        return view($this->routeView . '.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        try {
            DB::beginTransaction();

            $params = $request->all();

            $inventory = Inventory::where('reference_id', $params['item_id'])
                            ->where('type_inventory', $params['item_type'])
                            ->first();

            if (empty($inventory)) {
                $inventory = Inventory::create([
                    'type_inventory' => $params['item_type'],
                    'reference_id' => $params['item_id'],
                    'cost_of_good' => $params['cost_of_good_after_adjustment'],
                    'stock' => $params['different_stock'],
                ]);

                $inventoryWH = $inventory->inventory_warehouses()->create([
                    'warehouse_id' => $params['warehouse_id'],
                    'stock' => $params['different_stock']
                ]);

                if($params['item_type'] == Inventory::TYPE_INVENTORY_RAW) {
                    $rawMaterial = RawMaterial::find($params['item_id']);
                    $inventoryWHNumber = $rawMaterial->number . '-' . 0 . '-' . $inventoryWH->id;

                    $inventoryWH->update(['inventory_warehouse_number' => $inventoryWHNumber]);
                }

            }else {
                $inventoryWH = $inventory->inventory_warehouses()
                    ->where('warehouse_id', $params['warehouse_id'])
                    ->whereNull('receipt_detail_id')
                    ->first();

                if(empty($inventoryWH)) {
                    $inventoryWH = $inventory->inventory_warehouses()->create([
                        'warehouse_id' => $params['warehouse_id'],
                        'stock' => $params['different_stock']
                    ]);

                    if($inventory->type_inventory == Inventory::TYPE_INVENTORY_RAW) {
                        $rawMaterial = RawMaterial::find($inventory->reference_id);
                        $inventoryWHNumber = $rawMaterial->number . '-' . 0 . '-' . $inventoryWH->id;

                        $inventoryWH->update(['inventory_warehouse_number' => $inventoryWHNumber]);
                    }

                }else {
                    $inventoryWH->update([ 'stock' => $params['different_stock'] ]);
                }

                // update total stock, dan harga pengadaan
                $inv['stock'] = $inventory->inventory_warehouses()->sum('stock');
                $inv['cost_of_good'] = Inventory::where('type_inventory', $params['item_type'])
                                        ->where('reference_id', $params['item_id'])
                                        ->avg('cost_of_good');

                $inventory->update($inv);
            }

            // insert adjustment
            $inventoryWH->inventory_adjustments()->create([
                'stock_before_adjustment' => $params['stock_before_adjustment'] ? $params['stock_before_adjustment'] : 0,
                'stock_after_adjustment' => $params['stock_after_adjustment'],
                'cost_of_good_before_adjustment' => $params['cost_of_good_before_adjustment'],
                'cost_of_good_after_adjustment' => $params['cost_of_good_after_adjustment'],
                'created_by' => $request->user()->id
            ]);

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route . '/' . $inventory->id);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . $th->getMessage() . ' line: ' . $th->getLine(),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    public function storeSellingPrice (Request $request, $id)
    {
        try {

            $inventoryWH = InventoryWarehouse::find($id);
            $inventoryWH->update([ 'selling_price' => $request->selling_price ]);

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success updated',
            ]);

            return redirect($this->route . '/' . $inventoryWH->inventory_id);

        } catch (\Throwable $th) {

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $inventoryType = $this->inventoryType;
        $itemName = '';

        $inventory = $this->model::find($id);

        if($inventory->type_inventory == Inventory::TYPE_INVENTORY_RAW) {
            $rawMaterial = $inventory->raw_material;
            $itemName = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . ' ' . $rawMaterial->color->name;
        }else {
            $itemMaterial = $inventory->item_material;
            $itemName = $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . ' ' . $itemMaterial->color->name;
        }

        $query = InventoryWarehouse::with(['inventory', 'inventory.raw_material', 'inventory.item_material'])
                    ->where('inventory_id', $id);

        if ($request->ajax()) {
            return Datatables::of($query)
                        ->addColumn('warehouse_location', function (InventoryWarehouse $invWH) {
                            return $invWH->warehouse->name;
                        })
                        ->addColumn('item_name', function (InventoryWarehouse $invWH) {
                            $inventory = $invWH->inventory;
                            if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
                                $rawMaterial = $inventory->raw_material;
                                return $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . ' ' . $rawMaterial->color->name;
                            }

                            $itemMaterial = $inventory->item_material;
                            return $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . ' ' . $itemMaterial->color->name;
                        })
                        ->editColumn('type_inventory', function (InventoryWarehouse $invWH) use ($inventoryType) {
                            $inventory = $invWH->inventory;
                            return '<small class="label bg-'. $inventoryType[$inventory->type_inventory]['label-color'] . '">' . $inventoryType[$inventory->type_inventory]['label'] . '</small>';
                        })
                        ->editColumn('updated_at', function (InventoryWarehouse $invWH) {
                            return $invWH->updated_at->format('m/d/Y');
                        })
                        ->addColumn('action', function (InventoryWarehouse $invWH) {
                            return '<div class="btn-group">
                                <a
                                    href="'. url($this->route . '/' . $invWH->id) .'/edit"
                                    class="btn btn-default"
                                    title="adjustment inventory"
                                    data-toggle="tooltip">
                                    <i class="fa fa-sliders" aria-hidden="true"></i>
                                </a>
                                <a
                                    href="'. url($this->route . '/' . $invWH->id) .'/update-selling-price"
                                    class="btn btn-default"
                                    title="update selling price"
                                    data-toggle="tooltip">
                                    <i class="fa fa-tag" aria-hidden="true"></i>
                                </a>
                            </div>';
                        })
                        ->rawColumns(['type_inventory', 'action'])
                        ->make(true);
        }

        $this->params['itemName'] = $itemName;
        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'inventory_warehouse_number', 'name' => 'inventory_warehouse_number', 'title' => 'Item code' ])
                                        ->addColumn([ 'data' => 'item_name', 'name' => 'item_name', 'title' => 'Item Name' ])
                                        ->addColumn([ 'data' => 'warehouse_location', 'name' => 'warehouse_location', 'title' => 'WH Location' ])
                                        ->addColumn([ 'data' => 'type_inventory', 'name' => 'type_inventory', 'title' => 'Inv. type' ])
                                        ->addColumn([ 'data' => 'stock', 'name' => 'stock', 'title' => 'Stock' ])
                                        ->addColumn([ 'data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Last update' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ]);

        return view($this->routeView . '.show', $this->params);
    }

    /**
     * Show the form for editing the specified resource.
     * untuk adjustment
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->params['model'] = InventoryWarehouse::find($id);
        return view($this->routeView . '.edit', $this->params);
    }

    public function editSellingPrice ($id)
    {
        $this->params['model'] = InventoryWarehouse::find($id);
        if($this->params['model']->inventory->type_inventory == Inventory::TYPE_INVENTORY_RAW) {
            $rawMaterial = $this->params['model']->inventory->raw_material;
            $this->params['model']->item_name = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . ' ' . $rawMaterial->color->name;
        }else {
            $itemMaterial = $this->params['model']->inventory->item_material;
            $this->params['model']->item_name = $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . ' ' . $itemMaterial->color->name;
        }

        return view($this->routeView . '.edit-selling-price', $this->params);
    }

    /**
     * Update the specified resource in storage.
     * untuk adjustment
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        try {
            DB::beginTransaction();

            $params = $request->all();

            // insert adjustment
            InventoryAdjustment::create([
                'inventory_warehouse_id' => $request->inventory_warehouse_id,
                'stock_before_adjustment' => $params['stock_before_adjustment'] ? $params['stock_before_adjustment'] : 0,
                'stock_after_adjustment' => $request->stock_after_adjustment,
                'created_by' => $request->user()->id
            ]);

            // update inventory
            $inventoryWH = InventoryWarehouse::find($request->inventory_warehouse_id);
            $inventory = $inventoryWH->inventory;

            // $invWH['selling_price'] = $request->selling_price_after_adjustment;
            $invWH['stock'] = $request->stock_after_adjustment;
            $inventoryWH->update($invWH);

            $invStock = $inventory->inventory_warehouses()->sum('stock');
            $inventory->update(['stock' => $invStock]);

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route . '/' . $inventory->id);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView),
            ]);

            return redirect()
                ->back()
                ->withInput();
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
        try {
            DB::beginTransaction();
            $purchase = $this->model->find($id);
            $purchase->purchase_details()->delete();
            $purchase->delete();

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
            // 'warehouse_id' => ['required'],
            'created_by' => ['required'],
            'stock_after_adjustment' => ['required'],
            'cost_of_good_after_adjustment' => ['required'],
        ]);
    }
}
