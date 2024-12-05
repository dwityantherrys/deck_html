<?php

namespace App\Http\Controllers\Mobile\Voucher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\Mobile\Voucher\Voucher;
use App\Models\Mobile\Voucher\VoucherUsage;


class VoucherUsageController extends Controller
{ 
    private $route = 'mobile/voucher/usage';
    private $routeView = 'mobile.voucher.usage';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new VoucherUsage();
      $this->datatablesBuilder = $datatablesBuilder;

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
        $usageStatus = [
            $this->model::STATUS_AVAILABLE => ['label' => 'available', 'label-color' => 'blue'],
            $this->model::STATUS_USED => ['label' => 'used', 'label-color' => 'success'],
            $this->model::STATUS_EXPIRED => ['label' => 'expired', 'label-color' => 'red']
        ];
        
        if ($request->ajax()) {
            return Datatables::of($this->model::with(['voucher', 'customer']))
                        ->addColumn('voucher', function (VoucherUsage $voucherUsage) { 
                            return $voucherUsage->voucher->name . ' (' . $voucherUsage->voucher->code . ')'; 
                        })
                        ->addColumn('user', function (VoucherUsage $voucherUsage) { 
                            return $voucherUsage->customer->name; 
                        })
                        ->editColumn('status_usage', function (VoucherUsage $voucherUsage) use ($usageStatus) { 
                            return '<small class="label bg-'. $usageStatus[$voucherUsage->status_usage]['label-color'] . '">' . $usageStatus[$voucherUsage->status_usage]['label'] . '</small>'; 
                        })
                        ->addColumn('action', function (VoucherUsage $voucherUsage) { 
                            $isDisableDelete = ($voucherUsage->status_usage === $voucherUsage::STATUS_USED) ? 'disabled' : '';

                            return '<div class="btn-group">
                                <button 
                                class="confirmation-delete btn btn-default text-red"
                                data-target="' . url($this->route . '/' . $voucherUsage->id) . '"
                                data-token="' . csrf_token() . '"' .
                                $isDisableDelete . 
                                '>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>'; 
                        })
                        ->rawColumns(['status_usage', 'action'])          
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'voucher', 'name' => 'voucher', 'title' => 'voucher' ])
                                        ->addColumn([ 'data' => 'user', 'name' => 'user', 'title' => 'Customer' ])
                                        ->addColumn([ 'data' => 'status_usage', 'name' => 'status_usage', 'title' => 'Status Usage' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                            }',
                                        ]);

        return view($this->routeView . '.index', $this->params);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['model'] = $this->model;

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
        $redirectOnSuccess = $this->route;
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
            $params['status_usage'] = $this->model::STATUS_AVAILABLE;
            
            $voucher = Voucher::find($request->voucher_id);
            if(!$voucher->is_active) {
                $request->session()->flash('notif', [
                    'code' => 'failed ' . __FUNCTION__ . 'd',
                    'message' => 'voucher tidak tersedia',
                ]);

                return redirect()
                    ->back()
                    ->withInput();                
            }
            if(!$voucher->is_multiple_usage && $voucher->voucher_usages()->where('status_usage', $this->model::STATUS_USED)->count() > 0) {
                $request->session()->flash('notif', [
                    'code' => 'failed ' . __FUNCTION__ . 'd',
                    'message' => 'voucher terbatas, voucher telah digunakan',
                ]);

                return redirect()
                    ->back()
                    ->withInput();                
            }

            $item = $this->model::create($params);
            
            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            
            return redirect($redirectOnSuccess);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->params['model'] = $this->model->find($id);
        return view($this->routeView . '.edit', $this->params);
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
            $voucherUsage = $this->model->find($id);
            if($voucherUsage->status_usage !== $this->model::STATUS_USED) $voucherUsage->delete();
            
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
            // 'sales_id' => ['required'],
            'user_id' => ['required'],
            'voucher_id' => ['required'],
        ]);
    }
}
