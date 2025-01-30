<?php

namespace App\Http\Controllers;

use App\Models\Lab_Trans;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LabTransController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/labtrans",
     *     tags={"Lab Trans"},
     *     summary="Search Lab Trans by various fields",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query that will be applied to multiple fields (e.g., LabNumber, LabTest, PatientID)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Lab Trans",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LabTrans")
     *         )
     *     )
     * )
     */


    public function index(Request $request)
    {
        // Get the search query (if provided)
        $search = $request->query('q');

        // Query LabTrans dengan filter gcrecord = 0
        $labTrans = Lab_Trans::with([
            'patient' => function ($query) {
                $query->where('gcrecord', 0); // Pastikan hanya pasien dengan gcrecord = 0
            },
            'labTransDetails' => function ($query) {
                $query->where('gcrecord', 0); // Pastikan hanya Lab_Trans_Details dengan gcrecord = 0
            },
            'labTransOthers' => function ($query) {
                $query->where('gcrecord', 0); // Pastikan hanya Lab_Trans_Others dengan gcrecord = 0
            }
        ])
            ->where('gcrecord', 0) // Filter hanya data Lab_Trans yang belum dihapus
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('LabNumber', 'like', '%' . $search . '%')
                        ->orWhere('LabTest', 'like', '%' . $search . '%')
                        ->orWhere('TransDate', 'like', '%' . $search . '%')
                        ->orWhereHas('patient', function ($query) use ($search) {
                            $query->where('FullName', 'like', '%' . $search . '%')
                                ->orWhere('NIK', 'like', '%' . $search . '%');
                        });
                });
            })
            ->get();

        // Format the response
        $formattedData = $labTrans->map(function ($labTrans) {
            return [
                'ID' => $labTrans->ID,
                'LabNumber' => $labTrans->LabNumber,
                'LabTest' => $labTrans->LabTest,
                'TransDate' => $labTrans->TransDate,
                'DoctorReferral' => $labTrans->DoctorReferral,
                'Age' => $labTrans->Age,
                'Anamnesa' => $labTrans->Anamnesa,
                'BB' => $labTrans->BB,
                'TB' => $labTrans->TB,
                'LP' => $labTrans->LP,
                'TD' => $labTrans->TD,
                'BMI' => $labTrans->BMI,
                'FinalStatement' => $labTrans->FinalStatement,
                'FinalResult' => $labTrans->FinalResult,
                'Status' => $labTrans->Status,
                'CreateDate' => $labTrans->CreateDate,
                'CreateBy' => $labTrans->CreateBy,
                'LastModifiedDate' => $labTrans->LastModifiedDate,
                'LastModifiedBy' => $labTrans->LastModifiedBy,
                'gcrecord' => $labTrans->gcrecord,
                'PatientID' => $labTrans->PatientID,
                'patient' => $labTrans->patient ? [
                    [
                        'NIK'                 => $labTrans->patient->NIK ?? null,
                        'PatientID_Provider'  => $labTrans->patient->PatientID_Provider ?? null,
                        'FullName'            => $labTrans->patient->FullName ?? null,
                        'Sex'                 => $labTrans->patient->Sex ?? null,
                        'BirthDate'           => $labTrans->patient->BirthDate ?? null,
                        'Address'             => $labTrans->patient->Address ?? null,
                        'Phone'               => $labTrans->patient->Phone ?? null,
                        'CreateBy'            => $labTrans->patient->CreateBy ?? null,
                        'LastModifiedBy'      => $labTrans->patient->LastModifiedBy ?? null,
                        'gcrecord'            => $labTrans->patient->gcrecord ?? null,
                    ]
                ] : [],
                'Lab_Trans_Details' => $labTrans->labTransDetails->map(function ($detail) {
                    return [
                        'LabTransID'       => $detail->LabTransID,
                        'ItemTestID'       => $detail->ItemTestID,
                        'ResultValue'      => $detail->ResultValue,
                        'Unit'             => $detail->Unit,
                        'ReferenceValue'   => $detail->ReferenceValue,
                        'ResultNotes'      => $detail->ResultNotes,
                        'CreateDate'       => $detail->CreateDate,
                        'CreateBy'         => $detail->CreateBy ?? null,
                        'LastModifiedDate' => $detail->LastModifiedDate ?? null,
                        'LastModifiedBy'   => $detail->LastModifiedBy ?? null,
                        'gcrecord'         => $detail->gcrecord ?? null,
                    ];
                }),

                'Lab_Trans_Others' => $labTrans->labTransOthers->map(function ($other) {
                    return [
                        'LabTransID'          => $other->LabTransID,
                        'SupportServiceID'    => $other->SupportServiceID,
                        'SupportServiceNotes' => $other->SupportServiceNotes,
                        'CreateDate'          => $other->CreateDate,
                        'CreateBy'            => $other->CreateBy ?? null,
                        'LastModifiedDate'    => $other->LastModifiedDate ?? null,
                        'LastModifiedBy'      => $other->LastModifiedBy ?? null,
                        'gcrecord'            => $other->gcrecord ?? null,
                    ];
                }),

            ];
        });

        return response()->json($formattedData, 200);
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
     *             @OA\Property(property="PatientID", type="integer", example=1),
     *             @OA\Property(property="Age", type="string", example="30"),
     *             @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
     *             @OA\Property(property="BB", type="string", example="70"),
     *             @OA\Property(property="TB", type="string", example="170"),
     *             @OA\Property(property="LP", type="string", example="80"),
     *             @OA\Property(property="TD", type="string", example="120/80"),
     *             @OA\Property(property="BMI", type="string", example="22.5"),
     *             @OA\Property(property="FinalStatement", type="string", example="Sehat"),
     *             @OA\Property(property="FinalResult", type="string", example="Normal"),
     *             @OA\Property(property="Status", type="string", example="Completed"),
     *             @OA\Property(property="CreateDate", type="string", format="date", example="2024-09-06"),
     *             @OA\Property(property="CreateBy", type="string", example="admin"),
     *             @OA\Property(property="LastModifiedDate", type="string", format="date", example="2024-09-06"),
     *             @OA\Property(property="LastModifiedBy", type="string", example="admin"),
     *             @OA\Property(property="gcrecord", type="boolean", example=false),
     *             @OA\Property(
     *                 property="Lab_Trans_Details",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="LabTransId", type="integer", example=201),
     *                     @OA\Property(property="ItemTestID", type="integer", example=101),
     *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
     *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
     *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
     *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
     *                     @OA\Property(property="CreateDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="normal"),
     *                     @OA\Property(property="gcrecord", type="boolean", example="false"),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="Lab_Trans_Others",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="LabTransId", type="integer", example=201),
     *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
     *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
     *                     @OA\Property(property="CreateDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="normal"),
     *                     @OA\Property(property="gcrecord", type="boolean", example="false"),
     *                 )
     *             )
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
     *                 @OA\Property(property="PatientID", type="integer", example=1)
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
        $validatedData = $request->validate([
            'LabNumber' => 'required|string|max:20',
            'LabTest' => 'required|string|max:20',
            'TransDate' => 'required|date',
            'DoctorReferral' => 'nullable|string|max:50',
            'PatientID' => 'required|exists:Patient,ID',
            'Age' => 'nullable|string|max:10',
            'Anamnesa' => 'nullable|string|max:250',
            'BB' => 'nullable|string|max:10',
            'TB' => 'nullable|string|max:10',
            'LP' => 'nullable|string|max:10',
            'TD' => 'nullable|string|max:10',
            'BMI' => 'nullable|string|max:10',
            'FinalStatement' => 'nullable|string|max:250',
            'FinalResult' => 'nullable|string|max:20',
            'Status' => 'nullable|string|max:20',
            'CreateDate' => 'nullable|date',
            'CreateBy' => 'nullable|string|max:20',
            'LastModifiedDate' => 'nullable|date',
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

        // Create the lab transaction
        $labTrans = Lab_Trans::create($validatedData);

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $labTrans
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
     *             @OA\Property(property="Age", type="string", example="30"),
     *             @OA\Property(property="Anamnesa", type="string", example="Pasien mengalami demam tinggi"),
     *             @OA\Property(property="BB", type="string", example="70"),
     *             @OA\Property(property="TB", type="string", example="170"),
     *             @OA\Property(property="LP", type="string", example="80"),
     *             @OA\Property(property="TD", type="string", example="120/80"),
     *             @OA\Property(property="BMI", type="string", example="22.5"),
     *             @OA\Property(property="FinalStatement", type="string", example="Sehat"),
     *             @OA\Property(property="FinalResult", type="string", example="Normal"),
     *             @OA\Property(property="Status", type="string", example="Completed"),
     *             @OA\Property(property="LastModifiedDate", type="string", format="date-time", example="2024-09-06T12:30:00"),
     *             @OA\Property(property="LastModifiedBy", type="string", example="admin"),
     *             @OA\Property(
     *                 property="Lab_Trans_Details",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="LabTransId", type="integer", example=201),
     *                     @OA\Property(property="ItemTestID", type="integer", example=101),
     *                     @OA\Property(property="ResultValue", type="string", example="5.4"),
     *                     @OA\Property(property="Unit", type="string", example="mmol/L"),
     *                     @OA\Property(property="ReferenceValue", type="string", example="3.9-6.1"),
     *                     @OA\Property(property="ResultNotes", type="string", example="Normal"),
     *                     @OA\Property(property="CreateDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="normal"),
     *                     @OA\Property(property="gcrecord", type="boolean", example="false"),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="Lab_Trans_Others",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="LabTransId", type="integer", example=201),
     *                     @OA\Property(property="SupportServiceID", type="integer", example=201),
     *                     @OA\Property(property="SupportServiceNotes", type="string", example="Rontgen dada dilakukan"),
     *                     @OA\Property(property="CreateDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="CreateBy", type="string", example="admin"),
     *                     @OA\Property(property="LastModifiedDate", type="string", format="date", example="2024-09-06"),
     *                     @OA\Property(property="LastModifiedBy", type="string", example="normal"),
     *                     @OA\Property(property="gcrecord", type="boolean", example="false"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data berhasil diupdate"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="ID", type="integer"),
     *                 @OA\Property(property="LabNumber", type="string"),
     *                 @OA\Property(property="LabTest", type="string"),
     *                 @OA\Property(property="TransDate", type="string"),
     *                 @OA\Property(property="PatientID", type="integer"),
     *                 @OA\Property(property="patient", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="Lab_Trans_Details", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="Lab_Trans_Others", type="array", @OA\Items(type="object"))
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
        $labTrans = Lab_Trans::with(['patient', 'labTransDetails', 'labTransOthers'])
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
            'Age' => 'nullable|string|max:10',
            'Anamnesa' => 'nullable|string|max:250',
            'BB' => 'nullable|string|max:10',
            'TB' => 'nullable|string|max:10',
            'LP' => 'nullable|string|max:10',
            'TD' => 'nullable|string|max:10',
            'BMI' => 'nullable|string|max:10',
            'FinalStatement' => 'nullable|string|max:250',
            'FinalResult' => 'nullable|string|max:20',
            'Status' => 'nullable|string|max:20',
            'LastModifiedDate' => 'nullable|date',
            'LastModifiedBy' => 'nullable|string|max:20',
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

        // Update the lab transaction
        $labTrans->update($validatedData);

        // Format the response
        $formattedData = [
            'ID' => $labTrans->ID,
            'LabNumber' => $labTrans->LabNumber,
            'LabTest' => $labTrans->LabTest,
            'TransDate' => $labTrans->TransDate,
            'DoctorReferral' => $labTrans->DoctorReferral,
            'Age' => $labTrans->Age,
            'Anamnesa' => $labTrans->Anamnesa,
            'BB' => $labTrans->BB,
            'TB' => $labTrans->TB,
            'LP' => $labTrans->LP,
            'TD' => $labTrans->TD,
            'BMI' => $labTrans->BMI,
            'FinalStatement' => $labTrans->FinalStatement,
            'FinalResult' => $labTrans->FinalResult,
            'Status' => $labTrans->Status,
            'CreateDate' => $labTrans->CreateDate,
            'CreateBy' => $labTrans->CreateBy,
            'LastModifiedDate' => $labTrans->LastModifiedDate,
            'LastModifiedBy' => $labTrans->LastModifiedBy,
            'gcrecord' => $labTrans->gcrecord,
            'patient' => $labTrans->patient ? [
                [
                    'NIK' => $labTrans->patient->NIK ?? null,
                    'PatientID_Provider' => $labTrans->patient->PatientID_Provider ?? null,
                    'FullName' => $labTrans->patient->FullName ?? null,
                    'Sex' => $labTrans->patient->Sex ?? null,
                    'BirthDate' => $labTrans->patient->BirthDate ?? null,
                    'Address' => $labTrans->patient->Address ?? null,
                    'Phone' => $labTrans->patient->Phone ?? null,
                    'CreateBy' => $labTrans->patient->CreateBy ?? null,
                    'LastModifiedBy' => $labTrans->patient->LastModifiedBy ?? null,
                    'gcrecord' => $labTrans->patient->gcrecord ?? null,
                ]
            ] : [],
            'Lab_Trans_Details' => $labTrans->labTransDetails->map(function ($detail) {
                return [
                    'LabTransID' => $detail->LabTransID,
                    'ItemTestID' => $detail->ItemTestID,
                    'ResultValue' => $detail->ResultValue,
                    'Unit' => $detail->Unit,
                    'ReferenceValue' => $detail->ReferenceValue,
                    'ResultNotes' => $detail->ResultNotes,
                    'CreateDate' => $detail->CreateDate,
                    'CreateBy' => $detail->CreateBy ?? null,
                    'LastModifiedDate' => $detail->LastModifiedDate ?? null,
                    'LastModifiedBy' => $detail->LastModifiedBy ?? null,
                    'gcrecord' => $detail->gcrecord ?? null,
                ];
            }),
            'Lab_Trans_Others' => $labTrans->labTransOthers->map(function ($other) {
                return [
                    'LabTransID' => $other->LabTransID,
                    'SupportServiceID' => $other->SupportServiceID,
                    'SupportServiceNotes' => $other->SupportServiceNotes,
                    'CreateDate' => $other->CreateDate,
                    'CreateBy' => $other->CreateBy ?? null,
                    'LastModifiedDate' => $other->LastModifiedDate ?? null,
                    'LastModifiedBy' => $other->LastModifiedBy ?? null,
                    'gcrecord' => $other->gcrecord ?? null,
                ];
            }),
        ];

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $formattedData
        ], 200);
    }
}
