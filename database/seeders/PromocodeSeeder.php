<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocodeSeeder extends Seeder
{
  static $promocodes = [
    array('text' => 'mUCpQEJM2P2330kfe0', 'value' => 60, 'product_id' => 0),
    array('text' => 'rfzfdgg76fg6gdf876', 'value' => 325, 'product_id' => 1),
    array('text' => 'xfdgfdsgsfdgdfggdf', 'value' => 600, 'product_id' => 2),
    array('text' => 'bcxbcvbcvbxbcvbfgs', 'value' => 1800, 'product_id' => 3),
    array('text' => 'ljkljkljkljkljkllk', 'value' => 3850, 'product_id' => 4),
    array('text' => 'hytjtyjtyjtytyjtyj', 'value' => 8100, 'product_id' => 5),
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //
    foreach (self::$promocodes as $promocode) {
      DB::table('promocode')->insert([
        'text' => $promocode['text'],
        'value' => $promocode['value'],
        'product_id' => $promocode['product_id'],
        'status' => 1,
      ]);
    }
  }

  public function getProductId($uc)
  {
    switch ($uc['value']) {
      case 60:
        return 0;
      case 325:
        return 1;
    }
  }
}
