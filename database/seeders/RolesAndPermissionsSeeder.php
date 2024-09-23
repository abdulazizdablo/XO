<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*     // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('edit articles');

        // or may be done by chaining
        $role = Role::create(['name' => 'moderator'])
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all()); */

        // Permission::create(['name' => 'assign_task']);
        // Permission::create(['name' => 'view_task']);
        // Permission::create(['name' => 'edit_task']);

        Role::create(['name' => 'main-admin']);
        Role::create(['name' => 'inventory-admin']);
        Role::create(['name' => 'order-admin']);
        Role::create(['name' => 'delivery-admin']);
        Role::create(['name' => 'delivery-boy']);
        Role::create(['name' => 'data-entry']);





    }
}
