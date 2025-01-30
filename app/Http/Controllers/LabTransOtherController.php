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
     *     summary="Get all LabTransOthers",
     *     tags={"LabTransOther"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LabTransOther")
     *         )
     *     )
     * )
     */
    public function index()
{
    $labTransOthers = LabTransOther::with([
        'labTrans' => function ($query) {
            $query->where('gcrecord', 0); // Ubah NULL ke 0 jika perlu
        },
        'supportService'
    ])
    ->where('gcrecord', 0) // Sesuaikan dengan struktur database
    ->get();

    if ($labTransOthers->isEmpty()) {
        return response()->json(['message' => 'Data not found'], 404);
    }

    // Format ulang data untuk respons
    $formattedData = $labTransOthers->map(function ($labTransOther) {
        return [
            'ID' => $labTransOther->ID,
            'LabTransID' => $labTransOther->LabTransID,
            'LabTrans' => $labTransOther->labTrans ? [
                'LabNumber' => $labTransOther->labTrans->LabNumber,
                'LabTest' => $labTransOther->labTrans->LabTest,
                'TransDate' => $labTransOther->labTrans->TransDate,
            ] : null,
            'SupportServiceID' => $labTransOther->SupportServiceID,
            'SupportService' => $labTransOther->supportService ? [
                'SupportServiceCode' => $labTransOther->supportService->SupportServiceCode,
                'SupportServiceName' => $labTransOther->supportService->SupportServiceName,
            ] : null,
            'SupportServiceNotes' => $labTransOther->SupportServiceNotes,
        ];
    });

    return response()->json($formattedData, 200);
}

}
