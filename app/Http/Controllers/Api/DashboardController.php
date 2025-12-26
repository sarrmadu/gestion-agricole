<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/kpis",
     *     summary="KPI du dashboard",
     *     tags={"Dashboard"},
     *     @OA\Parameter(
     *         name="mois",
     *         in="query",
     *         description="Mois au format YYYY-MM",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI récupérés"
     *     )
     * )
     */
    public function kpis(Request $request)
    {
        $mois = $request->input('mois', date('Y-m'));
        
        return response()->json([
            'success' => true,
            'mois' => $mois,
            'data' => $this->dashboardService->getAllKPIs($mois)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/statistiques",
     *     summary="Statistiques détaillées",
     *     tags={"Dashboard"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de statistiques (recoltes|ventes)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="date_debut",
     *         in="query",
     *         description="Date début (YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="date_fin",
     *         in="query",
     *         description="Date fin (YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées"
     *     )
     * )
     */
    public function statistiques(Request $request)
    {
        $request->validate([
            'type' => 'sometimes|in:recoltes,ventes',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'sometimes|date|after_or_equal:date_debut'
        ]);

        $type = $request->input('type', 'recoltes');
        $dateDebut = $request->input('date_debut', date('Y-m-01'));
        $dateFin = $request->input('date_fin', date('Y-m-d'));

        $data = match($type) {
            'recoltes' => $this->dashboardService->getRecoltesChartData($dateDebut, $dateFin),
            'ventes' => $this->dashboardService->getVentesChartData($dateDebut, $dateFin),
            default => []
        };

        return response()->json([
            'success' => true,
            'type' => $type,
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/alertes-stocks",
     *     summary="Alertes stocks",
     *     tags={"Dashboard"},
     *     @OA\Parameter(
     *         name="seuil_jours",
     *         in="query",
     *         description="Seuil en jours pour alerte",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alertes récupérées"
     *     )
     * )
     */
    public function alertesStocks(Request $request)
    {
        $seuilJours = $request->input('seuil_jours', 7);
        
        return response()->json([
            'success' => true,
            'seuil_jours' => $seuilJours,
            'data' => $this->dashboardService->getStocksAlerte($seuilJours)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/top-varietes",
     *     summary="Top variétés",
     *     tags={"Dashboard"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de résultats",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Top variétés"
     *     )
     * )
     */
    public function topVarietes(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        return response()->json([
            'success' => true,
            'limit' => $limit,
            'data' => $this->dashboardService->getTopVarietes($limit)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/ventes-jour",
     *     summary="Ventes du jour",
     *     tags={"Dashboard"},
     *     @OA\Response(
     *         response=200,
     *         description="Ventes du jour"
     *     )
     * )
     */
    public function ventesJour()
    {
        return response()->json([
            'success' => true,
            'date' => date('Y-m-d'),
            'data' => $this->dashboardService->getVentesDuJour()
        ]);
    }
}