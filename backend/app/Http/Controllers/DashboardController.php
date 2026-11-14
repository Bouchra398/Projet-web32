<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Reception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $commandesParStatut = Commande::select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $facturesParStatut = Facture::select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $topFournisseurs = Fournisseur::withCount('commandes')
            ->withSum('commandes', 'montant_total')
            ->orderByDesc('commandes_sum_montant_total')
            ->take(5)
            ->get(['id', 'nom']);

        return response()->json([
            'total_fournisseurs' => Fournisseur::count(),
            'fournisseurs_actifs' => Fournisseur::where('statut', 'actif')->count(),
            'total_commandes' => Commande::count(),
            'total_receptions' => Reception::count(),
            'total_factures' => Facture::count(),
            'montant_commandes' => (float) Commande::sum('montant_total'),
            'montant_factures_ttc' => (float) Facture::sum('montant_ttc'),
            'montant_impaye' => (float) Facture::whereIn('statut', ['en_attente', 'en_retard'])->sum('montant_ttc'),
            'commandes_par_statut' => $commandesParStatut,
            'factures_par_statut' => $facturesParStatut,
            'top_fournisseurs' => $topFournisseurs,
        ]);
    }
}