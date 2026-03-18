# FMV Referral Application

Laravel application for FMV Referral system.

## Setup

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

## Docker Commands

Start containers:
```bash
docker compose up -d
```

Access PHP container:
```bash
docker exec -it referral-php bash
```

Stop containers:
```bash
docker compose down
```
