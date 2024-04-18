<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO users (nombre, correo, password, created_at) VALUES (?, ?, ?, NOW())';

        DB::insert($query, ['web', null, null]);

        DB::insert($query, [
            'Test',
            'test@test.com',
            Hash::make('Test'),
        ]);
    }
}
