<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab_Trans extends Model
{
    use HasFactory;

    protected $table = 'Lab_Trans'; 
    protected $primaryKey = 'ID'; 
    public $timestamps = false; 

    protected $fillable = [
        'LabNumber',
        'LabTest',
        'TransDate',
        'DoctorReferral',
        'PatientID',
        'Age',
        'Anamnesa',
        'BB',
        'TB',
        'LP',
        'TD',
        'BMI',
        'FinalStatement',
        'FinalResult',
        'Status',
        'CreateDate',
        'CreateBy',
        'LastModifiedDate',
        'LastModifiedBy',
        'gcrecord',
    ];

    // Relasi Many-to-One ke tabel Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'PatientID', 'ID'); // 'PatientID' adalah foreign key, 'id' adalah primary key di tabel Patient
    }
    public function labTransDetails()
    {
        return $this->hasMany(LabTransDetail::class, 'LabTransID', 'ID');
    }
    public function labTransOthers()
    {
        return $this->hasMany(LabTransOther::class, 'LabTransID', 'ID');
    }
}

