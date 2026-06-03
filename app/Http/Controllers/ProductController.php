<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
  public function index(): View
  {
    $arr = [];
    $products = Product::where('status', 1)->get();

    foreach ($products as $product) {
      $arr[] = array(
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'type_id' => $product['type_id']
      );
    }
      //dd($arr);
    return view("home", [
      'products' => $arr,
    ]);
  }


  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreproductRequest $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(product $product)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(product $product)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateproductRequest $request, product $product)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(product $product)
  {
    //
  }
}
