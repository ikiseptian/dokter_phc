<?php

namespace App\Http\Controllers;

use App\Models\SupportService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SupportServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/supportservice",
     *     summary="Get all Support Services",
     *     description="Retrieve all support services, with an optional search parameter.",
     *     tags={"SupportService"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search support services by code or name",
     *         required=false,
     *         @OA\Schema(type="string", example="Health")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SupportService")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = SupportService::whereNull('gcrecord')->orWhere('gcrecord', 0); 
    
        // Tambahkan parameter pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('SupportServiceCode', 'LIKE', "%{$search}%")
                  ->orWhere('SupportServiceName', 'LIKE', "%{$search}%");
            });
        }
    
        $supportServices = $query->get();
        return response()->json($supportServices, 200);
    }
    

    /**
     * @OA\Post(
     *     path="/api/supportservice",
     *     summary="Create a new Support Service",
     *     tags={"SupportService"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SupportService")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SupportService created",
     *         @OA\JsonContent(ref="#/components/schemas/SupportService")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'SupportServiceCode' => 'nullable|string|max:200',
            'SupportServiceName' => 'nullable|string|max:200',
            'CreateDate' => 'nullable|date',
            'CreateBy' => 'nullable|string|max:20',
            'LastModifiedDate' => 'nullable|date',
            'LastModifiedBy' => 'nullable|string|max:20',
            'gcrecord' => 'nullable|boolean',
        ]);

        $supportService = SupportService::create($validated);
        return response()->json($supportService, 201);
    }
}
