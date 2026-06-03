@extends('app')
@section('title', 'Вопросы и ответы')
@section('content')
  <div class="container pt-4">
    <h1>Вопросы и ответы</h1>
    <div class="content">
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Как работает
          пополнение?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>Наш сервис даёт возможность пополнить ваш кошелёк Steam в рублях, а также другие
              сервисы и игры. Помимо пополнения баланса напрямую по игровому логину (например: Steam), вы также можете
              приобрести себе коды на получение игровой валюты или определенных товаров в различных играх. Описание
              товаров, время зачисления и другие детали отражены на странице товаров, а также на соответствующих
              страницах FAQ. При совершении оплаты вы в обязательном порядке принимаете условия <a
                  href="/info/agreement">пользовательского соглашения</a> сайта, а также
              подтверждаете факт ознакомления с FAQ.</p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Что вводить в
          поле логина Steam?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>В поле логина нужно вводить ваш логин Steam, который вы используете при авторизации.
              Его также
              можно найти в клиенте Steam в правом верхнем углу или на странице «Об аккаунте». Если вы укажете неверный
              логин – мы не сможем
              вернуть вам средства или перевести на правильный аккаунт, будьте внимательны!</p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Что такое Steam
          GOLD?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>Мы постоянно стараемся снизить комиссию для комфортных и менее затратных платежей для
              наших
              клиентов. <a href="">Steam GOLD</a> предоставляют меньшую
              комиссию но на фиксированные цены или акционные товары. Например: Prime статус в CS:GO или Dota+ в Dota 2.
              После совершения транзакции вы получите средства на ваш аккаунт Steam для дальнейшей покупки нужного вам
              товара.</p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name" id="acc">Какие
          аккаунты Steam можно пополнить?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>Пополнять основным способом можно только аккаунты из СНГ региона (Россия, Казахстан,
              Узбекистан, Украина, Кыргызстан и др.)! Обязательно сохраняйте ID операции, который вы можете найти на
              странице после оплаты! Мы также не можем пополнить баланс аккаунтов, на которые выданы блокировки (Красная
              Табличка)!</p>
            <div class="message message--type-error "><span class="icon "><svg xmlns="http://www.w3.org/2000/svg"
                                                                               width="20px" height="20px"
                                                                               viewBox="0 0 24 24" fill="none"
                                                                               stroke="currentColor" stroke-width="2"
                                                                               stroke-linecap="round"
                                                                               stroke-linejoin="round"
                                                                               class="feather feather-alert-circle "><circle
                      cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16"
                                                                                                        x2="12.01"
                                                                                                        y2="16"></line></svg></span>
              <div class="info ">
                <div class="content">Если вы пополняете аккаунт на котором до этого не было финансовых операций, его
                  регион может измениться на Узбекистан, а валюта на CIS $ (или другие). Чтобы этого избежать и
                  сохранить российский регион у аккаунта перед пополнением добавьте в библиотеку Steam любую бесплатную
                  игру, а лучше несколько (Например: <a
                      href="https://store.steampowered.com/app/578080/PUBG_BATTLEGROUNDS/">PUBG</a>). Всё это нужно
                  делать под русским IP адресом!
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name" id="krim">Как
          пополнить аккаунт из Крыма / Луганска / Донецка?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>В связи с тем, что Steam проверяет аккаунт на наличие активной сессии из Крыма,
              Луганска и
              Донецка пополнение данных аккаунтов можно совершить выполнив несколько действий:</p>
            <ol>
              <li>Завершить все активные сессии Steam из данных регионов (Выйти из аккаунта на ПК и в браузере)</li>
              <li>Перевести мобильное устройство, где установлен аутентификатор Steam в авиарежим (если вы
                находитесь в одном из данных регионов)
              </li>
              <li>Используя VPN с российским IP (желательно Москва или Санкт-Петербург) авторизоваться в
                браузере Steam (мобильный аутентификатор даже в авиарежиме сможет дать код для входа)
              </li>
              <li>Подождать 5 минут после успешной авторизации и попробовать произвести пополнение используя наш
                сайт. <a href="">Steam GOLD</a> тоже подходит!
              </li>
            </ol>
            <p>Если у вас возникнут вопросы - наши специалисты в службе поддержки, чат которой находится в
              правом нижнем углу, с удовольствием вам помогут с совершением оплаты!</p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Средства не
          дошли</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>Очень редко могут случаться случаи, когда средства не доходят до вашего аккаунта
              Steam. Обычно,
              данные неполадки возникают не на нашей стороне, но мы оперативно готовы помочь вам с данными
              проблемами, для решения которых нужно обратиться в нашу службу поддержки, чат которой находится
              в нижнем правом углу сайта.</p>
            <div class="message message--type-error "><span class="icon "><svg xmlns="http://www.w3.org/2000/svg"
                                                                               width="20px" height="20px"
                                                                               viewBox="0 0 24 24" fill="none"
                                                                               stroke="currentColor" stroke-width="2"
                                                                               stroke-linecap="round"
                                                                               stroke-linejoin="round"
                                                                               class="feather feather-alert-circle "><circle
                      cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16"
                                                                                                        x2="12.01"
                                                                                                        y2="16"></line></svg></span>
              <div class="info ">
                <div class="content">В редких случаях, когда средства не дошли вам на аккаунт или вы получили неверный
                  код - решение
                  ошибки может занять до трёх рабочих дней, но зачастую в течение нескольких часов. Заранее
                  приносим извинения за неудобства! Мы постараемся возместить вам потраченное время бонусами и скидками!
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Лимиты и
          ограничения</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>У нас присутствуют ограничения на минимальные суммы для всех способов зачисления. Если
              вы
              пополняете аккаунт более чем на 500$ за 24 часа, часть средств может сразу не дойти - для
              ускорения процесса обратитесь в чат поддержки в правом нижнем углу.</p>
            <div class="message message--type-error "><span class="icon "><svg xmlns="http://www.w3.org/2000/svg"
                                                                               width="20px" height="20px"
                                                                               viewBox="0 0 24 24" fill="none"
                                                                               stroke="currentColor" stroke-width="2"
                                                                               stroke-linecap="round"
                                                                               stroke-linejoin="round"
                                                                               class="feather feather-alert-circle "><circle
                      cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16"
                                                                                                        x2="12.01"
                                                                                                        y2="16"></line></svg></span>
              <div class="info ">
                <div class="content">ВНИМАНИЕ: ограничение распространяется на все способы пополнения. Если вы где-то
                  уже пополнили
                  ваш Steam на сумму 500$, наше пополнение может не пройти в связи с лимитом, будьте внимательны!
                </div>
              </div>
            </div>
            <p>Вы можете попробовать превысить этот лимит, для этого обратитесь в чат поддержки.</p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Способы
          пополнения</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>На данный момент на сервисе пополнение возможно через платёжные системы LAVA и
              FreeKassa,
              включающие платежи через Qiwi, Банковские карты, Систему Быстрых Платежей, Криптовалюту(Bitcoin,
              USDT), ЮMoney и другие.
              <br>
              Мы работаем над расширением спектра возможных способов, включая мобильные платежи и электронные кошельки.
            </p></div>
        </div>
      </div>
      <div itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"><h2 itemprop="name">Есть
          вопросы?</h2>
        <div itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
          <div itemprop="text"><p>На нашем сайте имеется удобная форма техподдержки, также вы можете подписаться на наш
              телеграм
              канал или паблик ВК, где вы найдете актуальные новости и сможете задать интересующие вас вопросы
              и мы вам ответим, как только сможем.</p></div>
        </div>
      </div>
    </div>
  </div>
@endsection