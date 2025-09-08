# VoM News Aggregator Setup Guide

## Quick Start

### 1. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

**Required Environment Variables:**
```env
APP_NAME="VoM News Aggregator"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=vom_news_aggregator
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# News API Keys (Get these from respective providers)
NEWSAPI_KEY=your-newsapi-key-here
GUARDIAN_API_KEY=your-guardian-api-key-here
NYT_API_KEY=your-nyt-api-key-here
```

### 2. Getting API Keys

#### NewsAPI (https://newsapi.org/)
1. Visit https://newsapi.org/register
2. Create a free account
3. Copy your API key
4. Add to `.env` as `NEWSAPI_KEY=your-key-here`

#### The Guardian (https://open-platform.theguardian.com/)
1. Visit https://bonobo.capi.gutools.co.uk/register/developer
2. Register for a developer key
3. Copy your API key
4. Add to `.env` as `GUARDIAN_API_KEY=your-key-here`

#### New York Times (https://developer.nytimes.com/)
1. Visit https://developer.nytimes.com/get-started
2. Create an account and register an app
3. Enable Article Search API
4. Copy your API key
5. Add to `.env` as `NYT_API_KEY=your-key-here`

### 3. Database Setup

#### Option A: SQLite (Recommended for development)
```bash
# Create database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

#### Option B: MySQL
```bash
# Create database
mysql -u root -p
CREATE DATABASE vom_news_aggregator;
exit

# Update .env with MySQL credentials
# Run migrations
php artisan migrate
```

### 4. Initial Data Population

Fetch some initial articles:
```bash
php artisan news:fetch --days=1
```

### 5. Testing the Setup

Run the test suite to ensure everything is working:
```bash
php artisan test
```

Test the API endpoints:
```bash
# Get articles
curl http://localhost:8000/api/articles

# Get sources
curl http://localhost:8000/api/articles/sources
```

## Production Setup

### 1. Server Requirements
- PHP 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Web server (Apache/Nginx)
- Database (MySQL 8.0+, PostgreSQL 12+, or SQLite)
- Composer
- Supervisor (for queue processing)

### 2. Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Use Redis for better performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vom_news_aggregator
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
```

### 3. Scheduler Setup
Add to crontab:
```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/the/project && php artisan schedule:run >> /dev/null 2>&1
```
or run 

```
php artisan schedule:work
```
