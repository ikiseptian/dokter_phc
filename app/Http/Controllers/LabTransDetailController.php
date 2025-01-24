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
     *     tags={"Lab Trans"},
     *     summary="Get all Lab Trans",
     *     @OA\Response(
     *         response=200,
     *         description="A list of Lab Trans",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LabTransDetail")
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Ambil data LabTransDetail beserta relasi labTrans dan itemTest
        $labTransDetails = LabTransDetail::with(['labTrans', 'itemTest'])->get();

        // Jika data tidak ditemukan
        if ($labTransDetails->isEmpty()) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // Format respons
        $formattedData = $labTransDetails->map(function ($detail) {
            return [
                'ID' => $detail->id,
                'LabTransID' => $detail->LabTransID,
                'labTrans' => $detail->labTrans ? [
                    'LabNumber' => $detail->labTrans->LabNumber,
                    'LabTest' => $detail->labTrans->LabTest,
                    'TransDate' => $detail->labTrans->TransDate,
                ] : null,
                'ItemTestID' => $detail->ItemTestID,
                'itemTest' => $detail->itemTest ? [
                    'ItemTestCode' => $detail->itemTest->ItemTestCode,
                    'ItemTestName' => $detail->itemTest->ItemTestName,
                ] : null,
                'ResultValue' => $detail->ResultValue,
                'Unit' => $detail->Unit,
                'ReferenceValue' => $detail->ReferenceValue,
                'ResultNotes' => $detail->ResultNotes,
                'CreateDate' => $detail->CreateDate,
            ];
        });

        return response()->json($formattedData);
    }

}