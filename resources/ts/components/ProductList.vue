<template>
  <div v-for="(product, i) in productList" :key="product.id" class="productList"
  >
    {{ product.name }}
  </div>
</template>
<script lang="ts" setup>
import {ref, computed, onMounted, VNode} from 'vue'

export interface Product {
  id: number
  name: string
  price?: number
  type_id?: number
}

const props = {
  products: {
    required: true,
    type: Array
  }
}
const productList = ref<Product[]>([
  {id: 0, name: '60 UC', price: 90},
  {id: 1, name: '325 UC', price: 450},
  {id: 2, name: '600 UC', price: 900},
  {id: 3, name: '1800 UC', price: 2200},
  {id: 4, name: '3850 UC', price: 4090},
  {id: 5, name: '8100 UC', price: 7990}
])
const productSelect = ref(productList.value[0])
// lifecycle hooks
onMounted(() => {

})

</script>


