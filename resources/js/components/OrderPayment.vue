<!-- resources/js/components/OrderPayment.vue -->
<template>
  <div>
    <div v-if="paymentLink" class="mt-4">
      <button @click="goToPayment" class="btn btn-success">Перейти к оплате</button>
    </div>

    <!-- Попап после оплаты -->
    <div v-if="paid" class="alert alert-success mt-4">
      Спасибо за оплату! Ваш заказ №{{ order.id }} оплачен.
      <div class="mt-3">
        <button @click="openJivoChat" class="btn btn-primary">Открыть чат с поддержкой</button>
      </div>
    </div>

    <!-- Кнопка "Оформить заказ" (старая) — скрываем после оплаты -->
    <button v-if="!paid" onclick="openDialog();" class="btn btn-primary mt-3">
      Оформить заказ
    </button>
  </div>
</template>

<script>
export default {
  props: ['order', 'product'],
  data() {
    return {
      paymentLink: null,
      paid: this.getUrlParam('paid') === '1',
    };
  },
  mounted() {
    if (!this.paid) {
      this.createPaymentLink();
    } else {
      this.openJivoChat();
    }
  },
  methods: {
    async createPaymentLink() {
      try {
        const res = await axios.post('/payment/create', {
          order_id: this.order.id,
        });
        this.paymentLink = res.data.link;
      } catch (e) {
        alert('Ошибка создания платежа');
      }
    },
    goToPayment() {
      window.location.href = this.paymentLink;
    },
    openJivoChat() {
      if (window.jivo_api) {
        jivo_api.open();
        jivo_api.sendMessage(
          `Заказ №${this.order.id}, UID: ${this.order.uid}, ${this.product.name}, ${this.product.price} ₽ — оплачен!`
        );
      }
    },
    getUrlParam(name) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }
  }
};
</script>