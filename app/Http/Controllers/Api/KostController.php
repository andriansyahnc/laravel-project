<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Kost\KostRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Validator;

class KostController extends Controller
{
    protected $kostRepository;

    public function __construct(Container $app)
    {
        $this->kostRepository = $app->make(KostRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        try {
            $params = [
                'search' => $this->kostRepository->buildSearchParams($request),
                'sort' => $this->kostRepository->buildSortParams($request),
            ];
            $kosts = $this->kostRepository->findByParams($params);
            return response()->json([
                "status" => true,
                "data" => $kosts,
            ], 200);
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if (!$request->user()->tokenCan('owner')) {
                return response()->json([
                    'status' => false,
                    'error' => 'Forbidden',
                ], 403);
            }
            $kosts = $this->kostRepository->findByOwner($request->user()->id);
            return response()->json([
                "status" => true,
                "data" => $kosts,
            ], 200);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (!$request->user()->tokenCan('owner')) {
                return response()->json([
                    'status' => false,
                    'error' => 'Forbidden',
                ], 403);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:30',
                'description' => 'required|max:100',
                'room_area' => 'required|numeric|min:1',
                'location' => 'required|',
                'price' => 'required|numeric|min:0'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "error" => $validator->errors(),
                ], 422);
            }
            $input = $request->only('name', 'description', 'room_area', 'location', 'price');
            $kost = $this->kostRepository->store($input, $request->user()->id);
    
            return response()->json([
                "status" => true,
                "data" => $kost,
            ], 200);
        } catch (\Exception $e) {
            throw $e;
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
        try {
            $kosts = $this->kostRepository->findById($id);
            return response()->json([
                "status" => true,
                "data" => $kosts,
            ], 200);
        } catch (\Exception $e) {
            throw $e;
        }
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
        try {
           
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
           
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
