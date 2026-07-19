<template>
  <div class="uc-account">
    <div v-if="loadingUser" class="uc-account__loading">Загрузка…</div>

    <div v-else-if="!user" class="uc-account__login">
      <h1 class="uc-account__title">Вход в личный кабинет</h1>

      <form v-if="step === 'email'" class="uc-account__form" @submit.prevent="handleRequestCode">
        <label class="uc-account__label" for="account-email">Email</label>
        <input
          id="account-email"
          v-model="email"
          type="email"
          required
          autocomplete="email"
          class="uc-account__input"
          placeholder="you@example.com"
        >
        <button type="submit" class="uc-account__button" :disabled="submitting">
          {{ submitting ? 'Отправляем…' : 'Получить код' }}
        </button>
      </form>

      <form v-else class="uc-account__form" @submit.prevent="handleVerifyCode">
        <p class="uc-account__hint">Код отправлен на {{ email }}</p>
        <label class="uc-account__label" for="account-code">Код из письма</label>
        <input
          id="account-code"
          v-model="code"
          inputmode="numeric"
          pattern="\d{6}"
          maxlength="6"
          required
          class="uc-account__input"
          placeholder="123456"
        >
        <button type="submit" class="uc-account__button" :disabled="submitting">
          {{ submitting ? 'Проверяем…' : 'Войти' }}
        </button>
        <button type="button" class="uc-account__link" @click="resetToEmailStep">
          Изменить email
        </button>
      </form>

      <p v-if="errorMessage" class="uc-account__error">{{ errorMessage }}</p>
      <p v-if="infoMessage" class="uc-account__info">{{ infoMessage }}</p>
    </div>

    <div v-else class="uc-account__dashboard">
      <div class="uc-account__header">
        <div>
          <h1 class="uc-account__title">Личный кабинет</h1>
          <p class="uc-account__email">{{ user.email }}</p>
        </div>
        <button type="button" class="uc-account__logout" @click="handleLogout">
          Выйти
        </button>
      </div>

      <section class="uc-card">
        <h2 class="uc-card__title">Мои заказы</h2>

        <p v-if="loadingOrders" class="uc-account__loading">Загружаем заказы…</p>
        <p v-else-if="orders.length === 0" class="uc-account__empty">
          У вас пока нет заказов, оформленных из личного кабинета.
        </p>

        <ul v-else class="uc-account__orders">
          <li v-for="order in orders" :key="order.id" class="uc-account__order">
            <div class="uc-account__order-row">
              <span class="uc-account__order-id">Заказ #{{ order.id }}</span>
              <span class="uc-account__order-status" :data-status="order.status.code">
                {{ order.status.label }}
              </span>
            </div>
            <div class="uc-account__order-row">
              <span>{{ order.product.name }}</span>
              <span>{{ order.price }} ₽</span>
            </div>
            <div class="uc-account__order-row uc-account__order-row--muted">
              <span>{{ formatDate(order.created_at) }}</span>
              <span v-if="order.pubg_id">ID: {{ order.pubg_id }}</span>
            </div>
            <a :href="buildSupportLink(order)" target="_blank" class="uc-account__support-link">
              Написать в поддержку
            </a>
          </li>
        </ul>

        <div v-if="orders.length > 0" class="uc-account__pagination">
          <button
            type="button"
            class="uc-account__page-button"
            :disabled="currentPage <= 1"
            @click="changePage(currentPage - 1)"
          >
            Назад
          </button>
          <span class="uc-account__page-info">{{ currentPage }} / {{ lastPage }}</span>
          <button
            type="button"
            class="uc-account__page-button"
            :disabled="currentPage >= lastPage"
            @click="changePage(currentPage + 1)"
          >
            Далее
          </button>
        </div>
      </section>

      <PubgAccounts />
      <NotificationSettings />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { AuthUser, OrderSummary } from '../services/auth';
import {
  fetchCurrentUser,
  requestLoginCode,
  verifyLoginCode,
  fetchOrders,
  logout as logoutRequest,
} from '../services/auth';
import PubgAccounts from './PubgAccounts.vue';
import NotificationSettings from './NotificationSettings.vue';

function buildSupportLink(order: OrderSummary): string {
  const text = `Здравствуйте! Вопрос по заказу #${order.id}\n`
    + `Товар: ${order.product.name}\n`
    + `PUBG ID: ${order.pubg_id || '—'}\n`
    + `Статус: ${order.status.label}`;

  return `https://t.me/ucshop_air?text=${encodeURIComponent(text)}`;
}

const user = ref<AuthUser | null>(null);
const loadingUser = ref(true);
const step = ref<'email' | 'code'>('email');
const email = ref('');
const code = ref('');
const submitting = ref(false);
const errorMessage = ref('');
const infoMessage = ref('');

const orders = ref<OrderSummary[]>([]);
const loadingOrders = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);

function formatDate(value: string | null): string {
  if (!value) {
    return '—';
  }

  return new Date(value).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

async function loadOrders(page = 1): Promise<void> {
  loadingOrders.value = true;

  try {
    const response = await fetchOrders(page);
    orders.value = response.data;
    currentPage.value = response.meta.current_page;
    lastPage.value = response.meta.last_page;
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Ошибка загрузки заказов.';
  } finally {
    loadingOrders.value = false;
  }
}

function changePage(page: number): void {
  if (page < 1 || page > lastPage.value) {
    return;
  }

  loadOrders(page);
}

async function handleRequestCode(): Promise<void> {
  errorMessage.value = '';
  infoMessage.value = '';
  submitting.value = true;

  try {
    const response = await requestLoginCode(email.value.trim().toLowerCase());
    infoMessage.value = response.message;
    step.value = 'code';
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось отправить код.';
  } finally {
    submitting.value = false;
  }
}

async function handleVerifyCode(): Promise<void> {
  errorMessage.value = '';
  submitting.value = true;

  try {
    const response = await verifyLoginCode(email.value.trim().toLowerCase(), code.value.trim());
    user.value = response.user;
    await loadOrders(1);
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Код неверен или истёк.';
  } finally {
    submitting.value = false;
  }
}

function resetToEmailStep(): void {
  step.value = 'email';
  code.value = '';
  errorMessage.value = '';
  infoMessage.value = '';
}

async function handleLogout(): Promise<void> {
  await logoutRequest();
  user.value = null;
  orders.value = [];
  step.value = 'email';
  email.value = '';
  code.value = '';
}

onMounted(async () => {
  loadingUser.value = true;
  const currentUser = await fetchCurrentUser();
  user.value = currentUser;
  loadingUser.value = false;

  if (currentUser) {
    await loadOrders(1);
  }
});
</script>

<style scoped>
.uc-account {
  max-width: 480px;
  margin: 0 auto;
  padding: 24px 16px 48px;
  color: #fff;
  font-family: inherit;
}

.uc-card {
  background: #1e2227;
  border: 1px solid #2a3140;
  border-radius: 14px;
  padding: 18px 16px;
  margin-top: 20px;
}

.uc-card__title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #f2f4f7;
  margin: 0 0 14px;
  padding-left: 12px;
  border-left: 3px solid #ffc107;
  line-height: 1.2;
}

.uc-account__title {
  font-size: 1.5rem;
  margin-bottom: 4px;
}

.uc-account__form {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 16px;
}

.uc-account__label {
  font-size: 0.85rem;
  color: #cbd5e1;
}

.uc-account__input {
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #334056;
  background: #14161a;
  color: #fff;
  font-size: 1rem;
}

.uc-account__input:focus {
  outline: none;
  border-color: #ffc107;
}

.uc-account__button {
  background: #ffc107;
  color: #14161a;
  border: none;
  border-radius: 8px;
  padding: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: filter 0.15s ease;
}

.uc-account__button:hover {
  filter: brightness(1.08);
}

.uc-account__button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.uc-account__link {
  background: none;
  border: none;
  color: #ffc107;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 4px 0;
  text-align: left;
}

.uc-account__error {
  color: #ff6b6b;
  margin-top: 12px;
  font-size: 0.9rem;
}

.uc-account__info {
  color: #7ee787;
  margin-top: 12px;
  font-size: 0.9rem;
}

.uc-account__hint {
  color: #9aa5b1;
  font-size: 0.85rem;
  margin: 0 0 4px;
}

.uc-account__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.uc-account__email {
  color: #9aa5b1;
  font-size: 0.85rem;
}

.uc-account__logout {
  background: transparent;
  border: 1px solid #334056;
  color: #fff;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.85rem;
  cursor: pointer;
  white-space: nowrap;
  transition: border-color 0.15s ease;
}

.uc-account__logout:hover {
  border-color: #ffc107;
}

.uc-account__loading,
.uc-account__empty {
  color: #9aa5b1;
  font-size: 0.9rem;
}

.uc-account__orders {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 0;
  margin: 0;
}

.uc-account__order {
  background: #262c36;
  border: 1px solid #334056;
  border-radius: 10px;
  padding: 12px 14px;
}

.uc-account__order-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  margin-bottom: 4px;
}

.uc-account__order-row--muted {
  color: #9aa5b1;
  font-size: 0.8rem;
}

.uc-account__order-status {
  font-weight: 600;
}

.uc-account__order-status[data-status="completed"] {
  color: #7ee787;
}

.uc-account__order-status[data-status="pending_payment"] {
  color: #ffc107;
}

.uc-account__order-status[data-status="failed"] {
  color: #ff6b6b;
}

.uc-account__support-link {
  display: inline-block;
  margin-top: 8px;
  color: #ffc107;
  font-size: 0.8rem;
  text-decoration: none;
}

.uc-account__support-link:hover {
  text-decoration: underline;
}

.uc-account__pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
}

.uc-account__page-button {
  background: #334056;
  border: none;
  color: #fff;
  border-radius: 8px;
  padding: 8px 14px;
  cursor: pointer;
  font-size: 0.85rem;
}

.uc-account__page-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.uc-account__page-info {
  color: #9aa5b1;
  font-size: 0.85rem;
}
</style>