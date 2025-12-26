<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VenteService;
use Illuminate\Http\Request;

class VenteController extends Controller
{
    protected $venteService;

    public function __construct(VenteService $venteService)
    {
        $this->venteService = $venteService;
    }

    /**
     * Effectuer une vente
     */
    public function store(Request $request)
    {
        $result = $this->venteService->effectuerVente($request->all());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erreur lors de la vente',
                'errors' => $result['errors'] ?? []
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vente effectuée avec succès'
        ], 201);
    }

    /**
     * Chiffre d'affaires
     */
    public function chiffreAffaires(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut'
        ]);

        $ca = $this->venteService->getChiffreAffaires(
            $request->date_debut,
            $request->date_fin
        );

        return response()->json([
            'success' => true,
            'periode' => [
                'debut' => $request->date_debut,
                'fin' => $request->date_fin
            ],
            'data' => $ca,
            'total' => array_sum(array_column($ca, 'ca_total'))
        ]);
    }

    /**
     * Top ventes
     */
    public function topVentes(Request $request)
    {
        $limit = $request->input('limit', 10);
        $topVentes = $this->venteService->getTopVentes($limit);

        return response()->json([
            'success' => true,
            'limit' => $limit,
            'data' => $topVentes
        ]);
    }
}