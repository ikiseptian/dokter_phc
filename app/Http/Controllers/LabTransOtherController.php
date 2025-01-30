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
 *         name="search",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string"),
 *         description="Search across multiple fields (LabNumber, SupportServiceNotes, etc.)"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/LabTransOther")
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
     $query = LabTransOther::with(['labTrans', 'supportService'])
         ->where('gcrecord', 0);
 
     if ($request->has('search')) {
         $search = $request->search;
         $query->where(function ($q) use ($search) {
             $q->where('SupportServiceNotes', 'like', "%{$search}%")
               ->orWhereHas('labTrans', function ($q) use ($search) {
                   $q->where('LabNumber', 'like', "%{$search}%")
                     ->orWhere('DoctorReferral', 'like', "%{$search}%");
               })
               ->orWhereHas('supportService', function ($q) use ($search) {
                   $q->where('SupportServiceCode', 'like', "%{$search}%")
                     ->orWhere('SupportServiceName', 'like', "%{$search}%");
               });
         });
     }
 
     $labTransOthers = $query->get();
 
     if ($labTransOthers->isEmpty()) {
         return response()->json(['message' => 'Data not found'], 404);
     }
 
    
 

        // Format ulang data untuk respons
        $formattedData = $labTransOthers->map(function ($labTransOther) {
            return [
                'ID' => $labTransOther->ID,
                'SupportServiceNotes' => $labTransOther->SupportServiceNotes,
                'CreateDate' => $labTransOther->CreateDate,
                'CreateBy' => $labTransOther->CreateBy,
                'LastModifiedDate' => $labTransOther->LastModifiedDate,
                'LastModifiedBy	' => $labTransOther->LastModifiedBy,
                'gcrecord' => $labTransOther->gcrecord,

                'LabTransID' => $labTransOther->LabTransID,
                'LabTrans' => $labTransOther->labTrans ? [
                    'LabNumber'        => $labTransOther->labTrans->LabNumber,
                    'LabTest'          => $labTransOther->labTrans->LabTest,
                    'TransDate'        => $labTransOther->labTrans->TransDate,
                    'DoctorReferral'   => $labTransOther->labTrans->DoctorReferral,
                    'PatientID'        => $labTransOther->labTrans->PatientID,
                    'Age'              => $labTransOther->labTrans->Age,
                    'Anamnesa'         => $labTransOther->labTrans->Anamnesa,
                    'BB'               => $labTransOther->labTrans->BB,
                    'TB'               => $labTransOther->labTrans->TB,
                    'LP'               => $labTransOther->labTrans->LP,
                    'TD'               => $labTransOther->labTrans->TD,
                    'BMI'              => $labTransOther->labTrans->BMI,
                    'FinalStatement'   => $labTransOther->labTrans->FinalStatement,
                    'FinalResult'      => $labTransOther->labTrans->FinalResult,
                    'Status'           => $labTransOther->labTrans->Status,
                    'CreateDate'       => $labTransOther->labTrans->CreateDate,
                    'CreateBy'         => $labTransOther->labTrans->CreateBy,
                    'LastModifiedDate' => $labTransOther->labTrans->LastModifiedDate,
                    'LastModifiedBy'   => $labTransOther->labTrans->LastModifiedBy,
                    'gcrecord'         => $labTransOther->labTrans->gcrecord,
                ] : null,

                'SupportServiceID' => $labTransOther->SupportServiceID,
                'SupportService' => $labTransOther->supportService ? [
                    'SupportServiceCode' => $labTransOther->supportService->SupportServiceCode,
                    'SupportServiceName' => $labTransOther->supportService->SupportServiceName,
                    'CreateDate' => $labTransOther->supportService->CreateDate,
                    'CreateBy' => $labTransOther->supportService->CreateBy,
                    'LastModifiedDate' => $labTransOther->supportService->LastModifiedDate,
                    'LastModifiedBy' => $labTransOther->supportService->LastModifiedBy,
                ] : null,

            ];
        });

        return response()->json($formattedData, 200);
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
 *             @OA\Property(property="SupportServiceNotes", type="string"),
 *             @OA\Property(property="CreateDate", type="string", format="date"),
 *             @OA\Property(property="CreateBy", type="string"),
 *             @OA\Property(property="LastModifiedDate", type="string", format="date"),
 *             @OA\Property(property="LastModifiedBy", type="string"),
 *             @OA\Property(property="LabTransID", type="integer"),
 *             @OA\Property(property="SupportServiceID", type="integer"),
 *             @OA\Property(property="gcrecord", type="integer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/LabTransOther")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Data not found"
 *     )
 * )
 */
public function update(Request $request, $ID) // Gunakan $ID (huruf besar)
{
    // Cari data berdasarkan ID dan pastikan gcrecord = 0
    $labTransOther = LabTransOther::where('ID', $ID)->where('gcrecord', 0)->first();

    if (!$labTransOther) {
        return response()->json(['message' => 'Data not found'], 404);
    }

    // Validasi semua field yang bisa di-update
    $validatedData = $request->validate([
        'SupportServiceNotes' => 'nullable|string',
        'CreateDate' => 'nullable|date',
        'CreateBy' => 'nullable|string',
        'LastModifiedDate' => 'nullable|date',
        'LastModifiedBy' => 'nullable|string',
        'LabTransID' => 'nullable|integer|exists:Lab_Trans,ID',
        'SupportServiceID' => 'nullable|integer|exists:Support_Service,ID',
        'gcrecord' => 'nullable|integer'
    ]);

    // Update data
    $labTransOther->update($validatedData);

    return response()->json([
        'message' => 'Data updated successfully',
        'data' => $labTransOther
    ], 200);
}
}