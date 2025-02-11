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
git clone https://github.com/nazeru/most-library.git
cd most-library
```

Создайте файл .env на основе .env.example и настройте его:

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

Создание библиотекаря

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
