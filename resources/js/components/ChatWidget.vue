<template>
  <Transition name="chat-widget">
    <div v-if="isOpen" class="chat-widget">
      <div class="chat-widget__header">
        <div class="chat-widget__header-info">
          <span class="chat-widget__status" :class="{ 'chat-widget__status--online': isOnline }"></span>
          <div>
            <h3 class="chat-widget__title">Поддержка</h3>
            <p class="chat-widget__subtitle">{{ isOnline ? 'Онлайн' : 'Оффлайн' }}</p>
          </div>
        </div>
        <button class="chat-widget__close" @click="close" aria-label="Закрыть чат">✕</button>
      </div>

      <div class="chat-widget__messages" ref="messagesContainer">
        <div v-for="(message, index) in messages" :key="index" class="chat-message" :class="`chat-message--${message.type}`">
          <div class="chat-message__content">{{ message.text }}</div>
          <div class="chat-message__time">{{ formatTime(message.timestamp) }}</div>
        </div>
        <div v-if="isTyping" class="chat-typing">
          <span></span><span></span><span></span>
        </div>
      </div>

      <form class="chat-widget__input-form" @submit.prevent="sendMessage">
        <input
          v-model="inputMessage"
          type="text"
          class="chat-widget__input"
          placeholder="Напишите сообщение..."
          :disabled="!isOnline"
          maxlength="500"
        />
        <button type="submit" class="chat-widget__send" :disabled="!inputMessage.trim() || !isOnline" aria-label="Отправить">
          ➤
        </button>
      </form>
    </div>
  </Transition>

  <button class="chat-widget__trigger" @click="toggle" aria-label="Открыть чат поддержки">
    <span v-if="unreadCount > 0" class="chat-widget__badge">{{ unreadCount }}</span>
    💬
  </button>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, type Ref } from 'vue'

interface Message {
  text: string
  type: 'user' | 'support'
  timestamp: Date
}

const isOpen: Ref<boolean> = ref(false)
const isOnline: Ref<boolean> = ref(true)
const isTyping: Ref<boolean> = ref(false)
const inputMessage: Ref<string> = ref('')
const unreadCount: Ref<number> = ref(0)
const messages: Ref<Message[]> = ref([
  {
    text: 'Здравствуйте! Чем могу помочь?',
    type: 'support',
    timestamp: new Date(),
  },
])
const messagesContainer: Ref<HTMLElement | null> = ref(null)

const toggle = (): void => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    unreadCount.value = 0
    nextTick(() => scrollToBottom())
  }
}

const close = (): void => {
  isOpen.value = false
}

const sendMessage = async (): Promise<void> => {
  const text = inputMessage.value.trim()
  if (!text || !isOnline.value) return

  messages.value.push({
    text,
    type: 'user',
    timestamp: new Date(),
  })

  inputMessage.value = ''
  scrollToBottom()

  isTyping.value = true
  await simulateResponse()
  isTyping.value = false
}

const simulateResponse = async (): Promise<void> => {
  await new Promise(resolve => setTimeout(resolve, 1500))

  const responses = [
    'Спасибо за сообщение! Скоро с вами свяжется оператор.',
    'Проверяю информацию, один момент...',
    'UC обычно приходят в течение 5 минут после оплаты.',
    'Если у вас возникли проблемы, напишите свой Order ID.',
  ]

  messages.value.push({
    text: responses[Math.floor(Math.random() * responses.length)],
    type: 'support',
    timestamp: new Date(),
  })

  if (!isOpen.value) {
    unreadCount.value++
  }

  scrollToBottom()
}

const scrollToBottom = (): void => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const formatTime = (date: Date): string => {
  return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  setTimeout(() => {
    isOnline.value = true
  }, 1000)
})
</script>

<style scoped lang="scss">
$primary: #ffa500;
$dark: #1a1d23;
$gray: #2a2e35;
$light: #ffffff;
$border: rgba(255, 255, 255, 0.1);

.chat-widget {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 360px;
  max-height: 500px;
  background: $dark;
  border: 1px solid $border;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  overflow: hidden;

  &__header {
    padding: 1rem;
    background: linear-gradient(135deg, rgba(255, 165, 0, 0.1), rgba(255, 165, 0, 0.05));
    border-bottom: 1px solid $border;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  &__header-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  &__status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #666;
    transition: background 0.3s;

    &--online {
      background: #4caf50;
      box-shadow: 0 0 8px rgba(76, 175, 80, 0.6);
    }
  }

  &__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: $light;
  }

  &__subtitle {
    margin: 0;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
  }

  &__close {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s;

    &:hover {
      background: rgba(255, 255, 255, 0.1);
      color: $light;
    }
  }

  &__messages {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: $gray;

    &::-webkit-scrollbar {
      width: 6px;
    }

    &::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 3px;
    }
  }

  &__input-form {
    padding: 1rem;
    background: $dark;
    border-top: 1px solid $border;
    display: flex;
    gap: 0.5rem;
  }

  &__input {
    flex: 1;
    padding: 0.75rem;
    background: $gray;
    border: 1px solid $border;
    border-radius: 8px;
    color: $light;
    font-size: 0.9rem;
    outline: none;
    transition: border 0.3s;

    &:focus {
      border-color: $primary;
    }

    &::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  &__send {
    padding: 0.75rem 1rem;
    background: $primary;
    border: none;
    border-radius: 8px;
    color: $dark;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover:not(:disabled) {
      background: darken($primary, 10%);
      transform: translateY(-2px);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  &__trigger {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: $primary;
    border: none;
    border-radius: 50%;
    font-size: 1.75rem;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(255, 165, 0, 0.4);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;

    &:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(255, 165, 0, 0.6);
    }
  }

  &__badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f44336;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
  }
}

.chat-message {
  max-width: 75%;
  padding: 0.75rem;
  border-radius: 12px;
  animation: slideIn 0.3s ease-out;

  &--user {
    align-self: flex-end;
    background: $primary;
    color: $dark;
  }

  &--support {
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.05);
    color: $light;
  }

  &__content {
    font-size: 0.9rem;
    line-height: 1.4;
    word-wrap: break-word;
  }

  &__time {
    font-size: 0.7rem;
    opacity: 0.6;
    margin-top: 0.25rem;
  }
}

.chat-typing {
  display: flex;
  gap: 0.25rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  width: fit-content;

  span {
    width: 8px;
    height: 8px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    animation: typing 1.4s infinite;

    &:nth-child(2) {
      animation-delay: 0.2s;
    }

    &:nth-child(3) {
      animation-delay: 0.4s;
    }
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-10px);
  }
}

.chat-widget-enter-active,
.chat-widget-leave-active {
  transition: all 0.3s ease;
}

.chat-widget-enter-from,
.chat-widget-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

@media (max-width: 480px) {
  .chat-widget {
    width: calc(100vw - 40px);
    right: 20px;
    bottom: 80px;
  }
}
</style>
