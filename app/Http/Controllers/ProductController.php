<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
  public function index(): View
  {
$products = Product::where('status', 1)->get()->map(function ($p) {
    return [
        'id'            => $p->id,
        'name'          => $p->name,
        'price'         => $p->price,
        'type_id'       => $p->type_id,
        'delivery_mode' => $p->delivery_mode ?? 'auto',
    ];
});

      return view('home', [
          'products' => $products,
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
