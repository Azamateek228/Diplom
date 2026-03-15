# 📧 Настройка почты

## Текущая конфигурация (локальная разработка)

Для тестирования настроен **Mailpit** — локальный SMTP-сервер.

### 📬 Как проверить почту:
1. Откройте браузер: **http://localhost:8025**
2. Все письма будут отображаться в веб-интерфейсе

### 🚀 Запуск Mailpit:
```bash
C:\mailpit\mailpit.exe
```

Или дважды кликните по ярлыку на рабочем столе (если создан).

---

## Тестирование почты

```bash
cd d:\Diplom
php artisan mail:test
```

---

## Настройка для реальной отправки (Gmail)

### 1. Включите 2FA в Google-аккаунте:
https://myaccount.google.com/security

### 2. Создайте пароль приложения:
1. Перейдите: https://myaccount.google.com/apppasswords
2. Выберите "Почта" → "Другое (своё название)"
3. Скопируйте 16-значный пароль (например: `abcd efgh ijkl mnop`)

### 3. Обновите `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ваш-email@gmail.com
MAIL_PASSWORD=abcd-efgh-ijkl-mnop  # 16-значный пароль (без пробелов)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=ваш-email@gmail.com
MAIL_FROM_NAME="Кино на колёсах"
MAIL_EHLO_DOMAIN=localhost
```

### 4. Очистите кеш:
```bash
php artisan config:clear
```

### 5. Проверьте:
```bash
php artisan mail:test
```

---

## Настройка для Яндекс.Почты

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yandex.ru
MAIL_PORT=465
MAIL_USERNAME=ваш-email@yandex.ru
MAIL_PASSWORD=пароль-приложения
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=ваш-email@yandex.ru
MAIL_FROM_NAME="Кино на колёсах"
```

---

## Письма в системе

### 1. Двухфакторная аутентификация
- **При входе** (если включена 2FA)
- **При включении 2FA** в настройках профиля
- **Повторная отправка** кода (кнопка с таймером 60 сек)

### 2. Сброс пароля
- Ссылка действительна **24 часа**
- Отправляется на email пользователя

---

## Шаблоны писем

- `resources/views/emails/two-factor-code.blade.php` — 2FA-код
- `resources/views/emails/reset-password.blade.php` — сброс пароля

---

## Mailable-классы

- `app/Mail/TwoFactorCodeMail.php`
- `app/Mail/ResetPasswordMail.php`
