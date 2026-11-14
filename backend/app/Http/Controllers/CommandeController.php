<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Commande::with('fournisseur:id,nom');

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

        $commandes = $query->orderByDesc('date_commande')
            ->paginate($request->get('per_page', 10));

        return response()->json($commandes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_commande' => 'required|date',
            'date_livraison_prevue' => 'nullable|date|after_or_equal:date_commande',
            'statut' => 'nullable|in:brouillon,envoyee,recue,annulee',
            'montant_total' => 'required|numeric|min:0',
            'remarques' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['numero'] = $this->genererNumero();

        $commande = Commande::create($data);

        return response()->json($commande->load('fournisseur:id,nom'), 201);
    }

    public function show(Commande $commande): JsonResponse
    {
        return response()->json(
            $commande->load(['fournisseur', 'user:id,name,role', 'receptions', 'factures'])
        );
    }

    public function update(Request $request, Commande $commande): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_commande' => 'required|date',
            'date_livraison_prevue' => 'nullable|date|after_or_equal:date_commande',
            'statut' => 'nullable|in:brouillon,envoyee,recue,annulee',
            'montant_total' => 'required|numeric|min:0',
            'remarques' => 'nullable|string',
        ]);

        $commande->update($data);

        return response()->json($commande->load('fournisseur:id,nom'));
    }

    public function destroy(Commande $commande): JsonResponse
    {
        $commande->delete();

        return response()->json(['message' => 'Commande supprimée.']);
    }

    private function genererNumero(): string
    {
        $annee = now()->year;
        $dernier = Commande::whereYear('created_at', $annee)->count() + 1;

        return sprintf('CMD-%d-%04d', $annee, $dernier);
    }
}