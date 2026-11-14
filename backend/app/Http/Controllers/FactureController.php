<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Facture::with('fournisseur:id,nom', 'commande:id,numero');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('fournisseur', function ($f) use ($search) {
                      $f->where('nom', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        $factures = $query->orderByDesc('date_facture')
            ->paginate($request->get('per_page', 10));

        return response()->json($factures);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'reception_id' => 'nullable|exists:receptions,id',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_facture',
            'montant_ht' => 'required|numeric|min:0',
            'tva' => 'nullable|numeric|min:0|max:100',
            'statut' => 'nullable|in:en_attente,payee,en_retard,annulee',
        ]);

        $data['tva'] = $data['tva'] ?? 20;
        $data['montant_ttc'] = $this->calculerTtc($data['montant_ht'], $data['tva']);
        $data['numero'] = $this->genererNumero();

        $facture = Facture::create($data);

        return response()->json($facture->load('fournisseur:id,nom'), 201);
    }

    public function show(Facture $facture): JsonResponse
    {
        return response()->json(
            $facture->load(['fournisseur', 'commande', 'reception'])
        );
    }

    public function update(Request $request, Facture $facture): JsonResponse
    {
        $data = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'reception_id' => 'nullable|exists:receptions,id',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_facture',
            'montant_ht' => 'required|numeric|min:0',
            'tva' => 'nullable|numeric|min:0|max:100',
            'statut' => 'nullable|in:en_attente,payee,en_retard,annulee',
        ]);

        $data['tva'] = $data['tva'] ?? 20;
        $data['montant_ttc'] = $this->calculerTtc($data['montant_ht'], $data['tva']);

        $facture->update($data);

        return response()->json($facture->load('fournisseur:id,nom'));
    }

    public function destroy(Facture $facture): JsonResponse
    {
        $facture->delete();

        return response()->json(['message' => 'Facture supprimée.']);
    }

    private function calculerTtc(float $ht, float $tva): float
    {
        return round($ht * (1 + $tva / 100), 2);
    }

    private function genererNumero(): string
    {
        $annee = now()->year;
        $dernier = Facture::whereYear('created_at', $annee)->count() + 1;

        return sprintf('FAC-%d-%04d', $annee, $dernier);
    }
}