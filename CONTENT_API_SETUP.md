# Content Management API Setup Instructions

This guide will help you set up the complete content management API for articles and videos.

## 🚀 **Quick Setup**

### 1. **Run Migrations**
```bash
cd ../api
php artisan migrate
```

### 2. **Run Seeders**
```bash
php artisan db:seed --class=ContentSeeder
```

### 3. **Clear Cache**
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 4. **Link Storage**
```bash
php artisan storage:link
```

### 5. **Start Development Server**
```bash
php artisan serve
```

Your API endpoints are now available at `http://localhost:8000/api`

## 📁 **Created Files**

### **Database Migrations**
- `database/migrations/2024_01_20_000001_create_articles_table.php`
- `database/migrations/2024_01_20_000002_create_videos_table.php`
- `database/migrations/2024_01_20_000003_create_content_categories_table.php`
- `database/migrations/2024_01_20_000004_create_content_tags_table.php`
- `database/migrations/2024_01_20_000005_create_article_tag_table.php`
- `database/migrations/2024_01_20_000006_create_video_tag_table.php`

### **Models**
- `app/Models/Article.php`
- `app/Models/Video.php`
- `app/Models/ContentCategory.php`
- `app/Models/ContentTag.php`

### **Controllers**
- `app/Http/Controllers/Api/ArticleController.php`
- `app/Http/Controllers/Api/VideoController.php`
- `app/Http/Controllers/Api/ContentController.php`

### **Routes**
- `routes/content.php` (new file - add to `routes/web.php`)

### **Seeders**
- `database/seeders/ContentSeeder.php`

## 🛠 **API Endpoints**

### **Public Routes** (No Authentication Required)
```
GET /api/content/categories     - Get all categories
GET /api/content/tags           - Get all tags
GET /api/content/stats          - Get content statistics
GET /api/content/search         - Search content

GET /api/articles              - Get articles (public only)
GET /api/articles/slug/{slug}  - Get article by slug

GET /api/videos                 - Get videos (public only)
GET /api/videos/slug/{slug}    - Get video by slug
```

### **Protected Routes** (Authentication Required)
```
POST   /api/articles            - Create article
GET    /api/articles/{id}      - Get article details
PUT    /api/articles/{id}      - Update article
DELETE /api/articles/{id}      - Delete article
POST   /api/articles/{id}/view  - Track article view

POST   /api/videos               - Create video
GET    /api/videos/{id}         - Get video details
PUT    /api/videos/{id}         - Update video
DELETE /api/videos/{id}         - Delete video
POST   /api/videos/{id}/view    - Track video view
```

### **Query Parameters**

**Articles API:**
- `search` - Search in title, excerpt, content
- `category` - Filter by category slug
- `tag` - Filter by tag slug
- `status` - Filter by status (published, draft, archived)
- `author` - Filter by author UUID
- `sort` - Sort by (latest, popular, oldest, title)
- `limit` - Number of results (max 50)

**Videos API:**
- `search` - Search in title, description
- `category` - Filter by category slug
- `tag` - Filter by tag slug
- `status` - Filter by status
- `author` - Filter by author UUID
- `sort` - Sort by (latest, popular, oldest, title, duration)
- `limit` - Number of results (max 50)

## 📝 **Response Format**

### **Success Response:**
```json
{
  "success": true,
  "message": "Articles retrieved successfully",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48
  }
}
```

### **Error Response:**
```json
{
  "success": false,
  "message": "Resource not found",
  "data": null
}
```

## 🔧 **Add Routes to Web.php**

Add this line to your `routes/web.php` to include the content routes:

```php
// Add at the bottom of routes/web.php
require __DIR__.'/content.php';
```

## 🎯 **Sample Data**

The seeder will create:

**Categories (6):**
- Meditasi, Anxiety, Depression, Sleep, Relationships, Self-Care

**Tags (15):**
- meditasi, stres, kesehatan-mental, anxiety, kerja, depression, terapi, cbt, etc.

**Articles (3):**
- "5 Teknik Meditasi untuk Mengurangi Stres"
- "Cara Mengatasi Anxiety di Tempat Kerja"
- "Manfaat Terapi CBT untuk Depression"

**Videos (3):**
- "Guided Meditation: 10 Minutes for Beginners"
- "Breathing Exercises for Anxiety Relief"
- "Sleep Better: Mindfulness Techniques"

## 🔐 **Authentication**

Protected routes require a valid Bearer token. Include this header:

```
Authorization: Bearer {your_token}
```

## 📸 **File Uploads**

Article featured images are stored in:
- `storage/app/public/articles/images/`

Make sure your `filesystems.php` has the public disk configured.

## 🚀 **Test the API**

### Test Articles Endpoint:
```bash
curl -X GET "http://localhost:8000/api/articles"
```

### Test Categories:
```bash
curl -X GET "http://localhost:8000/api/content/categories"
```

### Test with Search:
```bash
curl -X GET "http://localhost:8000/api/articles?search=meditasi"
```

## 🔄 **Frontend Integration**

Your frontend should now work with real data! The mock data fallback will automatically switch to live API calls when the endpoints are available.

## 🐛 **Troubleshooting**

1. **404 Errors**: Make sure routes are included in web.php
2. **Migration Issues**: Run `php artisan migrate:fresh --seed`
3. **Permission Issues**: Check file permissions in storage directory
4. **CORS Issues**: Add frontend URL to CORS middleware

## 📱 **Mobile App Ready**

The API is fully RESTful and ready for mobile app integration with the same endpoints used by the web frontend.