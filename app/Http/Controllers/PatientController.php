<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;


class PatientController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/pasien",
 *     summary="Ambil semua data pasien",
 *     description="Mengambil daftar semua pasien yang terdaftar. Bisa menggunakan parameter pencarian berdasarkan ID atau teks.",
 *     tags={"Pasien"},
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Cari berdasarkan ID pasien yang spesifik (integer match).",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         description="Cari berdasarkan NIK, FullName, Phone, atau Address dengan pencarian LIKE.",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Daftar pasien berhasil diambil",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Patient")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Data tidak ditemukan"
 *     )
 * )
 */
public function index(Request $request)
{
    $id = $request->query('id');
    $query = $request->query('query');

    $patients = Patient::where('gcrecord', 0) // Pastikan hanya mengambil data dengan gcrecord = 0
    ->when($id, function ($q) use ($id) {
        $q->where('ID', $id); // Filter berdasarkan ID (gunakan "=")
    })
    ->when(!$id && $query, function ($q) use ($query) {
        if (stripos($query, 'urine') !== false) {
            // Jika query berisi "urine", filter hanya alamat atau nama yang mengandung "urine"
            $q->where('Address', 'LIKE', '%urine%')
              ->orWhere('FullName', 'LIKE', '%urine%');
        } else {
            // Jika query bukan "urine", cari di beberapa kolom menggunakan LIKE
            $q->where(function ($subQ) use ($query) {
                $subQ->where('NIK', 'LIKE', "%{$query}%")
                     ->orWhere('PatientID_Provider', 'LIKE', "%{$query}%")
                     ->orWhere('FullName', 'LIKE', "%{$query}%")
                     ->orWhere('Sex', 'LIKE', "%{$query}%")
                     ->orWhere('BirthDate', 'LIKE', "%{$query}%")
                     ->orWhere('Address', 'LIKE', "%{$query}%")
                     ->orWhere('Phone', 'LIKE', "%{$query}%")
                     ->orWhere('CreateBy', 'LIKE', "%{$query}%")
                     ->orWhere('LastModifiedBy', 'LIKE', "%{$query}%");
            });
        }
    })
    ->get();

    // Hitung total data setelah filter
    $total = $patients->count();

    if ($patients->isEmpty()) {
        return response()->json([
            'message' => 'Data tidak ditemukan',
            'total' => 0,
            'data' => []
        ], 404);
    }

    // Format respons dengan memastikan CreateDate di-cast sebagai datetime
    $formattedData = $patients->map(function ($patient) {
        return [
            'ID' => $patient->ID,
            'NIK' => $patient->NIK,
            'FullName' => $patient->FullName,
            'Sex' => $patient->Sex,
            'BirthDate' => $patient->BirthDate,
            'Phone' => $patient->Phone,
            'Address' => $patient->Address,
            'CreateDate' => $patient->getOriginal('CreateDate') ? date('Y-m-d H:i:s', strtotime($patient->getOriginal('CreateDate'))) : null,
            'CreateBy' => $patient->CreateBy,
            'LastModifiedDate' => $patient->LastModifiedDate,
            'LastModifiedBy' => $patient->LastModifiedBy
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
     *     path="/api/pasien",
     *     summary="Tambah data pasien baru",
     *     description="Menambahkan pasien baru ke dalam database.",
     *     tags={"Pasien"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"NIK", "PatientID_Provider"},
     *             @OA\Property(property="NIK", type="string", example="1234567890123456"),
     *             @OA\Property(property="PatientID_Provider", type="string", example="P-001"),
     *             @OA\Property(property="FullName", type="string", example="John Doe"),
     *             @OA\Property(property="Sex", type="string", enum={"M", "F"}, example="M"),
     *             @OA\Property(property="BirthDate", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="Address", type="string", example="Jl. Contoh No.1"),
     *             @OA\Property(property="Phone", type="string", example="081234567890"),
     *             @OA\Property(property="CreateBy", type="string", example="admin"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pasien berhasil ditambahkan",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'NIK' => 'required|string|size:16|unique:Patient,NIK',
        'PatientID_Provider' => 'required|string|max:20',
        'FullName' => 'nullable|string|max:50',
        'Sex' => 'nullable|string|in:M,F',
        'BirthDate' => 'nullable|date',
        'Address' => 'nullable|string|max:250',
        'Phone' => 'nullable|string|max:50',
        'CreateBy' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:20',
        'gcrecord' => 'nullable|boolean',
    ]);

    // Set zona waktu ke Indonesia (Jawa Barat)
    date_default_timezone_set('Asia/Jakarta');
    
    // Pastikan gcrecord selalu false atau 0
    $validated['gcrecord'] = false; // atau gunakan `0` jika di database berupa integer
    $validated['CreateDate'] = now()->toDateTimeString();

    $patient = Patient::create($validated);
    $patient->refresh(); // Memastikan data terbaru diambil dari database

    return response()->json([
        'message' => 'Patient record created successfully',
        'data' => [
            'ID' => $patient->ID,
            'NIK' => $patient->NIK,
            'PatientID_Provider' => $patient->PatientID_Provider,
            'FullName' => $patient->FullName,
            'Sex' => $patient->Sex,
            'BirthDate' => $patient->BirthDate,
            'Address' => $patient->Address,
            'Phone' => $patient->Phone,
            'CreateDate' => !empty($patient->CreateDate) ? Carbon::parse($patient->CreateDate)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'CreateBy' => $patient->CreateBy,
        ]
    ], 201);
}




       /**
     * @OA\Put(
     *     path="/api/pasien/{id}",
     *     summary="Perbarui data pasien yang ada",
     *     description="Memperbarui informasi pasien berdasarkan ID yang diberikan.",
     *     tags={"Pasien"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID pasien yang akan diperbarui",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"FullName", "Sex", "BirthDate", "Address", "Phone", "LastModifiedBy"},
     *             @OA\Property(property="FullName", type="string", example="Jane Doe"),
     *             @OA\Property(property="Sex", type="string", enum={"M", "F"}, example="F"),
     *             @OA\Property(property="BirthDate", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="Address", type="string", example="Jl. Contoh No.2"),
     *             @OA\Property(property="Phone", type="string", example="081298765432"),
     *             @OA\Property(property="LastModifiedBy", type="string", example="admin"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pasien berhasil diperbarui",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Pasien tidak ditemukan")
     * )
     */
    public function update(Request $request, $id)
{
    $patient = Patient::where('ID', $id)->where('gcrecord', 0)->first();

    if (!$patient) {
        return response()->json(['message' => 'Patient not found'], 404);
    }

    $validatedData = $request->validate([
        'FullName' => 'sometimes|string|max:255',
        'Sex' => 'sometimes|string|in:M,F',
        'BirthDate' => 'nullable|date',
        'Address' => 'nullable|string|max:255',
        'Phone' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:50',
        'gcrecord' => 'nullable|boolean',
    ]);

    date_default_timezone_set('Asia/Jakarta');

    // Pastikan LastModifiedDate selalu diisi dengan waktu sekarang
    $validatedData['LastModifiedDate'] = now()->format('Y-m-d H:i:s');

    $patient->update($validatedData);

    return response()->json([
        'ID' => $patient->ID,
        'FullName' => $patient->FullName,
        'Sex' => $patient->Sex,
        'BirthDate' => $patient->BirthDate,
        'Address' => $patient->Address,
        'Phone' => $patient->Phone,
        'LastModifiedDate' => $patient->LastModifiedDate ?? now()->format('Y-m-d H:i:s'),
        'LastModifiedBy' => $patient->LastModifiedBy,
    ], 200);
}
}