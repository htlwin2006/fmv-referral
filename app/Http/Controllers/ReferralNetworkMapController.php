<?php

namespace App\Http\Controllers;

use App\Domain\Referral\Models\ReferralAcquisition;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralNetworkMapController extends Controller
{
    public function index(): View
    {
        // Fetch all referral acquisitions
        $acquisitions = ReferralAcquisition::select('referrer_user_id', 'acquired_user_id')
            ->whereNotNull('referrer_user_id')
            ->whereNotNull('acquired_user_id')
            ->get();

        // Build nodes and edges for the network graph
        $nodes = [];
        $edges = [];
        $nodeSet = [];

        foreach ($acquisitions as $acquisition) {
            $referrerId = $acquisition->referrer_user_id;
            $acquiredId = $acquisition->acquired_user_id;

            // Add referrer node if not exists
            if (!isset($nodeSet[$referrerId])) {
                $nodeSet[$referrerId] = true;
                $nodes[] = [
                    'id' => $referrerId,
                    'label' => (string) $referrerId,
                    'title' => "User ID: {$referrerId}",
                ];
            }

            // Add acquired node if not exists
            if (!isset($nodeSet[$acquiredId])) {
                $nodeSet[$acquiredId] = true;
                $nodes[] = [
                    'id' => $acquiredId,
                    'label' => (string) $acquiredId,
                    'title' => "User ID: {$acquiredId}",
                ];
            }

            // Add edge (relationship)
            $edges[] = [
                'from' => $referrerId,
                'to' => $acquiredId,
                'arrows' => 'to',
                'title' => "{$referrerId} referred {$acquiredId}",
            ];
        }

        // Calculate statistics
        $stats = [
            'total_acquisitions' => $acquisitions->count(),
            'unique_users' => count($nodes),
            'referrers_count' => $acquisitions->unique('referrer_user_id')->count(),
            'acquired_count' => $acquisitions->unique('acquired_user_id')->count(),
        ];

        return view('referral-network-map', [
            'nodes' => $nodes,
            'edges' => $edges,
            'stats' => $stats,
        ]);
    }

    public function getData(): \Illuminate\Http\JsonResponse
    {
        // API endpoint to fetch data dynamically
        $acquisitions = ReferralAcquisition::select('referrer_user_id', 'acquired_user_id')
            ->whereNotNull('referrer_user_id')
            ->whereNotNull('acquired_user_id')
            ->get();

        $nodes = [];
        $edges = [];
        $nodeSet = [];

        foreach ($acquisitions as $acquisition) {
            $referrerId = $acquisition->referrer_user_id;
            $acquiredId = $acquisition->acquired_user_id;

            if (!isset($nodeSet[$referrerId])) {
                $nodeSet[$referrerId] = true;
                $nodes[] = [
                    'id' => $referrerId,
                    'label' => (string) $referrerId,
                    'title' => "User ID: {$referrerId}",
                ];
            }

            if (!isset($nodeSet[$acquiredId])) {
                $nodeSet[$acquiredId] = true;
                $nodes[] = [
                    'id' => $acquiredId,
                    'label' => (string) $acquiredId,
                    'title' => "User ID: {$acquiredId}",
                ];
            }

            $edges[] = [
                'from' => $referrerId,
                'to' => $acquiredId,
                'arrows' => 'to',
                'title' => "{$referrerId} referred {$acquiredId}",
            ];
        }

        return response()->json([
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ]);
    }
}
