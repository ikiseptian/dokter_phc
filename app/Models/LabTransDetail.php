<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTransDetail extends Model
{
    use HasFactory;

    protected $table = 'Lab_Trans_Detail';  // Sesuaikan nama tabel
    public $timestamps = false; 
    protected $primaryKey = 'ID';
    protected $fillable = [
        'LabTransID',
        'ItemTestID',
        'ResultValue',
        'Unit',
        'ReferenceValue',
        'ResultNotes',
        'CreateDate',
        'CreateBy',
        'LastModifiedDate',
        'LastModifiedBy',
        'gcrecord',
    ];

    public function labTrans()
    {
        return $this->belongsTo(Lab_Trans::class, 'LabTransID', 'ID');
    }
    public function itemTest()
    {
        return $this->belongsTo(ItemTest::class, 'ItemTestID', 'ID'); // Pastikan foreign key dan primary key benar
    }
}
