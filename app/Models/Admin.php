<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function lombaDiverifikasi()
    {
        return $this->hasMany(Lomba::class, 'id_admin', 'id_admin');
    }

    public function timDisetujui()
    {
        return $this->hasMany(Tim::class, 'id_admin', 'id_admin');
    }
}
