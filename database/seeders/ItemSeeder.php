<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        for ($i = 0; $i < 30; $i++) {
            $item = new Item();
            $item->description = $faker->word();
            $item->cost_price = $faker->randomFloat(2, 20, 100);

            $item->sell_price =  $faker->randomFloat(2, 20, 100);
            $item->img_path = 'default.jpg';
            $item->save();
        }
    }
}
