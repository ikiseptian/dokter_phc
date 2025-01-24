<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Identitas_Pasien extends Model
{
    use HasFactory;
    protected $table = 'identitas_pasien'; 
    protected $fillable = ['nik', 'tanggal_lahir', 'jenis_kelamin']; 
}
