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
     *     summary="Get all Item Tests",
     *     @OA\Response(
     *         response=200,
     *         description="A list of Item Tests",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ItemTest")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $items = ItemTest::all();
        return response()->json($items);
    }

    /**
     * @OA\Post(
     *     path="/api/itemtest",
     *     summary="Create a new Item Test",
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
}
