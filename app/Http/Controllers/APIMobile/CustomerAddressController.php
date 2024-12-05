<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Models\Master\Profile\ProfileAddress as CustomerAddress;

class CustomerAddressController extends Controller
{
    public function __construct ()
    {
        $this->model = new CustomerAddress();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {   
        $activeUser = $request->user;
        $customerAddress = $this->model->where('profile_id', $activeUser->profile->id)->paginate(5);
        foreach ($customerAddress as $address) {
            //karena tidak pakai kecamatan jadi langsung
            $address->province_id = $address->region_city->province_id;
            $address->city_id = $address->region_id;
        }

        return response()->json($customerAddress)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        //params $id, district_id tidak di pakai, menghindari ambigu;

        try {
            $activeUser = $request->user;

            \DB::beginTransaction();
            
            $validator = Validator::make($request->all(), [
                            'address' => ['required', 'string'],
                        ]);
                
            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->get('*') as $key => $value) {
                    $errorMessages[$key] = implode(', ', $value);
                }

                return response()->json(['message' => $errorMessages], 400);
            }

            $this->_checkStatusDefaultBilling($request['is_default'], $request['is_billing_address']);

            $regionType = !empty($request->district_id) ? User::REGION_TYPE_DISTRICT : User::REGION_TYPE_CITY;
            $regionId = !empty($request->district_id) ? $request->district_id : $request->city_id;

            $customer = $this->model->create([
                'address' => $request['address'],
                'longtitude' => $request['longtitude'],
                'latitude' => $request['latitude'],
                'profile_id' => $activeUser->profile->id,
                'is_default' => !empty($request['is_default']) ? $request['is_default'] : 0,
                'is_billing_address' => !empty($request['is_billing_address']) ? $request['is_billing_address'] : 0,
                'region_type' => $regionType,
                'region_id' => $regionId
            ]);
                
            \DB::commit();
            return response()->json($customer, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function _checkStatusDefaultBilling($isDefault, $isBilling)
    {
        //cek status IsBillingAddress & IsDefault, hanya satu data yang boleh true.
        if(!empty($isDefault) && $isDefault) //jika isDefault true
        {
            $addressDefault = $this->model->where('is_default', 1)->get();
            if($addressDefault->count() > 0)
                $this->model->where('is_default', 1)->update(['is_default' => 0]);
        }

        if(!empty($isBilling) && $isBilling) //jika true
        {
            $addressBilling = $this->model->where('is_billing_address', 1)->get();
            if($addressBilling->count() > 0)
                $this->model->where('is_billing_address', 1)->update(['is_billing_address' => 0]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $addressId)
    {
        try {
            $address = $this->model->find($addressId);
            
            //karena tidak pakai kecamatan jadi langsung
            $address->province_id = $address->region_city->province_id;
            $address->city_id = $address->region_id;
            return response()->json($address, 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $addressId)
    {
        $activeUser = $request->user;

        try {
            $validator = Validator::make($request->all(), [
                'address' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->get('*') as $key => $value) {
                $errorMessages[$key] = implode(', ', $value);
                }
                return response()->json(['message' => $errorMessages], 400);
            }

            $address = $this->model->find($addressId);

            $this->_checkStatusDefaultBilling($request['is_default'], $request['is_billing_address']);

            $regionType = !empty($request->district_id) ? User::REGION_TYPE_DISTRICT : User::REGION_TYPE_CITY;
            $regionId = !empty($request->district_id) ? $request->district_id : $request->city_id;

            $address->update([
                'address' => $request['address'],
                'longtitude' => $request['longtitude'],
                'latitude' => $request['latitude'],
                'profile_id' => $activeUser->profile->id,
                'is_default' => !empty($request['is_default']) ? $request['is_default'] : 0,
                'is_billing_address' => !empty($request['is_billing_address']) ? $request['is_billing_address'] : 0,
                'region_type' => $regionType,
                'region_id' => $regionId
            ]);

            return response()->json($address, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $addressId)
    {
        try {
            $address = $this->model->find($addressId);
            $address->delete();

            return response()->json(['message' => 'alamat telah dihapus.'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
