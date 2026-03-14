@extends('layouts.app')

@section('page_title', 'Reports')
@section('page_subtitle', 'Download stock movement history and audit trails')

@section('content')
<div class="card-soft p-4 mb-4">
    <div class="section-title">Exports</div>
    <p style="color: var(--ink-500);">Generate CSV reports for reconciliation and audits.</p>
    <a class="btn btn-primary-custom" href="{{ route('reports.ledger') }}">Download Stock Ledger CSV</a>
</div>

<div class="card-soft p-4">
    <div class="section-title">Latest Ledger Entries</div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ optional($entry->occurred_at)->format('M d, H:i') }}</td>
                        <td>{{ $entry->product?->name }}</td>
                        <td>{{ $entry->warehouse?->name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $entry->type)) }}</td>
                        <td>{{ $entry->quantity_change }}</td>
                        <td>{{ $entry->balance_after }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="color: var(--ink-500);">No ledger entries yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
