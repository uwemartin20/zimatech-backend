<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialLagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Material::query()->update(['lager_id' => 2]);

        $this->command->info(
            'Updated lager_id = 2 for ' . Material::count() . ' materials.'
        );
    }
}
