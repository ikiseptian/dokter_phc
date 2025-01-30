<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class ItemTest extends Model
{
    use HasFactory;

    protected $table = 'Item_Test'; 
    protected $primaryKey = 'ID';  
    protected $fillable = [
        'ItemTestCode', 'ItemTestName', 'Group', 'SubGroup', 'Descriptions', 
        'CreateDate', 'CreateBy', 'LastModifiedDate', 'LastModifiedBy', 'gcrecord'
    ];

    public $timestamps = false; 

    public function labTransDetail()
    {
        return $this->hasMany(LabTransDetail::class, 'ItemTestID', 'ID');
    }
}
