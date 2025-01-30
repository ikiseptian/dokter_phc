<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTransOther extends Model
{
    use HasFactory;

    protected $table = 'Lab_Trans_Other';
    protected $primaryKey = 'ID';
   
    protected $fillable = [
        'LabTransID',
        'SupportServiceID',
        'SupportServiceNotes',
        'CreateDate',
        'CreateBy',
        'LastModifiedDate',
        'LastModifiedBy',
        'gcrecord',
    ];

    
    public $timestamps = false;

    public function labTrans()
    {
        return $this->belongsTo(Lab_Trans::class, 'LabTransID', 'ID');
    }
    public function supportService()
    {
        return $this->belongsTo(SupportService::class, 'SupportServiceID', 'ID');
    }
}
