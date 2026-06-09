# auth-service

Symfony 7 microservice responsible for user registration, login, and JWT issuance.
Publishes domain events to RabbitMQ consumed by downstream services.

## Running

Requires Docker. Starts auth-service + PostgreSQL + RabbitMQ.

```bash
docker compose up -d --build
```

Migrations and JWT key generation run automatically on container start.

```bash
docker compose logs -f auth-service        # tail logs
docker compose exec auth-service sh        # shell into container
docker compose down                        # stop and remove containers
```

Swagger UI: http://localhost:8001/api/doc
RabbitMQ management: http://localhost:15672 (guest / guest)

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/register` | — | Register a new user |
| POST | `/login` | — | Authenticate, receive JWT |
| GET | `/me` | JWT | Current user identity from token |

### POST /register

```json
{
  "email": "user@example.com",
  "password": "secret123",
  "phone": "+48123456789"
}
```

`phone` is optional. Must be in E.164 format. Required to receive SMS notifications.

Response `201`:
```json
{ "message": "User registered successfully." }
```

Errors: `400` missing fields · `409` email already taken · `422` invalid email or phone format

---

### POST /login

```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```

Response `200`:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "user": {
    "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "email": "user@example.com"
  }
}
```

Errors: `400` missing fields · `401` invalid credentials

---

### GET /me *(requires JWT)*

```
Authorization: Bearer <token>
```

Response `200`:
```json
{
  "email": "user@example.com",
  "roles": ["ROLE_USER"]
}
```

## Architecture

Hexagonal Architecture with CQRS-lite.

```
src/User/
├── Domain/
│   ├── Entity/User.php
│   ├── ValueObject/      Email · UserId · HashedPassword · PhoneNumber
│   ├── Enum/UserStatus.php
│   ├── Repository/UserRepositoryInterface.php
│   └── Event/UserRegistered.php
│
├── Application/
│   ├── Port/             PasswordHasherPort · EventPublisherPort
│   ├── Command/RegisterUser/   RegisterUserCommand + Handler
│   └── Query/LoginUser/        LoginUserQuery + Handler + LoginResult
│
├── Infrastructure/
│   ├── Persistence/      DoctrineUserRepository
│   ├── Security/         SymfonyPasswordHasher · JwtUserAdapter
│   └── Messaging/        MessengerEventPublisher · UserRegisteredMessage
│
└── UI/Http/Controller/   RegisterController · LoginController · MeController
```

### Key design decisions

| Decision | Rationale |
|---|---|
| `LoginUserQuery` (not Command) | Login reads state and returns data — correct CQRS semantics |
| `HashedPassword` VO wraps a string | Hashing is Infrastructure concern, Domain only holds the result |
| Domain event → Infrastructure message | `UserRegistered` (Domain) translated to `UserRegisteredMessage` (AMQP) by `MessengerEventPublisher` |
| Two Messenger buses | `command.bus` for write operations, `query.bus` for reads |
| JWT keys generated at container start | Not committed to repo, generated in `docker/entrypoint.sh` |

### Published event

Exchange `auth.events`, routing key `user.registered`:

```json
{
  "userId": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "email": "user@example.com",
  "occurredAt": "2024-01-01T12:00:00+00:00",
  "phoneNumber": "+48123456789"
}
```

`phoneNumber` is `null` when not provided at registration.

## Tech stack

PHP 8.3 · Symfony 7.2 · Doctrine ORM 3 · Symfony Messenger + AMQP
LexikJWTAuthenticationBundle 3 · PostgreSQL 15 · PHPUnit 11
