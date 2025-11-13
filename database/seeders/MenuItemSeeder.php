<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (User::where(['role'=>RoleEnum::ADMIN->value, 'active'=>true])->get() as $user) {

            $menu = $user->menus()
                ->where('active', true)
                ->first();
            if(!$menu) {
                continue;
            }
            
            $categories = $user->categories()
                ->where('active', true)
                ->pluck('id')
                ->toArray();

            foreach ($categories as $categoryId) {
                $products = $user->products()
                    ->where('active', true)
                    ->where('category_id', $categoryId)
                    ->pluck('id')
                    ->toArray();
                
                foreach ($products as $product_id) {

                    MenuItem::factory()->create([
                        'menu_id' => $menu->id,
                        'product_id' => $product_id,
                        'active' => true,
                    ]);
                }
            }
        }
    }
}
