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
 *         name="search",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string"),
 *         description="Search across multiple fields"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Filtered list of Lab Trans",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/LabTransDetail")
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
     $query = LabTransDetail::with(['labTrans', 'itemTest'])
         ->where('gcrecord', 0); // Hanya mengambil data yang tidak dihapus
 
     if ($request->has('search')) {
         $search = $request->search;
         $query->where(function ($q) use ($search) {
             $q->where('ResultValue', 'like', "%{$search}%")
               ->orWhere('Unit', 'like', "%{$search}%")
               ->orWhere('ReferenceValue', 'like', "%{$search}%")
               ->orWhereHas('labTrans', function ($q) use ($search) {
                   $q->where('LabNumber', 'like', "%{$search}%");
               })
               ->orWhereHas('itemTest', function ($q) use ($search) {
                   $q->where('ItemTestName', 'like', "%{$search}%");
               });
         });
     }
 
     $labTransDetails = $query->get();
 
     if ($labTransDetails->isEmpty()) {
         return response()->json(['message' => 'Data not found'], 404);
     }
 
     
 


        // Format respons
        $formattedData = $labTransDetails->map(function ($detail) {
            return [
                'ID' => $detail->ID,
                'ResultValue' => $detail->ResultValue,
                'Unit' => $detail->Unit,
                'ReferenceValue' => $detail->ReferenceValue,
                'ResultNotes' => $detail->ResultNotes,
                'CreateDate' => $detail->CreateDate,
                'CreateDate' => $detail->CreateDate,
                'gcrecord' => $detail->gcrecord,
                'LabTransID' => $detail->LabTransId,
                'labTrans' => $detail->labTrans ? [
                    'LabNumber'        => $detail->labTrans->LabNumber,
                    'LabTest'          => $detail->labTrans->LabTest,
                    'TransDate'        => $detail->labTrans->TransDate,
                    'DoctorReferral'   => $detail->labTrans->DoctorReferral,
                    'PatientID'        => $detail->labTrans->PatientID,
                    'Age'              => $detail->labTrans->Age,
                    'Anamnesa'         => $detail->labTrans->Anamnesa,
                    'BB'               => $detail->labTrans->BB,
                    'TB'               => $detail->labTrans->TB,
                    'LP'               => $detail->labTrans->LP,
                    'TD'               => $detail->labTrans->TD,
                    'BMI'              => $detail->labTrans->BMI,
                    'FinalStatement'   => $detail->labTrans->FinalStatement,
                    'FinalResult'      => $detail->labTrans->FinalResult,
                    'Status'           => $detail->labTrans->Status,
                    'CreateDate'       => $detail->labTrans->CreateDate,
                    'CreateBy'         => $detail->labTrans->CreateBy,
                    'LastModifiedDate' => $detail->labTrans->LastModifiedDate,
                    'LastModifiedBy'   => $detail->labTrans->LastModifiedBy,
                    'gcrecord'         => $detail->labTrans->gcrecord,
                ] : null,

                'ItemTestID' => $detail->ItemTestID,
                'itemTest' => $detail->itemTest ? [
                    'ItemTestCode'      => $detail->itemTest->ItemTestCode,
                    'ItemTestName'      => $detail->itemTest->ItemTestName,
                    'Group'            => $detail->itemTest->Group,
                    'SubGroup'         => $detail->itemTest->SubGroup,
                    'Descriptions'     => $detail->itemTest->Descriptions,
                    'CreateDate'       => $detail->itemTest->CreateDate,
                    'CreateBy'         => $detail->itemTest->CreateBy,
                    'LastModifiedDate' => $detail->itemTest->LastModifiedDate,
                    'LastModifiedBy'   => $detail->itemTest->LastModifiedBy,
                    'gcrecord'         => $detail->itemTest->gcrecord,
                ] : null,


            ];
        });

        return response()->json($formattedData);
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
     *             @OA\Property(property="ResultValue", type="string", example="Positive"),
     *             @OA\Property(property="Unit", type="string", example="mg/dL"),
     *             @OA\Property(property="ReferenceValue", type="string", example="70-110"),
     *             @OA\Property(property="ResultNotes", type="string", example="Normal Range"),
     *             @OA\Property(property="ItemTestID", type="integer", example=5),
     *             @OA\Property(property="LabTransID", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LabTransDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found"
     *     )
     * )
     */
    public function update(Request $request, $ID)
    {
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
            'ItemTestID' => 'nullable|integer|exists:Item_Test,ID',
            'LabTransID' => 'nullable|integer|exists:Lab_Trans,ID'
        ]);

        // Update data
        $labTransDetail->update($validatedData);

        return response()->json([
            'message' => 'Data updated successfully',
            'data' => $labTransDetail
        ], 200);
    }
}
