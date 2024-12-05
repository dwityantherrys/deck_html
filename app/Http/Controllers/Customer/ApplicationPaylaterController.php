<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\Master\Profile\ApplicationPaylater;
use Carbon\Carbon;


class ApplicationPaylaterController extends Controller
{
    private $route = 'customer/application-paylater';
    private $routeView = 'customer.application-paylater';
    private $params = [];
    private $applicationStatus = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new ApplicationPaylater();
      $this->datatablesBuilder = $datatablesBuilder;
      $this->applicationStatus = [
        $this->model::APPLICATION_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
        $this->model::APPLICATION_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
        $this->model::APPLICATION_DECLINE => ['label' => 'decline', 'label-color' => 'red']
      ];

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $applicationStatus = $this->applicationStatus;

        if ($request->ajax()) {
            return Datatables::of($this->model::with(['profile']))
                        ->addColumn('customer_name', function (ApplicationPaylater $appPaylater) {
                            return $appPaylater->profile->name;
                        })
                        ->editColumn('date_application', function (ApplicationPaylater $appPaylater) {
                            return $appPaylater->date_application->format('m/d/Y');
                        })
                        ->editColumn('status', function (ApplicationPaylater $appPaylater) use ($applicationStatus) {
                            return '<small class="label bg-'. $applicationStatus[$appPaylater->status]['label-color'] . '">' . $applicationStatus[$appPaylater->status]['label'] . '</small>';
                        })
                        ->editColumn('date_validation', function (ApplicationPaylater $appPaylater) {
                            return optional($appPaylater->date_validation)->format('m/d/Y');
                        })
                        ->addColumn('action', function (ApplicationPaylater $appPaylater) {
                            return '<div class="btn-group">
                                <a
                                    href="'. url('master/customer/' . $appPaylater->profile_id . '/edit') .'"
                                    class="btn btn-default"
                                    title="detail application"
                                    data-toggle="tooltip">
                                    <i class="fa fa-th-list" aria-hidden="true"></i>
                                </a>
                            </div>';
                        })
                        ->rawColumns(['status', 'action'])
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'customer_name', 'name' => 'customer_name', 'title' => 'Customer Name' ])
                                        ->addColumn([ 'data' => 'date_application', 'name' => 'date_application', 'title' => 'Date App.' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Status App.' ])
                                        ->addColumn([ 'data' => 'date_validation', 'name' => 'date_validation', 'title' => 'Date Validation' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ]);

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
                    'cost_of_good' => 0,
                    'stock' => $params['stock_after_adjustment']
                ]);

                $inventoryWH = $inventory->inventory_warehouses()->create([
                    'warehouse_id' => $params['warehouse_id'],
                    'selling_price' => $params['selling_price_after_adjustment'],
                    'stock' => $params['stock_after_adjustment']
                ]);

            }else {
                $inventoryWH = $inventory->inventory_warehouses()
                    ->where('warehouse_id', $params['warehouse_id'])
                    ->whereNull('receipt_detail_id')
                    ->first();

                if(empty($inventoryWH)) {

                    $inventoryWH = $inventory->inventory_warehouses()->create([
                        'warehouse_id' => $params['warehouse_id'],
                        'selling_price' => $params['selling_price_after_adjustment'],
                        'stock' => $params['stock_after_adjustment']
                        ]);

                }else {
                    $inventoryWH->update([
                        'selling_price' => $params['selling_price_after_adjustment'],
                        'stock' => $params['stock_after_adjustment']
                    ]);
                }

                $inv['stock'] = $inventory->inventory_warehouses()->sum('stock');
                $inventory->update($inv);
            }

            // insert adjustment
            $inventoryWH->inventory_adjustments()->create([
                'stock_before_adjustment' => 0,
                'stock_after_adjustment' => $params['stock_after_adjustment'],
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
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage() . ' line : ' . $th->getLine(),
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
      // return dd($request->all());
      $applicationStatus = $this->applicationStatus;

      if ($request->ajax()) {
      $query = $this->model::with(['profile']);
      if ($request->filled("filter_customer_name")) {
        $query = $query->whereHas("profile", function($q) use($request) {
          $q->where("name", "like", "%$request->filter_customer_name%");
        });
      }
      if ($request->filled("date_app_awal") && $request->filled("date_app_akhir")) {
        $query = $query->whereBetween("date_application", [Carbon::parse($request->date_app_awal)->startOfDay(), Carbon::parse($request->date_app_akhir)->endOfDay()]);
      }
      if ($request->filled("date_valid_awal") && $request->filled("date_valid_akhir")) {
        $query = $query->whereBetween("date_validation", [Carbon::parse($request->date_valid_awal)->startOfDay(), Carbon::parse($request->date_valid_akhir)->endOfDay()]);
      }
      if ($request->filled("filter_status_app")) {
        $query = $query->where("status", $request->filter_status_app);
      }
        return Datatables::of($query)
        ->addColumn('customer_name', function (ApplicationPaylater $appPaylater) {
          return $appPaylater->profile->name;
        })
        ->editColumn('date_application', function (ApplicationPaylater $appPaylater) {
          return $appPaylater->date_application->format('m/d/Y');
        })
        ->editColumn('status', function (ApplicationPaylater $appPaylater) use ($applicationStatus) {
          return '<small class="label bg-'. $applicationStatus[$appPaylater->status]['label-color'] . '">' . $applicationStatus[$appPaylater->status]['label'] . '</small>';
        })
        ->editColumn('date_validation', function (ApplicationPaylater $appPaylater) {
          return optional($appPaylater->date_validation)->format('m/d/Y');
        })
        ->addColumn('action', function (ApplicationPaylater $appPaylater) {
          return '<div class="btn-group">
          <a
          href="'. url('master/customer/' . $appPaylater->profile_id . '/edit') .'"
          class="btn btn-default"
          title="detail application"
          data-toggle="tooltip">
          <i class="fa fa-th-list" aria-hidden="true"></i>
          </a>
          </div>';
        })
        ->rawColumns(['status', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'customer_name', 'name' => 'customer_name', 'title' => 'Customer Name' ])
      ->addColumn([ 'data' => 'date_application', 'name' => 'date_application', 'title' => 'Date App.' ])
      ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Status App.' ])
      ->addColumn([ 'data' => 'date_validation', 'name' => 'date_validation', 'title' => 'Date Validation' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ]);

      return view($this->routeView . '.index', $this->params);
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
                'stock_before_adjustment' => $request->stock_before_adjustment,
                'stock_after_adjustment' => $request->stock_after_adjustment,
                'created_by' => $request->user()->id
            ]);

            // update inventory
            $inventoryWH = InventoryWarehouse::find($request->inventory_warehouse_id);
            $inventory = $inventoryWH->inventory;

            $invWH['selling_price'] = $request->selling_price_after_adjustment;
            $invWH['stock'] = $request->stock_after_adjustment;
            $inventoryWH->update($invWH);

            $invStock = $inventoryWH->sum('stock');
            $inventory->update(['stock' => $invStock]);

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route . '/' . $id);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage() . ' line : ' . $th->getLine(),
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
            'selling_price_after_adjustment' => ['required'],
        ]);
    }
}
