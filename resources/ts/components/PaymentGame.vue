<template>
  <form class="payment-game justify-content-evenly p-4 rounded">
    <div class="payment-game__form text-start">
      <h1>Пополнение UC PUBG Mobile</h1>
      <div class="form-group mb-3">
        <div class="d-flex play-id position-relative">
          <label for="play-id">Игровой ID</label>
          <span class="what-uid">?</span>
          <div class="tooltip-uid position-absolute">
            <img src="/images/uid-info.png">
          </div>
        </div>
        <input :class="[errors.uid ? 'is-invalid' : 'is-valid']" type="text" id="play-id" class="form-control mb-2"
               v-bind="uid" placeholder="Ваш игровой ID"
               @keypress="numeric"
               inputmode="numeric"
               autocomplete="off">
        <div v-if="errors.uid" class="text-danger">
          {{ errors.uid }}
        </div>
      </div>

      <div :class="{'error': (!Object.keys(errors).length)}" class="form-group mt-3 ">
        <label for="product">Количество UC:</label>
        <!--              <select v-model="selectedProduct" type="text" class="form-control" id="product" aria-describedby="product"
                      >
                        <option v-for="(product, i) in productsList" :key="i" :value="product"
                        >
                          {{ product.name }} - {{ product.price }} ₽
                        </option>
                      </select>-->

        <div class="product-list">
          <div v-for="(product, i) in productsList" :key="product.id" class="product-item"
               :class="[(product === selectedProduct) ? 'is-selected' : '']"
               @click="selectProduct(product.id)">
            <span class="product-item__name">{{ product.name }}</span>
            <span class="product-item__price">{{ product.price }} ₽</span>
          </div>
        </div>
      </div>
    </div>
    <div class="payment-game__checkout text-start p-5" v-if="activeStep === 'view'">
      <div class="checkout"><h2 class="">Проверьте данные</h2>
        <div class="">
          <!--                <figure class=""><img src="/images/pubg_mobile.png" alt=""
                                                class=""></figure>-->
          <table class="table">
            <tbody>
            <tr class="form-group">
              <th class="">Игровой ID</th>
              <td class="" v-if="uid.value">{{ uid.value }}</td>
              <td class="error" v-else>Не заполнено</td>
            </tr>
            <tr class="">
              <th class="">Сумма</th>
              <td class="">
                <div class="">{{ orderPrice }} ₽

                </div>
              </td>
            </tr>
            </tbody>
          </table>

          <!--                <div class="checkout__payments">
                            <div class="form-group mt-3">
                              <div class="mb-2">Выберите способ оплаты</div>

                              <div v-for="payment in payments" :key="payment.id"
                                   class="form-check-inline checkout__payment ">
                                <label class="form-check-label" :class="{'checked': paymentSelect === payment.id}" :for="'payment-' + payment.id">
                                  <input type="radio"
                                         :id="'payment-' + payment.id"
                                         :name="'payment-' + payment.id"
                                         v-bind:value="payment.id" v-model="paymentSelect" class="form-check-input">
                                  <figure class=""><img :src="payment.img" alt="" class="">
                                  </figure>

                                </label>
                              </div>
                            </div>
                          </div>-->

          <div class="form-group mt-3" v-show="false">
            <label for="InputEmail">Укажите почту для получения чека</label>
            <input v-bind="email" type="text"
                   class="form-control"
                   :class="{'is-invalid':!validation('email')}"
                   id="InputEmail"
                   placeholder="Ваш Email">{{ validation('email') }}
            <div v-if="!validation('email')" class="text-danger">
              {{ errors.email }}
            </div>
          </div>

          <button type="button" @click="onSubmit" class="btn-primary mt-3" :disabled="errors.uid"
                  :class="{'btn-primary':!!errors.length}">
<!--            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>-->
            Продолжить
          </button>
          <p class="terms ">
            <span class="small">Продолжая, я принимаю условия
              <a href="/agrement"
                 class="" target="_blank">Пользовательского Соглашения</a> и подтверждаю ознакомление с
    <a href="/faq" class="">FAQ</a></span>
          </p>
        </div>
      </div>
    </div>
  </form>
</template>

<script lang="ts" setup>

import {ref, computed, onMounted} from 'vue'
import {useForm,} from 'vee-validate';
import * as Yup from 'yup';
import {localize} from '@vee-validate/i18n';

const prods = document.getElementById('payment-game').getAttribute('products')
//const products = defineProps(prods)

/*const showTooltip = (e)=>{
  return (e)
}*/

Yup.setLocale({
  // use constant translation keys for messages without values
  mixed: {
    required: 'Поле обязательно для заполнения',
  },
  // use functions to generate an error object that includes the value from the schema
  number: {
    min: ({min}) => ({key: 'field_too_short', values: {min}}),
    max: ({max}) => ({key: 'field_too_big', values: {max}})
  },
});

let steps = [
  'view',
  'order',
  'payment'
]

let activeStep = ref('view')

const schema = Yup.object({
  //email: Yup.string().required().email(),
  uid: Yup.string().required().min(9, 'Игровой ID введен не верно').max(13, 'Игровой ID введен не верно'),
});

const {values, errors, defineInputBinds, handleSubmit} = useForm({
  validationSchema: schema,
});

const uid = defineInputBinds('uid');
const email = defineInputBinds('email');

export interface Product {
  id: number
  name: string
  price?: number
  type_id?: number
}

export interface Payment {
  id: number
  name: string,
  img: string
}

const flag = ref(true)
const order = ref({})

const productsList = ref<Product[]>([
  {id: 1, name: '60 UC', price: 86},
  {id: 2, name: '325 UC', price: 410},
  {id: 3, name: '600 UC', price: 830},
  {id: 4, name: '1800 UC', price: 2000},
  {id: 5, name: '3850 UC', price: 3900},
  {id: 6, name: '8100 UC', price: 7890}
])
const products = productsList.value.reduce((acc, it) => ({...acc, [it.id]: it}), {})

const payments = ref<Payment[]>([
  {id: 1, name: 'СБП', img: '/images/sbp.svg'},
  {id: 2, name: 'Юмани', img: '/images/yoomoney.svg'},
  //{id: 3, name: 'Оплата по ссылке', img: '/images/link.svg'}
])


const selectedPayment = ref<Payment>(payments.value[1])
const selectedProduct = ref<Product>(productsList.value[0])

//
const activeSubmitButton = computed(() => {
  return (Object.keys(errors).length)
})
const orderPrice = computed(() => {
  return {...selectedProduct.value}.price.toLocaleString()
})


// Выбор продукта
const selectProduct = (id) => {
  selectedProduct.value = products[id]
}

const onSubmit = handleSubmit((values, i) => {
  console.log(i)
  console.log(JSON.stringify(values, null, 2));
  go()
});

const go = async () => {
  const data = {
    'client_id': 'client_id',
    'product_id': selectedProduct.value.id,
    'uid': uid.value.value,
  }

  if (true) {
    const createOrder = async function () {
      return fetch('/order/add', {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        mode: "cors", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": "application/json",
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').attributes.content.nodeValue
          // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: "follow", // manual, *follow, error
        referrerPolicy: "no-referrer", // no-referrer, *client
        body: JSON.stringify(data)
      }).then((response) => {
        return response.json();
      })
    }

    const order = await createOrder()
    console.log(order)
    location.href = '/order/' + order.id + '/' + uid.value.value

  }
}

const submit = function (selectedProduct) {
  // подключение оплаты
  console.log(selectedProduct.value)
  // отправка промокода через бэк
}

// lifecycle hooks
onMounted(() => {
  console.log(`The initial count is ${productsList.value[0].price}.`)


})

// helpers
const numeric = (event) => {
  let keyCode = event.keyCode;
  if (keyCode
      < 48 || keyCode > 57) {
    event.preventDefault();
  }
}
const validation = (field) => (errors[field])

/*

ИД должен содержать 13 цифр

Кнопка должна активироваться при валидации

Методы
  Запуск оплаты

* */

</script>


<style lang="scss" scoped>
.terms {
  margin-top: 1rem
}

.product {
  &-list {
    display: flex;
    flex-wrap: wrap;
    @media screen and (max-width: 768px) {
      justify-content: center;
    }
  }

  &-item {
    padding: 10px 5px;
    margin-right: 10px;
    margin-bottom: 10px;
    min-width: 100px;
    cursor: pointer;
    color: var(--uc-dark);
    background-color: var(--uc-white);
    text-align: center;
    border-radius: 4px;

    &.is-selected {
      background: var(--uc-primary);
      /*align-items: center;
            justify-content: center;*/
      .product-item__name {
        color: var(--uc-black);
      }

      .product-item__price {
        display: block;
      }
    }

    &.active {
      color: var(--uc-dark);
      background-color: var(--uc-primary);
    }

    &__name {
      display: block;
      color: var(--uc-primary);
      font-weight: 700;
      font-size: 16px;
    }

    &__price {
      display: block;
      white-space: nowrap;
    }
  }
}

.payment-game {
  display: flex;
  background: #334056f7;
  border-radius: 6px;
  width: 100%;
  min-height: 400px;
  margin: auto;
  padding: 20px;

  @media screen and (max-width: 768px) {
    flex-wrap: wrap;
  }

  &__form {
    padding: 20px;
  }

  .play-id{
    .what-uid{
      &:hover{
       ~ .tooltip-uid{
         display: block;
       }
      }
    }
    .tooltip-uid{
      left: 82px;
      bottom: 15px;
      display: none;
      border: 3px solid white;
      border-radius: 6px;
      &:hover{
        display: block;
      }
    }
  }
  #play-id {
    max-width: 200px;
    @media screen and (max-width: 768px) {
      max-width: inherit;
    }
  }

  &__checkout {
    background: #23282e;
    max-width: 400px;
    @media screen and (max-width: 991.98px) {
      max-width: 350px;
    }

    table.table {
      font-size: 16px;

      td:nth-child(2) {
        text-align: right;
      }

      th, td {
        font-size: 16px;
        color: white;
        background: none;
      }
    }

    button {
      background: #edba45;
      padding: 10px 15px;
      border: none;
      border-radius: 6px;

      &:hover {
        background: #ffc237;
      }
    }

  }
}

/*.payment-game-wrap {
  min-height: 75vh;
}*/
</style>

