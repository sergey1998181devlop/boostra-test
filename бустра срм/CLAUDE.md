# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Boostra CRM is a hybrid PHP application combining:
- **Legacy System**: Simpla CMS-based admin interface (index.php entry point)
- **Modern API**: Custom framework with DDD architecture (app.php entry point)

**PHP Version**: 7.4

**Important**: This project uses PHP 7.4, which has limitations:
- No native enum support (use `myclabs/php-enum` package)
- No union types
- No named arguments
- No match expressions
- No nullsafe operator (`?->`)

## Development Environment

### Docker Setup

```bash
# Start development environment
docker-compose up -d

# Rebuild containers
docker-compose up -d --build

# Using makefile
make up      # Start container
make down    # Stop container
```

Access: http://localhost:8089/

**Important**: Configuration files required (obtain from team):
- `./config/config.php`
- `./config/.env`

## Git Workflow

### Branch Structure
- `develop` - pre-release development branch (default working branch)
- `master` - production branch with stable code

### Creating Feature Branches

```bash
# Always branch from develop
git checkout develop
git pull origin develop

# Create feature branch with appropriate prefix
git checkout -b feature/CS-123  # New features
git checkout -b fix/CS-456      # Bug fixes
git checkout -b hotfix/CS-789   # Critical fixes
```

### Merge Request Process
1. Push changes to feature branch
2. Create MR to `develop` (never merge directly)
3. Wait for code review and approval
4. Test on staging environment using GitLab Pipelines
5. Senior developers handle `develop` → `master` merges

### Commit Message Convention

**MANDATORY**: All commit messages MUST follow semantic commit convention in English.

Format: `<type>(<scope>): <description>`

**Types:**
- `feat` - new feature
- `fix` - bug fix
- `docs` - documentation changes
- `style` - code formatting (no logic changes)
- `refactor` - code refactoring
- `test` - adding or updating tests
- `chore` - routine tasks, dependencies, build config
- `perf` - performance improvements

**Examples:**
```bash
git commit -m "feat(clients): add client identification endpoint"
git commit -m "fix(auth): resolve token expiration issue"
git commit -m "refactor(database): optimize query performance"
git commit -m "docs(api): update routing documentation"
git commit -m "test(orders): add unit tests for order service"
```

**Scope** (optional but recommended): Module or component name (clients, orders, auth, database, etc.)

**Description**: Brief summary in imperative mood (add, fix, update, not added, fixed, updated)

## Architecture

### Dual System Architecture

**Legacy System** (`index.php`):
- Simpla CMS framework
- Located in `api/` directory
- Used for admin interface and legacy features

**Modern API** (`app.php`):
- Custom framework with PSR-4 autoloading
- RESTful API endpoints
- Domain-Driven Design (DDD) modules

### Directory Structure

```
app/
├── Core/               # Framework core
│   ├── Application/    # Application layer (Request, Response, Container)
│   ├── Router/         # Routing system
│   ├── Middleware/     # Middleware contracts
│   ├── Database/       # Database abstraction (BaseDatabase, SimplaDatabase)
│   ├── Logger/         # Logging infrastructure
│   ├── Messenger/      # Message bus
│   └── Helpers/        # Core helpers (autoloaded)
├── Modules/            # Business domain modules (DDD)
│   ├── Clients/
│   ├── Card/
│   ├── Faq/
│   ├── Manager/
│   ├── Notifications/
│   ├── RecurrentsCenter/
│   ├── TicketAssignment/
│   └── [other modules]
├── Http/               # HTTP layer
│   ├── Controllers/    # API controllers
│   └── Middleware/     # HTTP middleware (auth, validation)
├── Service/            # Application services
├── Repositories/       # Data repositories
├── Models/             # Data models
├── Helpers/            # Helper functions (autoloaded)
└── Enums/              # Enumeration classes

routes/
└── api.php            # API route definitions

database/
├── migrations/        # Phinx migrations
└── seeds/             # Database seeds
```

### Module Structure (DDD)

Each module follows Domain-Driven Design:

```
app/Modules/[ModuleName]/
├── Application/       # Application layer
│   ├── DTO/          # Data Transfer Objects
│   └── Service/      # Application services
├── Domain/           # Domain layer
│   ├── Entity/       # Domain entities
│   └── Repository/   # Repository interfaces
└── Infrastructure/   # Infrastructure layer
    └── Repository/   # Repository implementations
```

## Routing

Routes are defined in `routes/api.php` using the Router facade:

```php
use App\Core\Application\Facades\Router;

// Basic route
Router::get('path', [Controller::class, 'method']);

// Route with parameters
Router::post('path/:id', [Controller::class, 'method']);

// Route with middleware
Router::post('path', [Controller::class, 'method'], ['app.token.verify']);
```

**Route Parameters**: Use `:paramName` syntax (e.g., `/clients/:id`)

**Middleware**: Defined in `app/Http/Middleware/`
- `app.token.verify` - Application token verification (CheckApplicationToken)
- Custom middleware implement `MiddlewareInterface`

## Database

### Migrations (Phinx)

```bash
# Create migration
vendor/bin/phinx create MigrationName

# Run migrations
vendor/bin/phinx migrate

# Rollback
vendor/bin/phinx rollback

# Check status
vendor/bin/phinx status
```

Migrations located in: `database/migrations/`

Configuration: `phinx.php` (uses Simpla config for DB credentials)

### Database Access

Two database layers:
- `BaseDatabase` - Modern database abstraction
- `SimplaDatabase` - Legacy Simpla integration
- Uses Medoo library for query building

## Testing

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/Modules/Faq/Repositories/FaqRepositoryTest.php

# Run tests by type
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Feature/
vendor/bin/phpunit tests/api/
```

Test structure:
- `tests/Unit/` - Unit tests
- `tests/Feature/` - Feature tests
- `tests/api/` - API integration tests

## Dependencies

Key packages (see `composer.json`):
- `catfan/medoo` - Database query builder
- `robmorgan/phinx` - Database migrations
- `monolog/monolog` - Logging
- `vlucas/phpdotenv` - Environment configuration
- `symfony/messenger` - Message bus
- `phpunit/phpunit` - Testing framework

## Common Tasks

### Adding New API Endpoint

1. Define route in `routes/api.php`
2. Create/update controller in `app/Http/Controllers/`
3. Implement business logic in module's Application/Service layer
4. Add middleware if authentication/authorization required

### Creating New Module

1. Create module directory: `app/Modules/[ModuleName]/`
2. Structure: `Application/`, `Domain/`, `Infrastructure/`
3. Define entities in `Domain/Entity/`
4. Create repository interfaces in `Domain/Repository/`
5. Implement repositories in `Infrastructure/Repository/`
6. Create DTOs and services in `Application/`

### Adding Middleware

1. Create class in `app/Http/Middleware/`
2. Implement `MiddlewareInterface`
3. Reference in route definition: `['middleware.name']`

## Logging

Logs located in: `logs/` directory
- `error.log` - PHP errors (configured in app.php)
- Use `logger()` helper function for application logging
- Monolog configured for structured logging

## Configuration

- Modern app config: `config/.env`
- Legacy config: `config/config.php`
- Database config: Loaded via Simpla (api/Simpla.php)

## Code Style

- PSR-4 autoloading
- Namespace: `App\`
- Autoloaded helpers in `composer.json` files section
- Use type hints and return types
- Follow existing patterns in each module

BEFORE ANYTHING ELSE: run 'bd onboard' and follow the instructions