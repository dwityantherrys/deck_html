<?php

namespace App\Http\Controllers\Master\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Master\Payment\PaymentBankChannel;

class PaymentBankChannelController extends Controller
{
    private $route = 'master/payment/bank-channel';
    private $routeView = 'master.payment.bank-channel';
    private $params = [];

    public function __construct ()
    {
      $this->model = new PaymentBankChannel();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $this->params['bankChannels'] = $this->model->get();
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
            \DB::beginTransaction();

            $this->model::create([
                'name' => $request->name,
                'rekening_name' => $request->rekening_name,
                'rekening_number' => $request->rekening_number,
                'kode_akun' => $request->kode_akun,
                'is_active' => $request->is_active
            ]);

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            \DB::commit();

            return redirect($this->route);

        } catch (\Throwable $th) {
            \DB::rollback();

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
            \DB::beginTransaction();

            $bankChannel = $this->model::where('id', $id)->first();

            unset($request['_token'], $request['_method'], $request['id']);
            $bankChannel->update([
                'name' => $request->name,
                'rekening_name' => $request->rekening_name,
                'rekening_number' => $request->rekening_number,
                'kode_akun' => $request->kode_akun,
                'is_active' => $request->is_active
            ]);

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            \DB::commit();

            return redirect($this->route);

        } catch (\Throwable $th) {
            \DB::rollback();

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
            $bankChannel = $this->model->find($id);
            $bankChannel->delete();

            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'name' => ['required'],
            'rekening_name' => ['required'],
            'rekening_number' => ['required'],
        ]);
    }
}
