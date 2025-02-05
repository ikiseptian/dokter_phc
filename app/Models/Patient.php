<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="Patient",
 *     type="object",
 *     required={"NIK", "PatientID_Provider", "FullName", "Sex", "BirthDate"},
 *     @OA\Property(property="NIK", type="string", example="1234567890123456"),
 *     @OA\Property(property="PatientID_Provider", type="string", example="P-00123"),
 *     @OA\Property(property="FullName", type="string", example="John Doe"),
 *     @OA\Property(property="Sex", type="string", example="Male"),
 *     @OA\Property(property="BirthDate", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="Address", type="string", example="Jl. Sudirman No. 10"),
 *     @OA\Property(property="Phone", type="string", example="08123456789"),
 *     @OA\Property(property="CreateBy", type="string", example="admin"),
 *     @OA\Property(property="LastModifiedBy", type="string", example="editor"),
 *     @OA\Property(property="gcrecord", type="boolean", example=false)
 * )
 */

 class Patient extends Model
 {
     use HasFactory;
 
     // Nama tabel di database
     protected $table = 'Patient';
 
     // Primary key
     protected $primaryKey = 'ID';
 
     // Kolom yang bisa diisi (mass assignable)
     protected $fillable = [
         'NIK',
         'PatientID_Provider',
         'FullName',
         'Sex',
         'BirthDate',
         'Address',
         'Phone',
         'CreateBy',
         'LastModifiedBy',
         'gcrecord',
         'CreateDate', // Tambahkan CreateDate agar bisa diisi dalam mass assignment
         'LastModifiedDate'
     ];
 
     // Relasi One-to-Many ke tabel Lab_Trans
     public function labTrans()
     {
         return $this->hasMany(Lab_Trans::class, 'PatientID', 'ID'); // 'PatientID' adalah foreign key di tabel Lab_Trans
     }
 
     // Kolom yang otomatis diisi (timestamps)
     public $timestamps = false;
 
     // Format kolom timestamp
     protected $dates = [
         'CreateDate',
         'LastModifiedDate',
         'BirthDate',
     ];
 }