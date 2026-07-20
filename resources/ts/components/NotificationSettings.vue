<template>
  <section class="uc-card">
    <h2 class="uc-card__title">Уведомления</h2>

    <div class="uc-notify__card">
      <label class="uc-notify__row">
        <span class="uc-notify__label">Email</span>
        <span class="uc-toggle">
          <input
            type="checkbox"
            class="uc-toggle__input"
            :checked="prefs.notify_email"
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
import type { TelegramLinkStatus } from '../services/auth';
import {
  fetchNotificationPrefs,
  updateNotificationPrefs,
  fetchTelegramStatus,
  linkTelegramWidget,
  unlinkTelegram,
} from '../services/auth';

declare global {
  interface Window {
    onTelegramAuth?: (user: Record<string, any>) => void;
  }
}

const prefs = ref({ notify_email: true, notify_telegram: false });
const links = ref<TelegramLinkStatus[]>([]);
const errorMessage = ref('');

const hasTelegramLink = computed(() => links.value.length > 0);
const linkedUsername = computed(() => links.value[0]?.telegram_username || 'Telegram');

async function load(): Promise<void> {
  try {
    prefs.value = await fetchNotificationPrefs();
    links.value = await fetchTelegramStatus();
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
</style>