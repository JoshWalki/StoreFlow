<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs for the merchant.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $query = AuditLog::where('merchant_id', $merchantId)
            ->with(['user:id,name,email'])
            ->select('id', 'merchant_id', 'user_id', 'entity', 'entity_id', 'action', 'meta_json', 'created_at');

        // Filter by entity type
        if ($request->has('entity')) {
            $query->where('entity', $request->input('entity'));
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by period
        if ($request->has('period')) {
            $period = $request->input('period');
            $now = now();

            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'week':
                    $query->where('created_at', '>=', $now->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', $now->startOfMonth());
                    break;
                case 'year':
                    $query->where('created_at', '>=', $now->startOfYear());
                    break;
                case 'all':
                    // No date filter
                    break;
            }
        }

        // Filter by custom date range (overrides period if provided)
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        // Search in meta_json
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('entity_id', 'like', "%{$search}%")
                  ->orWhere('meta_json', 'like', "%{$search}%");
            });
        }

        // Optimized pagination - reduced from 50 to 25 for better performance
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(25);

        // Only load filter options when needed (not on every request)
        // These are cached to avoid repeated queries
        $entities = cache()->remember("audit_entities_{$merchantId}", 3600, function() use ($merchantId) {
            return AuditLog::where('merchant_id', $merchantId)
                ->distinct()
                ->pluck('entity');
        });

        $actions = cache()->remember("audit_actions_{$merchantId}", 3600, function() use ($merchantId) {
            return AuditLog::where('merchant_id', $merchantId)
                ->distinct()
                ->pluck('action');
        });

        return Inertia::render('AuditLogs/Index', [
            'store' => $store,
            'user' => $user,
            'logs' => $logs,
            'entities' => $entities,
            'actions' => $actions,
            'filters' => $request->only(['entity', 'action', 'user_id', 'date_from', 'date_to', 'search', 'period']),
        ]);
    }

    /**
     * Display audit logs for a specific entity.
     */
    public function show(Request $request, string $entity, int $entityId): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;

        $logs = AuditLog::where('merchant_id', $merchantId)
            ->where('entity', $entity)
            ->where('entity_id', $entityId)
            ->with(['user:id,name,email'])
            ->select('id', 'merchant_id', 'user_id', 'entity', 'entity_id', 'action', 'meta_json', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return Inertia::render('AuditLogs/Show', [
            'entity' => $entity,
            'entityId' => $entityId,
            'logs' => $logs,
        ]);
    }

    /**
     * Export audit logs as CSV.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;

        $query = AuditLog::where('merchant_id', $merchantId)
            ->with(['user:id,name,email']);

        // Apply same filters as index
        if ($request->has('entity')) {
            $query->where('entity', $request->input('entity'));
        }
        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['ID', 'Date/Time', 'Entity', 'Entity ID', 'Action', 'User', 'User Email', 'Metadata']);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->entity,
                    $log->entity_id,
                    $log->action,
                    $log->user?->name ?? 'System',
                    $log->user?->email ?? 'N/A',
                    json_encode($log->meta_json),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit statistics for dashboard.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;

        $days = $request->input('days', 7);
        $dateFrom = now()->subDays($days);

        $stats = [
            'total' => AuditLog::where('merchant_id', $merchantId)
                ->where('created_at', '>=', $dateFrom)
                ->count(),

            'by_action' => AuditLog::where('merchant_id', $merchantId)
                ->where('created_at', '>=', $dateFrom)
                ->select('action', \DB::raw('count(*) as count'))
                ->groupBy('action')
                ->get()
                ->pluck('count', 'action'),

            'by_entity' => AuditLog::where('merchant_id', $merchantId)
                ->where('created_at', '>=', $dateFrom)
                ->select('entity', \DB::raw('count(*) as count'))
                ->groupBy('entity')
                ->get()
                ->pluck('count', 'entity'),

            'by_user' => AuditLog::where('merchant_id', $merchantId)
                ->where('created_at', '>=', $dateFrom)
                ->with('user:id,name')
                ->select('user_id', \DB::raw('count(*) as count'))
                ->groupBy('user_id')
                ->get()
                ->map(function($item) {
                    return [
                        'user' => $item->user?->name ?? 'System',
                        'count' => $item->count,
                    ];
                }),

            'recent' => AuditLog::where('merchant_id', $merchantId)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}
