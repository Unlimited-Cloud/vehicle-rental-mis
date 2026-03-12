<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissioncreateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $permissions = ["index", "create", "read", "update", "delete"];
            $module_name = 'emails_emaillogs';
            $submodule_name = 'emaillogs';
            $module_id = 34;
            foreach ($permissions as $permission) {
                $addData = [
                    'name' => $permission . '_' . $module_name,
                    'module_id' => $module_id,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'submodule_name' => $submodule_name
                ];
                DB::table('permissions')->insert($addData);
            }
            dd("success");
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
