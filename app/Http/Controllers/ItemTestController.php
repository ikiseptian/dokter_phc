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
 *     summary="Get all Item Tests or search by ID or query (Urine filter)",
 *     tags={"Item Test"},
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         required=false,
 *         description="Search by exact ID (integer match)",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         required=false,
 *         description="Search by text query (LIKE match, 'urine' filter applied)",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="A list of Item Tests (filtered by ID or Urine query)",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/ItemTest")
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    $id = $request->query('id');
    $query = $request->query('query');

    $items = ItemTest::where('gcrecord', 0)
        ->when($id, function ($q) use ($id) {
            $q->where('ID', $id); // Jika ID diberikan, cari berdasarkan ID saja
        })
        ->when(!$id && $query, function ($q) use ($query) {
            if (stripos($query, 'urine') !== false) {
                $q->where('Descriptions', 'LIKE', "%urine%");
            } else {
                $q->where('ItemTestCode', 'LIKE', "%{$query}%")
                    ->orWhere('ItemTestName', 'LIKE', "%{$query}%")
                    ->orWhere('Group', 'LIKE', "%{$query}%")
                    ->orWhere('SubGroup', 'LIKE', "%{$query}%")
                    ->orWhere('Descriptions', 'LIKE', "%{$query}%");
            }
        });

    // Dapatkan total jumlah data sebelum mengambil data
    $totalData = $items->count();

    $formattedData = $items->get()->map(function ($item) {
        return [
            'ID' => $item->ID,
            'ItemTestCode' => $item->ItemTestCode,
            'ItemTestName' => $item->ItemTestName,
            'Group' => $item->Group,
            'SubGroup' => $item->SubGroup,
            'Descriptions' => $item->Descriptions,
            'CreateDate' => $item->CreateDate,
            'CreateBy' => $item->CreateBy,
            'LastModifiedDate' => $item->LastModifiedDate,
            'LastModifiedBy' => $item->LastModifiedBy,
        ];
    });

    return response()->json([
        'totaldata' => $totalData,
        'data' => $formattedData
    ], 200);
}


    /**
     * @OA\Post(
     *     path="/api/itemtest",
     *     summary="Tambah data item baru",
     *     description="Menambahkan item baru ke dalam database.",
     *     tags={"Item Test"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ItemTestCode", "ItemTestName"},
     *             @OA\Property(property="ItemTestCode", type="string", example="ITC-001"),
     *             @OA\Property(property="ItemTestName", type="string", example="Test Hemoglobin"),
     *             @OA\Property(property="Group", type="string", example="Hematologi"),
     *             @OA\Property(property="SubGroup", type="string", example="Darah"),
     *             @OA\Property(property="Descriptions", type="string", example="Tes kadar hemoglobin dalam darah"),
     *             @OA\Property(property="CreateBy", type="string", example="admin"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item berhasil ditambahkan",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ItemTest")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */

     
     
    

public function store(Request $request)
{
    // Validasi input
    $validated = $request->validate([
        'ItemTestCode' => 'required|string|max:10|unique:Item_Test,ItemTestCode', // Validasi unik
        'ItemTestName' => 'required|string|max:30',
        'Group' => 'nullable|string|max:30',
        'SubGroup' => 'nullable|string|max:30',
        'Descriptions' => 'nullable|string|max:250',
        'CreateBy' => 'nullable|string|max:20',
        'gcrecord' => 'nullable|boolean',
    ]);

    // Set timezone secara eksplisit
    date_default_timezone_set('Asia/Jakarta');

    // Tambahkan CreateDate dengan format timestamp agar cocok dengan kolom datetime di database
    $validated['CreateDate'] = now()->format('Y-m-d H:i:s');

    // Jika gcrecord tidak dikirim, set default ke false
    $validated['gcrecord'] = $validated['gcrecord'] ?? false;

    // Simpan ke database
    $item = ItemTest::create($validated);

    // Response body dengan format yang benar
    return response()->json([
        'message' => 'Item created successfully',
        'data' => [
            'ID' =>$item->ID,
            'ItemTestCode' => $item->ItemTestCode,
            'ItemTestName' => $item->ItemTestName,
            'Group' => $item->Group,
            'SubGroup' => $item->SubGroup,
            'Descriptions' => $item->Descriptions,
            'CreateDate' => $item->CreateDate, // Jangan panggil format() karena sudah dalam format datetime
            'CreateBy' => $item->CreateBy,
        ]
    ], 201);
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
 *     description="Memperbarui item test berdasarkan ID.",
 *     tags={"Item Test"},
 *     @OA\Parameter(
 *         name="ID",
 *         in="path",
 *         required=true,
 *         description="ID dari Item Test yang akan diperbarui",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"ItemTestCode", "ItemTestName"},
 *             @OA\Property(property="ItemTestName", type="string", example="Test Hemoglobin"),
 *             @OA\Property(property="Group", type="string", example="Hematologi"),
 *             @OA\Property(property="SubGroup", type="string", example="Darah"),
 *             @OA\Property(property="Descriptions", type="string", example="Tes kadar hemoglobin dalam darah"),
 *             @OA\Property(property="LastModifiedBy", type="string", example="admin"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Item berhasil diperbarui",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Item updated successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/ItemTest")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Item Test tidak ditemukan"),
 *     @OA\Response(response=400, description="Bad request")
 * )
 */
public function update(Request $request, $id)
{
    // Cari item berdasarkan ID
    $item = ItemTest::find($id);

    if (!$item) {
        return response()->json(['message' => 'Item not found'], 404);
    }

    $validated = $request->validate([
        'ItemTestCode' => 'string|max:10',
        'ItemTestName' => 'required|string|max:30',
        'Group' => 'nullable|string|max:30',
        'SubGroup' => 'nullable|string|max:30',
        'Descriptions' => 'nullable|string|max:250',
        'LastModifiedBy' => 'nullable|string|max:250',
        'gcrecord' => 'nullable|boolean',
    ]);

    // Set nilai `LastModifiedDate` dan `LastModifiedBy`
    date_default_timezone_set('Asia/Jakarta');

    // Tambahkan CreateDate dengan format timestamp agar cocok dengan kolom datetime di database
    $validated['LastModifiedDate'] = now()->format('Y-m-d H:i:s');

    // $validated['LastModifiedBy'] = $request->input('CreateBy', 'admin');

    // Update item
    $item->update($validated);

    // Sembunyikan field `CreateDate` dalam response
    return response()->json($item->only([ 
        'ID',
        'ItemTestCode',
        'ItemTestName',
        'Group',
        'SubGroup',
        'Descriptions',
        'LastModifiedDate',
        'LastModifiedBy'
    ]), 200);
}
}