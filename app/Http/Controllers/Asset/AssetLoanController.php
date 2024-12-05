<?php

namespace App\Http\Controllers\Asset;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\LogPrint;
use App\Models\Asset\AssetLoan;
use App\Models\Asset\AssetStock;

class AssetLoanController extends Controller
{
  private $route = 'asset/loan';
  private $routeView = 'asset.loan';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new AssetLoan();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
  }

  public function search(Request $request)
  {
    $where = "loan_status = " . $this->model::LOAN_REQUEST;
    $where .= " and loan_date is null";
    $response = [];

    if ($request->searchKey) {
      $where .= " and loan_number like '%{$request->searchKey}%'";
    }

    try {
      $results = $this->model->whereRaw($where)
      ->get()
      ->makeHidden(['created_at', 'updated_at']);

      foreach ($results as $key => $result) {
        $result->name = $result->loan_number;
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
      'loan_details' => function ($query) {
        $query->withTrashed();
      },
      'loan_return' => function ($query) {
        $query->withTrashed();
      },
      // 'log_print' => function ($query) {
      //   $query->with(['employee']);
      // }
      ])->withTrashed()->first();

      // foreach ($result->loan_details as $loanDetail) {
      //     $salesDetail->length_options = [];
      //     $salesDetail->length_selected = $salesDetail->is_custom_length ? 0 : $salesDetail->length;
      // }

      $result->name = $result->quotation_number;
      return response()->json($result, 200);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
      $loanStatus = [
        $this->model::LOAN_REQUEST => ['label' => 'on request', 'label-color' => 'yellow'],
        $this->model::LOAN_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
        $this->model::LOAN_REJECT => ['label' => 'reject', 'label-color' => 'red'],
        $this->model::LOAN_RETURNED => ['label' => 'returned', 'label-color' => 'green']
      ];

      if ($request->ajax()) {
        return Datatables::of($this->model::with(['loan_details', 'pic', 'customer'])->withTrashed())
        ->setRowAttr([
          'style' => function(AssetLoan $loan) {
            if(empty($sales->deleted_at)) return;
            return 'background: #ffb9b9';
          }
        ])
        ->addColumn('total_item', function (AssetLoan $loan) {
          return $loan->loan_details->count();
        })
        ->editColumn('loan_status', function (AssetLoan $loan) use ($loanStatus) {
          return '<small class="label bg-'. $loanStatus[$loan->loan_status]['label-color'] . '">' . $loanStatus[$loan->loan_status]['label'] . '</small>';
        })
        ->editColumn('loan_date', function (AssetLoan $loan) {
          $roleUser = request()->user()->role->name;
          $isSuperAdmin = $roleUser === 'super_admin';

          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-id="'.  $loan->id .'"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $loan->id . '/ajax-form') . '"
          data-is-superadmin="'. $isSuperAdmin . '">
          ' . $loan->loan_date->format('m/d/Y') . ' - ' . $loan->id . '
          </a>';
        })
        ->addColumn('action', function (AssetLoan $loan) {
          return \TransAction::table($this->route, $loan, 'loan_number', $loan->log_print);
        })
        ->rawColumns(['loan_date', 'loan_status', 'transaction_channel', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'loan_date', 'name' => 'loan_date', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'loan_number', 'name' => 'loan_number', 'title' => 'Loan No' ])
      ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
      ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
      ->addColumn([ 'data' => 'loan_status', 'name' => 'loan_status', 'title' => 'Progress Status' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
      ->parameters([
        'initComplete' => 'function() {
          $.getScript("'. asset("js/utomodeck.js") .'");
          $.getScript("'. asset("js/loan/loan-index.js") .'");
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
      $this->params['model']['loan_number'] = \RunningNumber::generate('asset_loans', 'loan_number', \Config::get('transactions.asset_loan.code'));

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
        $loanDetails = $request->loan_details;

        $params = $request->all();
        $params['loan_date'] = date('Y-m-d', strtotime($request->loan_date));
        $params['loan_expiration_date'] = date('Y-m-d', strtotime($request->loan_expiration_date));
        $params['loan_status'] = $this->model::LOAN_REQUEST;

        unset($params['submit'], $params['_token'], $params['loanDetails']);

        $loan = $this->model::create($params);
        if(!empty($loanDetails) && count($loanDetails) > 0) {
          foreach ($loanDetails as $key => $loanDetail) {
            $loanDetail['loan_status'] = $this->model::LOAN_REQUEST;
            $loanDetail['asset_stock_id'] = $loanDetail['asset_stock_id'];
            $loanDetail['quantity'] = 1;

            $loan->loan_details()->create($loanDetail);
          }
        }

        if($submitAction == 'save_print') {
          $redirectOnSuccess .= "?print=" .$loan->id;
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
      $result = $this->model->where('id', $id)->with([
        'sales_details' => function ($query) {
          $query->withTrashed();
        },
        'log_print' => function ($query) {
          $query->with(['employee']);
        }
        ])->withTrashed()->first();

        foreach ($result->sales_details as $salesDetail) {
          $salesDetail->length_options = [];
          $salesDetail->length_selected = $salesDetail->is_custom_length ? 0 : $salesDetail->length;
        }

        $this->params['model'] = $result;
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
        $keeploanDetails = [];
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
          $loanDetails = $request->sales_details;

          $params = $request->all();
          $params['quotation_date'] = date('Y-m-d', strtotime($request->quotation_date));
          $params['quotation_status'] = $this->model::QUOTATION_PENDING;
          $params['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
          $params['transaction_channel'] = $this->model::TRANSACTION_CHANNEL_WEB;
          $params['discount'] = str_replace(',', '', $request->discount);
          $params['downpayment'] = str_replace(',', '', $request->downpayment);

          unset($params['submit'], $params['_token'], $params['salesDetails']);

          $sales = $this->model->where('id', $id)->first();
          $sales->update($params);

          if(!empty($salesDetails) && count($salesDetails) > 0) {
            foreach ($salesDetails as $key => $salesDetail) {
              $id = $salesDetail['id'];
              $itemMaterial = ItemMaterial::find($salesDetail['item_material_id']);
              $item = $itemMaterial->item;

              unset($params['length_selected']);

              $salesDetail['quotation_status'] = $this->model::QUOTATION_PENDING;
              $salesDetail['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
              $salesDetail['is_custom_length'] = $salesDetail['is_custom_length'] == 'true' ? 1 : 0;
              $salesDetail['width'] = $item->width;
              $salesDetail['height'] = $item->height;
              $salesDetail['weight'] = $item->weight*$salesDetail['length'];
              $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
              $salesDetail['price'] = str_replace(',', '', $salesDetail['price']);
              $salesDetail['total_price'] = str_replace(',', '', $salesDetail['total_price']);
              // belum hitung kalau custom length

              $currentSalesDetail = $sales->sales_details()->where('id', $id)->first();

              if(!empty($currentSalesDetail)) {
                $currentSalesDetail->update($salesDetail);
                $keepSalesDetails[] = $currentSalesDetail->id;
                continue;
              }

              $newSalesDetail = $sales->sales_details()->create($salesDetail);
              $keepSalesDetails[] = $newSalesDetail->id;
            }

            // hapus yang gk ada di request
            $sales->sales_details()->whereNotIn('id', $keepSalesDetails)->delete();
          }

          if($submitAction == 'save_print') {
            $redirectOnSuccess .= "?print=" .$sales->id;
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
          $loan = $this->model->find($id);
          $loan->loan_details()->delete();
          $loan->delete();

          DB::commit();
          return response()->json([], 204);
        } catch (\Throwable $th) {
          DB::rollback();
          return response()->json(['message' => $th->getMessage()], 500);
        }
      }

      public function print(Request $request, $id)
      {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        try {
          DB::beginTransaction();
          $loan = $this->model->find($id);
          $params['model'] = $loan;

          if(!empty($loan->log_print)) {
            if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Asset-Loan-' . $loan->quotation_number);
            //print with watermark
            return \PrintFile::copy($this->routeView . '.pdf', $params, 'Asset-Loan-' . $sales->quotation_number);
          }

          //print without watermark
          LogPrint::create([
            'transaction_code' => \Config::get('transactions.asset_loan.code'),
            'transaction_number' => $loan->loan_number,
            'employee_id' => Auth()->user()->id,
            'date' => now()
          ]);

          $loan->loan_details()->update(['loan_status' => $this->model::LOAN_ACCEPT]);
          $loan->loan_status = $this->model::LOAN_ACCEPT;
          $loan->save();

          DB::commit();
          return \PrintFile::original($this->routeView . '.pdf', $params, 'Loan-Asset-' . $loan->loan_number);
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

      private function _validate ($request)
      {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
          // 'sales_id' => ['required'],
          'customer_id' => ['required'],
          'warehouse_id' => ['required'],
          'created_by' => ['required'],
          'loan_number' => ['required', 'unique:asset_loans,loan_number' . $ignoredId],
          'loan_date' => ['required'],
          'loan_expiration_date' => ['required'],
          // 'loan_details.*.loan_status' => ['required'],
          // 'loan_details.*.quantity' => ['required'],
        ]);
      }
    }
