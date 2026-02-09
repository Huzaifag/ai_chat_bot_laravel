# Analytics and API Usage Tracking

## Overview

This chatbot backend now includes comprehensive analytics and API usage tracking capabilities. The dashboard provides real-time insights into API usage, chat activity, document interactions, and system performance.

## Features

### 📊 Dashboard Analytics

The admin dashboard ([dashboard.blade.php](resources/views/admin/dashboard.blade.php)) now includes:

1. **API Usage by Provider**
   - Doughnut chart showing distribution of API calls
   - Breakdown by provider (Gemini, OpenAI, etc.)
   - Total API calls, tokens used, and estimated costs

2. **API Usage Trends (Last 7 Days)**
   - Line chart showing daily API usage
   - Separate trends for each provider
   - Helps identify usage patterns

3. **Top Documents by Interactions**
   - Horizontal bar chart of most-used documents
   - Shows which knowledge base documents are most valuable
   - Useful for content optimization

4. **Chat Activity (Last 30 Days)**
   - Area chart showing conversation volume over time
   - Helps track user engagement trends

5. **Enhanced Stats Cards**
   - Total Documents
   - Total Embeddings
   - Total Chats
   - Average Response Time
   - System Status

### 🔍 API Usage Tracking

Each bot response now tracks:
- **API Provider**: Which AI service was used (gemini, openai, etc.)
- **Tokens Used**: Number of tokens consumed
- **API Cost**: Estimated cost based on provider pricing
- **Response Time**: Milliseconds taken to generate response

### 📁 Database Schema

**Migration**: `2026_01_22_173217_add_api_usage_tracking_to_chats_table.php`

Added columns to `chats` table:
```php
$table->string('api_provider')->nullable();
$table->integer('api_tokens_used')->default(0);
$table->decimal('api_cost', 10, 6)->default(0);
$table->integer('response_time_ms')->nullable();
```

## Setup Instructions

### 1. Run Migration

```bash
php artisan migrate
```

This adds the API tracking columns to your `chats` table.

### 2. (Optional) Seed Sample Data

To see the analytics in action with test data:

```bash
php artisan db:seed --class=ChatAnalyticsSeeder
```

This creates 30 days of sample chat data with realistic API usage metrics.

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Usage

### Viewing Analytics

1. Log into the admin panel
2. Navigate to the Dashboard
3. View the analytics charts automatically

### API Cost Estimation

The system automatically calculates costs based on:
- **Gemini API**: $0.00025 per 1K tokens (example rate)
- **OpenAI API**: $0.0015 per 1K tokens for GPT-3.5 (example rate)

> **Note**: Update these rates in [ChatController.php](app/Http/Controllers/ChatController.php) to match your actual API pricing.

### Customizing Analytics

Edit [AdminController.php](app/Http/Controllers/AdminController.php) `getAnalyticsData()` method to:
- Change date ranges
- Add new metrics
- Filter data differently

## File Changes

### Modified Files

1. **app/Http/Controllers/AdminController.php**
   - Added `getAnalyticsData()` method
   - Enhanced dashboard controller with analytics

2. **app/Http/Controllers/ChatController.php**
   - Updated `generateAIResponse()` to return metrics
   - Modified `sendMessage()` to track API usage
   - Updated `processSessionMessages()` with tracking

3. **app/Models/Chat.php**
   - Added new fillable fields for API tracking

4. **resources/views/admin/dashboard.blade.php**
   - Complete redesign with Chart.js visualizations
   - 4 interactive charts
   - Enhanced stats cards

5. **resources/views/admin/layouts/app.blade.php**
   - Added Chart.js library (v4.4.1)

### New Files

1. **database/migrations/2026_01_22_173217_add_api_usage_tracking_to_chats_table.php**
   - Migration for API tracking columns

2. **database/seeders/ChatAnalyticsSeeder.php**
   - Sample data generator for testing

3. **ANALYTICS_README.md** (this file)
   - Complete documentation

## Chart.js Integration

The dashboard uses [Chart.js v4.4.1](https://www.chartjs.org/) for visualizations:

- **Doughnut Chart**: API provider distribution
- **Line Chart**: Usage trends over time
- **Bar Chart**: Top documents ranking
- **Area Chart**: Chat activity timeline

All charts are:
- Responsive and mobile-friendly
- Dark mode compatible
- Interactive with tooltips
- Automatically styled to match the theme

## API Pricing Configuration

To update API pricing, edit the `generateAIResponse()` method in [ChatController.php](app/Http/Controllers/ChatController.php):

```php
// For Gemini
$cost = ($tokens / 1000) * 0.00025; // Your actual rate

// For OpenAI
$cost = ($tokens / 1000) * 0.0015; // Your actual rate
```

## Token Estimation

Currently using a simple estimation:
```php
$tokens = (int)(strlen($prompt . $response) / 4);
```

For more accurate tracking, consider integrating:
- OpenAI's `tiktoken` library
- Provider-specific token counting APIs
- Actual token counts from API responses

## Future Enhancements

Potential improvements:
- [ ] Export analytics as CSV/PDF
- [ ] Real-time cost alerts
- [ ] Budget limits per provider
- [ ] User-level analytics
- [ ] Document popularity scores
- [ ] Response quality metrics
- [ ] A/B testing between providers
- [ ] Cost optimization recommendations

## Troubleshooting

### Charts not displaying?
- Check browser console for JavaScript errors
- Ensure Chart.js is loading: View page source and verify CDN link
- Clear browser cache

### No data in charts?
- Run the seeder: `php artisan db:seed --class=ChatAnalyticsSeeder`
- Check that migrations ran successfully
- Verify `api_provider` column exists in `chats` table

### Wrong cost calculations?
- Update pricing in `ChatController::generateAIResponse()`
- Verify token estimation logic
- Check decimal places in database column

## Support

For issues or questions:
1. Check the migration status: `php artisan migrate:status`
2. Review error logs: `storage/logs/laravel.log`
3. Verify database schema matches migration

## License

Same as parent project.
