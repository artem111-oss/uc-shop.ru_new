<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
class OrderController extends Controller
{
    //
  public function createOrder(OrderRequest $request)
  {

    //
    $contact = new Order();
    $contact->client_id = $request->input('client_id');
    $contact->status_id = 1; // заказ создан
    $contact->price = $request->input('price'); // рассчитать цену корзины

    $contact->save();
    return $contact->id;
    //return redirect()->route('home')->with('success', 'Сообщение было добавлено.');
  }
}
