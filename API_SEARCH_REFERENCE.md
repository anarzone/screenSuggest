# Movie Search API Reference

## Search Endpoint
```
GET /movies
```

## Parameters

### Required
- `q` - Search query (minimum 3 characters)

### Optional Filters
- `genre` - Filter by genre name
- `yearStart` - Filter movies from this year
- `yearEnd` - Filter movies until this year  
- `imdbRatingMin` - Minimum IMDB rating (e.g., 7.0)
- `imdbRatingMax` - Maximum IMDB rating (e.g., 9.5)

### Pagination
- `page` - Page number (default: 1)
- `limit` - Results per page (default: 20, max: 100)

### Sorting
- `sortBy` - Sort field: `releaseDate`, `imdbRating`, `title`
- `sortOrder` - Sort direction: `ASC` or `DESC`

## Example Requests

### Basic Search
```
GET /movies?q=forrest
```

### Search with Filters
```
GET /movies?q=batman&yearStart=2000&yearEnd=2020&imdbRatingMin=7.0&genre=Action
```

### Paginated Search
```
GET /movies?q=godfather&page=2&limit=10&sortBy=imdbRating&sortOrder=DESC
```

## Response Format
```json
{
  "message": "Movies retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Forrest Gump",
      "originalTitle": "Forrest Gump", 
      "description": "Movie description...",
      "releaseDate": "1994-07-06",
      "duration": 142,
      "imdbRating": 8.8,
      "imdbVotes": 2100000,
      "genres": ["Drama", "Romance"],
      "actors": ["Tom Hanks", "Robin Wright"],
      "directors": [{"name": "Robert Zemeckis"}],
      "posterPath": "/path/to/poster.jpg"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 15, 
    "totalItems": 300,
    "itemsPerPage": 20
  }
}
```

## Search Features

### Enhanced Matching
- **Partial words**: "forr" matches "Forrest Gump"
- **Popular movies**: High-rated classics rank higher
- **Fuzzy matching**: Handles typos and variations
- **Only rated movies**: Filters out movies without IMDB scores

### Performance Notes
- Minimum 3 characters required for search
- Results are ranked by relevance + popularity
- Debounce frontend requests (300ms recommended)
- Maximum 100 results per page