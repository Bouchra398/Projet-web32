<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'statut',
    ];

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}