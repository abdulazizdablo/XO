<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'main_admin',
            'data_entry',
            'warehouse_admin',
            'warehouse_manager',
            'delivery_admin',
            'operation_manager',
            'delivery_boy'
        ];

        

        foreach ($roles as $role) {

            Role::create(['name' => $role]);
        }
    }
}
