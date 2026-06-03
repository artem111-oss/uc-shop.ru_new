@extends('app')
@section('title', 'Платеж успешно выполнен ✓')
@section('content')
<section class="uc-payment-result">
  <div class="container">
    <div class="uc-result-card uc-result-card--success">
      <div class="uc-result-icon">
        <svg viewBox="0 0 52 52" class="uc-checkmark">
          <circle class="uc-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
          <path class="uc-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
      </div>
      
      <h1 class="uc-result-title">Платеж успешно выполнен!</h1>
      <p class="uc-result-subtitle">UC будут зачислены в течение 30-60 секунд</p>
      
      <div class="uc-order-details">
        <div class="uc-detail-row">
          <span class="uc-detail-label">Номер заказа:</span>
          <span class="uc-detail-value">#{{ $order->id }}</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Количество UC:</span>
          <span class="uc-detail-value uc-detail-value--highlight">{{ $order->uc_amount ?? $order->product->name }}</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Игровой ID:</span>
          <span class="uc-detail-value">{{ $order->game_id }}</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Сумма:</span>
          <span class="uc-detail-value">{{ number_format($order->amount, 0, '.', ' ') }} ₽</span>
        </div>
        <div class="uc-detail-row">
          <span class="uc-detail-label">Дата:</span>
          <span class="uc-detail-value">{{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>
      </div>
      
      <div class="uc-result-actions">
        <a href="{{ route('home') }}" class="uc-btn uc-btn--primary">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
          </svg>
          Вернуться на главную
        </a>
      </div>
      
      <div class="uc-result-note">
        <p>💡 <strong>Что делать дальше?</strong></p>
        <ol>
          <li>Откройте игру PUBG Mobile</li>
          <li>Проверьте баланс UC в профиле</li>
          <li>Если UC не пришли через 5 минут — напишите в <a href="https://t.me/ucshop_dobro" target="_blank">Telegram @ucshop_dobro</a></li>
        </ol>
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

.uc-checkmark {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: block;
  stroke-width: 2;
  stroke: #51cf66;
  stroke-miterlimit: 10;
  margin: 0 auto;
  animation: scaleIn 0.5s ease-out;
}

.uc-checkmark-circle {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 2;
  stroke-miterlimit: 10;
  stroke: #51cf66;
  fill: rgba(81, 207, 102, 0.1);
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.uc-checkmark-check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

.uc-error-icon {
  font-size: 5rem;
  color: #ff6b6b;
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
  
  &--highlight {
    color: #51cf66;
    font-size: 1.1rem;
  }
}

.uc-result-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 2rem;
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
    background: linear-gradient(135deg, #51cf66, #37b24d);
    color: white;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(81, 207, 102, 0.3);
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
  background: rgba(3, 150, 255, 0.1);
  border: 1px solid rgba(3, 150, 255, 0.3);
  border-radius: 8px;
  padding: 1.5rem;
  text-align: left;
  
  p {
    margin: 0 0 1rem;
    color: rgba(255, 255, 255, 0.9);
  }
  
  ol {
    margin: 0;
    padding-left: 1.5rem;
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

@keyframes scaleIn {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes stroke {
  100% {
    stroke-dashoffset: 0;
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
