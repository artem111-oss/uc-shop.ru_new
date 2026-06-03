@extends('app')
@section('title', 'Ваш заказ № ' . $order['id'] . ' создан!')
@section('content')

  <div class="container pt-4">
    <h1>Ваш заказ создан!</h1>
    <div class="content">
      <br>
      <p>Номер вашего UID: <strong>{{$order['uid']}}</strong></p>
      <p>Номер вашего заказа: <strong>№ {{$order['id']}}</strong></p>

      <h4>Состав заказа:</h4>
      <table class="table-dark table">
        <thead>
          <tr>
            <th scope="col">Кол-во</th>
            <th scope="col">Игра</th>
            <th scope="col">Товар</th>
            <th scope="col">Цена</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td scope="row">1</td>
            <td>PUBG Mobile</td>
            <td>{{$product->name}}</td>
            <th>{{$product->price}} ₽</th>
          </tr>
        </tbody>
      </table>
      <br>
      <h2>Оплата заказа</h2>
      <p>Нажмите кнопку, чтобы перейти к оплате.</p>
      <button id="payButton" class="btn btn-primary">Оплатить заказ</button>

      <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Оплата прошла успешно!</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body text-center">
              <p>Ваш заказ № <strong>{{$order['id']}}</strong> оплачен.</p>
              <p>Мы свяжемся с вами в чате для подтверждения UID и выдачи товара.</p>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-success" data-dismiss="modal">Понял, жду в чате</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>




<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Popup] DOM загружен');

    const urlParams = new URLSearchParams(window.location.search);
    const paid = urlParams.get('paid');
    console.log('[Popup] Параметр paid:', paid);

    if (paid === '1') {
        console.log('[Popup] Параметр найден, открываем модалку');
        const modal = document.getElementById('paymentModal');

        if (!modal) {
            console.warn('[Popup] Модалка не найдена в DOM!');
            return;
        }

        modal.classList.add('show');
        console.log('[Popup] Класс .show добавлен, модалка должна появиться');

        setTimeout(() => {
            if (typeof jivo_api !== 'undefined') {
                console.log('[Popup] Jivo найден, отправляем сообщение');
                jivo_api.open();
                jivo_api.sendMessage(`Заказ №: 29217, Игровой ID: 551179601, 60 UC, 85 ₽`);
            } else {
                console.warn('[Popup] Jivo не найден, возможно ещё не загружен');
            }
        }, 800);
    } else {
        console.log('[Popup] Параметр paid отсутствует или не равен 1');
    }

    // События для закрытия
    const closeModal = document.getElementById('closeModal');
    const okModal = document.getElementById('okModal');
    const modalOverlay = document.getElementById('paymentModal');

    if (closeModal && okModal && modalOverlay) {
        console.log('[Popup] Навешиваем обработчики закрытия');
        closeModal.addEventListener('click', () => {
            modalOverlay.classList.remove('show');
            console.log('[Popup] Закрыто крестиком');
        });

        okModal.addEventListener('click', () => {
            modalOverlay.classList.remove('show');
            console.log('[Popup] Закрыто кнопкой ОК');
        });

        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.classList.remove('show');
                console.log('[Popup] Закрыто кликом вне окна');
            }
        });
    } else {
        console.warn('[Popup] Не удалось найти один из элементов управления');
    }
});
</script>

<script>
document.getElementById('payButton').addEventListener('click', async function () {
    const orderId = '{{ $order->id }}';

    try {
        const R = await async function() {
            return fetch("/order/payment", {
                method: "POST",
                mode: "cors",
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": '{{ csrf_token() }}'
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body: JSON.stringify({ order_id: orderId })
            }).then(U => U.json());
        }();

        console.log(R);

        if (R.link) {
            location.href = R.link;
        } else {
            alert(R.error || 'Ошибка при создании платежа');
        }

    } catch (e) {
        console.error(e);
        alert('Ошибка соединения');
    }
});

</script>
@endsection