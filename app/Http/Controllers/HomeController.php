<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $leistungen = [
            [
                'name' => 'Projekte',
                'description'=> 'Alle unternehmensprojekte effizient Manage und Uberwachen.',
                'image'=> 'images/cards/projects.jpg',
                'route'=> 'projects',
            ],
            [
                'name' => 'Machine Logs',
                'description'=> 'Verfolgen Sie Maschinenaktivitäten und Wartungsprotokolle in Echtzeit.',
                'image'=> 'images/cards/machine_logs.png',
                'route'=> 'projects.logs',
            ],
            [
                'name' => 'Zeiten Erfassung',
                'description'=> 'Arbeitszeiten und Zeiterfassung der Mitarbeiter erfassen und verwalten.',
                'image'=> 'images/cards/zeiten.jpg',
                'route'=> 'time-records.list',
            ],
            [
                'name' => 'Benutzer',
                'description'=> 'Benutzerkonten und Berechtigungen anzeigen, bearbeiten und verwalten.',
                'image'=> 'images/cards/users.jpg',
                'route'=> 'login',
            ]
        ];
        return view('user.home.index', compact('leistungen'));
    }
}
