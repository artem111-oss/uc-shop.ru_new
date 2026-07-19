<template>
  <section class="uc-card">
    <h2 class="uc-card__title">Игровые аккаунты</h2>

    <p v-if="loading" class="uc-account__loading">Загрузка…</p>

    <ul v-else-if="accounts.length > 0" class="uc-pubg__list">
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

    <p v-else class="uc-account__empty">У вас пока нет привязанных PUBG ID.</p>

    <form class="uc-pubg__form" @submit.prevent="handleAdd">
      <input
        v-model="newId"
        type="text"
        inputmode="numeric"
        placeholder="PUBG ID (например 512345678)"
        required
      >
      <input
        v-model="newNickname"
        type="text"
        placeholder="Ник (необязательно)"
      >
      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Добавляем…' : 'Добавить PUBG ID' }}
      </button>
    </form>

    <p v-if="errorMessage" class="uc-account__error">{{ errorMessage }}</p>
  </section>
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
.uc-pubg__list {
  list-style: none;
  padding: 0;
  margin: 0 0 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.uc-pubg__item {
  background: #262c36;
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
  color: #e6e9ee;
  letter-spacing: 0.3px;
}

.uc-pubg__badge {
  background: #ffc107;
  color: #14161a;
  font-size: 0.68rem;
  padding: 3px 9px;
  border-radius: 6px;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.uc-pubg__nickname {
  color: #8a94a3;
  font-size: 0.82rem;
  margin-top: 4px;
}

.uc-pubg__actions {
  display: flex;
  gap: 18px;
  margin-top: 10px;
}

.uc-pubg__link {
  background: none;
  border: none;
  color: #ffc107;
  font-size: 0.82rem;
  cursor: pointer;
  padding: 0;
  font-weight: 500;
  transition: opacity 0.15s ease;
}

.uc-pubg__link:hover {
  opacity: 0.75;
}

.uc-pubg__link--danger {
  color: #ff6b6b;
}

.uc-pubg__form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
}

.uc-pubg__form input {
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #334056;
  background: #14161a;
  color: #fff;
  font-size: 0.95rem;
}

.uc-pubg__form input::placeholder {
  color: #5c6570;
}

.uc-pubg__form input:focus {
  outline: none;
  border-color: #ffc107;
}

.uc-pubg__form button {
  background: #ffc107;
  color: #14161a;
  border: none;
  border-radius: 8px;
  padding: 12px;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: filter 0.15s ease;
}

.uc-pubg__form button:hover {
  filter: brightness(1.08);
}

.uc-pubg__form button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>