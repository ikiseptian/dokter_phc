<?php

namespace App\Http\Controllers;

use App\Models\Lab_Trans;
use App\Models\LabTransDetail;
use App\Models\Patient;
use App\Models\LabTransOther;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LabTransController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/labtrans",
 *     tags={"Lab Trans"},
 *     summary="Search Lab Trans by ID or query (including urine filter)",
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Search by exact ID (integer match)",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         description="Search by text query (LIKE match), filtering for 'urine' if provided",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Data berhasil diambil"),
 *             @OA\Property(property="totaldata", type="integer", example=5),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="ID", type="integer", example=1),
 *                 @OA\Property(property="LabNumber", type="string", example="LAB-12345"),
 *                 @OA\Property(property="LabTest", type="string", example="Blood Test"),
 *                 @OA\Property(property="TransDate", type="string", format="date", example="2024-09-06"),
 *                 @OA\Property(property="DoctorReferral", type="string", example="Dr. John Doe"),
 *                 @OA\Property(property="Age", type="integer", example=30),
 *                 @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
 *                 @OA\Property(property="BB", type="string", example="70"),
 *                 @OA\Property(property="TB", type="string", example="170"),
 *                 @OA\Property(property="LP", type="string", example="80"),
 *                 @OA\Property(property="TD", type="string", example="120/80"),
 *                 @OA\Property(property="BMI", type="string", example="22.5"),
 *                 @OA\Property(
 *                     property="patient",
 *                     type="object",
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="NIK", type="string", example="1234567890123456"),
 *                     @OA\Property(property="FullName", type="string", example="John Doe"),
 *                     @OA\Property(property="Sex", type="string", example="Male"),
 *                     @OA\Property(property="BirthDate", type="string", format="date", example="1994-05-12"),
 *                     @OA\Property(property="Address", type="string", example="Jl. Merdeka No. 10, Jakarta"),
 *                     @OA\Property(property="Phone", type="string", example="08123456789"),
 *                 ),
 *                 @OA\Property(
 *                     property="Lab_Trans_Details",
 *                     type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="ID", type="integer", example=1),
 *                         @OA\Property(property="ItemTestID", type="integer", example=101),
 *                         @OA\Property(property="ItemTestCode", type="string", example="T-001"),
 *                         @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
 *                         @OA\Property(property="Group", type="string", example="Hematology"),
 *                         @OA\Property(property="SubGroup", type="string", example="Blood"),
 *                         @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level"),
 *                         @OA\Property(property="ResultValue", type="string", example="5.4"),
 *                         @OA\Property(property="Unit", type="string", example="mmol/L"),
 *                         @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
 *                         @OA\Property(property="ResultNotes", type="string", example="Normal"),
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="Lab_Trans_Others",
 *                     type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="ID", type="integer", example=1),
 *                         @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                         @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                         @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                         @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
 *                     )
 *                 ),
 *                 @OA\Property(property="FinalStatement", type="string", example="Sehat"),
 *                 @OA\Property(property="FinalResult", type="string", example="Normal"),
 *                 @OA\Property(property="Status", type="string", example="Completed"),
 *                 @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                 @OA\Property(property="CreateBy", type="string", example="admin"),
 *                 @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                 @OA\Property(property="LastModifiedBy", type="string", example="admin")
 *             )
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    $id = $request->query('id');
    $query = $request->query('query');

    $labTrans = Lab_Trans::with([
        'patient' => function ($q) {
            $q->where('gcrecord', 0);
        },
        'labTransDetails' => function ($q) {
            $q->where('gcrecord', 0)->with('itemTest');
        },
        'labTransOthers' => function ($q) {
            $q->where('gcrecord', 0)->with('supportService');
        }
    ])
        ->where('gcrecord', 0)
        ->when($id, function ($q) use ($id) {
            $q->where('ID', $id);
        })
        ->when(!$id && $query, function ($q) use ($query) {
            if (stripos($query, 'urine') !== false) {
                $q->whereHas('labTransDetails.itemTest', function ($subQuery) {
                    $subQuery->where('Descriptions', 'LIKE', '%urine%');
                });
            } else {
                $q->where('LabNumber', 'LIKE', "%{$query}%")
                    ->orWhere('LabTest', 'LIKE', "%{$query}%")
                    ->orWhereHas('patient', function ($subQuery) use ($query) {
                        $subQuery->where('FullName', 'LIKE', "%{$query}%")
                            ->orWhere('NIK', 'LIKE', "%{$query}%");
                    });
            }
        })
        ->get();

    $total = $labTrans->count();

    $formattedData = $labTrans->map(function ($labTrans) {
        $age = null;
        if ($labTrans->patient && $labTrans->patient->BirthDate) {
            $birthYear = date('Y', strtotime($labTrans->patient->BirthDate));
            $currentYear = date('Y');
            $age = (string) ($currentYear - $birthYear);
        }

        return [
            'ID' => $labTrans->ID, 
            'LabNumber' => $labTrans->LabNumber,
            'LabTest' => $labTrans->LabTest,
            'TransDate' => $labTrans->TransDate,
            'DoctorReferral' => $labTrans->DoctorReferral,
            'Age' => $age,
            'Anamnesa' => $labTrans->Anamnesa,
            'BB' => $labTrans->BB,
            'TB' => $labTrans->TB,
            'LP' => $labTrans->LP,
            'TD' => $labTrans->TD,
            'BMI' => $labTrans->BMI,
            'PatientID' => $labTrans->PatientID,
            'patient' => $labTrans->patient ? [
                'ID'                  => $labTrans->patient->ID ?? null,
                'NIK'                 => $labTrans->patient->NIK ?? null,
                'PatientID_Provider'  => $labTrans->patient->PatientID_Provider ?? null,
                'FullName'            => $labTrans->patient->FullName ?? null,
                'Sex'                 => $labTrans->patient->Sex ?? null,
                'BirthDate'           => $labTrans->patient->BirthDate ?? null,
                'Address'             => $labTrans->patient->Address ?? null,
                'Phone'               => $labTrans->patient->Phone ?? null,
            ] : null,
            'Lab_Trans_Details' => $labTrans->labTransDetails->map(function ($detail) {
                return [
                    'ID'              => $detail->ID, 
                    'ItemTestID'      => $detail->ItemTestID,
                    'ItemTestCode'    => $detail->itemTest->ItemTestCode ?? null,
                    'ItemTestName'    => $detail->itemTest->ItemTestName ?? null,
                    'Group'           => $detail->itemTest->Group ?? null,
                    'SubGroup'        => $detail->itemTest->SubGroup ?? null,
                    'Descriptions'    => $detail->itemTest->Descriptions ?? null,
                    'ResultValue'     => $detail->ResultValue,
                    'Unit'            => $detail->Unit,
                    'ReferenceValue'  => $detail->ReferenceValue,
                    'ResultNotes'     => $detail->ResultNotes,
                ];
            }),
            'Lab_Trans_Others' => $labTrans->labTransOthers->map(function ($other) {
                return [
                    'ID'                   => $other->ID, 
                    'SupportServiceID'     => $other->SupportServiceID,
                    'SupportServiceCode'   => $other->supportService->SupportServiceCode ?? null,
                    'SupportServiceName'   => $other->supportService->SupportServiceName ?? null,
                    'SupportServiceNotes'  => $other->SupportServiceNotes,
                ];
            }),
            'FinalStatement'       => $labTrans->FinalStatement ?? "N/A",
            'FinalResult'          => $labTrans->FinalResult ?? "N/A",
            'Status'               => $labTrans->Status ?? "N/A",
            'CreateDate'           => $labTrans->CreateDate ?? "N/A",
            'CreateBy'             => $labTrans->CreateBy ?? "N/A",
            'LastModifiedDate'     => $labTrans->LastModifiedDate ?? "N/A",
            'LastModifiedBy'       => $labTrans->LastModifiedBy ?? "N/A",
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
 *     path="/api/labtrans",
 *     summary="Create a new Lab Transaction",
 *     tags={"Lab Trans"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"LabNumber", "LabTest", "TransDate", "PatientID"},
 *             @OA\Property(property="LabNumber", type="string", example="LAB-12345"),
 *             @OA\Property(property="LabTest", type="string", example="Blood Test"),
 *             @OA\Property(property="TransDate", type="string", format="date", example="2024-09-06"),
 *             @OA\Property(property="DoctorReferral", type="string", example="Dr. John Doe"),
 *             @OA\Property(property="NIK", type="string", example="1234567890123456"),
 *             @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
 *             @OA\Property(property="BB", type="string", example="70"),
 *             @OA\Property(property="TB", type="string", example="170"),
 *             @OA\Property(property="LP", type="string", example="80"),
 *             @OA\Property(property="TD", type="string", example="120/80"),
 *             @OA\Property(property="BMI", type="string", example="22.5"),
 *             @OA\Property(
 *                 property="Lab_Trans_Details",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="ItemTestID", type="integer", example=101),
 *                     @OA\Property(property="ItemTestCode", type="string", example="T-001"),
 *                     @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
 *                     @OA\Property(property="Group", type="string", example="Hematology"),
 *                     @OA\Property(property="SubGroup", type="string", example="Blood"),
 *                     @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level"),
 *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
 *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
 *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
 *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="Lab_Trans_Others",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                     @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                     @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
 *                 )
 *             ),
 *             @OA\Property(property="FinalStatement", type="string", example="Sehat"),
 *             @OA\Property(property="FinalResult", type="string", example="Normal"),
 *             @OA\Property(property="Status", type="string", example="Completed"),
 *             @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *             @OA\Property(property="CreateBy", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Data berhasil disimpan",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Data berhasil disimpan"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="ID", type="integer", example=1),
 *                 @OA\Property(property="LabNumber", type="string", example="LAB-12345"),
 *                 @OA\Property(property="LabTest", type="string", example="Blood Test"),
 *                 @OA\Property(property="TransDate", type="string", format="date", example="2024-09-06"),
 *                 @OA\Property(property="DoctorReferral", type="string", example="Dr. John Doe"),
 *                 @OA\Property(property="Age", type="integer", example=30),
 *                 @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
 *                 @OA\Property(property="BB", type="string", example="70"),
 *                 @OA\Property(property="TB", type="string", example="170"),
 *                 @OA\Property(property="LP", type="string", example="80"),
 *                 @OA\Property(property="TD", type="string", example="120/80"),
 *                 @OA\Property(property="BMI", type="string", example="22.5"),
 *                 @OA\Property(
 *                     property="patient",
 *                     type="object",
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="NIK", type="string", example="1234567890123456"),
 *                     @OA\Property(property="FullName", type="string", example="John Doe"),
 *                     @OA\Property(property="Sex", type="string", example="Male"),
 *                     @OA\Property(property="BirthDate", type="string", format="date", example="1994-05-12"),
 *                     @OA\Property(property="Address", type="string", example="Jl. Merdeka No. 10, Jakarta"),
 *                     @OA\Property(property="Phone", type="string", example="08123456789"),
 *                 ),
 *                 @OA\Property(
 *                     property="Lab_Trans_Details",
 *                     type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="ID", type="integer", example=1),
 *                         @OA\Property(property="ItemTestID", type="integer", example=101),
 *                         @OA\Property(property="ItemTestCode", type="string", example="T-001"),
 *                         @OA\Property(property="ItemTestName", type="string", example="Hemoglobin Test"),
 *                         @OA\Property(property="Group", type="string", example="Hematology"),
 *                         @OA\Property(property="SubGroup", type="string", example="Blood"),
 *                         @OA\Property(property="Descriptions", type="string", example="Test for hemoglobin level"),
 *                         @OA\Property(property="ResultValue", type="string", example="5.4"),
 *                         @OA\Property(property="Unit", type="string", example="mmol/L"),
 *                         @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
 *                         @OA\Property(property="ResultNotes", type="string", example="Normal"),
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="Lab_Trans_Others",
 *                     type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="ID", type="integer", example=1),
 *                         @OA\Property(property="SupportServiceID", type="integer", example=201),
 *                         @OA\Property(property="SupportServiceCode", type="string", example="SS-001"),
 *                         @OA\Property(property="SupportServiceName", type="string", example="Radiology"),
 *                         @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
 *                     )
 *                 ),
 *                 @OA\Property(property="FinalStatement", type="string", example="Sehat"),
 *                 @OA\Property(property="FinalResult", type="string", example="Normal"),
 *                 @OA\Property(property="Status", type="string", example="Completed"),
 *                 @OA\Property(property="CreateDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                 @OA\Property(property="CreateBy", type="string", example="admin")
 *             )
 *         )
 *     )
 * )
 */


    public function store(Request $request)
{
    // Validasi input
    $validatedData = $request->validate([
        'LabNumber' => 'required|string|max:20|unique:Lab_Trans,LabNumber',
        'LabTest' => 'required|string|max:20',
        'TransDate' => 'required|date',
        'DoctorReferral' => 'nullable|string|max:50',
        'NIK' => 'required|string|exists:Patient,NIK', // Ubah validasi menjadi berdasarkan NIK
        'Anamnesa' => 'nullable|string|max:250',
        'BB' => 'nullable|string|max:10',
        'TB' => 'nullable|string|max:10',
        'LP' => 'nullable|string|max:10',
        'TD' => 'nullable|string|max:10',
        'BMI' => 'nullable|string|max:10',
        'FinalStatement' => 'nullable|string|max:250',
        'FinalResult' => 'nullable|string|max:20',
        'Status' => 'nullable|string|max:20',
        'CreateBy' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:20',
        'gcrecord' => 'nullable|boolean',
        'Lab_Trans_Details' => 'nullable|array',
        'Lab_Trans_Details.*.ItemTestID' => 'required|integer',
        'Lab_Trans_Details.*.ResultValue' => 'nullable|string',
        'Lab_Trans_Details.*.Unit' => 'nullable|string',
        'Lab_Trans_Details.*.ReferenceValue' => 'nullable|string',
        'Lab_Trans_Details.*.ResultNotes' => 'nullable|string',
        'Lab_Trans_Others' => 'nullable|array',
        'Lab_Trans_Others.*.SupportServiceID' => 'required|integer',
        'Lab_Trans_Others.*.SupportServiceNotes' => 'nullable|string',
    ]);

    // Cek apakah NIK ada di tabel Patient
    $patient = Patient::where('NIK', $validatedData['NIK'])->first();

    // Jika NIK tidak ditemukan, kembalikan error validasi
    if (!$patient) {
        return response()->json([
            'message' => 'NIK tidak ditemukan dalam database pasien',
            'errors' => ['NIK' => ['NIK tidak terdaftar']],
        ], 400);
    }

    // Ambil PatientID berdasarkan NIK yang ditemukan
    $validatedData['PatientID'] = $patient->ID;

    // Hitung umur jika BirthDate tersedia
    $age = null;
    if ($patient->BirthDate) {
        $birthYear = date('Y', strtotime($patient->BirthDate));
        $currentYear = date('Y');
        $age = (string) ($currentYear - $birthYear);
    }
    $validatedData['Age'] = $age;

    // Tambahkan nilai default
    $validatedData['gcrecord'] = false;
    date_default_timezone_set('Asia/Jakarta');

    // Pastikan LastModifiedDate selalu diisi dengan waktu sekarang
    $validatedData['CreateDate'] = now()->format('Y-m-d H:i:s');

    // Simpan data ke tabel Lab_Trans
    $labTrans = Lab_Trans::create($validatedData);

    // Simpan data ke tabel Lab_Trans_Details jika ada
    if (!empty($validatedData['Lab_Trans_Details'])) {
        foreach ($validatedData['Lab_Trans_Details'] as $detail) {
            $detail['LabTransID'] = $labTrans->ID;
            $detail['CreateDate'] = now();
            $detail['CreateBy'] = $validatedData['CreateBy'] ?? 'system';
            $detail['gcrecord'] = false;
            LabTransDetail::create($detail);
        }
    }

    // Simpan data ke tabel Lab_Trans_Others jika ada
    if (!empty($validatedData['Lab_Trans_Others'])) {
        foreach ($validatedData['Lab_Trans_Others'] as $other) {
            $other['LabTransID'] = $labTrans->ID;
            $other['CreateDate'] = now();
            $other['CreateBy'] = $validatedData['CreateBy'] ?? 'system';
            $other['gcrecord'] = false;
            LabTransOther::create($other);
        }
    }

    // Format response
    $formattedData = [
        'ID' => $labTrans->ID,
        'LabNumber' => $labTrans->LabNumber,
        'LabTest' => $labTrans->LabTest,
        'TransDate' => $labTrans->TransDate,
        'DoctorReferral' => $labTrans->DoctorReferral,
        'Age' => $age,
        'Anamnesa' => $labTrans->Anamnesa,
        'BB' => $labTrans->BB,
        'TB' => $labTrans->TB,
        'LP' => $labTrans->LP,
        'TD' => $labTrans->TD,
        'BMI' => $labTrans->BMI,
        'NIK' => $validatedData['NIK'],
        'patient' => [
            'ID'                  => $patient->ID ?? null,
            'NIK'                 => $patient->NIK ?? null,
            'PatientID_Provider'  => $patient->PatientID_Provider ?? null,
            'FullName'            => $patient->FullName ?? null,
            'Sex'                 => $patient->Sex ?? null,
            'BirthDate'           => $patient->BirthDate ?? null,
            'Address'             => $patient->Address ?? null,
            'Phone'               => $patient->Phone ?? null,
        ],
        'Lab_Trans_Details' => $labTrans->labTransDetails->map(function ($detail) {
            return [
                'ID'              => $detail->ID,
                'ItemTestID'      => $detail->ItemTestID,
                'ResultValue'     => $detail->ResultValue,
                'Unit'            => $detail->Unit,
                'ReferenceValue'  => $detail->ReferenceValue,
                'ResultNotes'     => $detail->ResultNotes,
            ];
        }),
        'Lab_Trans_Others' => $labTrans->labTransOthers->map(function ($other) {
            return [
                'ID'                   => $other->ID,
                'SupportServiceID'     => $other->SupportServiceID,
                'SupportServiceNotes'  => $other->SupportServiceNotes,
            ];
        }),
        'CreateDate'=> $labTrans->CreateDate,
        'CreateBy'=> $labTrans->CreateBy,
    ];

    return response()->json([
        'message' => 'Data berhasil disimpan',
        'data' => $formattedData
    ], 201);
}


    /**
 * @OA\Put(
 *     path="/api/labtrans/{id}",
 *     summary="Update an existing Lab Transaction",
 *     tags={"Lab Trans"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the Lab Transaction",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"LabNumber", "LabTest", "TransDate", "PatientID"},
 *             @OA\Property(property="LabNumber", type="string", example="LAB-12345"),
 *             @OA\Property(property="LabTest", type="string", example="Blood Test"),
 *             @OA\Property(property="TransDate", type="string", format="date", example="2024-09-06"),
 *             @OA\Property(property="DoctorReferral", type="string", example="Dr. John Doe"),
 *             @OA\Property(property="PatientID", type="integer", example=1),
 *             @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
 *             @OA\Property(property="BB", type="string", example="70"),
 *             @OA\Property(property="TB", type="string", example="170"),
 *             @OA\Property(property="LP", type="string", example="80"),
 *             @OA\Property(property="TD", type="string", example="120/80"),
 *             @OA\Property(property="BMI", type="string", example="22.5"),
 *             @OA\Property(property="FinalStatement", type="string", example="Sehat"),
 *             @OA\Property(property="FinalResult", type="string", example="Normal"),
 *             @OA\Property(property="Status", type="string", example="Completed"),
 *             @OA\Property(property="LastModifiedBy", type="string", example="admin")
 *         )
 *     ),
 *      * @OA\Response(
 *         response=200,
 *         description="Data berhasil diupdate",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Data berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="ID", type="integer", example=1),
 *                 @OA\Property(property="LabNumber", type="string", example="LAB-12345"),
 *                 @OA\Property(property="LabTest", type="string", example="Blood Test"),
 *                 @OA\Property(property="TransDate", type="string", format="date", example="2024-09-06"),
 *                 @OA\Property(property="DoctorReferral", type="string", example="Dr. John Doe"),
 *                 
 *                 @OA\Property(property="patient", type="object",
 *                     @OA\Property(property="ID", type="integer", example=1),
 *                     @OA\Property(property="NIK", type="string", example="123456789"),
 *                     @OA\Property(property="FullName", type="string", example="John Doe"),
 *                     @OA\Property(property="Sex", type="string", example="Male"),
 *                     @OA\Property(property="BirthDate", type="string", format="date", example="1994-09-06"),
 *                     @OA\Property(property="Address", type="string", example="Jakarta, Indonesia"),
 *                     @OA\Property(property="Phone", type="string", example="081234567890"),
 *                 ),
 *                 
 *                 @OA\Property(property="Age", type="string", example="30"), 
 *                 @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
 *                 @OA\Property(property="BB", type="string", example="70"),
 *                 @OA\Property(property="TB", type="string", example="170"),
 *                 @OA\Property(property="LP", type="string", example="80"),
 *                 @OA\Property(property="TD", type="string", example="120/80"),
 *                 @OA\Property(property="BMI", type="string", example="22.5"),
 *                 @OA\Property(property="FinalStatement", type="string", example="Sehat"),
 *                 @OA\Property(property="FinalResult", type="string", example="Normal"),
 *                 @OA\Property(property="Status", type="string", example="Completed"),
 *                 @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2025-02-07 10:00:00"),
 *                 @OA\Property(property="LastModifiedBy", type="string", example="admin")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Lab Transaction not found"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request"
 *     )
 * )
 */

 public function update(Request $request, $id)
{
    // Find the lab transaction
    $labTrans = Lab_Trans::with(['patient'])
        ->where('ID', $id)
        ->where('gcrecord', 0)
        ->firstOrFail();

    // Validate the request data
    $validatedData = $request->validate([
        'LabNumber' => 'required|string|max:20',
        'LabTest' => 'required|string|max:20',
        'TransDate' => 'required|date',
        'DoctorReferral' => 'nullable|string|max:50',
        'PatientID' => 'required|exists:Patient,ID',
        'Anamnesa' => 'nullable|string|max:250',
        'BB' => 'nullable|string|max:10',
        'TB' => 'nullable|string|max:10',
        'LP' => 'nullable|string|max:10',
        'TD' => 'nullable|string|max:10',
        'BMI' => 'nullable|string|max:10',
        'FinalStatement' => 'nullable|string|max:250',
        'FinalResult' => 'nullable|string|max:20',
        'Status' => 'nullable|string|max:20',
        'LastModifiedBy' => 'nullable|string|max:20',
    ]);

    // Update LastModifiedDate otomatis ke waktu sekarang
    date_default_timezone_set('Asia/Jakarta');
    $validatedData['LastModifiedDate'] = now()->format('Y-m-d H:i:s');

    // Update the lab transaction
    $labTrans->update($validatedData);

    // Ambil data pasien jika gcrecord = 0, jika gcrecord = 1 maka kosongkan
    $patient = ($labTrans->patient && $labTrans->patient->gcrecord == 0) ? $labTrans->patient : null;

    // Hitung umur otomatis berdasarkan BirthDate pasien jika patient ada
    $age = null;
    if ($patient && $patient->BirthDate) {
        $birthYear = date('Y', strtotime($patient->BirthDate));
        $currentYear = date('Y');
        $age = (string) ($currentYear - $birthYear);
    }

    // Format the response
    $formattedData = [
        'ID' => $labTrans->ID,
        'LabNumber' => $labTrans->LabNumber,
        'LabTest' => $labTrans->LabTest,
        'TransDate' => $labTrans->TransDate,
        'DoctorReferral' => $labTrans->DoctorReferral,
        'patient' => $patient ? [
            'ID'                  => $patient->ID ?? null,
            'NIK'                 => $patient->NIK ?? null,
            'PatientID_Provider'  => $patient->PatientID_Provider ?? null,
            'FullName'            => $patient->FullName ?? null,
            'Sex'                 => $patient->Sex ?? null,
            'BirthDate'           => $patient->BirthDate ?? null,
            'Address'             => $patient->Address ?? null,
            'Phone'               => $patient->Phone ?? null,
            // 'LastModifiedBy'      => $patient->LastModifiedBy ?? null,
        ] : null, // Jika gcrecord = 1, maka patient = null
        'Age' => $age, // Umur otomatis dihitung jika pasien tersedia
        'Anamnesa' => $labTrans->Anamnesa,
        'BB' => $labTrans->BB,
        'TB' => $labTrans->TB,
        'LP' => $labTrans->LP,
        'TD' => $labTrans->TD,
        'BMI' => $labTrans->BMI,
        'FinalStatement' => $labTrans->FinalStatement,
        'FinalResult' => $labTrans->FinalResult,
        'Status' => $labTrans->Status,
        'Status' => $labTrans->Status,
        'LastModifiedDate' => $labTrans->LastModifiedDate,
        'LastModifiedBy' => $labTrans->LastModifiedBy,
    ];

    return response()->json([
        'message' => 'Data berhasil diupdate',
        'data' => $formattedData
    ], 200);
}
}