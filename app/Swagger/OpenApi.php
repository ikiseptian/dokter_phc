<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Item Test API",
 *     version="1.0",
 *     description="API untuk mengelola data dokter phc"
 * )
 */

/**
 * @OA\Schema(
 *     schema="ItemTest",
 *     type="object",
 *     @OA\Property(property="ItemTestCode", type="string", maxLength=10),
 *     @OA\Property(property="ItemTestName", type="string", maxLength=30),
 *     @OA\Property(property="Group", type="string", maxLength=30, nullable=true),
 *     @OA\Property(property="SubGroup", type="string", maxLength=30, nullable=true),
 *     @OA\Property(property="Descriptions", type="string", maxLength=250, nullable=true),
 *     @OA\Property(property="CreateDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="CreateBy", type="string", maxLength=20, nullable=true),
 * )
 */

/**
 * @OA\Schema(
 *     schema="LabTrans",
 *     type="object",
 *     @OA\Property(property="LabNumber", type="string", maxLength=20),
 *     @OA\Property(property="LabTest", type="string", maxLength=20),
 *     @OA\Property(property="TransDate", type="string", format="date"),
 *     @OA\Property(property="DoctorReferral", type="string", maxLength=50, nullable=true),
 *     @OA\Property(property="PatientID", type="integer"),
 *     @OA\Property(property="Age", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="Anamnesa", type="string", maxLength=250, nullable=true),
 *     @OA\Property(property="BB", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="TB", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="LP", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="TD", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="BMI", type="string", maxLength=10, nullable=true),
 *     @OA\Property(property="FinalStatement", type="string", maxLength=250, nullable=true),
 *     @OA\Property(property="FinalResult", type="string", maxLength=20, nullable=true),
 *     @OA\Property(property="Status", type="string", maxLength=20, nullable=true),
 *     @OA\Property(property="CreateDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="CreateBy", type="string", maxLength=20, nullable=true),
 *     @OA\Property(property="LastModifiedDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="LastModifiedBy", type="string", maxLength=20, nullable=true),
 *     @OA\Property(property="gcrecord", type="boolean", example=false)
 * )
 */

/**
 * @OA\Schema(
 *     schema="LabTransDetail",
 *     type="object",
 *     required={
 *         "LabTransID", 
 *         "ItemTestID", 
 *         "ResultValue", 
 *         "Unit"
 *     },
 *     @OA\Property(property="LabTransID", type="integer", description="Lab Transaction ID"),
 *     @OA\Property(property="ItemTestID", type="integer", description="Item Test ID"),
 *     @OA\Property(property="ResultValue", type="string", description="Result Value"),
 *     @OA\Property(property="Unit", type="string", description="Unit of the result"),
 *     @OA\Property(property="ReferenceValue", type="string", description="Reference Value"),
 *     @OA\Property(property="ResultNotes", type="string", description="Result Notes"),
 *     @OA\Property(property="CreateDate", type="string", format="date", nullable=true, description="Date when the record was created"),
 *     @OA\Property(property="CreateBy", type="string", description="Name of the user who created the record"),
 *     @OA\Property(property="LastModifiedDate", type="string", format="date", nullable=true, description="Date when the record was last modified"),
 *     @OA\Property(property="LastModifiedBy", type="string", description="Name of the user who last modified the record"),
 *     @OA\Property(property="gcrecord", type="boolean", description="Indicator for soft delete"),
 *     @OA\Property(property="LabNumber", type="string", maxLength=20, description="Lab number identifier"),
 *     @OA\Property(property="LabTest", type="string", maxLength=20, description="Test type for the lab transaction"),
 *     @OA\Property(property="TransDate", type="string", format="date", description="Transaction date of the lab test"),
 *     @OA\Property(property="DoctorReferral", type="string", maxLength=50, description="Doctor who referred the lab test"),
 *     @OA\Property(property="PatientID", type="integer", description="ID of the patient associated with the lab test"),
 *     @OA\Property(property="Age", type="string", maxLength=10, description="Patient's age during the lab test"),
 *     @OA\Property(property="Anamnesa", type="string", maxLength=250, description="Patient's medical history description"),
 *     @OA\Property(property="BB", type="string", maxLength=10, description="Patient's body weight"),
 *     @OA\Property(property="TB", type="string", maxLength=10, description="Patient's height"),
 *     @OA\Property(property="LP", type="string", maxLength=10, description="Patient's waist circumference"),
 *     @OA\Property(property="TD", type="string", maxLength=10, description="Patient's blood pressure"),
 *     @OA\Property(property="BMI", type="string", maxLength=10, description="Patient's Body Mass Index"),
 *     @OA\Property(property="FinalStatement", type="string", maxLength=250, description="Doctor's final statement about the test"),
 *     @OA\Property(property="FinalResult", type="string", maxLength=20, description="Final result of the lab test"),
 *     @OA\Property(property="Status", type="string", maxLength=20, description="Status of the lab test"),
 * )
 */


/**
 * @OA\Schema(
 *     schema="LabTransOther",
 *     type="object",
 *     required={"LabTransID", "SupportServiceID"},
 *     @OA\Property(property="ID", type="integer", example=1),
 *     @OA\Property(property="LabTransID", type="integer", example=123),
 *     @OA\Property(property="SupportServiceID", type="integer", example=456),
 *     @OA\Property(property="SupportServiceNotes", type="string", example="Sample notes"),
 *          @OA\Property(property="CreateDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="CreateBy", type="string", example="admin"),
 *      @OA\Property(property="LastModifiedDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="LastModifiedBy", type="string", example="admin"),
 *     @OA\Property(property="gcrecord", type="boolean", example=false)
 * )
 */

/**
 * @OA\Schema(
 *     schema="SupportService",
 *     type="object",
 *     required={"SupportServiceCode", "SupportServiceName"},
 *     @OA\Property(property="ID", type="integer", example=1),
 *     @OA\Property(property="SupportServiceCode", type="string", example="SS123"),
 *     @OA\Property(property="SupportServiceName", type="string", example="Blood Test"),
 *     @OA\Property(property="CreateDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="CreateBy", type="string", example="admin"),
 *     @OA\Property(property="LastModifiedDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="LastModifiedBy", type="string", example="admin"),
 *     @OA\Property(property="gcrecord", type="boolean", example=false)
 * )
 */



class OpenApi
{
    // This file is used to define general API information and schema definitions
}
