@extends('app')
@section('title', 'Оплата прошла успешно — UC-SHOP')
@section('content')

<section class="uc-payment-result">
  <div class="container">
    <div class="uc-result-card uc-result-card--success">

      <div class="uc-result-icon uc-result-icon--success">
        <svg viewBox="0 0 100 100" width="56" height="56">
          <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="3"/>
          <polyline points="28,52 44,68 72,32" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" class="uc-checkmark-line"/>
        </svg>
      </div>

      <h1 class="uc-result-title">Оплата прошла!</h1>
      <p class="uc-result-subtitle">UC отправляются на твой аккаунт</p>

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
        @if($order->uc_amount)
        <div class="uc-result-row">
          <span class="uc-result-row__label">Товар</span>
          <span class="uc-result-row__value">{{ $order->uc_amount }}</span>
        </div>
        @endif
        <div class="uc-result-row">
          <span class="uc-result-row__label">Сумма</span>
          <span class="uc-result-row__value uc-result-row__value--accent">{{ number_format($order->price, 0, ',', ' ') }} ₽</span>
        </div>
        <div class="uc-result-row">
          <span class="uc-result-row__label">Статус</span>
          <span class="uc-result-badge uc-result-badge--success">Оплачено ✓</span>
        </div>
        <div class="uc-result-row">
          <span class="uc-result-row__label">Дата</span>
          <span class="uc-result-row__value">{{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>
      </div>

      <div class="uc-result-infobox">
        <strong>⚡ Мгновенная доставка:</strong> UC зачисляются в течение 30–60 секунд. Убедись, что игра открыта.
      </div>

      <div class="uc-result-actions">
        <a href="/" class="uc-result-btn uc-result-btn--primary">← На главную</a>
        <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-result-btn uc-result-btn--outline">💬 Поддержка</a>
      </div>

      <div class="uc-result-faq">
        <p class="uc-result-faq__title">Что делать если UC не пришли?</p>
        <ul>
          <li>Перезапусти PUBG Mobile и проверь баланс</li>
          <li>Перезагрузи устройство</li>
          <li>Напиши в поддержку с номером заказа <strong>#{{ $order->id }}</strong></li>
        </ul>
      </div>

      @if($order->email)
      <p class="uc-result-receipt">Чек отправлен на <strong>{{ $order->email }}</strong></p>
      @endif

    </div>
  </div>
</section>

@endsection