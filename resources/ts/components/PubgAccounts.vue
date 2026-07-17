<template>
  <div class="uc-pubg">
    <h2 class="uc-account__subtitle">Игровые аккаунты</h2>

    <p v-if="loading" class="uc-account__loading">Загрузка…</p>

    <ul v-else class="uc-pubg__list">
      <li v-for="account in accounts" :key="account.id" class="uc-pubg__item">
        <div class="uc-pubg__row">
          <span class="uc-pubg__id">{{ account.pubg_id }}</span>
          <span v-if="account.is_primary" class="uc-pubg__badge">Основной</span>
        </div>
        <div v-if="account.nickname" class="uc-pubg__nickname">{{ account.nickname }}</div>
        <div class="uc-pubg__actions">
          <button
            v-if="!account.is_primary"
            type="button"
            class="uc-pubg__link"
            @click="makePrimary(account.id)"
          >
            Сделать основным
          </button>
          <button type="button" class="uc-pubg__link uc-pubg__link--danger" @click="remove(account.id)">
            Удалить
          </button>
        </div>
      </li>
    </ul>

    <form class="uc-pubg__form" @submit.prevent="handleAdd">
      <input
        v-model="newId"
        type="text"
        inputmode="numeric"
        placeholder="PUBG ID (например 512345678)"
        class="uc-account__input"
        required
      >
      <input
        v-model="newNickname"
        type="text"
        placeholder="Ник (необязательно)"
        class="uc-account__input"
      >
      <button type="submit" class="uc-account__button" :disabled="submitting">
        {{ submitting ? 'Добавляем…' : 'Добавить PUBG ID' }}
      </button>
    </form>

    <p v-if="errorMessage" class="uc-account__error">{{ errorMessage }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { PubgAccount } from '../services/auth';
import {
  fetchPubgAccounts,
  createPubgAccount,
  updatePubgAccount,
  deletePubgAccount,
} from '../services/auth';

const accounts = ref<PubgAccount[]>([]);
const loading = ref(true);
const newId = ref('');
const newNickname = ref('');
const submitting = ref(false);
const errorMessage = ref('');

async function load(): Promise<void> {
  loading.value = true;
  try {
    accounts.value = await fetchPubgAccounts();
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Ошибка загрузки.';
  } finally {
    loading.value = false;
  }
}

async function handleAdd(): Promise<void> {
  errorMessage.value = '';
  submitting.value = true;

  try {
    await createPubgAccount(newId.value.trim(), newNickname.value.trim());
    newId.value = '';
    newNickname.value = '';
    await load();
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось добавить.';
  } finally {
    submitting.value = false;
  }
}

async function makePrimary(id: number): Promise<void> {
  errorMessage.value = '';
  try {
    await updatePubgAccount(id, { is_primary: true });
    await load();
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось обновить.';
  }
}

async function remove(id: number): Promise<void> {
  errorMessage.value = '';
  try {
    await deletePubgAccount(id);
    await load();
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Не удалось удалить.';
  }
}

onMounted(load);
</script>

<style scoped>
.uc-pubg {
  margin-top: 24px;
}

.uc-pubg__list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 16px;
}

.uc-pubg__item {
  background: #1e2227;
  border: 1px solid #334056;
  border-radius: 10px;
  padding: 12px 14px;
}

.uc-pubg__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.uc-pubg__id {
  font-weight: 600;
  font-size: 1rem;
}

.uc-pubg__badge {
  background: #ffc107;
  color: #000;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.uc-pubg__nickname {
  color: #9aa5b1;
  font-size: 0.85rem;
  margin-top: 2px;
}

.uc-pubg__actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.uc-pubg__link {
  background: none;
  border: none;
  color: #ffc107;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
}

.uc-pubg__link--danger {
  color: #ff6b6b;
}

.uc-pubg__form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
</style>