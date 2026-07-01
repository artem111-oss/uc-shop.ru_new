<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title') - UC SHOP</title>
<meta name="description" content="@yield('description')">
<meta name="theme-color" content="#1e2227">
<meta name="format-detection" content="telephone=no">

<link rel="canonical" href="{{ url()->current() }}">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="icon" type="image/svg+xml" href="/favicon.svg">

<meta property="og:type" content="website">
<meta property="og:title" content="@yield('title') - UC SHOP">
<meta property="og:description" content="@yield('description')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="UC SHOP">
<meta property="og:locale" content="ru_RU">
<meta property="og:image" content="https://uc-shop.ru/images/og-preview.jpg">
<meta property="og:image:secure_url" content="https://uc-shop.ru/images/og-preview.jpg">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="631">
<meta property="og:image:alt" content="Купить UC PUBG Mobile — 30 секунд">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title') - UC SHOP">
<meta name="twitter:description" content="@yield('description')">
<meta name="twitter:image" content="https://uc-shop.ru/images/og-preview.jpg">
<meta name="twitter:image:alt" content="Купить UC PUBG Mobile — 30 секунд">
{{-- Только если есть реальный X аккаунт --}}
{{-- <meta name="twitter:site" content="@your_real_username"> --}}

<meta name="yandex-verification" content="e632d81a8db0899f">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "UC SHOP",
  "url": "https://uc-shop.ru",
  "logo": "https://uc-shop.ru/images/logo.png",
  "description": "Магазин UC для PUBG Mobile. Быстрая и безопасная покупка внутриигровой валюты.",
  "sameAs": [
    "https://t.me/ucshop",
    "https://t.me/uc_artem"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "Customer Service",
    "url": "https://t.me/ucshop_air",
    "availableLanguage": "Russian"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Как купить UC?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "1. Введите свой Игровой ID в форму выше. 2. Выберите количество UC. 3. Нажмите ОПЛАТИТЬ. 4. Выберите способ оплаты. 5. Готово! UC придут в течение 1 минуты."
      }
    },
    {
      "@type": "Question",
      "name": "Как долго ждать доставки?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Обычно UC приходят за 30-60 секунд после успешной оплаты. В редких случаях может занять до 5 минут. Если ничего не пришло — свяжитесь с поддержкой!"
      }
    },
    {
      "@type": "Question",
      "name": "Это безопасно?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "100% безопасно! Мы используем проверенную платежную систему СБП. Ваши данные защищены SSL-шифрованием. Не требуем пароль от аккаунта — только Игровой ID!"
      }
    },
    {
      "@type": "Question",
      "name": "Что делать если UC не пришли?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "1. Проверьте, что оплата прошла успешно. 2. Перезагрузите игру. 3. Проверьте историю транзакций в профиле. 4. Если проблема осталась — напишите в Telegram @ucshop с номером заказа."
      }
    },
    {
      "@type": "Question",
      "name": "Есть ли скидки для постоянных?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Да! В ближайшее время мы запустим программу лояльности с приятными бонусами для постоянных клиентов. Следите за новостями на сайте и в нашем Telegram-канале!"
      }
    }
  ]
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "UC SHOP",
  "url": "https://uc-shop.ru"
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"></noscript>

<link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
<link rel="preconnect" href="https://code.jivo.ru" crossorigin>
<link rel="dns-prefetch" href="https://mc.yandex.ru">
<link rel="dns-prefetch" href="https://code.jivo.ru">

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=110321078', 'ym');

    ym(110321078, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/110321078" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<style>
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(2px);
    justify-content: center;
    align-items: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}
.modal-overlay.show {
    display: flex;
    opacity: 1;
}
.modal-content {
    background: #1e2227;
    color: #fff;
    border-radius: 16px;
    padding: 2rem;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 0 25px rgba(255, 193, 7, 0.2);
    position: relative;
    animation: fadeInUp 0.4s ease;
}
@keyframes fadeInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 1.8rem;
    color: #ffc107;
    cursor: pointer;
}
.close-btn:hover {
    color: #fff;
}
.ok-btn {
    background-color: #ffc107;
    color: #000;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    margin-top: 1rem;
    cursor: pointer;
    transition: 0.2s ease;
}
.ok-btn:hover {
    background-color: #ffcd38;
}
@media (max-width: 480px) {
    .modal-content {
        width: 95%;
        padding: 1.5rem;
    }
    .modal-content h2 {
        font-size: 1.2rem;
    }
}
.product-item {
  padding: 10px 5px;
  margin-right: 10px;
  margin-bottom: 10px;
  min-width: 100px;
  cursor: pointer;
  color: var(--uc-dark);
  background-color: var(--uc-white);
  text-align: center;
  border-radius: 4px;
  transition: all 0.2s ease;
  font-size: 14px;
}
@media (min-width: 1500px) {
  .product-item {
    width: 11%;
    font-size: 16px;
    padding: 18px 12px;
  }
}
@media (min-width: 2000px) {
  .product-item {
    width: calc(100% / 8 - 15px);
    font-size: 18px;
  }
}
</style>

<style>
:root{--uc-primary:#ffc107;--uc-white:#fff;--uc-dark:#1e2227;--uc-black:#000}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Roboto,sans-serif;background:#1e2227;color:#fff;min-height:100vh}
.uc-hero{position:relative;min-height:600px;display:flex;align-items:center;justify-content:center}
.uc-hero__background{position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 25%,#7e22ce 50%,#be185d 75%,#dc2626 100%);opacity:.7;z-index:-1}
.uc-hero__content{text-align:center;z-index:1;padding:2rem}
.uc-hero__title{font-size:2.5rem;font-weight:700;margin-bottom:1rem;color:#ffc107}
.payment-game{display:flex;background:#334056f7;border-radius:6px;width:100%;min-height:400px;margin:auto;padding:20px}
.payment-game__form{padding:20px}
.form-control{display:block;width:100%;padding:.375rem .75rem;font-size:1rem;line-height:1.5;color:#212529;background-color:#fff;border:1px solid #ced4da;border-radius:.25rem}
.btn-primary{background:#edba45;padding:10px 15px;border:none;border-radius:6px;color:#000;cursor:pointer}
.btn-primary:hover{background:#ffc237}
.container{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}
@media (min-width:576px){.container{max-width:540px}}
@media (min-width:768px){.container{max-width:720px}}
@media (min-width:992px){.container{max-width:960px}}
@media (min-width:1200px){.container{max-width:1140px}}
</style>

@vite(['resources/scss/app.scss', 'resources/ts/app.ts'])

<script>
  window.addEventListener('load', function() {
    setTimeout(function() {
      var jivoScript = document.createElement('script');
      jivoScript.src = 'https://code.jivo.ru/widget/7vv3uHNMMD';
      jivoScript.async = true;
      document.head.appendChild(jivoScript);
    }, 3000);
  });
</script>