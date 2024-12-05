<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GuzzleRequest;

use App\Models\LogCityDistance;
use App\Models\Master\Warehouse;
use App\Models\Master\City\City;
use App\Models\Master\Profile\ProfileAddress;
use App\Models\Sales\Sales;
use App\Models\Shipping\ShippingCost;

class ShippingCostController extends Controller
{
    private $httpRequest;

    public function __construct ()
    {
      $this->model = new ShippingCost();

      $this->httpRequest = new GuzzleRequest([ 'base_uri' => ENV("MAPBOX_BASEURL", "") ]);
      $this->accessToken = '?access_token=' . env("MAPBOX_TOKEN", "");
    }

    private function _getLongLat($cityName)
    {
      /** 
      * geocoding/v5/mapbox.places/Sidoarjo.json?access_token=pk.eyJ1IjoiYWRkaW5jdyIsImEiOiJjazRnZmhjOXoweXBjM3JvN2o0NXY0Y2J3In0.XbRou55u3AlsKrAt8IFyEg
      * object to get: features[0]->center[0] (lontitude), center[1] (latitude)
      */
      $getLongLat = $this->httpRequest->get('geocoding/v5/mapbox.places/' . $cityName . '.json' . $this->accessToken);
      $resultLongLat = json_decode($getLongLat->getBody());

      return [
        'longtitude' => $resultLongLat->features[0]->center[0],
        'latitude' => $resultLongLat->features[0]->center[1]
      ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      /**
       * flow: check longtitude, latitude empty
       * ya get api -> update -> check distance api
       * tidak, check distance api
       */

      $response = [
        'total_order_weight' => 0,
        'maximum_order_length' => 0,
        'shipping_cost_enforce' => [],
        'distance_in_km' => 0,
        'total_charge' => 0
      ];

      try {
        $sales = Sales::find($request->sales_id);
        $response['total_order_weight'] = $sales->sales_details()->sum('weight');
        $response['maximum_order_length'] = $sales->sales_details()->max('length');

        $shippingCost = ShippingCost::whereRaw("
          (min_weight <= {$response['total_order_weight']} and max_weight >= {$response['total_order_weight']}) and
          (min_length <= {$response['maximum_order_length']} and max_length >= {$response['maximum_order_length']})
        ")->first();

        if(empty($shippingCost)) return response()->json(['message' => 'Perhitungan belum tersedia'], 500);

        $response['shipping_cost_enforce'] = $shippingCost->toArray();

        $itemWH = Warehouse::find($sales->warehouse_id);
        $itemWHCity = $itemWH->region_city;
        
        $userAddress = ProfileAddress::find($request->address_id);
        $userAddressCity = $userAddress->region_city;

        if(empty($itemWHCity->longtitude) && empty($itemWHCity->latitude)) {
          $longLat = $this->_getLongLat($itemWHCity->name);

          $itemWHCity->longtitude = $longLat['longtitude'];
          $itemWHCity->latitude = $longLat['latitude'];
          $itemWHCity->save();
        }
        
        if(empty($UserAddressCity->longtitude) && empty($UserAddressCity->latitude)) {
          $longLat = $this->_getLongLat($userAddressCity->name);

          $userAddressCity->longtitude = $longLat['longtitude'];
          $userAddressCity->latitude = $longLat['latitude'];
          $userAddressCity->save();
        }     

        $logCityDistance = LogCityDistance::whereRaw("
            (departure_id = $itemWHCity->id and arrival_id = $userAddressCity->id) or
            (departure_id = $userAddressCity->id and arrival_id = $itemWHCity->id)
        ")->first();

        if($logCityDistance) {
          $response['distance_in_km'] = $logCityDistance->distance_in_km;
          $response['total_charge'] = $response['distance_in_km']*$response['shipping_cost_enforce']['charge_per_km'];
          return response()->json($response, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

        // https://api.mapbox.com/directions/v5/mapbox/driving/112.73333, -7.23333;112.71733, -7.45303?access_token=pk.eyJ1IjoiYWRkaW5jdyIsImEiOiJjazRnZmhjOXoweXBjM3JvN2o0NXY0Y2J3In0.XbRou55u3AlsKrAt8IFyEg
        $coordinates = $itemWHCity->longtitude . ', ' . $itemWHCity->latitude . ';' . $userAddressCity->longtitude . ', ' . $userAddressCity->latitude;
        $getCityDistance = $this->httpRequest->get('directions/v5/mapbox/driving/' . $coordinates . $this->accessToken);
        $resultCityDistance = json_decode($getCityDistance->getBody());

        if(!empty($resultCityDistance->code) && $resultCityDistance->code == 'NoRoute') {
          return response()->json(['message' => $resultCityDistance->message], 400)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }
        
        $logCityDistance = LogCityDistance::create([
          'departure_id' => $itemWHCity->id,
          'arrival_id' => $userAddressCity->id,
          'distance_in_km' => $resultCityDistance->routes[0]->distance/1000
        ]);

        $response['distance_in_km'] = $logCityDistance->distance_in_km;
        $response['total_charge'] = round($response['distance_in_km']*$response['shipping_cost_enforce']['charge_per_km'], 2);
        return response()->json($response, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage() . $e->getLine()], 500);
      }

    }
}
