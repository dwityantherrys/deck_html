<?php

namespace App\Http\Controllers\Master\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Models\Role;
use App\Models\Master\Profile\Profile;
use App\Models\Master\Profile\ProfileAddress;
use App\Models\Master\Profile\ProfileTransactionSetting;
use App\Models\Master\Payment\PaymentMethod;

class CustomerController extends Controller
{
    private $route = 'master/customer';
    private $routeView = 'master.customer';
    private $routeUpload = 'img/customer';
    private $params = [];

    public function __construct ()
    {
      $this->model = new Profile();
      $this->roleNotDisplay = Role::whereIn('name', ['super_admin', 'employee'])->get();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['region_type'] = [
          'city' => User::REGION_TYPE_CITY,
          'district' => User::REGION_TYPE_DISTRICT
      ];
    }

    // di pakai di purchase dan sales, arahin pakai id user saja, biar seragam dengan api
    // relation model untuk purchase dan sales check arahnya seharusnya ke user id
    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and name like '%{$request->searchKey}%'";
      }

      try {
        $results = User::whereRaw($where)
                   ->whereNotIn('role_id', $this->roleNotDisplay->pluck('id'))
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById($id)
    {
      return response()->json(User::find($id), 200);
    }

    public function searchAddressByUserId($id)
    {
        $user = User::find($id);

        $results = $user->profile->addresses()
                    ->get()
                    ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $value) {
            $value->name = $value->address . ' ' . $value->region_city->name;
        }

        $response['results'] = $results;

        return response()->json($response, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $this->params['profiles'] = $this->model->whereHas('user', function ($query) {
                $query->whereNotIn('role_id', $this->roleNotDisplay->pluck('id'));
            }
        )->get();
      return view($this->routeView . '.index', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['paymentMethodOptions'] = PaymentMethod::active()->get();
        $this->params['tempoTypes'] = ProfileTransactionSetting::TEMPO_TYPE;
        $this->params['model'] = $this->model;
        return view($this->routeView . '.create', $this->params);
    }

    public function edit($id)
    {
        $profile = $this->model->with(['addresses', 'transaction_setting', 'application_paylater'])->find($id);

        $user = $profile->user()->with(['region_district.city', 'region_city'])->first();
        $profileTransactionSetting = $profile->transaction_setting()->first();

        foreach ($profile->addresses as $address) {
            $address->province_id = optional($address->region_city)->province_id;
            $address->city_id = $address->region_id;
            $address->load_city_firsttime = true;
        }

        $model = array_merge(
            $user->toArray(),
            $profile->toArray(),
            ['id' => $profile->id ] //initialisasi ulang, karena kereplace id trasaction_setting
        );

        // dd((object) $model);

        $this->params['model'] = (object) $model;

        return view($this->routeView . '.edit', $this->params);
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

            $image = NULL;
            $identityImage = NULL;

            if ($request->hasFile('image')) {
                $image = $request->file('image')->store($this->routeUpload, 'public');
            }else{
                $image = 'img/item/no-image.png';
            }

            $roleAsCustomer = Role::where('name', 'customer')->first()->id;
            $regionType = !empty($request->region_district_id) ?
                            User::REGION_TYPE_DISTRICT : User::REGION_TYPE_CITY;
            $regionId = !empty($request->region_district_id) ?
                            $request->region_district_id : $request->region_city_id;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->email),
                'role_id' => $roleAsCustomer,
            ]);

            $profile = $user->profile()->create([
                'name' => $request->name,
                'image' => $image,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'npwp_number' => $request->npwp_number,
                'identity_number' => $request->identity_number,
                'company_id' => $request->company_id,
                'is_active' => $request->is_active
            ]);

            foreach ($request->addresses as $key => $address) {
                if(empty($address['address'])) continue;

                unset($address['id']);

                $profile->addresses()->create($address);
            }

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route);

        } catch (\Throwable $th) {
            DB::rollback();

            if (!empty($image)) \Storage::disk('public')->delete($image);

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    protected function _tempoReverseHandler ($tempo_type, $tempo) {
        $params = [
            'tempo_type' => $tempo_type,
            'tempo' => [
                'NOT_USED' => [ 'charge_option'   => null, 'charge_extend'   => null ],
                'DAY'      => [ 'charge_option'   => null, 'charge_extend'   => null ],
                'WEEK'     => [ 'charge_option'   => null, 'charge_extend'   => null ],
                'MONTH'    => [ 'charge_option'   => null, 'charge_extend'   => null ]
            ]
        ];

        switch ($tempo_type) {
            case 'NOT_USED':
                // nilai sama dengan default
                break;
            case 'DAY':
                $params['tempo']['DAY']['charge_option']    = $tempo->tempo_charge_day;
                break;
            case 'WEEK':
                $params['tempo']['WEEK']['charge_option']   = $tempo->tempo_charge_week;
                $params['tempo']['WEEK']['charge_extend']   = $tempo->tempo_charge_day;
                break;
            case 'MONTH':
                $params['tempo']['MONTH']['charge_option']  = $tempo->tempo_charge_month;
                $params['tempo']['MONTH']['charge_extend']  = $tempo->tempo_charge_day;
                break;

            default:

                break;
        }

        return $params;
    }

    protected function _tempoHandler ($tempo_type, $tempo_option, $tempo_extend) {
        $params = [
            'tempo_type'    => $tempo_type,
            'tempo_charge_day'     => null,
            'tempo_charge_week'    => null,
            'tempo_charge_month'   => null,
        ];

        switch ($tempo_type) {
            case 'NOT_USED':
                // nilai sama dengan default
                break;
            case 'DAY':
                $params['tempo_charge_day']     = $tempo_option;
                break;
            case 'WEEK':
                $params['tempo_charge_day']     = $tempo_extend;
                $params['tempo_charge_week']    = $tempo_option;
                break;
            case 'MONTH':
                $params['tempo_charge_day']     = $tempo_extend;
                $params['tempo_charge_month']   = $tempo_option;
                break;

            default:

                break;
        }

        return $params;
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $validator = $this->_validate($request->all());
        //
        // if($validator->fails())
        // {
        //     return redirect()
        //         ->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        try {
            DB::beginTransaction();

            $profile = $this->model->find($id);
            $image = $profile->image;
            $identityImage = $profile->identity_image;

            if ($request->hasFile('image')) {
                if ($image) \Storage::disk('public')->delete($image);
                $image = $request->file('image')->store($this->routeUpload, 'public');
            }
            if ($request->hasFile('identity_image')) {
                if ($identityImage) \Storage::disk('public')->delete($identityImage);
                $identityImage = $request->file('identity_image')->store($this->routeUpload, 'public');
            }

            $regionType = !empty($request->region_district_id) ? User::REGION_TYPE_DISTRICT : User::REGION_TYPE_CITY;
            $regionId = !empty($request->region_district_id) ? $request->region_district_id : $request->region_city_id;

            $profile->user()->update([
                'name' => $request->name,
                'email' => $request->email,
                'region_type' => $regionType,
                'region_id' => $regionId
            ]);

            $profile->update([
                'name' => $request->name,
                'image' => $image,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'npwp_number' => $request->npwp_number,
                'identity_number' => $request->identity_number,
                'identity_image' => $identityImage,
                'company_id' => $request->company_id,
                'is_active' => $request->is_active
            ]);

            foreach ($request->addresses as $key => $address) {
                if(empty($address['address'])) continue;

                $id = $address['id'];
                $address['region_type'] = ProfileAddress::REGION_TYPE_CITY;
                $address['region_id'] = $address['city_id'];

                unset($address['id'], $address['province_id'], $address['city_id']);

                $profileAddress = $profile->addresses()->where('id', $id)->first();

                if(!empty($profileAddress)){
                    $profileAddress->update($address);
                    continue;
                }

                $profile->addresses()->create($address);
            }

            // check is allowed paylater
            if($profile->application_paylater && ($profile->application_paylater->status == $profile->application_paylater::APPLICATION_PENDING)) {
                $errorMessages = [];
                $applicationStatusValidation = $profile->application_paylater::APPLICATION_DECLINE;

                // validasi isian limit/price untuk kebutuhan pembayaran paylater
                if(is_null($request->is_allowed_paylater)) $errorMessages[] = "is_allowed_paylater harus diisi customer menunggu persetujuan";
                if($request->is_allowed_paylater == 1) {
                    if(empty($request->limit)) $errorMessages [] = "limit harus di tentukan";
                    if($request->tempo_type == 'NOT_USED') $errorMessages [] = "batas tempo harus di tentukan";

                    if(count($errorMessages) > 0) {
                        DB::rollback();
                        $request->session()->flash('notif', [
                            'code' => 'failed ' . __FUNCTION__ . 'd',
                            'message' => str_replace(".", " ", $this->routeView) . ' : ' . implode(',', $errorMessages),
                        ]);
                        return redirect()->back()->withInput();
                    }

                    $applicationStatusValidation = $profile->application_paylater::APPLICATION_ACCEPT;
                }

                $profile->application_paylater()->update([
                    'status' => $applicationStatusValidation,
                    'date_validation' => date('Y-m-d H:i:s')
                ]);
            } else if ($profile->application_paylater) {
                $applicationStatusValidation = ($request->is_allowed_paylater == 1) ?
                    $profile->application_paylater::APPLICATION_ACCEPT :
                    $profile->application_paylater::APPLICATION_DECLINE;

                $profile->application_paylater()->update([
                    'status' => $applicationStatusValidation,
                    'date_validation' => date('Y-m-d H:i:s')
                ]);
            }

            $tempoType = $request->tempo_type;
            $tempo = $this->_tempoHandler($tempoType, $request->tempo[$tempoType]['charge_option'], $request->tempo[$tempoType]['charge_extend']);

            $params = array_merge($tempo, [
                    'is_allowed_paylater' => !is_null($request->is_allowed_paylater) ? $request->is_allowed_paylater : 0,
                    'limit' => $request->limit,
                    'payment_method_id' => $request->payment_method_id,
                    'markdown_sales' => $request->markdown_sales,
                    'markdown_purchase' => $request->markdown_purchase,
                    'minimum_downpayment' => $request->minimum_downpayment,
                    'created_by' => $request->user()->id
                ]);

            $profile->transaction_setting()->update($params);

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route);

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
            $profile = $this->model->find($id);
            $userId = $profile->user_id;
            $image = $profile->image;
            $identityImage = $profile->identity_image;

            if($profile->addresses()) $profile->addresses()->delete();
            if($profile->transaction_setting()) $profile->transaction_setting()->delete();
            $profile->delete();
            User::find($userId)->delete();

            if (!empty($image)) \Storage::disk('public')->delete($image);
            if (!empty($identityImage)) \Storage::disk('public')->delete($identityImage);


            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json($th, 500);
        }
    }

    private function _validate ($request)
    {
        $ignoredProfileId = !empty($request['id']) ? ','.$request['id'] : '';
        $ignoredUserId = !empty($request['id']) ? ','.$this->model->find($request['id'])->user_id : '';

        return Validator::make($request, [
            'name' => ['required'],
            // 'email' => ['required', 'unique:users,email' . $ignoredUserId, 'email'],
            // 'phone' => ['required', 'unique:profiles,phone' . $ignoredProfileId, 'numeric', 'digits_between:7,12'],
            // 'identity_number' => ['nullable', 'unique:profiles,identity_number' . $ignoredProfileId, 'numeric', 'digits:16'],
        ]);
    }
}
