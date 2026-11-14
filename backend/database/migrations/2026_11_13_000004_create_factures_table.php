<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('reception_id')->nullable()->constrained('receptions')->nullOnDelete();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('tva', 5, 2)->default(20);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->enum('statut', ['en_attente', 'payee', 'en_retard', 'annulee'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};