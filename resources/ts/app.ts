import './bootstrap';
import {createApp} from 'vue'

import App from './components/PaymentGame.vue'
import AccountPage from './components/AccountPage.vue'

const paymentGameEl = document.querySelector('#payment-game');
if (paymentGameEl) {
  createApp(App).mount("#payment-game");
}

const accountPageEl = document.querySelector('#account-page');
if (accountPageEl) {
  createApp(AccountPage).mount("#account-page");
}

var offcanvasElementList = [].slice.call(document.querySelectorAll('.offcanvas'))
var offcanvasList = offcanvasElementList.map(function (offcanvasEl) {
  return new bootstrap.Offcanvas(offcanvasEl)
})