<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        $latest = StockLedger::with(['product', 'warehouse'])
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        return view('reports.index', ['entries' => $latest]);
    }

    public function exportLedger(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stock_ledger.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Product', 'Warehouse', 'Type', 'Change', 'Balance', 'Note']);

            StockLedger::with(['product', 'warehouse'])
                ->orderByDesc('occurred_at')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            optional($row->occurred_at)->format('Y-m-d H:i'),
                            $row->product?->name,
                            $row->warehouse?->name,
                            $row->type,
                            $row->quantity_change,
                            $row->balance_after,
                            $row->note,
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
