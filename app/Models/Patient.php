<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="Patient",
 *     type="object",
 *     required={"name", "age"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="age", type="integer"),
 *     @OA\Property(property="email", type="string", format="email")
 * )
 */
class Patient extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'Patient';

    // Primary key
    protected $primaryKey = 'id';

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
    ];

    // Relasi One-to-Many ke tabel Lab_Trans
    public function labTrans()
    {
        return $this->hasMany(Lab_Trans::class, 'PatientID', 'id'); // 'PatientID' adalah foreign key di tabel Lab_Trans
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
