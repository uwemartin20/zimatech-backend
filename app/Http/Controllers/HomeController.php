<?php

namespace App\Http\Controllers;

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
        // Statistics
        $stats = [
            'projects' => \App\Models\Project::count(),
            'machines' => \App\Models\Machine::count(),
            'users' => \App\Models\User::count(),
            'processes' => \DB::table('processes')->count(),
        ];

        $leistungen = [
            [
                'name' => 'Projekte',
                'description' => 'Alle unternehmensprojekte effizient Manage und Uberwachen.',
                'image' => 'images/cards/project card.png',
                'route' => 'projects',
            ],
            [
                'name' => 'Hochregal-Materialerfassung',
                'description' => 'Verfolgen Sie Materialverbrauch & Bestandsübersicht & Materialverwaltung (Werkstatt).',
                'image' => 'images/cards/hochregal card.webp',
                'route' => 'tablar.index',
            ],
            [
                'name' => 'Zeiten Erfassung',
                'description' => 'Arbeitszeiten und Zeiterfassung der Mitarbeiter erfassen und verwalten.',
                'image' => 'images/cards/time card.jpg',
                'route' => 'time-records.list',
            ],
            [
                'name' => 'Druckprobleme',
                'description' => 'Melden und verwalten Sie technische Probleme an den Druckmaschinen.',
                'image' => 'images/cards/printing problems.avif',
                'route' => 'printer-problems.index',
            ],
            [
                'name' => 'Ressourcenplanung',
                'description' => 'Belegung der Maschinen planen und einsehen. Keine Anmeldung erforderlich.',
                'image' => 'images/cards/scheduler_card.png',
                'route' => 'scheduler.index',
            ],
        ];

        return view('user.home.index', compact('leistungen', 'stats'));
    }
}
