<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('date_commande');
            $table->date('date_livraison_prevue')->nullable();
            $table->enum('statut', ['brouillon', 'envoyee', 'recue', 'annulee'])->default('brouillon');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->text('remarques')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};