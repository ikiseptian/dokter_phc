<?php

namespace App\Http\Controllers;

use App\Models\LabTransDetail;
use App\Models\Lab_Trans;
// use App\Models\LadTransDetail;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LabTransDetailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/labtransdetail",
     *     tags={"Lab Detail"},
     *     summary="Get all Lab Trans with a single search parameter",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Search by exact ID (integer match)"
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search by text query (LIKE match), filtering for 'urine' if provided"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered list of Lab Trans",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
     *             @OA\Property(property="totaldata", type="integer", example=10),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="ID", type="integer", example=1),
     *                     @OA\Property(property="LabTransID", type="integer", example=101),
     *                     @OA\Property(property="ItemTestID", type="integer", example=201),
     *                     @OA\Property(
     *                         property="itemTest",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="ItemTestCode", type="string", example="T-001"),
     *                         @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
     *                         @OA\Property(property="Group", type="string", example="Hematology"),
     *                         @OA\Property(property="SubGroup", type="string", example="Blood"),
     *                         @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level")
     *                     ),
     *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
     *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
     *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
     *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
     *                     @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-08 12:00:00"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="editor")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data not found"),
     *             @OA\Property(property="total", type="integer", example=0),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
public function index(Request $request)
{
    $id = $request->query('id');
    $query = $request->query('query');

    $labTransDetails = LabTransDetail::with(['labTrans', 'itemTest'])
        ->where('gcrecord', 0)
        ->when($id, function ($q) use ($id) {
            $q->where('ID', $id); // Filter berdasarkan ID (gunakan "=")
        })
        ->when(!$id && $query, function ($q) use ($query) {
            if (stripos($query, 'urine') !== false) {
                // Jika query berisi "urine", filter hanya deskripsi yang mengandung "urine"
                $q->whereHas('itemTest', function ($subQuery) {
                    $subQuery->where('Descriptions', 'LIKE', '%urine%');
                });
            } else {
                // Jika query bukan "urine", cari di beberapa kolom menggunakan LIKE
                $q->where('ResultValue', 'LIKE', "%{$query}%")
                    ->orWhere('Unit', 'LIKE', "%{$query}%")
                    ->orWhere('ReferenceValue', 'LIKE', "%{$query}%")
                    ->orWhereHas('labTrans', function ($subQuery) use ($query) {
                        $subQuery->where('LabNumber', 'LIKE', "%{$query}%");
                    })
                    ->orWhereHas('itemTest', function ($subQuery) use ($query) {
                        $subQuery->where('ItemTestName', 'LIKE', "%{$query}%");
                    });
            }
        })
        ->get();

    // Hitung total data setelah filter
    $total = $labTransDetails->count();

    if ($labTransDetails->isEmpty()) {
        return response()->json([
            'message' => 'Data not found',
            'total' => 0,
            'data' => []
        ], 404);
    }

    // Format respons
    $formattedData = $labTransDetails->map(function ($detail) {
        return [
            'ID' => $detail->ID,
            'LabTransID' => $detail->LabTransID,
            'ItemTestID' => $detail->ItemTestID,
            'itemTest' => $detail->itemTest ? [
                'ItemTestCode'   => $detail->itemTest->ItemTestCode,
                'ItemTestName'   => $detail->itemTest->ItemTestName,
                'Group'          => $detail->itemTest->Group,
                'SubGroup'       => $detail->itemTest->SubGroup,
                'Descriptions'   => $detail->itemTest->Descriptions,
            ] : null,
            'ResultValue' => $detail->ResultValue,
            'Unit' => $detail->Unit,
            'ReferenceValue' => $detail->ReferenceValue,
            'ResultNotes' => $detail->ResultNotes,
            'CreateDate' => $detail->CreateDate,
            'CreateBy' => $detail->CreateBy,
            'LastModifiedDate' => $detail->LastModifiedDate,
            'LastModifiedBy' => $detail->LastModifiedBy,
        ];
    });

    return response()->json([
        'message' => 'Data berhasil diambil',
        'totaldata' => $total,
        'data' => $formattedData
    ], 200);
}

     
    /**
 * @OA\Post(
 *     path="/api/labtransdetail",
 *     tags={"Lab Detail"},
 *     summary="Create a new LabTransDetail",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="LabTransID", type="integer", example=5),
 *             @OA\Property(property="ItemTestID", type="integer", example=10),
 *             @OA\Property(property="ResultValue", type="string", example="Positive"),
 *             @OA\Property(property="Unit", type="string", example="mg/dL"),
 *             @OA\Property(property="ReferenceValue", type="string", example="70-110"),
 *             @OA\Property(property="ResultNotes", type="string", example="Normal Range"),
 *             @OA\Property(property="CreateBy", type="string", example="Admin")
 *         )
 *     ),
 *    @OA\Response(
     *         response=200,
     *         description="Filtered list of Lab Trans",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
     *             @OA\Property(property="totaldata", type="integer", example=10),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="ID", type="integer", example=1),
     *                     @OA\Property(property="LabTransID", type="integer", example=101),
     *                     @OA\Property(property="ItemTestID", type="integer", example=201),
     *                     @OA\Property(
     *                         property="itemTest",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="ItemTestCode", type="string", example="T-001"),
     *                         @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
     *                         @OA\Property(property="Group", type="string", example="Hematology"),
     *                         @OA\Property(property="SubGroup", type="string", example="Blood"),
     *                         @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level")
     *                     ),
     *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
     *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
     *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
     *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
     *                     @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                 )
     *             )
     *         )
     *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request"
 *     )
 * )
 */
public function store(Request $request)
{
    // Validasi input
    $validatedData = $request->validate([
        'LabTransID' => 'required|integer|exists:Lab_Trans,ID',
        'ItemTestID' => 'required|integer|exists:Item_Test,ID',
        'ResultValue' => 'nullable|string',
        'Unit' => 'nullable|string',
        'ReferenceValue' => 'nullable|string',
        'ResultNotes' => 'nullable|string',
        'CreateBy' => 'nullable|string|max:20',
    ]);

    // Tambahkan CreateDate secara otomatis
    date_default_timezone_set('Asia/Jakarta');
    $validatedData['CreateDate'] = now()->format('Y-m-d H:i:s');
    $validatedData['gcrecord'] = false; // Set default gcrecord ke false

    // Simpan data ke tabel LabTransDetail
    $labTransDetail = LabTransDetail::create($validatedData);

    // Format response sesuai dengan GET
    $formattedData = [
        'ID' => $labTransDetail->ID,
        'LabTransID' => $labTransDetail->LabTransID,
        'ItemTestID' => $labTransDetail->ItemTestID,
        'itemTest' => $labTransDetail->itemTest ? [
            'ItemTestCode'      => $labTransDetail->itemTest->ItemTestCode,
            'ItemTestName'      => $labTransDetail->itemTest->ItemTestName,
            'Group'             => $labTransDetail->itemTest->Group,
            'SubGroup'          => $labTransDetail->itemTest->SubGroup,
            'Descriptions'      => $labTransDetail->itemTest->Descriptions,
        ] : null,
        'ResultValue' => $labTransDetail->ResultValue,
        'Unit' => $labTransDetail->Unit,
        'ReferenceValue' => $labTransDetail->ReferenceValue,
        'ResultNotes' => $labTransDetail->ResultNotes,
        'CreateDate' => $labTransDetail->CreateDate,
        'LastModifiedDate' => $labTransDetail->LastModifiedDate,
        'LastModifiedBy' => $labTransDetail->LastModifiedBy,
    ];

    return response()->json([
        'message' => 'Data berhasil disimpan',
        'data' => $formattedData
    ], 201);
}


    /**
     * @OA\Put(
     *     path="/api/labtransdetail/{ID}",
     *     tags={"Lab Detail"},
     *     summary="Update a specific LabTransDetail",
     *     @OA\Parameter(
     *         name="ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="LabTransDetail ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ItemTestID", type="integer", example=5),
     *             @OA\Property(property="LabTransID", type="integer", example=5),
     *             @OA\Property(property="ResultValue", type="string", example="Positive"),
     *             @OA\Property(property="Unit", type="string", example="mg/dL"),
     *             @OA\Property(property="ReferenceValue", type="string", example="70-110"),
     *             @OA\Property(property="ResultNotes", type="string", example="Normal Range"),
     *             @OA\Property(property="LastModifiedBy", type="string", example="Admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered list of Lab Trans",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
     *             @OA\Property(property="totaldata", type="integer", example=10),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="ID", type="integer", example=1),
     *                     @OA\Property(property="LabTransID", type="integer", example=101),
     *                     @OA\Property(property="ItemTestID", type="integer", example=201),
     *                     @OA\Property(
     *                         property="itemTest",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="ItemTestCode", type="string", example="T-001"),
     *                         @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
     *                         @OA\Property(property="Group", type="string", example="Hematology"),
     *                         @OA\Property(property="SubGroup", type="string", example="Blood"),
     *                         @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level")
     *                     ),
     *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
     *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
     *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
     *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-08 12:00:00"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="editor")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found"
     *     )
     * )
     */
    public function update(Request $request)
{
    // Ambil ID dari query parameter (?id=12)
    $ID = $request->query('id');

    // Jika ID tidak diberikan, kembalikan error
    if (!$ID) {
        return response()->json(['message' => 'ID is required'], 400);
    }

    // Cari data berdasarkan ID dan pastikan gcrecord = 0
    $labTransDetail = LabTransDetail::where('ID', $ID)->where('gcrecord', 0)->first();

    if (!$labTransDetail) {
        return response()->json(['message' => 'Data not found'], 404);
    }

    // Validasi input
    $validatedData = $request->validate([
        'ResultValue' => 'nullable|string',
        'Unit' => 'nullable|string',
        'ReferenceValue' => 'nullable|string',
        'ResultNotes' => 'nullable|string',
        'LastModifiedBy' => 'nullable|string',
        'ItemTestID' => 'nullable|integer|exists:Item_Test,ID',
        'LabTransID' => 'nullable|integer|exists:Lab_Trans,ID'
    ]);

    // Set timezone ke Asia/Jakarta
    date_default_timezone_set('Asia/Jakarta');

    // Update LastModifiedDate otomatis ke waktu sekarang
    $validatedData['LastModifiedDate'] = now()->format('Y-m-d H:i:s');

    // Update data
    $labTransDetail->update($validatedData);

    // Return response dengan format yang sesuai dengan GET
    return response()->json([
        'message' => 'LabTransDetail updated successfully',
        'data' => [
            'ID' => $labTransDetail->ID,
            'LabTransID' => $labTransDetail->LabTransID,
            'ItemTestID' => $labTransDetail->ItemTestID,
            'itemTest' => $labTransDetail->itemTest ? [
                'ItemTestCode'      => $labTransDetail->itemTest->ItemTestCode,
                'ItemTestName'      => $labTransDetail->itemTest->ItemTestName,
                'Group'             => $labTransDetail->itemTest->Group,
                'SubGroup'          => $labTransDetail->itemTest->SubGroup,
                'Descriptions'      => $labTransDetail->itemTest->Descriptions,
            ] : null,
            'ResultValue' => $labTransDetail->ResultValue,
            'Unit' => $labTransDetail->Unit,
            'ReferenceValue' => $labTransDetail->ReferenceValue,
            'ResultNotes' => $labTransDetail->ResultNotes,
            'LastModifiedDate' => $labTransDetail->LastModifiedDate,
            'LastModifiedBy' => $labTransDetail->LastModifiedBy,
        ]
    ], 200);
}
}