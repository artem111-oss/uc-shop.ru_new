<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{

  static $products = [
    ['id'=>1,'name'=> '60 UC', 'price'=> 90, 'type_id' => 1, 'status' => 1],
    ['id'=>2,'name'=> '325 UC', 'price'=> 450, 'type_id' => 1, 'status' => 1],
    ['id'=>3,'name'=> '600 UC', 'price'=> 900, 'type_id' => 1, 'status' => 1],
    ['id'=>4,'name'=> '1800 UC', 'price'=> 2200, 'type_id' => 1, 'status' => 1],
    ['id'=>5,'name'=> '3850 UC', 'price'=> 4090, 'type_id' => 1, 'status' => 1],
    ['id'=>6,'name'=> '8100 UC', 'price'=> 7990, 'type_id' => 1, 'status' => 1],
  ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
      foreach (self::$products as $product) {
        DB::table('products')->insert([
          'name' => $product['name'],
          'price' => $product['price'],
          'type_id' => $product['type_id'],
          'status' => $product['status']
        ]);
      }
    }
}
