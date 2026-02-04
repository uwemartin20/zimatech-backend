<?php

use App\Models\Machine;
use App\Models\Project;
use App\Models\Process;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class); // Clears the database before each test

test('it displays machine weekly records for the selected week', function () {
    // 1. FREEZE TIME to a specific date (e.g., a Wednesday in 2026)
    $knownDate = Carbon::create(2026, 2, 4); // Feb 4, 2026
    Carbon::setTestNow($knownDate);

    // 2. CREATE FAKE DATA
    // We create a machine and a project to link the process to
    $machine = Machine::factory()->create(['name' => 'CNC-01', 'company' => 'ZT']);
    $project = Project::factory()->create(['auftragsnummer_zf' => '12345', 'auftragsnummer_zt' => '23456', 'project_name' => 'New Project']);
    
    // Create a process that happened in the current week
    Process::factory()->create([
        'name'       => 'New Process',
        'machine_id' => $machine->id,
        'project_id' => $project->id,
        'start_time' => $knownDate->copy()->startOfWeek(), // Monday
        'end_time'   => $knownDate->copy()->startOfWeek()->addHours(8),
    ]);

    // 3. ACT: Visit the route
    // We simulate selecting "KW 06 / 2026" (which corresponds to Feb 4)
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    $this->actingAs($user);
    $response = $this->get(route('admin.time.logs', ['week' => '202606']));

    // 4. ASSERT: Check if the data is correct
    $response->assertStatus(200);
    $response->assertViewHas('weeklyRecords');
    
    // Check if our specific record appears in the list
    $records = $response->viewData('weeklyRecords');
    $records->dump();
    expect($records)->toHaveCount(1);
    expect($records->first()->machine_name)->toBe('CNC-01');
    expect($records->first()->auftragsnummer)->toBe('12345');
});