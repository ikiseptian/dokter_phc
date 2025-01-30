<?php

namespace App\Http\Controllers;

use App\Models\ItemTest;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ItemTestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/itemtest",
     *     summary="Get all Item Tests or search by query",
     *     tags={"Item Test"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=false,
     *         description="Search query for all fields",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Item Tests (filtered if query is provided)",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ItemTest")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = $request->query('query');

        $items = ItemTest::where('gcrecord', 0)
            ->when($query, function ($q) use ($query) {
                $q->where('ItemTestCode', 'LIKE', "%{$query}%")
                  ->orWhere('ItemTestName', 'LIKE', "%{$query}%")
                  ->orWhere('Group', 'LIKE', "%{$query}%")
                  ->orWhere('SubGroup', 'LIKE', "%{$query}%")
                  ->orWhere('Descriptions', 'LIKE', "%{$query}%");
            })
            ->get();

        return response()->json($items);
    }


    /**
     * @OA\Post(
     *     path="/api/itemtest",
     *     summary="Create a new Item Test",
     *      tags={"Item Test"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ItemTest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The created Item Test",
     *         @OA\JsonContent(ref="#/components/schemas/ItemTest")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'ItemTestCode' => 'required|string|max:10',
            'ItemTestName' => 'required|string|max:30',
            'Group' => 'nullable|string|max:30',
            'SubGroup' => 'nullable|string|max:30',
            'Descriptions' => 'nullable|string|max:250',
            'CreateDate' => 'nullable|date',
            'CreateBy' => 'nullable|string|max:20',
            'LastModifiedDate' => 'nullable|date',
            'LastModifiedBy' => 'nullable|string|max:20',
            'gcrecord' => 'nullable|boolean',
        ]);

        $item = ItemTest::create($request->all());

        return response()->json($item, 201);
    }
    public function dashboard()
    {
        // Pastikan path view sesuai dengan lokasi file
        return view('pasien.dashboard');
    }


        /**
     * @OA\Put(
     *     path="/api/itemtest/{ID}",
     *     summary="Update an existing Item Test",
     *      tags={"Item Test"},
     *     @OA\Parameter(
     *         name="ID",
     *         in="path",
     *         required=true,
     *         description="ID of the Item Test to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ItemTest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated Item Test",
     *         @OA\JsonContent(ref="#/components/schemas/ItemTest")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item Test not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
{
    // Menggunakan where dengan ID besar
    $item = ItemTest::where('ID', $id)->where('gcrecord', 0)->first();

    if (!$item) {
        return response()->json(['message' => 'Item Test not found'], 404);
    }

    $request->validate([
        'ItemTestCode' => 'sometimes|string|max:10',
        'ItemTestName' => 'sometimes|string|max:30',
        'Group' => 'nullable|string|max:30',
        'SubGroup' => 'nullable|string|max:30',
        'Descriptions' => 'nullable|string|max:250',
        'CreateDate' => 'nullable|date',
        'CreateBy' => 'nullable|string|max:20',
        'LastModifiedDate' => 'nullable|date',
        'LastModifiedBy' => 'nullable|string|max:20',
        'gcrecord' => 'nullable|boolean',
    ]);

    $item->update($request->all());

    return response()->json($item);
}

}
