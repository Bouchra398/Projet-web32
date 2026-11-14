<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reception extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'commande_id',
        'user_id',
        'date_reception',
        'statut',
        'remarques',
    ];

    protected $casts = [
        'date_reception' => 'date',
    ];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}