# План деплоя в staging (Laravel 10.10)

## 1. Подготовка кода
```bash
# Останавливаем текущие контейнеры
docker-compose down

# Создаем резервную копию БД
gunzip docker/s1152170_rf1.sql.gz
mysqldump -u root -p s1152170_rf1 > database_backup_$(date +%Y%m%d).sql

# Проверяем текущее состояние репозитория
git status

# Убедимся, что мы на правильной ветке (например, develop/staging)
git checkout staging || git checkout -b staging

# Обновляем зависимости
composer update
```

## 2. Настройка окружения
```bash
# Проверяем порт в docker-compose.yml
grep '8000' docker-compose.yml

# Устанавливаем настройки .env для staging
cat > .env <<EOL
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=s1152170_rf1
DB_USERNAME=root
DB_PASSWORD=root
APP_ENV=staging
EOL

# Запускаем контейнеры в фоновом режиме
docker-compose up -d
```

## 3. Миграции БД (с резервной копией)
```bash
# Применяем миграции
php artisan migrate --force

# Проверка резервной копии
cat database_backup_$(date +%Y%m%d).sql | wc -l
```

## 4. Проверка фронтенда (Vite + Vue)
- Открыть браузер по адресу http://localhost:8000
- Проверить работу всех маршрутов (routes/web.php)
- Убедиться, что Vite сборка завершена (проверяем public/js/)

## 5. План отката
```bash
# Останавливаем контейнеры
docker-compose down

# Восстанавливаем БД из резервной копии:
mysql -u root -p < database_backup_$(date +%Y%m%d).sql

# Возвращаемся к предыдущей версии (если нужно):
git checkout <предыдущий_коммит>
```

## Важные моменты:
- Убедиться, что .env содержит настройки staging
- Проверить логи в docker/log/ после деплоя
- Если возникают ошибки - остановить контейнеры и откатить