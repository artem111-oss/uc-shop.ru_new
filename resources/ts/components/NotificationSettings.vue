<template>
  <div class="uc-notify">
    <h2 class="uc-account__subtitle">Уведомления</h2>

    <label class="uc-notify__row">
      <span>Email</span>
      <input
        type="checkbox"
        :checked="prefs.notify_email"
        @change="toggle('notify_email', $event)"
      >
    </label>

    <label class="uc-notify__row">
      <span>Telegram</span>
      <input
        type="checkbox"
        :checked="prefs.notify_telegram"
        :disabled="!hasTelegramLink"
        @change="toggle('notify_telegram', $event)"
      >
    </label>

    <div v-if="!hasTelegramLink" class="uc-notify__link-block">
      <p class="uc-account__hint">Чтобы получать уведомления в Telegram, войдите через бота.</p>
      <div id="telegram-login-widget"></div>
    </div>

    <div v-else class="uc-notify__link-block">
      <p class="uc-account__hint">Привязан: {{ linkedUsername }}</p>
      <button type="button" class="uc-pubg__link uc-pubg__link--danger" @click="handleUnlink">
        Отключить Telegram
      </button>
    </div>

    <p v-if="errorMessage" class="uc-account__error">{{ errorMessage }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
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
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось отключить.';
  }
}

onMounted(async () => {
  await load();
  mountTelegramWidget();
});
</script>

<style scoped>
.uc-notify {
  margin-top: 24px;
}

.uc-notify__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #334056;
  font-size: 0.95rem;
}

.uc-notify__link-block {
  margin-top: 12px;
}
</style>