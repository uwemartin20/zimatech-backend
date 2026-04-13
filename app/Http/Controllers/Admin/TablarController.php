<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TablarController extends Controller
{
    public function index()
    {
        $materials = collect([
            ['name' => 'Aluminium Schraube', 'quantity' => 120, 'shelf' => 'A1'],
            ['name' => 'Befestigungsclip', 'quantity' => 75, 'shelf' => 'A2'],
            ['name' => 'Dichtung Ring', 'quantity' => 200, 'shelf' => 'B1'],
            ['name' => 'Kupfer Mutter', 'quantity' => 60, 'shelf' => 'B2'],
            ['name' => 'Metall Stift', 'quantity' => 90, 'shelf' => 'C1'],
            ['name' => 'Plastik Kappe', 'quantity' => 150, 'shelf' => 'C2'],
        ]);

        return view('admin.tablar.index', compact('materials'));
    }
}
