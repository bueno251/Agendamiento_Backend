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
        $query = 'INSERT INTO users
        (nombre, correo, password, created_at)
        VALUES (?, ?, ?, now())';

        DB::insert($query, [
            'juan',
            'juan2@email.com',
            Hash::make('juan_24#'),
        ]);
    }
}
