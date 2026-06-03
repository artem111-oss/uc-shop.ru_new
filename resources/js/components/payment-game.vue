<template>
  <div class="uc-payment-form-wrapper">
    <form class="uc-payment-form" @submit.prevent="handleSubmit" novalidate>
      <!-- Left Side: Input Fields -->
      <div class="uc-form-section uc-form-section--left">
        <!-- Game ID Input with Real-time Validation -->
        <div class="uc-form-group">
          <label for="game-id" class="uc-form-label">
            Введи свой Игровой ID
            <span class="uc-form-hint" title="Find it in PUBG settings">(?)</span>
          </label>
          <div class="uc-input-wrapper">
            <input
              id="game-id"
              v-model.trim="gameId"
              type="text"
              class="uc-form-input"
              :class="{ 'uc-form-input--error': gameIdError, 'uc-form-input--success': gameIdValid }"
              placeholder="Например: 5234891273"
              maxlength="20"
              autocomplete="off"
              @input="validateGameId"
              @focus="gameIdFocused = true"
              @blur="gameIdFocused = false"
            >
            <span v-if="gameIdError" class="uc-input-status uc-input-status--error">❌</span>
            <span v-else-if="gameIdValid" class="uc-input-status uc-input-status--success">✅</span>
          </div>
          <small v-if="gameIdError" class="uc-form-error">
            {{ gameIdError }}
          </small>
          <small v-if="gameIdValid && gameIdFocused" class="uc-form-success">
            ID принят! ✨
          </small>
        </div>

        <!-- UC Selection -->
        <div class="uc-form-group">
          <label class="uc-form-label">Выбери количество UC</label>
          <div class="uc-products-grid">
            <button
              v-for="product in productList"
              :key="product.id"
              type="button"
              class="uc-product-card"
              :class="{ 'uc-product-card--selected': selectedProduct?.id === product.id }"
              @click="selectProduct(product)"
              @focus="focusedProductId = product.id"
              @blur="focusedProductId = null"
            >
              <div class="uc-product-card__icon">🎮</div>
              <div class="uc-product-card__amount">{{ product.name }}</div>
              <div class="uc-product-card__price">{{ product.price }}₽</div>
              <div v-if="product.bonus" class="uc-product-card__bonus">{{ product.bonus }}</div>
              <div v-if="selectedProduct?.id === product.id" class="uc-product-card__check">✓</div>
            </button>
          </div>
        </div>

        <!-- Email (Optional) -->
        <div class="uc-form-group">
          <label for="email" class="uc-form-label">Email (опционально)</label>
          <input
            id="email"
            v-model.trim="email"
            type="email"
            class="uc-form-input"
            placeholder="Для чека и уведомлений"
            autocomplete="email"
          >
          <small class="uc-form-hint-text">На него отправим чек и промокод</small>
        </div>
      </div>

      <!-- Right Side: Order Summary -->
      <div class="uc-form-section uc-form-section--right">
        <div class="uc-order-summary">
          <h2 class="uc-summary-title">Проверьте данные</h2>

          <!-- Product Image -->
          <figure class="uc-summary-image">
            <img src="/images/pubg_mobile.avif" alt="PUBG Mobile UC" loading="lazy">
          </figure>

          <!-- Order Details Table -->
          <table class="uc-summary-table">
            <tbody>
              <tr>
                <td class="uc-summary-label">Игровой ID:</td>
                <td class="uc-summary-value">
                  <span v-if="gameId" class="uc-highlight">{{ gameId }}</span>
                  <span v-else class="uc-placeholder">Не заполнено</span>
                </td>
              </tr>
              <tr>
                <td class="uc-summary-label">Товар:</td>
                <td class="uc-summary-value">
                  <span v-if="selectedProduct" class="uc-highlight">{{ selectedProduct.name }}</span>
                  <span v-else class="uc-placeholder">Выберите</span>
                </td>
              </tr>
              <tr class="uc-summary-total">
                <td class="uc-summary-label">Сумма:</td>
                <td class="uc-summary-value">
                  <span v-if="selectedProduct" class="uc-total-price">{{ selectedProduct.price }}₽</span>
                  <span v-else class="uc-placeholder">—</span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Terms & Conditions -->
          <div class="uc-agreement">
            <input
              id="agree"
              v-model="agreeTerms"
              type="checkbox"
              class="uc-agreement-checkbox"
            >
            <label for="agree" class="uc-agreement-label">
              Согласен с
              <a href="/terms" target="_blank">Условиями использования</a> и
              <a href="/privacy" target="_blank">Политикой конфиденциальности</a>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="uc-btn-submit"
            :disabled="!canSubmit || isLoading"
            :class="{ 'uc-btn-submit--loading': isLoading }"
          >
            <span v-if="isLoading" class="uc-spinner"></span>
            <span v-else>⚡ ОПЛАТИТЬ</span>
          </button>

          <!-- Info Message -->
          <p class="uc-submit-info">
            <small>⏱️ Оплата займёт &lt;30 сек, потом UC прилетят в игру</small>
          </p>

          <!-- Error Message -->
          <div v-if="submitError" class="uc-alert uc-alert--error">
            {{ submitError }}
          </div>

          <!-- Success Message -->
          <div v-if="submitSuccess" class="uc-alert uc-alert--success">
            {{ submitSuccess }}
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

// Types
interface Product {
  id: number
  name: string
  price: number
  bonus?: string
}

// Reactive State
const gameId = ref<string>('')
const email = ref<string>('')
const selectedProduct = ref<Product | null>(null)
const agreeTerms = ref<boolean>(false)
const isLoading = ref<boolean>(false)
const submitError = ref<string>('')
const submitSuccess = ref<string>('')
const gameIdFocused = ref<boolean>(false)
const focusedProductId = ref<number | null>(null)

// Product list with all UC options (expanded)
const productList = ref<Product[]>([
  { id: 1, name: '60 UC', price: 90 },
  { id: 2, name: '180 UC', price: 280, bonus: '+20' },
  { id: 3, name: '325 UC', price: 450 },
  { id: 4, name: '385 UC', price: 610, bonus: '+25' },
  { id: 5, name: '660 UC', price: 900 },
  { id: 6, name: '720 UC', price: 990, bonus: '+60' },
  { id: 7, name: '985 UC', price: 1390 },
  { id: 8, name: '1320 UC', price: 1890, bonus: '+120' },
  { id: 9, name: '1800 UC', price: 2490 },
  { id: 10, name: '1920 UC', price: 2690, bonus: '+120' },
  { id: 11, name: '3850 UC', price: 4990 },
  { id: 12, name: '8100 UC', price: 9990, bonus: '+800' },
  { id: 13, name: '16200 UC', price: 19490 },
  { id: 14, name: '24300 UC', price: 28890 },
  { id: 15, name: '32400 UC', price: 37990, bonus: '+3240' },
  { id: 16, name: '40500 UC', price: 47490, bonus: '+4050' }
])

// Validation & Computed Properties
const gameIdError = computed<string>(() => {
  if (!gameId.value) return ''
  if (gameId.value.length < 4) return 'Минимум 4 символа'
  if (!/^\d+$/.test(gameId.value)) return 'Только цифры'
  return ''
})

const gameIdValid = computed<boolean>(() => {
  return gameId.value.length >= 4 && /^\d+$/.test(gameId.value)
})

const canSubmit = computed<boolean>(() => {
  return gameIdValid.value && selectedProduct.value !== null && agreeTerms.value && !isLoading.value
})

// Methods
const validateGameId = (): void => {
  // Real-time validation happens in computed properties
  submitError.value = ''
}

const selectProduct = (product: Product): void => {
  selectedProduct.value = product
  // Trigger micro-animation by adding animation class
  const card = event?.target as HTMLElement
  if (card) {
    card.classList.add('uc-product-card--animate')
    setTimeout(() => card.classList.remove('uc-product-card--animate'), 300)
  }
}

const handleSubmit = async (): Promise<void> => {
  if (!canSubmit.value) return

  submitError.value = ''
  submitSuccess.value = ''
  isLoading.value = true

  try {
    // Step 1: Create order on backend
    const createOrderResponse = await fetch('/api/orders/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
      },
      body: JSON.stringify({
        game_id: gameId.value,
        email: email.value || null,
        product_id: selectedProduct.value!.id
      })
    })

    if (!createOrderResponse.ok) {
      throw new Error('Ошибка при создании заказа')
    }

    const orderData = await createOrderResponse.json()
    const orderId = orderData.id

    // Step 2: Initialize payment with Platima
    const paymentResponse = await fetch('/api/payment/init', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
      },
      body: JSON.stringify({
        order_id: orderId,
        amount: selectedProduct.value!.price,
        game_id: gameId.value
      })
    })

    if (!paymentResponse.ok) {
      throw new Error('Ошибка при инициализации платежа')
    }

    const paymentData = await paymentResponse.json()

    if (!paymentData.link) {
      throw new Error('Не получена ссылка для оплаты')
    }

    // Step 3: Redirect to Platima payment page
    submitSuccess.value = '✅ Перенаправление на оплату...'
    window.location.href = paymentData.link

  } catch (error: unknown) {
    isLoading.value = false
    const errorMessage = error instanceof Error ? error.message : 'Неизвестная ошибка'
    submitError.value = `❌ ${errorMessage}. Попробуйте еще раз или свяжитесь с поддержкой.`
    console.error('Payment error:', error)
  }
}

const getCsrfToken = (): string => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  return token || ''
}

// Lifecycle
onMounted(() => {
  // Set first product as selected by default
  if (productList.value.length > 0) {
    selectedProduct.value = productList.value[3] // Select 385 UC by default
  }

  // Auto-focus on game ID input for better UX
  const gameIdInput = document.getElementById('game-id') as HTMLInputElement
  if (gameIdInput) {
    setTimeout(() => gameIdInput.focus(), 100)
  }
})

// Watch for product changes - trigger animation
watch(selectedProduct, (newProduct) => {
  if (newProduct) {
    // Micro-animation is handled in selectProduct
  }
})
</script>

<style scoped lang="scss">
// Variables for consistency
$primary-color: #ffa500;
$primary-hover: #ff8c00;
$success-color: #4caf50;
$error-color: #f44336;
$dark-bg: #23282e;
$light-text: #ffffff;
$border-color: #3d4451;
$transition-duration: 0.3s;

// Payment Form Wrapper
.uc-payment-form-wrapper {
  width: 100%;
  padding: 2rem 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 600px;
}

// Main Form
.uc-payment-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 2rem;
  width: 100%;
  max-width: 1200px;
  animation: slideUp 0.6s ease-out;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    padding: 1.5rem;
  }
}

// Form Sections
.uc-form-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;

  &--left {
    @media (max-width: 768px) {
      order: 2;
    }
  }

  &--right {
    @media (max-width: 768px) {
      order: 1;
    }
  }
}

// Form Groups
.uc-form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.uc-form-label {
  font-size: 0.95rem;
  font-weight: 600;
  color: $light-text;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.uc-form-hint {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  font-size: 0.75rem;
  cursor: help;
  opacity: 0.7;

  &:hover {
    opacity: 1;
    background: rgba(255, 165, 0, 0.2);
  }
}

// Input Wrapper
.uc-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

// Form Input
.uc-form-input {
  padding: 0.75rem 1rem;
  border: 2px solid $border-color;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.08);
  color: $light-text;
  font-size: 1rem;
  transition: all $transition-duration ease;
  outline: none;

  &::placeholder {
    color: rgba(255, 255, 255, 0.4);
  }

  &:focus {
    border-color: $primary-color;
    background: rgba(255, 165, 0, 0.08);
    box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
  }

  &--success {
    border-color: $success-color;
    background: rgba(76, 175, 80, 0.05);

    &:focus {
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }
  }

  &--error {
    border-color: $error-color;
    background: rgba(244, 67, 54, 0.05);

    &:focus {
      box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
    }
  }
}

// Input Status Icons
.uc-input-status {
  position: absolute;
  right: 0.75rem;
  font-size: 1rem;
  pointer-events: none;
  animation: scaleIn 0.3s ease-out;

  &--success {
    color: $success-color;
  }

  &--error {
    color: $error-color;
  }
}

// Form Help Text
.uc-form-error {
  font-size: 0.8rem;
  color: $error-color;
  animation: slideDown 0.3s ease-out;
}

.uc-form-success {
  font-size: 0.8rem;
  color: $success-color;
  animation: slideDown 0.3s ease-out;
}

.uc-form-hint-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
}

// Products Grid
.uc-packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(120px,1fr));
    gap: 0.2rem;
    max-width: 1000px;
    margin: -10px auto;

  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 0.6rem;
  }
}

// Product Card
.uc-product-card {
  position: relative;
  padding: 1rem 0.75rem;
  border: 2px solid transparent;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.05);
  color: $light-text;
  cursor: pointer;
  transition: all $transition-duration ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-align: center;
  font-size: 0.9rem;
  font-weight: 500;
  outline: none;

  &:hover:not(:disabled) {
    border-color: $primary-color;
    background: rgba(255, 165, 0, 0.1);
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(255, 165, 0, 0.15);
  }

  &:focus {
    border-color: $primary-color;
    box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.2);
  }

  &--selected {
    border-color: $primary-color;
    background: linear-gradient(135deg, rgba(255, 165, 0, 0.2), rgba(255, 165, 0, 0.05));
    box-shadow: 0 8px 20px rgba(255, 165, 0, 0.2);

    .uc-product-card__amount {
      color: $primary-color;
      font-weight: 700;
    }
  }

  &--animate {
    animation: cardPulse 0.3s ease-out;
  }

  &__icon {
    font-size: 1.5rem;
  }

  &__amount {
    font-weight: 600;
    color: $light-text;
  }

  &__price {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
  }

  &__bonus {
    font-size: 0.75rem;
    color: $success-color;
    font-weight: 700;
    background: rgba(76, 175, 80, 0.15);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
  }

  &__check {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: $success-color;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    animation: checkPop 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  }
}

// Order Summary
.uc-order-summary {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 165, 0, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
}

.uc-summary-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: $light-text;
  margin: 0;
}

.uc-summary-image {
  margin: 0;
  overflow: hidden;
  border-radius: 8px;
  aspect-ratio: 1;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }

  &:hover img {
    transform: scale(1.05);
  }
}

// Summary Table
.uc-summary-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;

  tbody tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);

    &:last-child {
      border-bottom: none;
    }

    &.uc-summary-total {
      background: rgba(255, 165, 0, 0.05);
      border-top: 2px solid $primary-color;
      border-bottom: 2px solid $primary-color;
    }
  }

  td {
    padding: 0.75rem 0;
  }
}

.uc-summary-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 500;
  text-align: left;
}

.uc-summary-value {
  text-align: right;
  color: $light-text;
  font-weight: 600;
}

.uc-highlight {
  color: $primary-color;
  font-weight: 700;
}

.uc-placeholder {
  color: rgba(255, 255, 255, 0.3);
  font-style: italic;
}

.uc-total-price {
  color: $success-color;
  font-size: 1.2rem;
  font-weight: 700;
}

// Agreement
.uc-agreement {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 165, 0, 0.05);
  border-radius: 8px;
}

.uc-agreement-checkbox {
  width: 20px;
  height: 20px;
  margin-top: 0.2rem;
  cursor: pointer;
  accent-color: $primary-color;
}

.uc-agreement-label {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  line-height: 1.4;

  a {
    color: $primary-color;
    text-decoration: none;
    transition: opacity $transition-duration ease;

    &:hover {
      opacity: 0.8;
      text-decoration: underline;
    }
  }
}

// Submit Button
.uc-btn-submit {
  padding: 0.85rem 1.5rem;
  border: none;
  border-radius: 8px;
  background: linear-gradient(135deg, $primary-color, $primary-hover);
  color: white;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all $transition-duration ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  outline: none;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 16px rgba(255, 165, 0, 0.3);
  min-height: 50px;

  &:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(255, 165, 0, 0.4);
  }

  &:not(:disabled):active {
    transform: translateY(0);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  &--loading {
    pointer-events: none;
  }
}

// Spinner
.uc-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

// Submit Info
.uc-submit-info {
  text-align: center;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
}

// Alerts
.uc-alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  animation: slideDown 0.3s ease-out;

  &--error {
    background: rgba(244, 67, 54, 0.15);
    color: #ffcdd2;
    border: 1px solid rgba(244, 67, 54, 0.3);
  }

  &--success {
    background: rgba(76, 175, 80, 0.15);
    color: #c8e6c9;
    border: 1px solid rgba(76, 175, 80, 0.3);
  }
}

// Animations
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes cardPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes checkPop {
  0% {
    transform: scale(0) rotate(-45deg);
    opacity: 0;
  }
  70% {
    transform: scale(1.2) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0);
    opacity: 1;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>