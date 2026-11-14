<?php

namespace App\Http\Controllers;

use App\Models\Reception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reception::with('commande:id,numero,fournisseur_id', 'commande.fournisseur:id,nom');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('commande', function ($c) use ($search) {
                      $c->where('numero', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('commande_id')) {
            $query->where('commande_id', $request->commande_id);
        }

        $receptions = $query->orderByDesc('date_reception')
            ->paginate($request->get('per_page', 10));

        return response()->json($receptions);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'date_reception' => 'required|date',
            'statut' => 'nullable|in:partielle,complete,refusee',
            'remarques' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['numero'] = $this->genererNumero();

        $reception = Reception::create($data);
        $this->synchroniserCommande($reception);

        return response()->json($reception->load('commande:id,numero'), 201);
    }

    public function show(Reception $reception): JsonResponse
    {
        return response()->json(
            $reception->load(['commande.fournisseur', 'user:id,name,role', 'factures'])
        );
    }

    public function update(Request $request, Reception $reception): JsonResponse
    {
        $data = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'date_reception' => 'required|date',
            'statut' => 'nullable|in:partielle,complete,refusee',
            'remarques' => 'nullable|string',
        ]);

        $reception->update($data);
        $this->synchroniserCommande($reception);

        return response()->json($reception->load('commande:id,numero'));
    }

    public function destroy(Reception $reception): JsonResponse
    {
        $reception->delete();

        return response()->json(['message' => 'Réception supprimée.']);
    }

    private function genererNumero(): string
    {
        $annee = now()->year;
        $dernier = Reception::whereYear('created_at', $annee)->count() + 1;

        return sprintf('REC-%d-%04d', $annee, $dernier);
    }

    private function synchroniserCommande(Reception $reception): void
    {
        if ($reception->statut === 'complete') {
            $reception->commande()->update(['statut' => 'recue']);
        }
    }
}