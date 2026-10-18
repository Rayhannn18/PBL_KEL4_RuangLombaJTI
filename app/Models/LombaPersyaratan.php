<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LombaPersyaratan extends Model
{
    use HasFactory;

    protected $table = 'lomba_persyaratan';

    protected $fillable = [
        'id_lomba',
        'persyaratan',
        'urutan',
    ];

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }
}
