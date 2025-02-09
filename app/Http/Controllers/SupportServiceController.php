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
 *     description="Retrieve all support services, with optional ID or search query.",
 *     tags={"SupportService"},
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Search by exact ID (integer match).",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         description="Search support services by code or name using LIKE match.",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
 *             @OA\Property(property="totaldata", type="integer", example=2),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                     @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                     @OA\Property(property="Descriptions", type="string", example="Radiology imaging services"),
 *                     @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                     @OA\Property(property="CreateBy", type="string", example="admin"),
 *                     @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-07 12:00:00"),
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

    $services = SupportService::where('gcrecord', 0) // Pastikan hanya mengambil data dengan gcrecord = 0
        ->when($id, function ($q) use ($id) {
            $q->where('ID', $id) // Filter berdasarkan ID (gunakan "=")
              ->where('gcrecord', 0); // Pastikan gcrecord tetap 0
        })
        ->when(!$id && $query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('SupportServiceName', 'LIKE', "%{$query}%")
                         ->orWhere('SupportServiceCode', 'LIKE', "%{$query}%");
            })->where('gcrecord', 0);
        })
        ->get();

    // Hitung total data setelah filter
    $total = $services->count();

    if ($services->isEmpty()) {
        return response()->json([
            'message' => 'Data not found',
            'total' => 0,
            'data' => []
        ], 404);
    }

    // Format data untuk respons
    $formattedData = $services->map(function ($service) {
        return [
            'ID' => $service->ID,
            'SupportServiceCode' => $service->SupportServiceCode,
            'SupportServiceName' => $service->SupportServiceName,
            'Descriptions' => $service->Descriptions,
            'CreateDate' => $service->CreateDate,
            'CreateBy' => $service->CreateBy,
            'LastModifiedDate' => $service->LastModifiedDate,
            'LastModifiedBy' => $service->LastModifiedBy,
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
 *     path="/api/supportservice",
 *     summary="Tambah data pasien baru",
 *     description="Menambahkan pasien baru ke dalam database.",
 *     tags={"SupportService"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *             @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *             @OA\Property(property="Descriptions", type="string", example=""),
 *             @OA\Property(property="CreateBy", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="SupportService created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Support service record created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="ID", type="integer", example=1),
 *                 @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                 @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                 @OA\Property(property="Descriptions", type="string", example="Radiology"),
 *                 @OA\Property(property="CreateBy", type="string", example="admin"),
 *                 @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00")
 *             )
 *         )
 *     )
 * )
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'SupportServiceCode' => 'nullable|string|max:200|unique:Support_Service,SupportServiceCode',
        'SupportServiceName' => 'nullable|string|max:200',
        'Descriptions' => 'nullable|string|max:200',
        'CreateBy' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:20',
    ]);

    // Pastikan `Descriptions` tidak null untuk menghindari error
    $validated['Descriptions'] = $validated['Descriptions'] ?? '';

    // Tambahkan CreateDate berdasarkan waktu sekarang
    date_default_timezone_set('Asia/Jakarta');
    $validated['CreateDate'] = now()->format('Y-m-d H:i:s');

    // Set default gcrecord = 0
    $validated['gcrecord'] = 0;

    $supportService = SupportService::create($validated);

    // Kembalikan response dengan `gcrecord` disembunyikan
    return response()->json([
        'message' => 'Support service record created successfully',
        'data' => [
            'ID' => $supportService->ID,
            'SupportServiceCode' => $supportService->SupportServiceCode,
            'SupportServiceName' => $supportService->SupportServiceName,
            'Descriptions' => $supportService->Descriptions,
            'CreateBy' => $supportService->CreateBy,
            'CreateDate' => $supportService->CreateDate,
        ]
    ], 201);
}




   /**
 * @OA\Put(
 *     path="/api/supportservice/{id}",
 *     summary="Update an existing Support Service",
 *     description="Memperbarui data layanan support yang ada di database.",
 *     tags={"SupportService"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID dari Support Service yang akan diperbarui",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *             @OA\Property(property="Descriptions", type="string", example="Radiology"),
 *             @OA\Property(property="LastModifiedBy", type="string", example="user123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="SupportService updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="ID", type="string", example="1"),
 *             @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *             @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *             @OA\Property(property="Descriptions", type="string", example="Radiology"),
 *             @OA\Property(property="LastModifiedBy", type="string", example="user123"),
 *             @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="SupportService not found"
 *     )
 * )
 */

 public function update(Request $request)
 {
     // Ambil ID dari query parameter (?id=12)
     $id = $request->query('id');
 
     // Jika ID tidak diberikan, kembalikan error
     if (!$id) {
         return response()->json(['message' => 'ID is required'], 400);
     }
 
     // Cari data berdasarkan ID
     $supportservice = SupportService::find($id);
 
     if (!$supportservice) {
         return response()->json(['message' => 'Support service not found'], 404);
     }
 
     // Validasi request
     $validated = $request->validate([
         'SupportServiceCode' => 'nullable|string|max:200',
         'SupportServiceName' => 'nullable|string|max:200',
         'Descriptions' => 'nullable|string|max:200',
         'CreateBy' => 'nullable|string|max:20',
         'LastModifiedBy' => 'nullable|string|max:20',
         'gcrecord' => 'nullable|boolean',
     ]);
 
     // Update LastModifiedDate secara otomatis
     date_default_timezone_set('Asia/Jakarta');
 
     $validated['LastModifiedDate'] = now()->format('Y-m-d H:i:s');
 
     // Update data SupportService
     $supportservice->update($validated);
 
     // Kembalikan response dengan field yang diinginkan
     return response()->json([
         'message' => 'Support service updated successfully',
         'data' => $supportservice->only([
             'ID',
             'SupportServiceName',
             'Descriptions',
             'LastModifiedDate',
             'LastModifiedBy',
         ])
     ], 200);
 }
} 