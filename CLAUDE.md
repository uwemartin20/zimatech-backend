# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Zimatech is a Laravel 12 manufacturing/production management system with German localization. It tracks employee time on machines/projects, manages projects with components (Bauteile), handles supplier relationships, and generates project offers with PDF/email capabilities.

## Common Commands

```bash
# Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build

# Development
composer run dev    # Runs server + queue + vite concurrently

# Testing
composer run test   # Clears config and runs Pest tests
php artisan test   # Direct test run

# Code quality
./vendor/bin/pint   # Laravel code formatter
```

## Architecture

### Feature Modules
Features are controlled via `config/modules.php`:
- `teams` - User management with role-based access (admin/user)
- `projects` - Project and Bauteil (component) management
- `time` - Time recording with machine tracking
- `project_offers` - Quote generation with calculations
- `emails` - Email integration via Microsoft Graph + IMAP
- `suppliers` - Supplier management
- `settings` - Machine status and project settings

Routes check these flags: `if (config('modules.projects'))` before registering module routes.

### Key Models
- **TimeRecord** - Main time tracking (belongsTo User, Machine, Project)
- **TimeLog** - Individual log entries within a TimeRecord
- **TimeChangeRequest** - Approval workflow for time corrections
- **Project** - Projects with positions and services
- **Bauteil** - Components/parts with measurements
- **Machine** - Production machines with status tracking

### Controller Organization
- `app/Http/Controllers/` - User-facing controllers (TimeRecordController, ProjectController)
- `app/Http/Controllers/Admin/` - Admin-only controllers with `auth` + `role:admin` middleware

### Database
- Uses MySQL (configured in `.env`)
- Migrations in `database/migrations/`
- Use `php artisan migrate` for schema changes

## Key Integration Points

### Microsoft Graph (Outlook)
- Used for sending project offer emails
- Config: `config/services.php` with Azure AD credentials
- Kiota HTTP client for API calls

### IMAP Email
- `webklex/laravel-imap` for receiving emails
- Config: `config/imap.php`

### PDF/Excel Generation
- DomPDF for PDF generation (`barryvdh/laravel-dompdf`)
- PhpSpreadsheet for Excel exports
- PhpPresentation for PowerPoint (vendor use only)

## Localization
- German language throughout the UI
- Translation files in `lang/` directory
- Language switcher: `LanguageController` at `/language/{locale}`

## Git Workflow
- `main` - Production branch
- `staging` - Staging branch
- Feature branches created as needed