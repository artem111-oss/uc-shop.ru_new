<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include("layouts.head")
  @vite(['resources/scss/app.scss','resources/ts/app.ts'])
    <style>
        /* Critical CSS - инлайн для быстрой отрисовки */
        .content p { line-height: 22px; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .wrap { min-height: 100vh; }
    </style>
  <script src="https://telegram.org/js/telegram-web-app.js" async></script>
</head>
<body>
<div id="paymentModal" class="modal-overlay">
<div class="modal-content">
    <button class="close-btn" id="closeModal">&times;</button>
    <h2>Спасибо за оплату!</h2>
    <p>Аккаунт будет пополнен в течении 1 минуты, если этого не произошло напиши в чат на сайте</p>
    <button class="ok-btn" id="okModal">ОК</button>
</div>
</div>
@include("layouts.header")

<div class="wrap">
  @include('inc/messages')
    @yield('content')
</div>
@include("layouts.footer")

@stack('scripts')

</body>
</html>
