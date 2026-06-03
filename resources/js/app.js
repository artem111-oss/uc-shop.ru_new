import './bootstrap'
import '../css/app.css'

const product = ['60 UC',
  '325 UC',
  '660 UC',
  '1800 UC',
  '3850 UC',
  '8100 UC',]


import {createApp} from 'vue'

import App from './components/payment-game.vue'

createApp(App).mount("#payment-game")
