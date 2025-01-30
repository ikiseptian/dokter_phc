<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pasien",
     *     summary="Ambil semua data pasien",
     *     description="Mengambil daftar semua pasien yang terdaftar. Bisa menggunakan parameter pencarian.",
     *     tags={"Pasien"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Parameter pencarian untuk mencocokkan data pasien berdasarkan NIK, PatientID_Provider, atau kolom lain.",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar pasien berhasil diambil",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Patient")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Patient::whereNull('gcrecord')->orWhere('gcrecord', 0); // Filter gcrecord diterapkan di awal
    
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('NIK', 'LIKE', "%{$search}%")
                    ->orWhere('PatientID_Provider', 'LIKE', "%{$search}%")
                    ->orWhere('FullName', 'LIKE', "%{$search}%")
                    ->orWhere('Sex', 'LIKE', "%{$search}%")
                    ->orWhere('BirthDate', 'LIKE', "%{$search}%")
                    ->orWhere('Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone', 'LIKE', "%{$search}%")
                    ->orWhere('CreateBy', 'LIKE', "%{$search}%")
                    ->orWhere('LastModifiedBy', 'LIKE', "%{$search}%");
            });
        }
    
        $patients = $query->get();
    
        return response()->json($patients, 200);
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
     *             @OA\Property(property="LastModifiedBy", type="string", example="admin"),
     *             @OA\Property(property="gcrecord", type="boolean", example=true)
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
        $validatedData = $request->validate([
            'NIK' => 'required|string|size:16',
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

        $patient = Patient::create([
            'NIK' => $validatedData['NIK'],
            'PatientID_Provider' => $validatedData['PatientID_Provider'],
            'FullName' => $validatedData['FullName'] ?? null,
            'Sex' => $validatedData['Sex'] ?? null,
            'BirthDate' => $validatedData['BirthDate'] ?? null,
            'Address' => $validatedData['Address'] ?? null,
            'Phone' => $validatedData['Phone'] ?? null,
            'CreateBy' => $validatedData['CreateBy'],
            'LastModifiedBy' => $validatedData['LastModifiedBy'],
            'gcrecord' => $validatedData['gcrecord'],
        ]);

        return response()->json([
            'message' => 'Patient created successfully',
            'data' => $patient
        ], 201);
    }
}
