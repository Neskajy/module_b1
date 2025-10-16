<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nickname = "admin";
        $email = "admin@admin";
        $password = "admin1";

        User::create([
            "nickname" => $nickname,
            "email" => $email,
            "password" => $password,
            "role" => "admin"
        ]);
    }
}
