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
        // Ambil data LabTransOther beserta relasi LabTrans dan SupportService
        $labTransOthers = LabTransOther::with(['labTrans', 'supportService'])->get();

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
