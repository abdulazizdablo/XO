<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;

class AccountRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i < Account::count(); $i++) {

            DB::table('account_role')->insert([
                'account_id' => $i,
                'role_id' => rand(1, 6)
            ]);
        };
    }
}
