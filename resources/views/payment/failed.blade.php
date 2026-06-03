@extends('app')
@section('title', 'Ошибка оплаты')
@section('content')
<section class="uc-payment-result">
  <div class="container">
    <div class="uc-result-card uc-result-card--failed">
      <div class="uc-result-icon">
        <div class="uc-error-icon">❌</div>
      </div>
      
      <h1 class="uc-result-title">Платеж не выполнен</h1>
      <p class="uc-result-subtitle">Что-то пошло не так при обработке платежа</p>
      
      @if(isset($order))
      <div class="uc-order-details">
        <div class="uc-detail-row">
          <span class="uc-detail-label">Номер заказа:</span>
          <span class="uc-detail-value">#{{ $order->id }}</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Количество UC:</span>
          <span class="uc-detail-value">{{ $order->uc_amount ?? $order->product->name }}</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Сумма:</span>
          <span class="uc-detail-value">{{ number_format($order->amount, 0, '.', ' ') }} ₽</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Статус:</span>
          <span class="uc-detail-value" style="color: #ff6b6b;">Отклонен</span>
        </div>
      </div>
      @endif
      
      <div class="uc-result-actions">
        <a href="{{ route('home') }}" class="uc-btn uc-btn--primary">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
          </svg>
          Попробовать снова
        </a>
        <a href="https://t.me/ucshop_dobro" target="_blank" class="uc-btn uc-btn--secondary">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
          </svg>
          Связаться с поддержкой
        </a>
      </div>
      
      <div class="uc-result-note" style="border-color: rgba(255, 107, 107, 0.3); background: rgba(255, 107, 107, 0.1);">
        <p>🔍 <strong>Возможные причины:</strong></p>
        <ul style="list-style: none; padding-left: 0;">
          <li>❌ Недостаточно средств на карте</li>
          <li>❌ Карта не поддерживает онлайн-платежи</li>
          <li>❌ Превышен лимит по карте</li>
          <li>❌ Неверно введены данные карты</li>
          <li>❌ Банк отклонил операцию</li>
        </ul>
        <p style="margin-top: 1rem;">
          💬 Если проблема повторяется — напишите в 
          <a href="https://t.me/ucshop_dobro" target="_blank">Telegram @ucshop_dobro</a>
        </p>
      </div>
    </div>
  </div>
</section>

<style>
.uc-payment-result {
  min-height: 80vh;
  display: flex;
  align-items: center;
  padding: 4rem 1rem;
  background: linear-gradient(135deg, #23282e, #2d3339);
}

.uc-result-card {
  max-width: 600px;
  margin: 0 auto;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
  border-radius: 16px;
  padding: 3rem 2rem;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: slideUp 0.6s ease-out;
  
  &--success {
    border: 2px solid rgba(81, 207, 102, 0.3);
  }
  
  &--failed {
    border: 2px solid rgba(255, 107, 107, 0.3);
  }
}

.uc-result-icon {
  margin-bottom: 2rem;
}

.uc-error-icon {
  font-size: 5rem;
  animation: shake 0.5s ease-out;
}

.uc-result-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: white;
}

.uc-result-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 2rem;
}

.uc-order-details {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  text-align: left;
}

.uc-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  
  &:last-child {
    border-bottom: none;
  }
}

.uc-detail-label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.95rem;
}

.uc-detail-value {
  color: white;
  font-weight: 600;
}

.uc-result-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.uc-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  
  &--primary {
    background: linear-gradient(135deg, #0396ff, #0276cc);
    color: white;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(3, 150, 255, 0.3);
    }
  }
  
  &--secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    
    &:hover {
      background: rgba(255, 255, 255, 0.15);
    }
  }
}

.uc-result-note {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 8px;
  padding: 1.5rem;
  text-align: left;
  
  p {
    margin: 0 0 1rem;
    color: rgba(255, 255, 255, 0.9);
    
    &:last-child {
      margin-bottom: 0;
    }
  }
  
  ul {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    
    li {
      margin-bottom: 0.5rem;
      
      &:last-child {
        margin-bottom: 0;
      }
    }
  }
  
  a {
    color: #0396ff;
    text-decoration: none;
    
    &:hover {
      text-decoration: underline;
    }
  }
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
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
  20%, 40%, 60%, 80% { transform: translateX(10px); }
}

@media (max-width: 768px) {
  .uc-result-card {
    padding: 2rem 1.5rem;
  }
  
  .uc-result-title {
    font-size: 1.5rem;
  }
  
  .uc-result-actions {
    flex-direction: column;
  }
  
  .uc-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
@endsection
