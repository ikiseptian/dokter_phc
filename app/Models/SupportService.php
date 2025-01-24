<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportService extends Model
{
    use HasFactory;

    protected $table = 'Support_Service';

    public $timestamps = false; // Nonaktifkan timestamps

    protected $fillable = [
        'SupportServiceCode',
        'SupportServiceName',
        'CreateDate',
        'CreateBy',
        'LastModifiedDate',
        'LastModifiedBy',
        'gcrecord',
    ];
    public function labTransOthers()
    {
        return $this->hasMany(LabTransOther::class, 'SupportServiceID', 'ID');
    }
}
