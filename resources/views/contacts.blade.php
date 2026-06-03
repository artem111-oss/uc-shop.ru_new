@extends('app')
@section('title', 'Контакты')
@section('content')
<div class="container pt-4">
    <h1>Контакты</h1>
    <div class="content">
        <div class="row">
            <div class="col-6 col-lg-6 mb-3"> 
                <p>Email: info@uc-shop.ru</p> 
                <p>Официальный канал UCSHOP <a href="https://t.me/ucshop" target="_blank">@ucshop</a></p> <a href="https://t.me/gdealerofficial" target="_blank">@gdealerofficial</a>
            </p>
            <p>Отзывы/Чат <a href="https://t.me/gdealerreviews" target="_blank">@gdealerreviews</a></p><a href="https://t.me/ucshop_otz" target="_blank">@ucshop_otz</a>
        </p><a href="https://t.me/ucshop_forum" target="_blank">@ucshop_forum</a>
    </p>
    <p>Админ <a href="https://t.me/ucshop_dobro" target="_blank">@air_pubg</a></p>
    <p>Операторы:</p> 
    <p><a href="https://t.me/ucshop_dobro" target="_blank">@ucshop_dobro</a></p>
</div>
<div class="col-6 col-lg-6 mb-3">
    <h2>Форма обратной связи</h2>
    <form action="{{route('contact-form')}}" method="post">
        @csrf
        <div class="mb-3">
            <label for="subject" class="form-label">Тема обращения</label>
            <input type="text" class="form-control" id="subject" name="subject">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control" id="name" name="name">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Ваше сообщение</label>
            <textarea class="form-control" id="message" name="message" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Отправить</button>
    </form>
</div>
@endsection
