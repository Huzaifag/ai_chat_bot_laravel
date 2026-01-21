# Chatbot Backend Documentation

This project is a Laravel-based backend for a KnowledgeBase Chatbot with admin management, document ingestion, and AI provider integration.

## Features

- Admin authentication and dashboard
- Document upload, chunking, and retrieval
- AI API configuration (OpenAI, Gemini, Anthropic, etc.)
- Chat session and chat log management
- Background job processing for document handling
- Secure API key storage (masked in admin panel)
- Modular service-based architecture

## Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL or compatible database
- Node.js & npm (for asset compilation)

### Installation
1. Clone the repository:
   ```bash
   git clone <repo-url>
   cd chatbot-backend
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JS dependencies:
   ```bash
   npm install && npm run build
   ```
4. Copy and configure environment:
   ```bash
   cp .env.example .env
   # Edit .env for your DB and mail settings
   php artisan key:generate
   ```
5. Run migrations and seeders:
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminSeeder
   ```
6. (Optional) Link storage:
   ```bash
   php artisan storage:link
   ```

### Running the App
- Start the server:
  ```bash
  php artisan serve
  ```
- Access the admin panel at: `http://localhost:8000/admin/login`

### Default Admin Credentials
- Email: `admin@botadmin.ai`
- Password: `password`

## Usage
- Upload documents via the admin panel
- Configure AI API keys in **Admin > AI API Configurations**
- Start chat sessions and view chat logs

## Project Structure
- `app/Models/` - Eloquent models (Admin, User, Document, etc.)
- `app/Http/Controllers/` - Route controllers
- `app/Services/` - Core business logic (AI, chunking, retrieval)
- `app/Jobs/` - Background jobs (e.g., document processing)
- `resources/views/` - Blade templates for admin UI
- `routes/web.php` - Web and admin routes
- `database/migrations/` - DB schema

## AI Provider Integration
- Add or edit API keys for OpenAI, Gemini, Anthropic, etc. in the admin panel
- API keys are masked and securely stored
- Switch between providers for chat and document processing

## Security
- Admin routes protected by authentication middleware
- API keys are never shown in plain text after saving
- CSRF protection enabled

## Testing
- Run tests with:
  ```bash
  php artisan test
  ```

## License
MIT

---
For more details, see inline code comments and each service's documentation.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Admin Login System

This application includes a secure admin login system for managing the KnowledgeBase chatbot.

### Admin Credentials

- **Email**: `admin@botadmin.ai`
- **Password**: `password`

### Features

- Secure authentication with separate admin guard
- Protected admin routes with middleware
- Beautiful login interface with dark mode support
- Session management and CSRF protection
- Automatic redirect to dashboard after login

### Admin Routes

- `/admin/login` - Admin login page
- `/admin/dashboard` - Admin dashboard (protected)
- `/admin/documents` - Document management (protected)
- `/admin/upload` - File upload (protected)
- `/admin/chatlogs` - Chat logs (protected)

### Setup

1. Run migrations: `php artisan migrate`
2. Seed admin user: `php artisan db:seed --class=AdminSeeder`
3. Visit `/admin/login` to access the admin panel

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## About the Author

**Muhammad Huzaifa Gulzar**

Muhammad Huzaifa Gulzar is a passionate full-stack developer specializing in Laravel and modern web technologies. He designed and built this chatbot backend project to provide a robust, scalable, and secure platform for AI-powered knowledge management. With a strong focus on clean architecture, usability, and security, Huzaifa brings innovative solutions to complex problems and is dedicated to delivering high-quality software.

For collaboration, feedback, or professional inquiries, feel free to reach out!
