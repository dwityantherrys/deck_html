<?php

namespace App\Http\Controllers\Mobile\Voucher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Mobile\Voucher\Voucher;
use App\Models\Mobile\Voucher\VoucherUsage;

class VoucherController extends Controller
{
    private $route = 'mobile/voucher/voucher';
    private $routeView = 'mobile.voucher.voucher';
    private $params = [];

    public function __construct ()
    {
      $this->model = new Voucher();

      $this->params['limitTypes'] = [
        $this->model::LIMIT_TYPE_ONCE => 'hanya sekali',
        $this->model::LIMIT_TYPE_DAILY => 'harian',
        $this->model::LIMIT_TYPE_WEEKLY => 'mingguan',
        $this->model::LIMIT_TYPE_MONTHLY => 'bulanan'
      ];
    
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];
      $filtered = [];

      if ($request->searchKey) {
        $where .= " and (name like '%{$request->searchKey}%' or code like '%{$request->searchKey})%";
      }

      try {
        $results = $this->model->whereRaw($where)
                   ->active()
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $result) {
           if(!$result->is_multiple_usage && $result->voucher_usages()->where('status_usage', VoucherUsage::STATUS_USED)->count() > 0) continue;

           $result->name = $result->name . ' (' . $result->code . ')';
           $filtered[] = $result;
        }           

        $response['results'] = $filtered;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById($id)
    {
      return response()->json($this->model->find($id), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $this->params['vouchers'] = $this->model->get();
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
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $params = $request->all();
            $params['value_type'] = Voucher::TYPE_DISCOUNT_SALES;
            $params['start_date'] = date('Y-m-d', strtotime($request->start_date));
            if(!empty($request->expiration_date)) $params['expiration_date'] = date('Y-m-d', strtotime($request->expiration_date));

            $this->model::create($params);
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
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
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $params = $request->all();
            $params['value_type'] = Voucher::TYPE_DISCOUNT_SALES;
            $params['start_date'] = date('Y-m-d', strtotime($request->start_date));
            if(!empty($request->expiration_date)) $params['expiration_date'] = date('Y-m-d', strtotime($request->expiration_date));

            unset( $params['_token'], $params['_method'], $params['id'] );

            $this->model::where('id', $id)->update($params);

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
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
            $this->model->find($id)->delete();

            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
            'name' => 'required',
            'code' => 'required|unique:vouchers,code' . $ignoredId . '|alpha_num',
            'value' => 'required|numeric',
            'start_date' => 'required',
            'is_active' => 'required',
            // 'is_multiple_usage' => 'required'
        ]);
    }
}
