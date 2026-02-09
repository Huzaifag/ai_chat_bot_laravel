## Quick Start - Analytics Setup

### Step 1: Run the migration
```bash
php artisan migrate
```

### Step 2: (Optional) Add sample data for testing
```bash
php artisan db:seed --class=ChatAnalyticsSeeder
```

### Step 3: View the dashboard
- Log into admin panel
- Navigate to Dashboard
- See analytics charts automatically

### What's New:
✅ API usage tracking (Gemini, OpenAI)
✅ 4 interactive charts with Chart.js
✅ Token usage and cost estimation
✅ Response time monitoring
✅ 30-day activity trends
✅ Top documents by interactions

### To customize API pricing:
Edit `app/Http/Controllers/ChatController.php` lines 255-260
