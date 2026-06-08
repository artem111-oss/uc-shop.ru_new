@extends('app')
@section('title', 'Платеж отклонен - UC-SHOP')
@section('description', 'Платеж был отклонен. Пожалуйста, попробуйте еще раз.')
@section('content')
  <div class="uc-payment-status uc-payment-status--failed">
    <div class="container">
      <div class="uc-status-card">
        <!-- Error Icon -->
        <div class="uc-status-icon uc-status-icon--error">
          ❌
        </div>

        <h1 class="uc-status-title">
          Платеж отклонен
        </h1>

        <p class="uc-status-subtitle">
          Не удалось обработать ваш платеж
        </p>

        <!-- Error Reasons -->
        <div class="uc-error-reasons">
          <h3 class="uc-reasons-title">Возможные причины:</h3>
          <ul class="uc-reasons-list">
            <li>❌ Недостаточно средств на карте</li>
            <li>❌ Карта заблокирована банком</li>
            <li>❌ Истек срок действия карты</li>
            <li>❌ Превышен лимит на платежи</li>
            <li>❌ Сбой в платежной системе (временно)</li>
          </ul>
        </div>

        <!-- Info Message -->
        <div class="uc-info-box uc-info-box--error">
          <p class="uc-info-text">
            <strong>✨ Хорошая новость:</strong> Ваш заказ сохранён. Вы можете попробовать оплатить ещё раз 
            с другой карты или способом оплаты.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="uc-action-buttons">
          <a href="/" class="uc-btn uc-btn--primary">
            ← Попробовать еще раз
          </a>

          <a href="https://t.me/pubgm_uc_reviews" target="_blank" rel="noopener" class="uc-btn uc-btn--secondary">
            📱 Помощь в поддержке
          </a>
        </div>

        <!-- Tips -->
        <div class="uc-tips-box">
          <h3 class="uc-tips-title">💡 Что попробовать:</h3>
          <ol class="uc-tips-list">
            <li>Убедитесь, что правильно введены данные карты</li>
            <li>Попробуйте другой способ оплаты (СБП, другая карта)</li>
            <li>Если у вас Т-Банк или Сбер - используйте встроенное приложение</li>
            <li>Подождите несколько минут и попробуйте еще раз</li>
            <li>Свяжитесь с банком - возможно блокировка безопасности</li>
          </ol>
        </div>

        <!-- Support -->
        <div class="uc-support-box">
          <p class="uc-support-text">
            <strong>🆘 Если проблема сохраняется:</strong> Напишите в Telegram @gdealerofficial 
            с описанием проблемы, и мы поможем вам решить её.
          </p>
        </div>
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

    .uc-payment-status--failed {
      background: linear-gradient(135deg, rgba(244, 67, 54, 0.05), rgba(35, 40, 46, 0.8));
    }

    .container {
      max-width: 600px;
    }

    .uc-status-card {
      background: rgba(255, 255, 255, 0.05);
      border: 2px solid rgba(244, 67, 54, 0.3);
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
      animation: shake 0.5s ease-out;
    }

    .uc-status-icon--error {
      background: rgba(244, 67, 54, 0.15);
      color: #f44336;
    }

    .uc-status-title {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin: 0 0 0.5rem;
    }

    .uc-status-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      margin: 0 0 2rem;
    }

    .uc-error-reasons {
      background: rgba(244, 67, 54, 0.1);
      border: 1px solid rgba(244, 67, 54, 0.2);
      border-radius: 12px;
      padding: 1.5rem;
      margin: 2rem 0;
      text-align: left;
    }

    .uc-reasons-title {
      margin: 0 0 1rem;
      color: white;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .uc-reasons-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .uc-reasons-list li {
      color: rgba(255, 255, 255, 0.75);
      padding: 0.5rem 0;
      font-size: 0.9rem;
    }

    .uc-info-box {
      background: rgba(255, 165, 0, 0.1);
      border: 1px solid rgba(255, 165, 0, 0.2);
      border-radius: 12px;
      padding: 1.5rem;
      margin: 2rem 0;
    }

    .uc-info-box--error {
      background: rgba(244, 67, 54, 0.1);
      border-color: rgba(244, 67, 54, 0.2);
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
      background: linear-gradient(135deg, #ffa500, #ff8c00);
      color: white;
      box-shadow: 0 4px 16px rgba(255, 165, 0, 0.3);
    }

    .uc-btn--primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(255, 165, 0, 0.4);
    }

    .uc-btn--secondary {
      background: rgba(76, 175, 80, 0.15);
      color: #4caf50;
      border: 2px solid #4caf50;
    }

    .uc-btn--secondary:hover {
      background: rgba(76, 175, 80, 0.25);
    }

    .uc-tips-box {
      text-align: left;
      background: rgba(255, 255, 255, 0.02);
      border-left: 4px solid #ff9800;
      padding: 1.5rem;
      margin: 2rem 0;
      border-radius: 8px;
    }

    .uc-tips-title {
      margin: 0 0 1rem;
      color: white;
      font-weight: 700;
    }

    .uc-tips-list {
      margin: 0;
      padding-left: 1.5rem;
      color: rgba(255, 255, 255, 0.75);
    }

    .uc-tips-list li {
      margin-bottom: 0.75rem;
      line-height: 1.5;
    }

    .uc-support-box {
      background: rgba(76, 175, 80, 0.1);
      border: 1px solid rgba(76, 175, 80, 0.2);
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 2rem;
    }

    .uc-support-text {
      margin: 0;
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
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

    @keyframes shake {
      0%, 100% {
        transform: translateX(0);
      }
      25% {
        transform: translateX(-10px);
      }
      75% {
        transform: translateX(10px);
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

      .uc-tips-list {
        font-size: 0.9rem;
      }
    }
  </style>
@endsection
