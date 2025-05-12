<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\City;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{


	public function __construct(
		protected InventoryService $inventoryService
	) {
	}

	public function index()
	{
		try {
			$inventories = $this->inventoryService->getAllInventories();

			return response()->success(
				$inventories,
				Response::HTTP_OK
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_OK
			);
		}
	}

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function getProductsStock(Request $request)
	{
		//  $inventory_id = request('inventory_id');
		$inventory_id = auth()->guard('api-employees')->user()->inventory_id;
		// return $user;

		$filter_data = $request->only(['sku_code', 'product_name', 'price_min', 'price_max']);

		$inventories = $this->inventoryService->getInventoryProducts($filter_data, $inventory_id);

		return response()->success([
			'inventories' => $inventories
		], Response::HTTP_OK);
	}

	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function create(Request $request)
	{
		//
	}

	/**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function store(Request $request)
	{
		try {
			$validate = Validator::make(
				$request->all(),
				[
					'name' => 'required|string|max:30',
					'code' => 'required|string|max:30|unique:inventories,code',
					'city' => 'required_without:city_id|string|max:255|unique:inventories,city',
					'city_id' => 'required_without:city|integer|exists:cities,id|unique:inventories,city_id',                              
				],
				[
					'name.required' => 'this field is required',
					'code.max' => 'this field maximun length is 255',
					'city.required' => 'this field is required',
					'city.max' => 'this field maximun length is 255',                    
					'code.required' => 'this field is required',
					'code.max' => 'this field maximun length is 255',
				]
			);

			if ($validate->fails()) {
				return response()->error(
					$validate->errors(),
					Response::HTTP_UNPROCESSABLE_ENTITY
				);
			}

			$validated_data = $validate->validated();
			if(isset($validated_data['city'])){
				$city = City::where('name->en',$validated_data['city'])->orWhere('name->ar',$validated_data['city'])->first();
			}
			if(isset($validated_data['city_id'])){
				$city = City::where('id', $validated_data['city_id'])->first();
			}
			$city_id = optional($validated_data)['city_id'] ?? optional($city)->id;
			$inventory = $this->inventoryService->createInventory($validated_data, $city);

			return response()->success(
				[
					'inventory' => $inventory
				],
				Response::HTTP_CREATED
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_OK
			);
		}
	}

	/**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
	public function show()
	{
		try {
			$inventory_id = request('inventory_id');
			$inventory = $this->inventoryService->getInventory($inventory_id);

			return response()->success(
				[
					'inventory' => $inventory
				],
				Response::HTTP_FOUND
			);
		} catch (Exception $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND
			);
		}
	}

	/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
	public function edit(Inventory $inventory)
	{
		//
	}

	/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
	public function update(Request $request)
	{
		try {
			$inventory_id = request('inventory_id');
			$validate = Validator::make(
				$request->all(),
				[
					'name' => 'sometimes|string|max:255',
					'code' => 'sometimes|string|max:255',
					'city_ar' => 'sometimes|string|max:255',
					'city_en' => 'sometimes|string|max:255',
				],
				[
					'name.sometimes' => 'this name is sometimes',
					'code.sometimes' => 'this code is sometimes',
					'city_ar.sometimes' => 'this city at is sometimes',
					'city_en.sometimes' => 'this city en is sometimes',
				]
			);

			if ($validate->fails()) {
				return response()->error(
					$validate->errors(),
					422
				);
			}

			return $inventory = $this->inventoryService->updateInventory($validate->validated(), $inventory_id);

			return response()->success(
				[
					'message' => 'Inventory updated successfully'
				],
				Response::HTTP_OK
				// OR HTTP_NO_CONTENT
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_OK
			);
		}
	}

	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
	public function destroy()
	{
		try {
			$inventory_id = request('inventory_id');
			$inventoryService = $this->inventoryService->delete($inventory_id);

			return response()->success(
				[
					'message' => 'Inventory deleted successfully'
				],
				Response::HTTP_OK
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_OK
			);
		}
	}

	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
	public function forceDelete()
	{
		try {
			$inventory_id = request('inventory_id');
			$inventoryService = $this->inventoryService->forceDelete($inventory_id);

			return response()->success(
				[
					'message' => 'Inventory deleted successfully'
				],
				Response::HTTP_OK
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_OK
			);
		}
	}

	public function getInventoryCount()
	{
		$employee = auth('api-employees')->user();

		if(!$employee){
			return response()->error('Unauthrized',401);
		}

		if ($employee->has_role('main_admin')){
			$inventory_id = request('inventory_id');
			if ($inventory_id == 0) {$inventory_id = null;}
		}else{
			$inventory_id = $employee->inventory_id;
		}
		// $dateScope=request('date_scope');
		// $from_date=request('from_date');
		// $to_date=request('to_date');

		$counts = $this->inventoryService->getInventoryCount($employee, $inventory_id);

		return response()->success(
			$counts,
			Response::HTTP_OK
		);
	}

	public function update_region(Request $request){
		$request->validate([
			'inventory_id' => 'required|exists:inventories,id',
			'polygon' => 'required|array',
		]);

		$inventory = Inventory::findOrFail($request->inventory_id);

		// تحويل إحداثيات `region` إلى WKT POLYGON

		$inventory->update([
        'polygon' => json_encode($request->polygon)
		]);

		return response()->json([
			'message' => 'تم تحديث بيانات المستودع بنجاح',
			'inventory' => $inventory
		]);

	}

	public function get_regions()
	{
		$inventories = Inventory::whereNotNull('polygon')
			->select('id', 'name', 'polygon')
			->get()
			->map(function ($inventory) {
				// تحويل النص `polygon` إلى مصفوفة
				$polygonArray = json_decode($inventory->polygon, true);

				// تحويل كل نقطة إلى كائن يحتوي على lng و lat
				$formattedPolygon = array_map(function ($point) {
					return ['lng' => $point[0], 'lat' => $point[1]];
				}, $polygonArray);

				// تحديث قيمة `polygon` بعد التنسيق
				$inventory->polygon = $formattedPolygon;
				return $inventory;
			});

		return response()->success($inventories, 200);
	}

	/**
 * تحويل `POLYGON` إلى Array من الإحداثيات
 */
	private function convertPolygonToCoordinates($region)
{
    // تحويل بيانات POLYGON إلى GeoJSON باستخدام ST_AsGeoJSON
    $geometry = DB::select(DB::raw("SELECT ST_AsGeoJSON(?) AS geojson", [$region]));
    
    // الآن نقوم بفك تشفير الـ GeoJSON
    $coords = [];
    if ($geometry && isset($geometry[0]->geojson)) {
        $geometryData = json_decode($geometry[0]->geojson);
        foreach ($geometryData->coordinates[0] as $coordinate) {
            $coords[] = [$coordinate[0], $coordinate[1]]; // [longitude, latitude]
        }
    }

    return $coords;
}

	public function search()
	{

		$key = request('key');
		// $dateScope=request('date_scope');
		// $from_date=request('from_date');
		// $to_date=request('to_date');

		$counts = $this->inventoryService->search($key);

		return response()->success(
			$counts,
			Response::HTTP_OK
		);
	}


	public function addToGroup()
	{
		try {

			$product_ids = request('product_ids');
			$group_id = request('group_id');
			$process = $this->inventoryService->addToGroup($product_ids, $group_id);

			return response()->success(
				"Products are added to the group",
				Response::HTTP_OK
			);
		} catch (Exception $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND,
			);
		}
	}

	public function assignToSubCategory()
	{
		try {
			$sub_id = request('sub_id');
			$product_ids = request('product_ids');

			$process = $this->inventoryService->assignToSubCategory($sub_id, $product_ids);

			return response()->success(
				'Products are assigned to another sub category',
				Response::HTTP_OK
			);
		} catch (\Throwable $th) {
			return $th->getMessage();
		}
	}
}
