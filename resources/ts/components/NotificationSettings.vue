<template>
  <section class="uc-card">
    <h2 class="uc-card__title">Уведомления</h2>

    <div class="uc-notify__card">
      <label class="uc-notify__row">
        <span class="uc-notify__label">Email</span>
        <span
          class="uc-toggle"
          :class="{ 'uc-toggle--disabled': !emailIsLinked }"
        >
          <input
            type="checkbox"
            class="uc-toggle__input"
            :checked="prefs.notify_email"
            :disabled="!emailIsLinked"
            @change="toggle('notify_email', $event)"
          >
          <span class="uc-toggle__track"></span>
        </span>
      </label>

      <label class="uc-notify__row">
        <span class="uc-notify__label">Telegram</span>
        <span class="uc-toggle" :class="{ 'uc-toggle--disabled': !hasTelegramLink }">
          <input
            type="checkbox"
            class="uc-toggle__input"
            :checked="prefs.notify_telegram"
            :disabled="!hasTelegramLink"
            @change="toggle('notify_telegram', $event)"
          >
          <span class="uc-toggle__track"></span>
        </span>
      </label>
    </div>

    <div v-if="!emailIsLinked" class="uc-notify__link-block uc-notify__email-link">
  <div class="uc-notify__email-heading">
    <span class="uc-notify__email-icon">✉</span>
    <div>
      <h3>Привязать email</h3>
      <p>Получайте чеки и уведомления о заказах.</p>
    </div>
  </div>

  <form
    v-if="emailStep === 'email'"
    class="uc-notify__email-form"
    @submit.prevent="handleRequestEmailCode"
  >
    <label class="uc-notify__email-label" for="notification-email">
      Ваш email
    </label>

    <input
      id="notification-email"
      v-model="email"
      type="email"
      required
      autocomplete="email"
      class="uc-notify__email-input"
      placeholder="name@example.com"
    >

    <button
      type="submit"
      class="uc-notify__email-button"
      :disabled="emailSubmitting"
    >
      {{ emailSubmitting ? 'Отправляем код…' : 'Получить код' }}
    </button>
  </form>

  <form
    v-else
    class="uc-notify__email-form"
    @submit.prevent="handleVerifyEmailCode"
  >
    <p class="uc-notify__email-sent">
      Код отправлен на <strong>{{ email }}</strong>
    </p>

    <label class="uc-notify__email-label" for="notification-email-code">
      Код из письма
    </label>

    <input
      id="notification-email-code"
      v-model="emailCode"
      type="text"
      inputmode="numeric"
      autocomplete="one-time-code"
      pattern="\d{6}"
      maxlength="6"
      required
      class="uc-notify__email-input uc-notify__email-input--code"
      placeholder="123456"
    >

    <button
      type="submit"
      class="uc-notify__email-button"
      :disabled="emailSubmitting"
    >
      {{ emailSubmitting ? 'Проверяем…' : 'Подтвердить email' }}
    </button>

    <button
      type="button"
      class="uc-notify__email-change"
      :disabled="emailSubmitting"
      @click="resetEmailStep"
    >
      Изменить email
    </button>
  </form>
</div>

    <div v-else class="uc-notify__email-linked">
      <span>Email для уведомлений:</span>
      <strong>{{ accountEmail }}</strong>
    </div>

    <div v-if="!hasTelegramLink" class="uc-notify__link-block">
      <p class="uc-account__hint">Чтобы получать уведомления в Telegram, войдите через бота</p>
      <div id="telegram-login-widget" class="uc-notify__widget"></div>
    </div>

    <div v-else class="uc-notify__link-block">
      <p class="uc-notify__linked">Привязан аккаунт: <strong>@{{ linkedUsername }}</strong></p>
      <button type="button" class="uc-notify__unlink" @click="handleUnlink">
        Отключить Telegram
      </button>
    </div>

    <p v-if="errorMessage" class="uc-account__error">{{ errorMessage }}</p>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue';
import type { AuthUser, TelegramLinkStatus } from '../services/auth';
import {
  fetchCurrentUser,
  fetchNotificationPrefs,
  updateNotificationPrefs,
  fetchTelegramStatus,
  linkTelegramWidget,
  unlinkTelegram,
  requestEmailLinkCode,
  verifyEmailLinkCode,
} from '../services/auth';

declare global {
  interface Window {
    onTelegramAuth?: (user: Record<string, any>) => void;
  }
}

const prefs = ref({ notify_email: true, notify_telegram: false });
const links = ref<TelegramLinkStatus[]>([]);
const errorMessage = ref('');
const accountUser = ref<AuthUser | null>(null);
const email = ref('');
const emailCode = ref('');
const emailStep = ref<'email' | 'code'>('email');
const emailSubmitting = ref(false);

const emailIsLinked = computed(() => accountUser.value?.email_is_linked === true);
const accountEmail = computed(() => accountUser.value?.email ?? '');

const hasTelegramLink = computed(() => links.value.length > 0);
const linkedUsername = computed(() => links.value[0]?.telegram_username || 'Telegram');

async function load(): Promise<void> {
  try {
    const [currentUser, notificationPrefs, telegramLinks] = await Promise.all([
      fetchCurrentUser(),
      fetchNotificationPrefs(),
      fetchTelegramStatus(),
    ]);

    accountUser.value = currentUser;
    prefs.value = notificationPrefs;
    links.value = telegramLinks;
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Ошибка загрузки настроек.';
  }
}

async function toggle(key: 'notify_email' | 'notify_telegram', event: Event): Promise<void> {
  const checked = (event.target as HTMLInputElement).checked;
  errorMessage.value = '';

  try {
    await updateNotificationPrefs({ [key]: checked });
    prefs.value = { ...prefs.value, [key]: checked };
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось сохранить.';
    (event.target as HTMLInputElement).checked = !checked;
  }
}

async function handleRequestEmailCode(): Promise<void> {
  errorMessage.value = '';
  emailSubmitting.value = true;

  try {
    await requestEmailLinkCode(email.value.trim().toLowerCase());
    emailStep.value = 'code';
  } catch (error) {
    errorMessage.value = error instanceof Error
      ? error.message
      : 'Не удалось отправить код.';
  } finally {
    emailSubmitting.value = false;
  }
}

async function handleVerifyEmailCode(): Promise<void> {
  errorMessage.value = '';
  emailSubmitting.value = true;

  try {
    const response = await verifyEmailLinkCode(
      email.value.trim().toLowerCase(),
      emailCode.value.trim()
    );

    accountUser.value = response.user;
    prefs.value = {
      notify_email: !!response.user.notify_email,
      notify_telegram: !!response.user.notify_telegram,
    };

    emailCode.value = '';
    emailStep.value = 'email';
  } catch (error) {
    errorMessage.value = error instanceof Error
      ? error.message
      : 'Не удалось подтвердить email.';
  } finally {
    emailSubmitting.value = false;
  }
}

function resetEmailStep(): void {
  emailStep.value = 'email';
  emailCode.value = '';
  errorMessage.value = '';
}

function mountTelegramWidget(): void {
  if (hasTelegramLink.value) {
    return;
  }

  const container = document.getElementById('telegram-login-widget');

  if (!container || container.childElementCount > 0) {
    return;
  }

  window.onTelegramAuth = async (user) => {
    errorMessage.value = '';
    try {
      await linkTelegramWidget({
        id: user.id,
        first_name: user.first_name,
        last_name: user.last_name,
        username: user.username,
        photo_url: user.photo_url,
        auth_date: user.auth_date,
        hash: user.hash,
      });
      await load();
    } catch (error) {
      errorMessage.value = error instanceof Error ? error.message : 'Не удалось привязать Telegram.';
    }
  };

  const script = document.createElement('script');
  script.src = 'https://telegram.org/js/telegram-widget.js?22';
  script.setAttribute('data-telegram-login', 'uctyt_bot');
  script.setAttribute('data-size', 'large');
  script.setAttribute('data-onauth', 'onTelegramAuth(user)');
  script.setAttribute('data-request-access', 'write');
  script.async = true;

  container.appendChild(script);
}

async function handleUnlink(): Promise<void> {
  errorMessage.value = '';

  try {
    await unlinkTelegram('uctyt');
    await load();

    await nextTick();
    mountTelegramWidget();
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось отключить.';
  }
}

onMounted(async () => {
  await load();

  await nextTick();
  mountTelegramWidget();
});
</script>

<style scoped>
.uc-notify__card {
  background: #262c36;
  border: 1px solid #334056;
  border-radius: 12px;
  padding: 4px 16px;
}

.uc-notify__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #334056;
  cursor: pointer;
}

.uc-notify__row:last-child {
  border-bottom: none;
}

.uc-notify__label {
  font-size: 0.95rem;
  color: #e6e9ee;
}

.uc-toggle {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 26px;
  flex-shrink: 0;
}

.uc-toggle__input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

.uc-toggle__track {
  position: absolute;
  inset: 0;
  background: #3a4356;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.uc-toggle__track::before {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s ease;
}

.uc-toggle__input:checked + .uc-toggle__track {
  background: #ffc107;
}

.uc-toggle__input:checked + .uc-toggle__track::before {
  transform: translateX(20px);
}

.uc-toggle--disabled {
  opacity: 0.4;
  pointer-events: none;
}

.uc-notify__link-block {
  margin-top: 16px;
  background: #262c36;
  border: 1px solid #334056;
  border-radius: 12px;
  padding: 16px;
}

.uc-notify__widget {
  margin-top: 10px;
  display: flex;
  justify-content: center;
}

.uc-notify__linked {
  color: #9aa5b1;
  font-size: 0.9rem;
  margin: 0 0 10px;
}

.uc-notify__linked strong {
  color: #e6e9ee;
}

.uc-notify__unlink {
  background: transparent;
  border: 1px solid #ff6b6b;
  color: #ff6b6b;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.85rem;
  cursor: pointer;
  width: 100%;
  transition: background 0.15s ease;
}

.uc-notify__unlink:hover {
  background: rgba(255, 107, 107, 0.1);
}

.uc-account__hint {
  color: #9aa5b1;
  font-size: 0.85rem;
  margin: 0 0 4px;
}

.uc-account__error {
  color: #ff6b6b;
  margin-top: 10px;
  font-size: 0.85rem;
}

.uc-notify__email-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.uc-notify__email-linked {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 16px;
  padding: 14px 16px;
  background: #262c36;
  border: 1px solid #334056;
  border-radius: 12px;
  color: #9aa5b1;
  font-size: 0.9rem;
  overflow-wrap: anywhere;
}

.uc-notify__email-linked strong {
  color: #e6e9ee;
}

.uc-notify__email-link {
  padding: 18px;
}

.uc-notify__email-heading {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 16px;
}

.uc-notify__email-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  border-radius: 10px;
  background: rgba(255, 193, 7, 0.14);
  color: #ffc107;
  font-size: 1.05rem;
}

.uc-notify__email-heading h3 {
  margin: 0 0 4px;
  color: #e6e9ee;
  font-size: 1rem;
}

.uc-notify__email-heading p,
.uc-notify__email-sent {
  margin: 0;
  color: #9aa5b1;
  font-size: 0.87rem;
  line-height: 1.45;
}

.uc-notify__email-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.uc-notify__email-label {
  color: #e6e9ee;
  font-size: 0.9rem;
  font-weight: 600;
}

.uc-notify__email-input {
  width: 100%;
  min-height: 46px;
  box-sizing: border-box;
  border: 1px solid #46536a;
  border-radius: 8px;
  outline: none;
  background: #171b22;
  color: #f1f3f5;
  padding: 11px 13px;
  font: inherit;
}

.uc-notify__email-input::placeholder {
  color: #718096;
}

.uc-notify__email-input:focus {
  border-color: #ffc107;
  box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.12);
}

.uc-notify__email-input--code {
  letter-spacing: 0.18em;
  text-align: center;
  font-size: 1.1rem;
  font-weight: 700;
}

.uc-notify__email-button {
  width: 100%;
  min-height: 46px;
  border: 0;
  border-radius: 8px;
  background: #ffc107;
  color: #20242c;
  cursor: pointer;
  font: inherit;
  font-weight: 700;
  transition: background 0.2s ease, transform 0.2s ease;
}

.uc-notify__email-button:hover:not(:disabled) {
  background: #ffcf32;
  transform: translateY(-1px);
}

.uc-notify__email-button:disabled,
.uc-notify__email-change:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.uc-notify__email-change {
  align-self: center;
  border: 0;
  background: transparent;
  color: #ffc107;
  cursor: pointer;
  font: inherit;
  font-size: 0.88rem;
  padding: 4px 8px;
}

.uc-notify__email-change:hover:not(:disabled) {
  text-decoration: underline;
}
</style>