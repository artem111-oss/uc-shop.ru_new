import './bootstrap';
import {createApp} from 'vue'

import App from './components/PaymentGame.vue'

const paymentGameEl = document.querySelector('#payment-game');
if (paymentGameEl) {
  createApp(App).mount("#payment-game");
}

var offcanvasElementList = [].slice.call(document.querySelectorAll('.offcanvas'))
var offcanvasList = offcanvasElementList.map(function (offcanvasEl) {
  return new bootstrap.Offcanvas(offcanvasEl)
})
