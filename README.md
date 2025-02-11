# 📚 Library API Project — README

---

## 🚀 **Проект: Library API**

Это RESTful API для управления библиотекой, реализованное на Laravel и Docker. API поддерживает аутентификацию, управление книгами и учетными записями пользователей с различными ролями (библиотекарь, читатель).

---

## 🗂️ **Технологии**

- **PHP 8.x**
- **Laravel 11.x**
- **MySQL 8.x**
- **Docker & Docker Compose**
- **JWT (JSON Web Tokens)** для аутентификации

---

## 🐳 **Установка и запуск проекта (с использованием Docker)**

### 📥 1. Клонирование репозитория

```bash
git clone https://github.com/your-repo/library-api.git
cd library-api
```

```bash
cp .env.example .env
```

Запуск Docker-контейнеров:

```bash
docker-compose up -d
```

Установка зависимостей (Composer):

```bash
docker-compose run --rm artisan composer install
```

Генерация ключа приложения:

```bash
docker-compose run --rm artisan key:generate
```

Генерация секретного ключа для JWT:

```bash
docker-compose run --rm artisan jwt:secret
```

Запуск миграций и сидеров для базы данных:

```bash
docker-compose run --rm artisan migrate --seed
```

📋 Создание библиотекаря
Выполнение команды для создания библиотекаря:

```bash
docker-compose run --rm artisan librarian:create
```

Следуйте инструкциям:

Введите имя библиотекаря
Введите email
Введите и подтвердите пароль
При необходимости измените роль существующего пользователя на библиотекаря
✅ Запуск тестов

```bash
docker-compose run --rm artisan test
```

🗑️ Полезные команды
Перезапуск контейнеров:

```bash
docker-compose down
docker-compose up -d
```

Очистка кэша:

```bash
docker-compose run --rm artisan cache:clear
docker-compose run --rm artisan config:clear
docker-compose run --rm artisan route:clear
docker-compose run --rm artisan view:clear
```

Повторная миграция базы данных:

```bash
docker-compose run --rm artisan migrate:fresh --seed
```
