@extends('app')
@section('title', 'Ошибка оплаты — UC-SHOP')
@section('content')

<section class="uc-payment-result">
  <div class="container">
    <div class="uc-result-card uc-result-card--failed">

      <div class="uc-result-icon uc-result-icon--failed">
        <svg viewBox="0 0 100 100" width="56" height="56">
          <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="3"/>
          <line x1="33" y1="33" x2="67" y2="67" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
          <line x1="67" y1="33" x2="33" y2="67" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
        </svg>
      </div>

      <h1 class="uc-result-title">Оплата не прошла</h1>
      <p class="uc-result-subtitle">Что-то пошло не так. Деньги не списаны.</p>

      @if($order)
      <div class="uc-result-details">
        <div class="uc-result-row">
          <span class="uc-result-row__label">Номер заказа</span>
          <span class="uc-result-row__value">#{{ $order->id }}</span>
        </div>
        @if($order->game_id)
        <div class="uc-result-row">
          <span class="uc-result-row__label">Игровой ID</span>
          <span class="uc-result-row__value">{{ $order->game_id }}</span>
        </div>
        @endif
        <div class="uc-result-row">
          <span class="uc-result-row__label">Сумма</span>
          <span class="uc-result-row__value">{{ number_format($order->price, 0, ',', ' ') }} ₽</span>
        </div>
        <div class="uc-result-row">
          <span class="uc-result-row__label">Статус</span>
          <span class="uc-result-badge uc-result-badge--failed">Не оплачено ✗</span>
        </div>
      </div>
      @endif

      <div class="uc-result-infobox uc-result-infobox--warning">
        <strong>ℹ️ Деньги не списаны.</strong> Попробуй оплатить снова или выбери другой способ оплаты.
      </div>

      <div class="uc-result-actions">
        <a href="/" class="uc-result-btn uc-result-btn--primary">← Попробовать снова</a>
        <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-result-btn uc-result-btn--outline">💬 Написать в поддержку</a>
      </div>

      <div class="uc-result-faq">
        <p class="uc-result-faq__title">Возможные причины:</p>
        <ul>
          <li>Недостаточно средств на карте</li>
          <li>Банк заблокировал платёж — позвони в банк</li>
          <li>Истекло время ожидания оплаты</li>
          <li>Технический сбой на стороне платёжной системы</li>
        </ul>
      </div>

    </div>
  </div>
</section>

@endsection