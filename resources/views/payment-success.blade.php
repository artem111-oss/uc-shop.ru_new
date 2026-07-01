@extends('app')
@section('title', 'Платеж принят - UC-SHOP')
@section('description', 'Ваш платеж принят. UC отправляются на ваш аккаунт.')
@section('content')

{{-- Яндекс.Метрика: Электронная коммерция + цель order_success --}}
<script>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    "ecommerce": {
      "purchase": {
        "actionField": {
          "id": "{{ $order->id }}",
          "revenue": "{{ $order->price }}"
        },
        "products": [{
          "id": "{{ $order->id }}",
          "name": "{{ $product ? $product->name : 'UC PUBG Mobile' }}",
          "price": "{{ $order->price }}",
          "quantity": 1
        }]
      }
    }
  });
  ym(110321078, 'reachGoal', 'order_success', {
    order_price: {{ $order->price }},
    order_id: "{{ $order->id }}"
  });
</script>

  <div class="uc-payment-status uc-payment-status--success">
    <div class="container">
      <div class="uc-status-card">
        <!-- Success Animation -->
        <div class="uc-status-icon uc-status-icon--success">
          <svg viewBox="0 0 100 100" class="uc-checkmark">
            <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="2"></circle>
            <polyline points="30,55 45,70 70,35" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
          </svg>
        </div>

        <h1 class="uc-status-title">
          ✨ Спасибо за покупку!
        </h1>

        <p class="uc-status-subtitle">
          Ваш платеж успешно принят
        </p>

        <!-- Order Details -->
        <div class="uc-status-details">
          <div class="uc-detail-row">
            <span class="uc-detail-label">Номер заказа:</span>
            <span class="uc-detail-value">{{ $order->id }}</span>
          </div>

          @if($order->game_id)
            <div class="uc-detail-row">
              <span class="uc-detail-label">Игровой ID:</span>
              <span class="uc-detail-value">{{ $order->game_id }}</span>
            </div>
          @endif

          @if($product)
            <div class="uc-detail-row">
              <span class="uc-detail-label">Товар:</span>
              <span class="uc-detail-value">{{ $product->name }}</span>
            </div>
          @endif

          <div class="uc-detail-row">
            <span class="uc-detail-label">Сумма:</span>
            <span class="uc-detail-value uc-detail-value--highlight">{{ number_format($order->price, 2, ',', ' ') }}₽</span>
          </div>

          <div class="uc-detail-row">
            <span class="uc-detail-label">Статус:</span>
            <span class="uc-detail-value uc-status-badge uc-status-badge--success">Оплачено ✓</span>
          </div>

          <div class="uc-detail-row">
            <span class="uc-detail-label">Дата:</span>
            <span class="uc-detail-value">{{ $order->created_at->format('d.m.Y H:i') }}</span>
          </div>
        </div>

        <!-- Info Message -->
        <div class="uc-info-box">
          <p class="uc-info-text">
            <strong>⚡ Мгновенная доставка:</strong> UC отправляются на ваш аккаунт в течение <strong>30-60 секунд</strong>. 
            Пожалуйста, убедитесь, что игра запущена.
          </p>
        </div>

        <!-- Actions -->
        <div class="uc-action-buttons">
          <a href="/" class="uc-btn uc-btn--primary">
            ← Вернуться на главную
          </a>

          <a href="https://t.me/gdealerofficial" target="_blank" rel="noopener" class="uc-btn uc-btn--secondary">
            📱 Чат поддержки (Telegram)
          </a>
        </div>

        <!-- FAQ -->
        <div class="uc-faq-mini">
          <h3 class="uc-faq-title">❓ Что дальше?</h3>
          <ul class="uc-faq-list">
            <li>
              <strong>Проверьте игру:</strong> Откройте PUBG Mobile и проверьте баланс UC в профиле
            </li>
            <li>
              <strong>Если ничего не пришло:</strong> Перезагрузите игру и перезагрузитесь устройство
            </li>
            <li>
              <strong>Проблема осталась?</strong> Напишите в поддержку с номером заказа <strong>#{{ $order->id }}</strong>
            </li>
          </ul>
        </div>

        <!-- Receipt Option -->
        @if($order->email)
          <div class="uc-receipt-info">
            <p class="uc-small-text">
              Чек отправлен на <strong>{{ $order->email }}</strong>. 
              Проверьте спам-папку, если не видите письмо.
            </p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <style scoped>
    .uc-payment-status {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background: linear-gradient(135deg, rgba(35, 40, 46, 0.8), rgba(50, 55, 65, 0.8));
    }

    .uc-payment-status--success {
      background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), rgba(35, 40, 46, 0.8));
    }

    .container {
      max-width: 600px;
    }

    .uc-status-card {
      background: rgba(255, 255, 255, 0.05);
      border: 2px solid rgba(76, 175, 80, 0.3);
      border-radius: 16px;
      padding: 3rem 2rem;
      text-align: center;
      animation: slideUp 0.6s ease-out;
    }

    .uc-status-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 3rem;
    }

    .uc-status-icon--success {
      background: rgba(76, 175, 80, 0.15);
      color: #4caf50;
    }

    .uc-checkmark {
      width: 100%;
      height: 100%;
      animation: checkmarkDraw 0.8s ease-out forwards;
    }

    .uc-status-title {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin: 0 0 0.5rem;
      animation: slideDown 0.6s ease-out 0.3s forwards;
      opacity: 0;
    }

    .uc-status-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      margin: 0 0 2rem;
      animation: slideDown 0.6s ease-out 0.4s forwards;
      opacity: 0;
    }

    .uc-status-details {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 165, 0, 0.1);
      border-radius: 12px;
      padding: 2rem;
      margin: 2rem 0;
      animation: slideUp 0.6s ease-out 0.5s forwards;
      opacity: 0;
    }

    .uc-detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .uc-detail-row:last-child {
      border-bottom: none;
    }

    .uc-detail-label {
      font-weight: 600;
      color: rgba(255, 255, 255, 0.6);
    }

    .uc-detail-value {
      color: rgba(255, 255, 255, 0.9);
      font-weight: 600;
    }

    .uc-detail-value--highlight {
      color: #4caf50;
      font-size: 1.2em;
    }

    .uc-status-badge {
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .uc-status-badge--success {
      background: rgba(76, 175, 80, 0.2);
      color: #4caf50;
    }

    .uc-info-box {
      background: rgba(255, 165, 0, 0.1);
      border: 1px solid rgba(255, 165, 0, 0.2);
      border-radius: 12px;
      padding: 1.5rem;
      margin: 2rem 0;
      animation: slideUp 0.6s ease-out 0.6s forwards;
      opacity: 0;
    }

    .uc-info-text {
      color: rgba(255, 255, 255, 0.85);
      margin: 0;
      line-height: 1.6;
    }

    .uc-action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin: 2rem 0;
      animation: slideUp 0.6s ease-out 0.7s forwards;
      opacity: 0;
    }

    .uc-btn {
      flex: 1;
      min-width: 200px;
      padding: 0.85rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      text-align: center;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      outline: none;
    }

    .uc-btn--primary {
      background: linear-gradient(135deg, #4caf50, #45a049);
      color: white;
      box-shadow: 0 4px 16px rgba(76, 175, 80, 0.3);
    }

    .uc-btn--primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(76, 175, 80, 0.4);
    }

    .uc-btn--secondary {
      background: rgba(255, 165, 0, 0.15);
      color: #ffa500;
      border: 2px solid #ffa500;
    }

    .uc-btn--secondary:hover {
      background: rgba(255, 165, 0, 0.25);
    }

    .uc-faq-mini {
      text-align: left;
      background: rgba(255, 255, 255, 0.02);
      border-left: 4px solid #ffa500;
      padding: 1.5rem;
      margin: 2rem 0;
      border-radius: 8px;
      animation: slideUp 0.6s ease-out 0.8s forwards;
      opacity: 0;
    }

    .uc-faq-title {
      margin: 0 0 1rem;
      color: white;
      font-weight: 700;
    }

    .uc-faq-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .uc-faq-list li {
      padding: 0.75rem 0;
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .uc-faq-list li:last-child {
      border-bottom: none;
    }

    .uc-receipt-info {
      padding: 1rem;
      background: rgba(76, 175, 80, 0.1);
      border-radius: 8px;
      margin-top: 1.5rem;
      animation: slideUp 0.6s ease-out 0.9s forwards;
      opacity: 0;
    }

    .uc-small-text {
      margin: 0;
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes checkmarkDraw {
      0% {
        stroke-dasharray: 1000;
        stroke-dashoffset: 1000;
      }
      100% {
        stroke-dasharray: 1000;
        stroke-dashoffset: 0;
      }
    }

    @media (max-width: 768px) {
      .uc-status-card {
        padding: 2rem 1.5rem;
      }

      .uc-status-title {
        font-size: 1.5rem;
      }

      .uc-action-buttons {
        flex-direction: column;
      }

      .uc-btn {
        min-width: auto;
        width: 100%;
      }
    }
  </style>
@endsection
