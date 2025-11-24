<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define entities
        $entities = [
            'user',
            'role',
            'anggota',
            'layanan',
            'subscription',
            'payment',
            'pesan kontak',
        ];

        // Define actions
        $actions = [
            'membuat',
            'melihat',
            'mengubah',
            'menghapus',
            'mengekspor',
        ];

        // Create permissions for each entity
        $permissions = [];
        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$entity}";
                $permissions[] = $permissionName;
                Permission::create(['name' => $permissionName]);
            }
        }

        // Create Super Admin role and assign all permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo($permissions);

        // Create Admin role and assign specific permissions
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            // User permissions
            'melihat user',
            'mengubah user',
            'mengekspor user',

            // Role permissions (read only)
            'melihat role',

            // Anggota permissions
            'membuat anggota',
            'melihat anggota',
            'mengubah anggota',
            'menghapus anggota',
            'mengekspor anggota',

            // Layanan permissions
            'membuat layanan',
            'melihat layanan',
            'mengubah layanan',
            'menghapus layanan',
            'mengekspor layanan',

            // Subscription permissions
            'membuat subscription',
            'melihat subscription',
            'mengubah subscription',
            'menghapus subscription',
            'mengekspor subscription',

            // Payment permissions
            'membuat payment',
            'melihat payment',
            'mengubah payment',
            'menghapus payment',
            'mengekspor payment',

            // Contact Message permissions
            'melihat pesan kontak',
            'mengubah pesan kontak',
            'menghapus pesan kontak',
            'mengekspor pesan kontak',
        ]);

        // Create Anggota (Pelanggan) role and assign limited permissions
        $anggota = Role::create(['name' => 'Anggota']);
        $anggota->givePermissionTo([
            // Layanan permissions (read only - untuk melihat layanan yang tersedia)
            'melihat layanan',

            // Subscription permissions (untuk berlangganan)
            'membuat subscription',
            'melihat subscription',
            'mengubah subscription',

            // Payment permissions (untuk melakukan pembayaran)
            'membuat payment',
            'melihat payment',

            // Contact Message permissions (untuk menghubungi admin)
            'membuat pesan kontak',
        ]);
    }
}
