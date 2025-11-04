<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetaljiNarudzbe extends Model
{
    use HasFactory;

    protected $table = 'detalji_narudzbe';
    protected $primaryKey = 'DetaljiNarudzbe_ID';
    public $timestamps = true; // migration includes timestamps

    protected $fillable = [
        'Narudzba_ID',
        'Proizvod_ID',
        'Kolicina',
    ];

    /** 🔗 Relations */
    public function narudzba()
    {
        return $this->belongsTo(Narudzba::class, 'Narudzba_ID');
    }

    public function proizvod()
    {
        return $this->belongsTo(Proizvod::class, 'Proizvod_ID');
    }

    
}
