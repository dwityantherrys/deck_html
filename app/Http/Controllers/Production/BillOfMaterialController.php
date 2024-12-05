<?php

namespace App\Http\Controllers\Production;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\Production\Bom;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;


class BillOfMaterialController extends Controller
{ 
    private $route = 'production/bom';
    private $routeView = 'production.bom';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new Bom();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      $where = "order_status = " . $this->model::ORDER_PENDING;
      $where .= " and quotation_status = " . $this->model::QUOTATION_ACCEPT;
      $where .= " and order_date is null";
      $response = [];

      if ($request->searchKey) {
        $where .= " and quotation_number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $result) {
            $result->name = $result->quotation_number;
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
            'bom_details'
        ])->first();

        return response()->json($result, 200);
    }

    public function searchByItemMaterialId ($itemMaterialId)
    {
        $result = $this->model->where('item_id', $itemMaterialId)
        ->where('production_category', $this->model::TYPE_CATEGORY_FINISH)
        ->with([
            'bom_details'
        ])->first();

        return response()->json($result, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $productionCategories = [
            $this->model::TYPE_CATEGORY_RAW => ['label' => 'raw material', 'label-color' => 'red'],
            $this->model::TYPE_CATEGORY_FINISH => ['label' => 'finish good', 'label-color' => 'blue'],
            $this->model::TYPE_CATEGORY_SEMI => ['label' => 'semi finish good', 'label-color' => 'yellow']
        ];
        
        if ($request->ajax()) {
            return Datatables::of($this->model::with(['bom_details']))
                        ->editColumn('production_category', function (Bom $bom) use ($productionCategories) { 
                            return '<small class="label bg-'. $productionCategories[$bom->production_category]['label-color'] . '">' . $productionCategories[$bom->production_category]['label'] . '</small>'; 
                        })
                        ->addColumn('bom_code', function (Bom $bom) {
                            $roleUser = request()->user()->role->name;
                            $isSuperAdmin = $roleUser === 'super_admin';
                             
                            return '<a class="has-ajax-form text-red" href="" 
                                data-toggle="modal" 
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $bom->id . '/ajax-form') . '"
                                data-is-superadmin="'. $isSuperAdmin . '">
                                ' . $bom->created_at->format('m/d/Y') . ' - ' . $bom->id . '
                                </a>'; 
                        })
                        ->addColumn('item_name', function (Bom $bom) { 
                            $itemMaterial = $bom->item_material;
                            return $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
                        })
                        ->addColumn('action', function (Bom $bom) { 
                            return '<div class="btn-group">
                                <button 
                                    class="confirmation-delete btn btn-default text-red"
                                    data-target="' . url($this->route . '/' . $bom->id) . '"
                                    data-token="' . csrf_token() . '">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>'; 
                        })
                        ->rawColumns(['bom_code', 'production_category', 'action'])          
                        ->make(true);
        }

        $this->params['defaultProductionCategory'] = [
            'id' => $this->model::TYPE_CATEGORY_FINISH, 
            'label' => 'finish good'
        ];
        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'bom_code', 'name' => 'bom_code', 'title' => 'Item Code' ])
                                        ->addColumn([ 'data' => 'item_name', 'name' => 'item_name', 'title' => 'Item Name' ])
                                        ->addColumn([ 'data' => 'production_category', 'name' => 'production_category', 'title' => 'Item Category' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'"); 
                                                $.getScript("'. asset("js/production/bom-index.js") .'"); 
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
        $this->params['defaultProductionCategory'] = [
            'id' => $this->model::TYPE_CATEGORY_FINISH, 
            'label' => 'finish good'
        ];

        $this->params['model'] = $this->model;
        $this->params['model']['quotation_number'] = \RunningNumber::generate('sales', 'quotation_number', \Config::get('transactions.sales_quotation.code'));

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
            $submitAction = $request->submit;
            $bomDetails = $request->bom_details;

            $params = $request->all();

            unset(
                $params['production_category_label'], 
                $params['submit'], 
                $params['_token'], 
                $params['bomDetails']
            );
            
            $bom = $this->model::create($params);

            if(!empty($bomDetails) && count($bomDetails) > 0) {
                foreach ($bomDetails as $key => $bomDetail) {
                    unset($bomDetail['id']);                  
                    
                    $bomDetail['quantity'] = str_replace(',', '', $bomDetail['quantity']);
                    $bomDetail['costing'] = str_replace(',', '', $bomDetail['costing']);
                    $bom->bom_details()->create($bomDetail);
                }
            }
            
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $redirectOnSuccess = $this->route;
        $keepBomDetails = [];
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
            $submitAction = $request->submit;
            $bomDetails = $request->bom_details;

            $params = $request->all();

            unset(
                $params['production_category_label'], 
                $params['submit'], 
                $params['_token'], 
                $params['bomDetails'],
                $params['id']
            );

            $bom = $this->model->where('id', $id)->first();
            $bom->update($params);

            if(!empty($bomDetails) && count($bomDetails) > 0) {
                foreach ($bomDetails as $key => $bomDetail) {
                    $id = $bomDetail['id'];
                    
                    unset($bomDetail['id']);

                    $bomDetail['quantity'] = str_replace(',', '', $bomDetail['quantity']);
                    $bomDetail['costing'] = str_replace(',', '', $bomDetail['costing']);

                    $currentbomDetail = $bom->bom_details()->where('id', $id)->first();

                    if(!empty($currentbomDetail)) {
                        $currentbomDetail->update($bomDetail);
                        $keepBomDetails[] = $currentbomDetail->id;
                        continue;
                    }

                    $newbomDetail = $bom->bom_details()->create($bomDetail);
                    $keepBomDetails[] = $newbomDetail->id;
                }

                // hapus yang gk ada di request
                $bom->bom_details()->whereNotIn('id', $keepBomDetails)->delete();
            }
           
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $bom = $this->model->find($id);
            $bom->bom_details()->delete();
            $bom->delete();
            
            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'item_id' => ['required'],
            'manufacture_quantity' => ['required'],
            'bom_details.*.material_id' => ['required'],
            'bom_details.*.costing' => ['required'],
            'bom_details.*.quantity' => ['required']
        ]);
    }
}
