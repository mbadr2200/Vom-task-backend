# VoM News Aggregator

A robust Laravel-based news aggregation system that collects articles from multiple news sources (NewsAPI, The Guardian, New York Times) and provides a clean REST API for accessing the aggregated content.

## 🚀 Features

- **Multi-Source Aggregation**: Fetches news from NewsAPI, The Guardian, and New York Times
- **REST API**: Clean, well-documented API endpoints with filtering and pagination
- **Automated Scheduling**: Configurable automatic news fetching with Laravel's scheduler
- **Comprehensive Testing**: Full test coverage using Pest testing framework
- **Performance Optimized**: Caching, database indexing, and query optimization
- **Clean Architecture**: SOLID principles, DRY code, and maintainable structure

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL/SQLite database
- API keys for news sources (optional but recommended)

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd vom-news-aggregator
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your database**
   Update the `.env` file with your database credentials or use SQLite (default)

5. **Add API keys** (optional but recommended)
   Get API keys from NewsAPI, Guardian, and NYT and add them to your `.env`

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Run the scheduler**
```
php artisan schedule:work
```
