# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.3 PHP application for managing movies and TV shows content, built with a Domain-Driven Design (DDD) architecture. It provides a REST API for content management with features like search, pagination, filtering, and user recommendations.

## Common Development Commands

### Testing
- **Run all tests**: `./vendor/bin/pest` or `bin/phpunit`
- **Run specific test file**: `./vendor/bin/pest tests/Feature/Content/Controller/MovieControllerTest.php`
- **Run tests with coverage**: `./vendor/bin/pest --coverage`

### Symfony Console Commands
- **Run console commands**: `php bin/console <command>`
- **Import dataset**: `php bin/console app:import-dataset`
- **Database migrations**: `php bin/console doctrine:migrations:migrate`
- **Create migration**: `php bin/console doctrine:migrations:generate`
- **Load fixtures**: `php bin/console doctrine:fixtures:load`
- **Clear cache**: `php bin/console cache:clear`

### Database Operations
- **Create database**: `php bin/console doctrine:database:create`
- **Drop database**: `php bin/console doctrine:database:drop --force`
- **Update schema**: `php bin/console doctrine:schema:update --force`
- **Validate schema**: `php bin/console doctrine:schema:validate`

## Architecture Overview

### Domain-Driven Design Structure
The application follows DDD principles with clear separation:

- **Domain Layer** (`src/Domain/`): Contains business logic, value objects, DTOs, and domain services
- **Infrastructure Layer** (`src/Entity/`, `src/Repository/`): Doctrine entities and data access
- **Application Layer** (`src/Domain/Content/Controller/`, `src/Domain/Content/Service/`): Controllers and application services

### Key Components

#### Content Domain (`src/Domain/Content/`)
- **Controllers**: REST API endpoints for movies and TV shows
- **DTOs**: Data transfer objects with validation and filtering
- **Services**: Business logic for content management, pagination, scraping
- **Hydrators**: Convert between requests/entities and DTOs
- **Validators**: Custom validation rules for unique titles/names
- **Value Objects**: Domain-specific value types (Duration, Title, Rating, etc.)

#### Entity Layer (`src/Entity/`)
Core entities: `Movie`, `TvShow`, `Actor`, `Director`, `Genre`, `User`, `Review`, `SearchHistory`, `UserContentRecommendation`

#### Custom Doctrine Types (`src/Doctrine/Custom/Type/`)
Custom Doctrine field types for content-specific data handling

### Testing Architecture
- Uses **Pest PHP** testing framework
- Test structure mirrors source with `tests/Feature/` for integration tests
- Symfony testing tools with Panther for browser testing
- Base test case in `tests/TestCase.php`

### Key Services
- **MovieService**: Core business logic for movie operations
- **PaginationService**: Generic pagination handling
- **ScrapingService**: External data scraping functionality
- **CSVConverterService**: Dataset import/export

## Development Notes

### Entity Management
- All entities use Doctrine ORM with custom repositories
- Database uses SQLite for development (`identifier.sqlite`)
- Migrations are version-controlled in `migrations/` directory

### API Structure
- RESTful JSON API with consistent response format
- Standard HTTP methods (GET, POST, PATCH, DELETE)
- Pagination and filtering built into list endpoints
- Validation errors return structured responses

### Data Import
- Large movie dataset available in `src/Domain/Content/dataset/TMDB_all_movies.csv`
- Custom import command handles bulk data processing

### Security
- Uses Symfony Security component
- User authentication and authorization configured
- CSRF protection and validation in place