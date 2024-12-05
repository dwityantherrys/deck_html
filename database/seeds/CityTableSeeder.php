<?php

use Illuminate\Database\Seeder;
use GuzzleHttp\Client as GuzzleRequest;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\DB;

use App\Models\Master\City\Province;
use App\Models\Master\City\City;
use App\Models\Master\City\District;

class CityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $httpRequest = new GuzzleRequest([
	        'base_uri' => 'https://api.rajaongkir.com/starter/',
	        'headers' => ['key' => '3d082ba7020078ab750b733c65aa81ab']
	    ]);

	    $respProvinces = $httpRequest->get('province');
	    $resultProvinces = json_decode($respProvinces->getBody());
	    $resultProvinces = $resultProvinces->rajaongkir->results;

	    $respCities = $httpRequest->get('city');
	    $resultCities = json_decode($respCities->getBody());
	    $resultCities = $resultCities->rajaongkir->results;

		// $reqDistricts = function ($cities) use ($httpRequest) {

		//     foreach ($cities as $city) {
		//     	$uri = 'subdistrict?city=' . $city->id;

		//         yield function() use ($httpRequest, $uri) {
		//             return $httpRequest->getAsync($uri);
		//         };
		//     }

		// };
		// $poolDistricts = new Pool($httpRequest, $reqDistricts(City::all()));
		// $promise = $poolDistricts->promise();
		// $respDistricts = $promise->wait();

		DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

	    DB::beginTransaction();

	    District::truncate();
	    City::truncate();
	    Province::truncate();

	    foreach ($resultProvinces as $result) {
	        Province::create(['id' => $result->province_id, 'name' => $result->province]);
	    }
	    foreach ($resultCities as $result) {
	        City::create([
	            'id' => $result->city_id,
	            'name' => $result->city_name,
	            'type' => $result->type,
	            'postal_code' => $result->postal_code,
	            'province_id' => $result->province_id
	        ]);
	    }
	    // foreach ($respDistricts as $indexRespDistrict) {
	    // 	$resultDistricts = json_decode($respDistricts[$indexRespDistrict]->getBody());
	    // 	$resultDistricts = $resultDistricts->rajaongkir->results;

	    //     foreach ($resultDistricts as $result) {
	    //         District::create([
	    //             'id' => $result->subdistrict_id,
	    //             'name' => $result->subdistrict_name,
	    //             'type' => $result->type,
	    //             'city_id' => $result->city_id
	    //         ]);
	    //     }
		// }
		
		// $this->call('UserTableSeeder');
        // $this->command->info('User table seeded!');

        // $path = 'app/developer_docs/countries.sql';
        // DB::unprepared(file_get_contents($path));
        // $this->command->info('Country table seeded!');


		DB::commit();
		
		DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
