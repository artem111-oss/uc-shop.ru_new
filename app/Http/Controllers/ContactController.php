<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    //
  public function submit(ContactRequest $request)
  {
    //
    $contact = new Contact();
    $contact->name = $request->input('name');
    $contact->email = $request->input('email');
    $contact->subject = $request->input('subject');
    $contact->message = $request->input('message');

    $contact->save();

    return redirect()->route('home')->with('success', 'Сообщение было добавлено.');
  }

  public function list(){
    return view('contacts', ['data'=>Contact::all()]);
  }
  // В функции принимаем ID определенной записи
  public function delete($id) {
    // Находим запись по ID и сразу же выполняем её удаление
    Contact::find($id)->delete();
  }
}
