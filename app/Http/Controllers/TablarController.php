<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TablarController extends Controller
{
    public function index()
    {
        // Hardcoded data (alphabetically sorted already)
        $materials = collect([
            ['name' => 'Aluminium Schraube', 'quantity' => 120, 'shelf' => 'A1'],
            ['name' => 'Befestigungsclip', 'quantity' => 75, 'shelf' => 'A2'],
            ['name' => 'Dichtung Ring', 'quantity' => 200, 'shelf' => 'B1'],
            ['name' => 'Kupfer Mutter', 'quantity' => 60, 'shelf' => 'B2'],
            ['name' => 'Metall Stift', 'quantity' => 90, 'shelf' => 'C1'],
            ['name' => 'Plastik Kappe', 'quantity' => 150, 'shelf' => 'C2'],
        ]);

        // Split into two columns
        $columns = $materials->chunk(ceil($materials->count() / 2));

        // For the Search (Resets keys and gives a clean flat array)
        $flatList = $materials->values();

        return view('user.tablar.index', compact('columns', 'flatList'));
    }
}
