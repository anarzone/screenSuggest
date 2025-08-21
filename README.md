# 🎬 ScreenSuggest

## 🚀 Deployment Status

| Environment | Branch | Status | Server      |
|-------------|--------|--------|-------------|
| **Development** | `dev` |[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Ffd8ef835-15ef-4599-9c5d-80f21539e897%3Fdate%3D1%26label%3D1%26commit%3D1&style=for-the-badge)](https://forge.laravel.com/servers/906819/sites/2821726) | `Staging`   |
| **Production** | `main` | [![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F256a3972-80ed-4e5e-b603-9f57b887fc61%3Fdate%3D1%26label%3D1%26commit%3D1&style=for-the-badge)](https://forge.laravel.com/servers/906819/sites/2821961) | `Production` |

A powerful movie and TV show discovery platform built with Symfony 7.3 and Domain-Driven Design (DDD) architecture. ScreenSuggest provides intelligent search, personalized recommendations, and comprehensive content management through a modern REST API.

## ✨ Features

### 🔍 Advanced Search System
- **Fuzzy Search** - Handles partial matches ("forr" finds "Forrest Gump")
- **Popularity-Based Ranking** - Classic and highly-rated movies appear first
- **Multi-Field Search** - Search across titles, descriptions, cast, and crew
- **IMDB Integration** - Comprehensive movie ratings and metadata
- **Real-Time Filtering** - Filter by genre, year, rating, and more

### 🎯 Smart Recommendations
- **Personalized Suggestions** - AI-powered content recommendations
- **Similar Movies** - Find movies similar to your favorites
- **Genre-Based Discovery** - Explore content by preferred genres
- **Rating-Based Filtering** - Discover highly-rated content

### 📊 Content Management
- **Rich Metadata** - Comprehensive movie and TV show information
- **Cast & Crew Data** - Detailed information about actors and directors
- **Image Management** - Movie posters and promotional images
- **Review System** - User ratings and reviews

## 🚀 Technology Stack

### Backend
- **PHP 8.2+** - Modern PHP with strict typing
- **Symfony 7.3** - Full-stack PHP framework
- **Doctrine ORM 3.1** - Database abstraction and ORM
- **MySQL** - Primary database with full-text search
- **Domain-Driven Design** - Clean architecture with separated concerns

### Architecture
- **DDD Structure** - Domain, Infrastructure, and Application layers
- **CQRS Patterns** - Command and Query separation
- **Value Objects** - Type-safe domain modeling
- **Repository Pattern** - Clean data access abstraction
- **Service Layer** - Business logic separation

### Testing & Quality
- **Pest PHP** - Modern testing framework
- **Symfony Panther** - Browser testing
- **Doctrine Fixtures** - Test data management
- **Custom Validators** - Domain-specific validation rules

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js & npm/yarn (for frontend assets)

## 🛠️ Installation

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/screenSuggest.git
cd screenSuggest
```

### 2. Install Dependencies
```bash
composer install
npm install  # or yarn install
```

### 3. Environment Setup
```bash
cp .env.example .env
# Configure your database connection and other settings
```

### 4. Database Setup
```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load sample data (optional)
php bin/console doctrine:fixtures:load
```

### 5. Import Movie Dataset (Optional)
```bash
# Import large TMDB movie dataset
php bin/console app:import-dataset
```

## 🎮 Usage

### Start Development Server
```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

### API Endpoints

#### Movies
- `GET /movies` - Search and list movies
- `GET /movies/{id}` - Get movie details
- `GET /movies/{id}/similar` - Get similar movies
- `POST /movies` - Create new movie
- `PATCH /movies/{id}` - Update movie
- `DELETE /movies/{id}` - Delete movie

#### Genres
- `GET /genres` - List all genres
- `GET /genres/{id}` - Get genre details

### API Usage Examples

#### Basic Movie Search
```bash
curl "http://localhost:8000/movies?q=godfather"
```

#### Advanced Search with Filters
```bash
curl "http://localhost:8000/movies?q=batman&yearStart=2000&yearEnd=2020&imdbRatingMin=7.0&genre=Action"
```

#### Paginated Results
```bash
curl "http://localhost:8000/movies?q=drama&page=2&limit=10&sortBy=imdbRating&sortOrder=DESC"
```

## 🧪 Testing

### Run All Tests
```bash
./vendor/bin/pest
```

### Run Specific Test Suite
```bash
./vendor/bin/pest tests/Feature/Content/Controller/MovieControllerTest.php
```

### Run with Coverage
```bash
./vendor/bin/pest --coverage
```

## 🏗️ Architecture Overview

### Domain Layer (`src/Domain/`)
- **Value Objects** - Type-safe domain concepts (Title, Duration, Rating)
- **DTOs** - Data transfer objects for API communication
- **Services** - Business logic and domain operations
- **Controllers** - HTTP request handling and response formatting

### Infrastructure Layer (`src/Entity/`, `src/Repository/`)
- **Entities** - Doctrine ORM entities for data persistence
- **Repositories** - Data access and query optimization
- **Custom Types** - Domain-specific Doctrine field types

### Key Components
- **MovieService** - Core movie business logic
- **SearchService** - Advanced search and ranking algorithms
- **PaginationService** - Generic pagination handling
- **MovieRepository** - Optimized database queries with fuzzy search

## 🔧 Console Commands

### Import TMDB Dataset
```bash
php bin/console app:import-dataset
```

### Database Operations
```bash
# Create migration
php bin/console doctrine:migrations:generate

# Validate schema
php bin/console doctrine:schema:validate

# Clear cache
php bin/console cache:clear
```

## 🌟 Search Features

### Enhanced Search Capabilities
- **Partial Matching** - "forr" matches "Forrest Gump"
- **Phonetic Search** - SOUNDEX algorithm for typo tolerance
- **Popularity Boost** - Highly-rated movies with more votes rank higher
- **Cultural Significance** - Classic movies (pre-2000, 8.5+ rating) get bonus points
- **Modern Blockbusters** - Popular recent movies (8.0+ rating, 1M+ votes) prioritized

### Search Parameters
```bash
# Required
q=search_term          # Minimum 3 characters

# Optional Filters
genre=Action           # Filter by genre
yearStart=2000         # Movies from year
yearEnd=2020          # Movies until year
imdbRatingMin=7.0     # Minimum IMDB rating
imdbRatingMax=9.5     # Maximum IMDB rating

# Pagination
page=1                # Page number
limit=20              # Results per page (max 100)

# Sorting
sortBy=imdbRating     # Sort field: releaseDate, imdbRating, title
sortOrder=DESC        # Sort direction: ASC, DESC
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Use type declarations and return types
- Follow Domain-Driven Design principles
- Add PHPDoc for public methods
- Use meaningful commit messages

## 🐛 Issues and Support

If you encounter any issues or have questions:

1. Check existing [Issues](https://github.com/yourusername/screenSuggest/issues)
2. Create a new issue with detailed information
3. Include error logs and reproduction steps

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [TMDB](https://www.themoviedb.org/) for comprehensive movie database
- [IMDB](https://www.imdb.com/) for movie ratings and metadata
- Symfony community for excellent documentation and tools
- Domain-Driven Design principles by Eric Evans

## 🔗 Links

- [API Documentation](API_SEARCH_REFERENCE.md)

---

**Built with ❤️ using Symfony 7.3 and Domain-Driven Design**
