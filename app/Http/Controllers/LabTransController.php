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
     
     // Query LabTrans with relations to Patient, applying search if provided
     $labTrans = Lab_Trans::with('patient')
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
 
     // Format the response as before
     $formattedData = $labTrans->map(function ($labTrans) {
         return [
             'ID' => $labTrans->ID,
             'LabNumber' => $labTrans->LabNumber,
             'LabTest' => $labTrans->LabTest,
             'TransDate' => $labTrans->TransDate,
             'PatientID' => $labTrans->PatientID,
             'patient' => $labTrans->patient ? [
                 'NIK' => $labTrans->patient->NIK ?? null,
                 'FullName' => $labTrans->patient->FullName ?? null,
                //  'PatientID_Provider' => $labTrans->patient->PatientID_Provider ?? null,
             ] : null,
             'Lab_Trans_Details' => $labTrans->labTransDetails->map(function ($detail) {
                 return [
                     'ItemTestID' => $detail->ItemTestID,
                     'ResultValue' => $detail->ResultValue,
                     'Unit' => $detail->Unit,
                 ];
             }),
             'Lab_Trans_Others' => $labTrans->labTransOthers->map(function ($other) {
                 return [
                     'SupportServiceID' => $other->SupportServiceID,
                     'SupportServiceNotes' => $other->SupportServiceNotes,
                 ];
             }),
         ];
     });
 
     return response()->json($formattedData, 200);
 }
 
    /**
     * @OA\Post(
     *     path="/api/labtrans",
     *     tags={"Lab Trans"},
     *     summary="Create a new Lab Trans",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LabTrans")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The created Lab Trans",
     *         @OA\JsonContent(ref="#/components/schemas/LabTrans")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'LabNumber' => 'required|string|max:20',
            'LabTest' => 'required|string|max:20',
            'TransDate' => 'required|date',
            'DoctorReferral' => 'nullable|string|max:50',
            'PatientID' => 'required|exists:Patient,id',
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
        ]);

        // Simpan data LabTrans
        $labTrans = Lab_Trans::create($request->all());

        // Format ulang respon
        $formattedResponse = [
            'ID' => $labTrans->id,
            'LabNumber' => $labTrans->LabNumber,
            'LabTest' => $labTrans->LabTest,
            'TransDate' => $labTrans->TransDate,
            'PatientID' => $labTrans->PatientID,
            'patient' => $labTrans->Patient ? [
                'id' => $labTrans->Patient->id,
                'NIK' => $labTrans->Patient->NIK ?? null,
                'FullName' => $labTrans->Patient->FullName ?? null,
                'Sex' => $labTrans->Patient->Sex ?? null,
                'BirthDate' => $labTrans->Patient->BirthDate ?? null,
                'Address' => $labTrans->Patient->Address ?? null,
                'Phone' => $labTrans->Patient->Phone ?? null,
            ] : null, // Jika Patient null, set patient sebagai null
        ];
        

        return response()->json($formattedResponse, 201);
    }
}
