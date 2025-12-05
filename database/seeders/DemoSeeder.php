<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a merchant
        $merchant = Merchant::create([
            'name' => 'Demo Merchant',
            'slug' => 'demo-merchant',
        ]);

        // Create owner user
        $owner = User::create([
            'merchant_id' => $merchant->id,
            'username' => 'owner',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'name' => 'Owner User',
            'email' => 'owner@demo.com',
        ]);

        // Update merchant with owner
        $merchant->update(['owner_user_id' => $owner->id]);

        // Create stores
        $store1 = Store::create([
            'merchant_id' => $merchant->id,
            'name' => 'Main Store',
            'description' => 'Our flagship location',
            'theme_key' => 'modern',
            'timezone' => 'America/New_York',
            'shipping_enabled' => true,
        ]);

        $store2 = Store::create([
            'merchant_id' => $merchant->id,
            'name' => 'Second Location',
            'description' => 'Our second store',
            'theme_key' => 'classic',
            'timezone' => 'America/New_York',
            'shipping_enabled' => true,
        ]);

        // Create manager user
        $manager = User::create([
            'merchant_id' => $merchant->id,
            'username' => 'manager',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'name' => 'Manager User',
            'email' => 'manager@demo.com',
        ]);

        // Assign manager to store1
        $manager->stores()->attach($store1->id, ['role' => 'manager']);

        // Create staff user
        $staff = User::create([
            'merchant_id' => $merchant->id,
            'username' => 'staff',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'name' => 'Staff User',
            'email' => 'staff@demo.com',
        ]);

        // Assign staff to both stores
        $staff->stores()->attach($store1->id, ['role' => 'staff']);
        $staff->stores()->attach($store2->id, ['role' => 'staff']);

        $this->command->info('Demo data created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Owner: username=owner, password=password');
        $this->command->info('Manager: username=manager, password=password');
        $this->command->info('Staff: username=staff, password=password');
    }
}
