<template>
    <form class="payment-game__form row d-flex p-4 rounded">
      <h1>Купить Промокод PUBG Mobile</h1>
      <div class="col-md-7">
        <div class="form-group">
          <label for="play-id">Игровой ID</label>
          <input type="text" id="play-id" class="form-control"
                 placeholder="XXXXXXXXXXX"
                 v-model.trim="playId"
                 @input="isNumb"
                 autocomplete="off">
        </div><!--@keypress="this.isNumber($event)"-->
        <div class="form-group mt-3">
          <label for="product">Товар</label>
          <select v-model="productSelect" type="text" class="form-control" id="product" aria-describedby="product"
          >
            <option v-for="(product, i) in productList" :key="i" :value="product"
            >
              {{ product.name }}
            </option>
          </select>
        </div>
        <div class="form-group mt-3">
          <label for="InputEmail">Email</label>
          <input type="text" class="form-control" id="InputEmail"
                 placeholder="Ваш Email">
        </div>
        <p>На него отправим промокод</p>
<!--        <div class="form-group mt-3">
          <ProductList :productList="productList" :productSelect="productSelect"/>
        </div>-->
      </div>
      <div class="payment-game__checkout col-md-5">
        <div class="checkout"><h2 class="">Проверьте данные</h2>
          <div class="">
            <figure class=""><img src="/images/pubg_mobile.png" alt=""
                                  class=""></figure>

            <table class="table">
              <tbody>
              <tr class="form-group">
                <th class="">Игровой ID</th>
                <td class="">{{ playId }}</td>
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
            <button type="button" @click="go" class="btn-primary" :disabled="!activeSubmitButton">Продолжить</button>
            <p>
            <span class="terms small">Продолжая, я принимаю условия
              <a href="/agrement"
                 class="">Пользовательского Соглашения</a> и подтверждаю ознакомление с
    <a href="/faq" class="">FAQ</a></span>
            </p>
          </div>
        </div>
      </div>
    </form>
</template>
<script lang="ts" setup>
import {ref, computed, onMounted} from 'vue'

export interface Product {
  id: number
  name: string
  price?: number
  type_id?: number
}

const playId = ref<string>('')


const flag = ref(false)

const productList = ref<Product[]>([
  {id: 0, name: '60 UC', price: 90},
  {id: 1, name: '325 UC', price: 450},
  {id: 1, name: '600 UC', price: 900},
  {id: 2, name: '1800 UC', price: 2200},
  {id: 3, name: '3850 UC', price: 4090},
  {id: 3, name: '8100 UC', price: 7990}
])

const productSelect = ref(productList.value[0])
// id
const activeSubmitButton = computed(() => {
  return ((flag.value) && productSelect.value)
})


const orderPrice = computed(() => {
  return productSelect.value['price']?.toLocaleString()
})

const isNumb = (e) => {
  if(!e.data.replace(/[^0-9]/g, "")){
    return false;
  }
}

const go = () => {
  if (flag.value) {
    const createOrder = async function (){
      let data = {product_id: 0, price: 123, client_id: '123'}
      fetch('/order/add',{
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

      }).then(response => response.json())
    }
  }
}



/*const getYearsList = () => {
  const startYear = 1990;
  const endYear = new Date().getFullYear();
  for (let i = endYear; i >= startYear; i--) {
    yearsList.value = [...yearsList.value, i]
  }
}*/

const submit = function (productSelect) {
  // подключение оплаты
  console.log(productSelect.value)
  // отправка промокода через бэк
}

// lifecycle hooks
onMounted(() => {
  console.log(`The initial count is ${productList.value[0].price}.`)
})

/*

ИД должен содержать 13 цифр

Кнопка должна активироваться при валидации

Методы
  Запуск оплаты



* */

</script>


