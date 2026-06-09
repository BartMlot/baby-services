# notification-service

Symfony 7 microservice that consumes domain events from RabbitMQ and delivers multi-channel notifications.
Runs as an AMQP consumer alongside a lightweight HTTP server for health checks and log inspection.

## Running

Requires Docker and the `platform_net` network created by auth-service.
Start [auth-service](../auth-service) first.

```bash
docker compose up -d --build
```

Migrations and the AMQP consumer start automatically on container start.

```bash
docker compose logs -f notification-service    # tail consumer logs
docker compose exec notification-service sh    # shell into container
docker compose down                            # stop and remove containers
```

Swagger UI: http://localhost:8002/api/doc

### Dead Letter Queue

Messages that exhaust all retries land in `notification_dead` (TTL 7 days).

```bash
docker compose exec notification-service php bin/console messenger:failed:show
docker compose exec notification-service php bin/console messenger:failed:retry
docker compose exec notification-service php bin/console messenger:failed:remove --all
```

## Notification channels

Active channels are wired through DI — no `isEnabled()` flags in code.
Edit `config/services_dev.yaml` or `config/services_prod.yaml` and tag the channels you want.

| Channel | Provider | When to use |
|---|---|---|
| `LogChannel` | stdout | Always active in dev |
| `MailtrapEmailChannel` | Mailtrap API | Dev email (real delivery to verified addresses) |
| `SesEmailChannel` | AWS SES | Production transactional email |
| `TwilioSmsChannel` | Twilio SMS | SMS — requires `phone` at registration |

## Architecture

```
RabbitMQ (auth.events / user.registered)
    │
    ▼
AmqpMessageSerializer      maps JSON payload → local UserRegisteredMessage
    │
    ▼
UserRegisteredConsumer     #[AsMessageHandler]
    │
    ▼
SendWelcomeNotificationHandler
    │
    ▼
NotificationDispatcher
    ├── LogChannel
    ├── MailtrapEmailChannel  (dev)
    ├── SesEmailChannel       (prod)
    └── TwilioSmsChannel      (dev + prod, only when phoneNumber present)
```

```
src/Notification/
├── Domain/
│   ├── Entity/NotificationLog.php
│   ├── ValueObject/NotificationId.php
│   └── Enum/NotificationType.php
│
├── Application/
│   ├── Port/         NotificationChannelInterface · NotificationLogRepositoryPort
│   ├── DTO/          NotificationContext
│   ├── Handler/      SendWelcomeNotificationHandler
│   └── Service/      NotificationDispatcher
│
└── Infrastructure/
    ├── Channel/      LogChannel · MailtrapEmailChannel · SesEmailChannel · TwilioSmsChannel
    ├── Messaging/    AmqpMessageSerializer · UserRegisteredMessage · UserRegisteredConsumer
    └── Persistence/  DoctrineNotificationLogRepository
```

### Key design decisions

| Decision | Rationale |
|---|---|
| Channels tagged via DI, not `isEnabled()` | Environment YAML controls behaviour — no conditionals in domain code |
| Each provider is its own class | Single Responsibility; swap one without touching others |
| `NotificationDispatcher` catches per-channel exceptions | Best-effort delivery — one failed channel does not abort others |
| No shared kernel with auth-service | `AmqpMessageSerializer` maps the incoming `type` header to a local class |
| Bidirectional serializer (`encode` + `decode`) | Required so Messenger can re-encode messages during DLQ retries |
| Per-provider mailer services with `autowire: false` | Prevents Messenger bus auto-injection which would dispatch emails async instead of sending directly |

### Consumed event

Exchange `auth.events`, routing key `user.registered`:

```json
{
  "userId": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "email": "user@example.com",
  "occurredAt": "2024-01-01T12:00:00+00:00",
  "phoneNumber": "+48123456789"
}
```

`phoneNumber` is `null` when not provided at registration. SMS channels skip silently.

### Retry & Dead Letter Queue

```
Delivery fails
    ├── retry 1 — delay  1 s
    ├── retry 2 — delay  2 s
    └── retry 3 — delay  4 s
         │
         ▼  all retries exhausted
    notification_dead queue  (TTL 7 days)
```

## Tech stack

PHP 8.3 · Symfony 7.2 · Doctrine ORM 3 · Symfony Messenger + AMQP
Symfony Mailer (Mailtrap · AWS SES) · Twilio SDK · PostgreSQL 15 · Monolog · PHPUnit 11
