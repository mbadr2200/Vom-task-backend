# VoM News Aggregator API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

No authentication required - all endpoints are publicly accessible.

## Rate Limiting

-   60 requests per minute per IP address
-   Rate limit headers included in responses

## Response Format

All API responses follow this structure:

```json
{
  "data": object|array,
}
```

## Error Handling

Error responses include appropriate HTTP status codes:

-   400: Bad Request
-   404: Not Found
-   422: Validation Error
-   500: Internal Server Error

## Endpoints

### 1. Get All Articles

```http
GET /api/articles
```

**Query Parameters:**

-   `source` (string): Filter by source name
-   `category` (string): Filter by category
-   `from_date` (date): Filter articles from this date (YYYY-MM-DD)
-   `to_date` (date): Filter articles to this date (YYYY-MM-DD)
-   `page` (integer): Page number for pagination
-   `per_page` (integer): Items per page (1-100, default: 20)

**Example Request:**

```bash
curl "http://localhost:8000/api/articles?category=technology&per_page=10"
```

**Example Response:**

```json
{
    "articles": [
        {
            "id": 1,
            "title": "Article Title",
            "description": "Article description...",
            "content": "Full article content...",
            "url": "https://example.com/article",
            "image_url": "https://example.com/image.jpg",
            "source_name": "NewsAPI",
            "category": "technology",
            "published_at": "2023-09-06T12:00:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

### 2. Get Single Article

```http
GET /api/articles/{id}
```

**Example Request:**

```bash
curl "http://localhost:8000/api/articles/1"
```

### 3. Get Available Sources

```http
GET /api/articles/sources
```

Returns list of all available news sources.

### 4. Get Available Categories

```http
GET /api/articles/categories
```

Returns list of all available categories.

## Usage Examples

### Fetch Technology Articles

```bash
curl "http://localhost:8000/api/articles?category=technology"
```

### Filter by Date Range

```bash
curl "http://localhost:8000/api/articles?from_date=2023-09-01&to_date=2023-09-06"
```

### Pagination

```bash
curl "http://localhost:8000/api/articles?page=2&per_page=50"
```
