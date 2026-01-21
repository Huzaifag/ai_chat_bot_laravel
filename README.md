# KnowledgeBase AI Chatbot Backend

A **Laravel-based AI-powered KnowledgeBase chatbot backend** that allows admins to upload documents on various topics and enables users to chat with an AI that answers **strictly based on those documents**.

Built with scalability, security, and multi-AI support in mind.

---

## 👨‍💻 Author

**Huzaifa Gulzar**
Software Engineer
Focus areas: Backend Engineering, Laravel, AI Integrations, Scalable Systems

---

## 🚀 Overview

This project provides a **complete backend system** for building document-driven AI chatbots.
Admins can upload documents, configure multiple AI providers, and manage chat history, while users can select their interests and interact with an AI assistant trained on the uploaded knowledge.

The system is **AI-provider agnostic** and supports fallback strategies to ensure reliability.

---

## ✨ Core Features

* Secure **Admin Authentication & Dashboard**
* Document upload (PDF, DOCX, TXT, CSV)
* Automatic **text extraction and chunking**
* Multiple document retrieval strategies (no vector dependency)
* AI provider integration:

  * OpenAI
  * Google Gemini
  * Anthropic (Claude)
* AI fallback mechanism (provider switch on failure)
* Chat session & message persistence
* Background job processing for heavy tasks
* Secure API key storage (encrypted & masked)
* Modular, service-based Laravel architecture

---

## 🧠 System Architecture (High Level)

1. Admin uploads documents via dashboard
2. Documents are stored securely
3. Background jobs:

   * Extract text
   * Split content into chunks
4. Chunks are indexed for retrieval
5. User starts a chat session
6. Chat engine:

   * Retrieves relevant document context
   * Builds structured AI prompt
   * Calls selected AI provider
   * Falls back if provider fails
7. Response is stored and returned to frontend

---

## 📚 Document Retrieval Strategy (No Vector Dependency)

### **Option A: Full Context Mode**

* Used for small documents
* Entire document or large chunks are injected into the AI prompt

### **Option B: Keyword Search Mode**

* Used for large documents
* Relevant chunks selected via:

  * SQL `LIKE`
  * MySQL `FULLTEXT`
* Only relevant chunks are added to prompt

This approach keeps the system **simple, cost-efficient, and fast** without requiring vector databases.

---

## 🔁 AI Provider Fallback Logic

* Primary AI provider is selected per topic or globally
* If the provider fails (quota, timeout, API error):

  * System automatically retries using a secondary provider
* Failures are logged for observability

---

## 🛠️ Tech Stack

* **Backend**: Laravel (PHP 8.1+)
* **Database**: MySQL
* **Queue System**: Laravel Queues (database / Redis)
* **Frontend (Admin)**: Blade + Tailwind CSS
* **AI Providers**: OpenAI, Gemini, Anthropic
* **File Storage**: Laravel Filesystem (private disk)

---

## ⚙️ Installation

### Prerequisites

* PHP 8.1+
* Composer
* MySQL
* Node.js & npm

### Setup

```bash
git clone <repo-url>
cd chatbot-backend
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file.

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
php artisan storage:link
php artisan serve
```

---

## 🔐 Default Admin Credentials

* **Email**: `admin@botadmin.ai`
* **Password**: `password`

Access admin panel:

```
http://localhost:8000/admin/login
```

---

## 🧪 Testing

```bash
php artisan test
```

---

## 🧩 Project Structure

```
app/
 ├── Models/
 ├── Http/Controllers/
 ├── Services/
 ├── Jobs/
database/
 ├── migrations/
resources/
 ├── views/
routes/
 └── web.php
```

---

## 🔑 Environment Variables

```env
DB_DATABASE=chatbot
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

OPENAI_API_KEY=
GEMINI_API_KEY=
ANTHROPIC_API_KEY=
```

---

## 🔒 Security Considerations

* Admin routes protected via authentication middleware
* API keys encrypted at rest
* API keys masked in admin UI
* File uploads validated and sanitized
* CSRF protection enabled
* User input sanitized before AI processing

---

## 💬 Chat Flow Example

1. User selects topic (e.g., *Medical*)
2. System loads associated documents
3. User sends a message
4. Backend:

   * Fetches chat history
   * Retrieves document context
   * Builds AI prompt
   * Calls AI provider
5. Response is saved and returned

---

## 📈 Future Enhancements

* Vector DB support (FAISS / Pinecone / Chroma)
* Streaming AI responses
* Multi-language document support
* Usage analytics dashboard
* Role-based access control
* Public API for external integrations

---

## 📜 License

MIT License

---

## 🌟 Final Note

This project is designed as a **production-ready foundation** for building intelligent, document-aware AI chatbots.
It demonstrates strong backend architecture, AI integration patterns, and scalable design principles.


