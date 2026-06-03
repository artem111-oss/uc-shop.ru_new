<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Payment URLs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        .url {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
        a {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        a:hover {
            background: #45a049;
        }
        .info {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h2>✅ Success Page Test</h2>
        <div class="url">http://127.0.0.1:8000/payment/success?order_id=29516</div>
        <a href="/payment/success?order_id=29516" target="_blank">Открыть страницу успеха</a>
        <p class="info">Должна показаться страница с зеленой галочкой и деталями заказа</p>
    </div>

    <div class="test-box">
        <h2>❌ Failed Page Test</h2>
        <div class="url">http://127.0.0.1:8000/payment/failed?order_id=29516</div>
        <a href="/payment/failed?order_id=29516" target="_blank">Открыть страницу ошибки</a>
        <p class="info">Должна показаться страница с красным крестиком</p>
    </div>

    <div class="test-box">
        <h2>🏠 Home Page</h2>
        <div class="url">http://127.0.0.1:8000/</div>
        <a href="/" target="_blank">Открыть главную</a>
        <p class="info">Кнопка "в магазин" в Platima будет вести сюда</p>
    </div>

    <div class="test-box">
        <h2>📝 Настройки Platima</h2>
        <p><strong>success_url:</strong> <code><?php echo url('/payment/success?order_id=ORDER_ID'); ?></code></p>
        <p><strong>fail_url:</strong> <code><?php echo url('/payment/failed?order_id=ORDER_ID'); ?></code></p>
        <p><strong>return_url:</strong> <code><?php echo url('/'); ?></code></p>
        <p><strong>callback_url:</strong> <code><?php echo url('/api/payment/webhook'); ?></code></p>
    </div>
</body>
</html>
