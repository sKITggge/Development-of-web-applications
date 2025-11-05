# Примеры запросов к API аутентификации (Postman)

## Базовый URL
```
http://localhost:8000/api
```
(Замените на ваш URL сервера)

---

## 1. Регистрация нового пользователя

### Endpoint
```
POST /register
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "name": "Иван Иванов",
    "email": "ivan@example.com",
    "password": "password123"
}
```

### Пример успешного ответа (201)
```json
{
    "message": "User registered successfully",
    "user": {
        "id": "67890abcdef1234567890123",
        "name": "Иван Иванов",
        "email": "ivan@example.com"
    },
    "token": "1|abcdef1234567890abcdef1234567890abcdef1234567890",
    "token_type": "Bearer"
}
```

### Пример ошибки валидации (422)
```json
{
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

---

## 2. Авторизация (Вход)

### Endpoint
```
POST /login
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "email": "ivan@example.com",
    "password": "password123"
}
```

### Пример успешного ответа (200)
```json
{
    "message": "Login successful",
    "user": {
        "id": "67890abcdef1234567890123",
        "name": "Иван Иванов",
        "email": "ivan@example.com"
    },
    "token": "2|xyz9876543210xyz9876543210xyz9876543210",
    "token_type": "Bearer"
}
```

### Пример ошибки неверных учетных данных (401)
```json
{
    "message": "Invalid credentials"
}
```

---

## 3. Получение профиля пользователя

### Endpoint
```
GET /profile
```

### Headers
```
Authorization: Bearer {ваш_токен}
Accept: application/json
```

### Пример заполнения в Postman:
В разделе **Authorization** выберите тип **Bearer Token** и вставьте полученный токен из ответа login/register

Или вручную:
```
Authorization: Bearer 1|abcdef1234567890abcdef1234567890abcdef1234567890
```

### Пример успешного ответа (200)
```json
{
    "user": {
        "id": "67890abcdef1234567890123",
        "name": "Иван Иванов",
        "email": "ivan@example.com",
        "tracked_categories": ["category_id_1", "category_id_2"],
        "tracked_sources": ["source_id_1"],
        "created_at": "2025-01-15T10:30:00.000000Z"
    }
}
```

### Пример ошибки без токена (401)
```json
{
    "message": "Unauthenticated."
}
```

---

## 4. Выход (Logout)

### Endpoint
```
POST /logout
```

### Headers
```
Authorization: Bearer {ваш_токен}
Accept: application/json
```

### Body
Запрос не требует body (можно отправить пустой или без body)

### Пример успешного ответа (200)
```json
{
    "message": "Logged out successfully"
}
```

### Пример ошибки без токена (401)
```json
{
    "message": "Unauthenticated."
}
```

---

## 5. Добавление категории в отслеживаемые

### Endpoint
```
POST /profile/categories
```

### Headers
```
Authorization: Bearer {ваш_токен}
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "category_id": "67890abcdef1234567890123"
}
```

### Пример успешного ответа (200)
```json
{
    "success": true,
    "message": "Category added to tracked"
}
```

---

## 6. Получение всех отслеживаемых категорий

### Endpoint
```
GET /profile/categories
```

### Headers
```
Authorization: Bearer {ваш_токен}
Accept: application/json
```

### Пример успешного ответа (200)
```json
{
    "categories": [
        {
            "_id": "67890abcdef1234567890123",
            "name": "Технологии",
            "created_at": "2025-01-15T10:30:00.000000Z",
            "updated_at": "2025-01-15T10:30:00.000000Z"
        }
    ],
    "count": 1
}
```

---

## 7. Удаление категории из отслеживаемых

### Endpoint
```
DELETE /profile/categories
```

### Headers
```
Authorization: Bearer {ваш_токен}
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "category_id": "67890abcdef1234567890123"
}
```

### Пример успешного ответа (200)
```json
{
    "success": true,
    "message": "Category removed from tracked"
}
```

---

## 8. Добавление источника в отслеживаемые

### Endpoint
```
POST /profile/sources
```

### Headers
```
Authorization: Bearer {ваш_токен}
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "source_id": "67890abcdef1234567890123"
}
```

### Пример успешного ответа (200)
```json
{
    "success": true,
    "message": "Source added to tracked",
    "tracked_sources": ["67890abcdef1234567890123"]
}
```

---

## 9. Получение всех отслеживаемых источников

### Endpoint
```
GET /profile/sources
```

### Headers
```
Authorization: Bearer {ваш_токен}
Accept: application/json
```

### Пример успешного ответа (200)
```json
{
    "sources": [
        {
            "_id": "67890abcdef1234567890123",
            "url": "https://example.com/rss",
            "title": "Example News",
            "logo": "https://example.com/logo.png",
            "created_at": "2025-01-15T10:30:00.000000Z",
            "updated_at": "2025-01-15T10:30:00.000000Z"
        }
    ],
    "count": 1
}
```

---

## 10. Удаление источника из отслеживаемых

### Endpoint
```
DELETE /profile/sources
```

### Headers
```
Authorization: Bearer {ваш_токен}
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON)
```json
{
    "source_id": "67890abcdef1234567890123"
}
```

### Пример успешного ответа (200)
```json
{
    "success": true,
    "message": "Source removed from tracked",
    "tracked_sources": []
}
```

---

## Настройка Postman Collection

### Шаг 1: Создайте переменную окружения
1. В Postman нажмите на значок шестеренки (⚙️) в правом верхнем углу
2. Добавьте новую переменную окружения, например `base_url` со значением `http://localhost:8000`
3. Добавьте переменную `token` (будет заполняться автоматически)

### Шаг 2: Настройте авторизацию для защищенных запросов
1. Создайте запрос (например, GET /profile)
2. Перейдите на вкладку **Authorization**
3. Выберите тип **Bearer Token**
4. В поле Token введите: `{{token}}`

### Шаг 3: Автоматическое сохранение токена (через Tests)
Для запросов `/register` и `/login` добавьте в раздел **Tests**:

```javascript
if (pm.response.code === 200 || pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.token) {
        pm.environment.set("token", jsonData.token);
        console.log("Token saved:", jsonData.token);
    }
}
```

Это автоматически сохранит токен в переменную окружения после успешной регистрации/авторизации.

---

## Порядок тестирования

1. **Регистрация** → Получите токен
2. **Авторизация** (если нужно перелогиниться) → Получите новый токен
3. **Получение профиля** → Проверьте, что токен работает
4. **Работа с категориями/источниками** → Тестируйте CRUD операции
5. **Выход** → Проверьте, что после logout запросы не проходят

---

## Примечания

- Токен действует до тех пор, пока не будет удален через `/logout`
- Все запросы кроме `/register` и `/login` требуют токен авторизации
- Используйте формат `Bearer Token` для заголовка Authorization
- MongoDB использует `_id` вместо `id` для идентификаторов документов
