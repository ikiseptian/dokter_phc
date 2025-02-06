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
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/SupportService")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Data not found"
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
            if (stripos($query, 'urine') !== false) {
                // Jika query berisi "urine", hanya cari di SupportServiceName atau SupportServiceCode yang mengandung "urine"
                $q->where(function ($subQuery) {
                    $subQuery->where('SupportServiceName', 'LIKE', '%urine%')
                             ->orWhere('SupportServiceCode', 'LIKE', '%urine%');
                })->where('gcrecord', 0); // Pastikan gcrecord tetap 0
            } else {
                // Jika query bukan "urine", cari di beberapa kolom menggunakan LIKE
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('SupportServiceName', 'LIKE', "%{$query}%")
                             ->orWhere('SupportServiceCode', 'LIKE', "%{$query}%");
                })->where('gcrecord', 0); // Pastikan gcrecord tetap 0
            }
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
     *          @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
     *          @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
     *          @OA\Property(property="CreateBy", type="string", example="admin"),
     *         )
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
        'SupportServiceCode' => 'nullable|string|max:200|unique:Support_Service,SupportServiceCode',
        'SupportServiceName' => 'nullable|string|max:200',
        'CreateBy' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:20',
        'gcrecord' => 'nullable|boolean',
    ]);

    // Tambahkan CreateDate berdasarkan waktu sekarang
    date_default_timezone_set('Asia/Jakarta');

    $validated['CreateDate'] = now()->format('Y-m-d H:i:s');

    $supportService = SupportService::create($validated);

    // Kembalikan response dengan body yang lengkap
    return response()->json([
        'message' => 'Support service record created successfully',
        'data' => [
            'ID' => $supportService->ID,
            'SupportServiceCode' => $supportService->SupportServiceCode,
            'SupportServiceName' => $supportService->SupportServiceName,
            'CreateBy' => $supportService->CreateBy,
            // 'LastModifiedBy' => $supportService->LastModifiedBy,
            'CreateDate' => $supportService->CreateDate,
            // 'gcrecord' => $supportService->gcrecord,
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
 *             @OA\Property(property="LastModifiedBy", type="string", example="user123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="SupportService updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *             @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *             @OA\Property(property="LastModifiedBy", type="string", example="user123"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="SupportService not found"
 *     )
 * )
 */

public function update(Request $request, $id)
{
    // Cari data berdasarkan ID
    $supportservice = SupportService::find($id);

    if (!$supportservice) {
        return response()->json(['message' => 'Support service not found'], 404);
    }

    // Validasi request
    $validated = $request->validate([
        'SupportServiceCode' => 'nullable|string|max:200',
        'SupportServiceName' => 'nullable|string|max:200',
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
    return response()->json($supportservice->only([
        'ID',
        // 'SupportServiceCode',
        'SupportServiceName',
        // 'CreateDate',
        // 'CreateBy',
        'LastModifiedDate',
        'LastModifiedBy',
        // 'gcrecord'
    ]), 200);
}
}