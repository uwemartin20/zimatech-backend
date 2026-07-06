<?php

use App\Models\Lager;
use App\Models\Material;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create the "Hochregal" Lager record that Admin\TablarController
 * hard-codes to lager_id = 2. Done in beforeEach so the row exists
 * for every test inside the RefreshDatabase transaction.
 */
beforeEach(function () {
    // id is not in Lager::$fillable, so use a raw insert.
    \DB::table('lager')->insert([
        'id' => 2,
        'name' => 'Hochregal',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('default threshold is null and does not trigger low stock notification', function () {
    $material = Material::create([
        'name' => 'Schraube M4',
        'quantity' => 10,
        'lager_id' => 2,
        // no threshold set on purpose
    ]);

    $this->assertNull($material->threshold);
    $this->assertSame('ok', $material->status);

    $response = $this->postJson(route('tablar.consume'), [
        'material_id' => $material->id,
        'quantity' => 1,
    ]);

    $response->assertOk();

    expect(Notification::where('type', 'low_stock')->count())->toBe(0);
});

test('threshold of zero behaves the same as null and does not trigger notification', function () {
    $material = Material::create([
        'name' => 'Beilagscheibe',
        'quantity' => 10,
        'threshold' => 0,
        'lager_id' => 2,
    ]);

    expect($material->status)->toBe('ok');

    $this->postJson(route('tablar.consume'), [
        'material_id' => $material->id,
        'quantity' => 1,
    ])->assertOk();

    expect(Notification::where('type', 'low_stock')->count())->toBe(0);
});

test('explicit positive threshold triggers low stock notification when consumed below', function () {
    $material = Material::create([
        'name' => 'Bolzen',
        'quantity' => 3,
        'threshold' => 5,
        'lager_id' => 2,
    ]);

    expect($material->fresh()->status)->toBe('low');

    $this->postJson(route('tablar.consume'), [
        'material_id' => $material->id,
        'quantity' => 1,
    ])->assertOk();

    // Exactly one notification for today
    expect(Notification::where('type', 'low_stock')
        ->where('message', 'like', '%'.$material->name.'%')
        ->whereDate('created_at', now()->toDateString())
        ->count())->toBe(1);

    // Second consume on the same day does NOT create a duplicate
    $this->postJson(route('tablar.consume'), [
        'material_id' => $material->id,
        'quantity' => 1,
    ])->assertOk();

    expect(Notification::where('type', 'low_stock')
        ->where('message', 'like', '%'.$material->name.'%')
        ->whereDate('created_at', now()->toDateString())
        ->count())->toBe(1);
});

test('store material as admin persists code and description', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->postJson(route('admin.tablar.store'), [
        'name' => 'Mutter M6',
        'code' => 'ART-001',
        'description' => 'Sechskantmutter, verzinkt',
        'quantity' => 100,
        'tablar' => 'A1',
        'threshold' => 10,
        'is_active' => true,
    ]);

    $response->assertOk();

    $material = Material::where('name', 'Mutter M6')->first();
    expect($material)->not->toBeNull();
    expect($material->code)->toBe('ART-001');
    expect($material->description)->toBe('Sechskantmutter, verzinkt');
});

test('update material as admin persists code and description', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $material = Material::create([
        'name' => 'Mutter M8',
        'quantity' => 50,
        'lager_id' => 2,
    ]);

    $response = $this->putJson(route('admin.tablar.update', $material->id), [
        'name' => 'Mutter M8',
        'code' => 'ART-002',
        'description' => 'Aktualisierte Notiz',
        'quantity' => 50,
        'tablar' => 'A2',
        'threshold' => 5,
        'is_active' => true,
    ]);

    $response->assertOk();

    $material->refresh();
    expect($material->code)->toBe('ART-002');
    expect($material->description)->toBe('Aktualisierte Notiz');
});

test('admin tablar overview excludes materials with null or zero threshold from low stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // Material without threshold — should NOT appear
    $noThreshold = Material::create([
        'name' => 'Ohne Schwelle',
        'quantity' => 1,
        'threshold' => null,
        'lager_id' => 2,
    ]);

    // Material with threshold = 0 but quantity = 1 — should NOT appear
    $zeroThreshold = Material::create([
        'name' => 'Schwelle null',
        'quantity' => 1,
        'threshold' => 0,
        'lager_id' => 2,
    ]);

    // Material with threshold = 5 and quantity = 3 — SHOULD appear
    $lowStock = Material::create([
        'name' => 'Bolzen',
        'quantity' => 3,
        'threshold' => 5,
        'lager_id' => 2,
    ]);

    // Material with threshold = 5 and quantity = 5 — should NOT appear (boundary)
    $boundary = Material::create([
        'name' => 'Genug',
        'quantity' => 5,
        'threshold' => 5,
        'lager_id' => 2,
    ]);

    $response = $this->get(route('admin.tablar.overview'));
    $response->assertOk();

    $lowStockMaterials = $response->viewData('lowStockMaterials');

    $names = $lowStockMaterials->pluck('name')->all();

    expect($names)->toContain('Bolzen');
    expect($names)->not->toContain('Ohne Schwelle');
    expect($names)->not->toContain('Schwelle null');
    expect($names)->not->toContain('Genug');
});
