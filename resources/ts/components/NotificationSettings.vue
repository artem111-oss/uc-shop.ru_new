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
      <p class="uc-account__hint">Чтобы получать уведомления в Telegram, привяжите бота.</p>
      <button type="button" class="uc-account__button" :disabled="linking" @click="handleLink">
        {{ linking ? 'Открываем…' : 'Привязать @uctyt_bot' }}
      </button>
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
  createTelegramLinkToken,
  unlinkTelegram,
} from '../services/auth';

const prefs = ref({ notify_email: true, notify_telegram: false });
const links = ref<TelegramLinkStatus[]>([]);
const linking = ref(false);
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

async function handleLink(): Promise<void> {
  errorMessage.value = '';
  linking.value = true;

  try {
    const response = await createTelegramLinkToken('uctyt');
    window.open(response.deep_link, '_blank');
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось создать ссылку.';
  } finally {
    linking.value = false;
  }
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

onMounted(load);
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