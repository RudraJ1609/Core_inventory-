<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return view('settings.warehouses', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:40', 'unique:warehouses,code'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        Warehouse::create($validated);

        return redirect()->route('settings.warehouses');
    }
}
