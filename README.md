# intricare_demo

# 📇 Laravel Contact Management System (Docker Setup)

A **Laravel 12 Contact Management System** built with:
- Modern Laravel Blade & AJAX-based UI
- Contact merging with custom field logic
- Merge logs and audit trails
- Docker-powered setup (PHP + Apache + MySQL + phpMyAdmin)

This project is fully containerized and runs seamlessly on any environment with Docker.

---

## 🚀 Features

✅ AJAX-powered Contact CRUD  
✅ Dynamic Filtering (Name, Email, Gender)  
✅ Profile Image & File Uploads  
✅ Extensible Custom Fields System  
✅ Merge Contacts with Conflict Resolution  
✅ Merge Policies (Keep Master / Use Secondary / Combine Both)  
✅ Structured Merge Logs with History  
✅ phpMyAdmin Integration  
✅ Ready-to-Deploy Docker Environment

---

## 🧱 Prerequisites

Before starting, make sure you have these installed:

- [Docker](https://www.docker.com/get-started)
- Docker Compose v2+
- [Git](https://git-scm.com/)

---

## 🐳 Docker Setup Steps

Follow these simple steps to get the project running in Docker.

---

### ⚙️ Step 1. Clone the Repository

git clone https://github.com/yourusername/intricare_demo.git
cd intricare_demo

cp src/.env.example src/.env

update mysql username password


docker exec -it laravel_app bash

composer install
php artisan key:generate
php artisan migrate

If you get storage or cache permission errors:
chmod -R 777 storage bootstrap/cache


docker compose up -d --build

