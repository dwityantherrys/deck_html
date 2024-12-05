<?php

namespace App\Http\Controllers\Master\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Master\Payment\PaymentMethod;

class PaymentMethodController extends Controller
{
    private $route = 'master/payment/method';
    private $routeView = 'master.payment.method';
    private $routeUpload = 'img/payment-method';
    private $availableOptions = [
        PaymentMethod::AVAILABLE_AT_WEB => 'web',
        PaymentMethod::AVAILABLE_AT_APP => 'app',
        PaymentMethod::AVAILABLE_AT_BOTH => 'both'
    ];
    
    private $params = [];

    public function __construct ()
    {
      $this->model = new PaymentMethod();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['availableOptions'] = $this->availableOptions;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $this->params['paymentMethods'] = $this->model->get();
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
            $image = NULL;
            if ($request->hasFile('image')) {
                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            $this->model::create([
                'code' => $request->code,
                'name' => $request->name,
                'image' => $image,
                'rekening_number' => $request->rekening_number,
                'channel' => $request->channel,
                'has_code_rule' => $request->has_code_rule,
                'guide' => $request->guide,
                'available_at' => $request->available_at,
                'is_active' => $request->is_active
            ]);
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            if ($request->hasFile('image')) {
                \Storage::disk('public')->delete($image);
            }
            
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
            $paymentMethod = $this->model::where('id', $id)->first();
            $image = $paymentMethod->image;

            if ($request->hasFile('image')) {
                if ($image) {
                    \Storage::disk('public')->delete($image);
                }

                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            unset($request['_token'], $request['_method'], $request['id']);
            $paymentMethod->update([
                'code' => $request->code,
                'name' => $request->name,
                'image' => $image,
                'rekening_number' => $request->rekening_number,
                'channel' => $request->channel,
                'has_code_rule' => $request->has_code_rule,
                'guide' => $request->guide,
                'available_at' => $request->available_at,
                'is_active' => $request->is_active               
            ]);
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            if ($request->hasFile('image')) {
                \Storage::disk('public')->delete($image);
            }

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
            $paymentMethod = $this->model->find($id);

            if ($paymentMethod->image) {
                \Storage::disk('public')->delete($paymentMethod->image);
            }

            $paymentMethod->delete();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'name' => ['required'],
            'code' => 'required_if:has_code_rule,1|numeric|digits_between:4,5',
            'rekening_number' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048'
        ]);
    }
}
