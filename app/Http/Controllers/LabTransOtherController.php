<?php

namespace App\Http\Controllers;

use App\Models\LabTransOther;
use App\Models\Lab_Trans;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LabTransOtherController extends Controller
{
  /**
 * @OA\Get(
 *     path="/api/labtransother",
 *     summary="Get all LabTransOthers with optional search",
 *     tags={"LabTransOther"},
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
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
 *             @OA\Property(property="totaldata", type="integer", example=5),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="LabTransID", type="integer", example=101),
 *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                     @OA\Property(
 *                         property="SupportService",
 *                         type="object",
 *                         nullable=true,
 *                         @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                         @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                         @OA\Property(property="Descriptions", type="string", example="")
 *                     ),
 *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
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

    $labTransOthers = LabTransOther::with([
        'labTrans' => function ($q) {
            $q->where('gcrecord', 0); // Pastikan labTrans juga tidak memiliki gcrecord
        },
        'supportService' => function ($q) {
            $q->where('gcrecord', 0); // Pastikan supportService juga tidak memiliki gcrecord
        }
    ])
    ->where('gcrecord', 0) // Pastikan data utama juga hanya mengambil gcrecord = 0
    ->when($id, function ($q) use ($id) {
        $q->where('ID', $id); // Filter berdasarkan ID (gunakan "=")
    })
    ->when(!$id && $query, function ($q) use ($query) {
        if (stripos($query, 'urine') !== false) {
            // Jika query berisi "urine", filter hanya data yang mengandung "urine" di SupportServiceNotes atau SupportServiceName
            $q->where('SupportServiceNotes', 'LIKE', '%urine%')
              ->orWhereHas('supportService', function ($subQuery) {
                  $subQuery->where('SupportServiceName', 'LIKE', '%urine%');
              });
        } else {
            // Jika query bukan "urine", cari di beberapa kolom menggunakan LIKE
            $q->where('SupportServiceNotes', 'LIKE', "%{$query}%")
              ->orWhereHas('labTrans', function ($subQuery) use ($query) {
                  $subQuery->where('LabNumber', 'LIKE', "%{$query}%")
                           ->orWhere('DoctorReferral', 'LIKE', "%{$query}%");
              })
              ->orWhereHas('supportService', function ($subQuery) use ($query) {
                  $subQuery->where('SupportServiceCode', 'LIKE', "%{$query}%")
                           ->orWhere('SupportServiceName', 'LIKE', "%{$query}%");
              });
        }
    })
    ->get();

    // Hitung total data setelah filter
    $total = $labTransOthers->count();

    if ($labTransOthers->isEmpty()) {
        return response()->json([
            'message' => 'Data not found',
            'total' => 0,
            'data' => []
        ], 404);
    }

    // Format ulang data untuk respons
    $formattedData = $labTransOthers->map(function ($labTransOther) {
        return [
            'ID' => $labTransOther->ID,
            'LabTransID' => $labTransOther->LabTransID,
            'SupportServiceID' => $labTransOther->SupportServiceID,
            'SupportService' => $labTransOther->supportService ? [
                'SupportServiceCode' => $labTransOther->supportService->SupportServiceCode,
                'SupportServiceName' => $labTransOther->supportService->SupportServiceName,
                'Descriptions' => $labTransOther->supportService->Descriptions,
            ] : null,
            'SupportServiceNotes' => $labTransOther->SupportServiceNotes,
            'CreateDate' => $labTransOther->CreateDate,
            'CreateBy' => $labTransOther->CreateBy,
            'LastModifiedDate' => $labTransOther->LastModifiedDate,
            'LastModifiedBy' => $labTransOther->LastModifiedBy,
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
 *     path="/api/labtransother",
 *     tags={"LabTransOther"},
 *     summary="Create a new LabTransOther",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="LabTransID", type="integer", example=5),
 *             @OA\Property(property="SupportServiceID", type="integer", example=10),
 *             @OA\Property(property="SupportServiceNotes", type="string", example="MRI Scan Required"),
 *             @OA\Property(property="CreateBy", type="string", example="Admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Data berhasil disimpan",
 *         @OA\JsonContent(
 *             type="object",
 *                 @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="LabTransID", type="integer", example=101),
 *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                     @OA\Property(
 *                         property="SupportService",
 *                         type="object",
 *                         nullable=true,
 *                         @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                         @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                         @OA\Property(property="Descriptions", type="string", example="")
 *                     ),
 *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
 *                     @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                     @OA\Property(property="CreateBy", type="string", example="admin"),
 *                     @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-08 12:00:00"),
 *                     @OA\Property(property="LastModifiedBy", type="string", example="editor")
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
        'SupportServiceID' => 'required|integer|exists:Support_Service,ID',
        'SupportServiceNotes' => 'nullable|string',
        'CreateBy' => 'nullable|string|max:20',
    ]);

    // Tambahkan CreateDate secara otomatis
    date_default_timezone_set('Asia/Jakarta');
    $validatedData['CreateDate'] = now()->format('Y-m-d H:i:s');
    $validatedData['gcrecord'] = false; // Set default gcrecord ke false

    // Simpan data ke tabel LabTransOther
    $labTransOther = LabTransOther::create($validatedData);

    // Format response sesuai dengan GET
    $formattedData = [
        'ID' => $labTransOther->ID,
        'LabTransID' => $labTransOther->LabTransID,
        'SupportServiceID' => $labTransOther->SupportServiceID,
        'SupportService' => $labTransOther->supportService ? [
            'SupportServiceCode' => $labTransOther->supportService->SupportServiceCode,
            'SupportServiceName' => $labTransOther->supportService->SupportServiceName,
        ] : null,
        'SupportServiceNotes' => $labTransOther->SupportServiceNotes,
        'CreateDate' => $labTransOther->CreateDate,
        'CreateBy' => $labTransOther->CreateBy,
        // 'LastModifiedDate' => $labTransOther->LastModifiedDate,
        // 'LastModifiedBy' => $labTransOther->LastModifiedBy,
    ];

    return response()->json([
        'message' => 'Data berhasil disimpan',
        'data' => $formattedData
    ], 201);
}



   /**
 * @OA\Put(
 *     path="/api/labtransother/{ID}",
 *     summary="Update a specific LabTransOther",
 *     tags={"LabTransOther"},
 *     @OA\Parameter(
 *         name="ID",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="LabTransOther ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
  *            @OA\Property(property="LabTransID", type="integer"),
 *             @OA\Property(property="SupportServiceID", type="integer"),
 *             @OA\Property(property="SupportServiceNotes", type="string"),
 *             @OA\Property(property="LastModifiedBy", type="string"),
 *         )
 *     ),
 *         @OA\Response(
 *         response=201,
 *         description="Data berhasil di update",
 *         @OA\JsonContent(
 *             type="object",
 *                 @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="LabTransID", type="integer", example=101),
 *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                     @OA\Property(
 *                         property="SupportService",
 *                         type="object",
 *                         nullable=true,
 *                         @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                         @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                         @OA\Property(property="Descriptions", type="string", example="")
 *                     ),
 *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
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
    $labTransOther = LabTransOther::where('ID', $ID)->where('gcrecord', 0)->first();

    if (!$labTransOther) {
        return response()->json(['message' => 'Data not found'], 404);
    }

    // Validasi semua field yang bisa di-update
    $validatedData = $request->validate([
        'SupportServiceNotes' => 'nullable|string',
        'LastModifiedBy' => 'nullable|string',
        'LabTransID' => 'nullable|integer|exists:Lab_Trans,ID',
        'SupportServiceID' => 'nullable|integer|exists:Support_Service,ID',
    ]);
    
    // Set timezone ke Asia/Jakarta untuk memastikan waktu update sesuai
    date_default_timezone_set('Asia/Jakarta');

    // Update LastModifiedDate secara otomatis ke waktu sekarang
    $validatedData['LastModifiedDate'] = now()->format('Y-m-d H:i:s');

    // Lakukan update data di database
    $labTransOther->update($validatedData);

    // Format response agar lebih informatif dan konsisten dengan response GET
    return response()->json([
        'message' => 'LabTransOther updated successfully',
        'data' => [
            'ID' => $labTransOther->ID,
            'LabTransID' => $labTransOther->LabTransID,
            'SupportServiceID' => $labTransOther->SupportServiceID,
            'SupportService' => $labTransOther->supportService ? [
                'SupportServiceCode' => $labTransOther->supportService->SupportServiceCode,
                'SupportServiceName' => $labTransOther->supportService->SupportServiceName,
            ] : null,
            'SupportServiceNotes' => $labTransOther->SupportServiceNotes,
            'LastModifiedDate' => $labTransOther->LastModifiedDate,
            'LastModifiedBy' => $labTransOther->LastModifiedBy,
        ]
    ], 200);
}
}