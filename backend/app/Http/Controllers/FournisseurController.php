<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FournisseurController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fournisseur::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $fournisseurs = $query->orderBy('nom')->paginate($request->get('per_page', 10));

        return response()->json($fournisseurs);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:fournisseurs,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'statut' => 'nullable|in:actif,inactif',
        ]);

        $fournisseur = Fournisseur::create($data);

        return response()->json($fournisseur, 201);
    }

    public function show(Fournisseur $fournisseur): JsonResponse
    {
        return response()->json($fournisseur);
    }

    public function update(Request $request, Fournisseur $fournisseur): JsonResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('fournisseurs', 'email')->ignore($fournisseur->id)],
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'statut' => 'nullable|in:actif,inactif',
        ]);

        $fournisseur->update($data);

        return response()->json($fournisseur);
    }

    public function destroy(Fournisseur $fournisseur): JsonResponse
    {
        $fournisseur->delete();

        return response()->json(['message' => 'Fournisseur supprimé.']);
    }
}