<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Narudzba extends Model
{
    use HasFactory;

    protected $table = 'narudzba';
    protected $primaryKey = 'Narudzba_ID';
    public $timestamps = true;

    protected $fillable = [
        'Kupac_ID',
        'NacinPlacanja_ID',
        'Datum_narudzbe',
        'Adresa_dostave',
        'Ukupni_iznos',
    ];

    /** 🔗 Relations */
    public function kupac()
    {
        return $this->belongsTo(Kupac::class, 'Kupac_ID');
    }

    public function detalji()
    {
        return $this->hasMany(DetaljiNarudzbe::class, 'Narudzba_ID');
    }

    public function nacinPlacanja()
    {
        return $this->belongsTo(NacinPlacanja::class, 'NacinPlacanja_ID');
    }

    // -----------------------------
    // Attribute accessors (compatibility helpers)
    // Allows views/controllers that expect standard Laravel names
    // (id, user_id, ukupna_cijena, status) to keep working.
    // -----------------------------
    public function getIdAttribute()
    {
        return $this->attributes['Narudzba_ID'] ?? null;
    }

    public function getUserIdAttribute()
    {
        return $this->attributes['Kupac_ID'] ?? null;
    }

    public function getUkupnaCijenaAttribute()
    {
        return $this->attributes['Ukupni_iznos'] ?? null;
    }

    public function getStatusAttribute()
    {
        // migration does not include Status column; return default if missing
        return $this->attributes['Status'] ?? ($this->attributes['Status'] ?? 'U obradi');
    }

    /**
     * Compatibility accessor for delivery address. DB column is `Adresa_dostave`.
     * Allows using $order->adresa_dostave in views.
     */
    public function getAdresaDostaveAttribute()
    {
        return $this->attributes['Adresa_dostave'] ?? null;
    }
}
