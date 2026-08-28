## Project Expedition - Laravel Candidate Assignment

## Guidelines

- **Time** — Please limit yourself to 3-4 hours of focused work. We want to see what you can accomplish in a realistic window, not how many hours you can pour in. Use `DISCUSSION.md` to document your thought process, any tradeoffs you made, and questions or comments you have about this assessment.

- **AI Tools** — Please refrain from using AI-assisted coding tools for this assignment. Project Expedition is an AI-forward company and we use these tools daily. However, we also value deep understanding. In order to properly assess your skills, there will be a short live coding exercise during the technical interview where we'll explore your work together.

---

This is a [Laravel](https://laravel.com/) project using [Livewire](https://livewire.laravel.com/) for reactive UI components.

## Requirements

- PHP 8.3 or newer with the `pdo_mysql`, `pdo_sqlite`, `mbstring`, `intl` and `bcmath` extensions
- Composer 2
- Node 22 (Vite 8 needs Node 20.19 or newer)
- MySQL 8, either from the provided Docker Compose file or a local server

## Getting Started

1. Install PHP dependencies:

```bash
composer install
```

2. Create your environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Start MySQL:

```bash
docker compose up -d
```

The `DB_*` values in `.env.example` already match this container. If you use your own MySQL server, create the `pe_destinations` database and update the `DB_*` values in `.env`.

4. Run migrations and seed the destinations:

```bash
php artisan migrate --seed
```

5. Install front-end dependencies and build assets:

```bash
npm install
npm run build
```

6. Run the development server:

```bash
php artisan serve
```

For live asset rebuilding during development:

```bash
npm run dev
```

`composer run setup` runs all of the steps above in one go.

## Tests and Checks

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Tests use an in-memory SQLite database, so they do not need MySQL. The same checks run in GitHub Actions on every push and pull request.

---

## Assignment

This app is a simple tool for browsing travel destinations offered by Project Expedition. The current implementation works — or at least it appears to — but the codebase has issues across every layer. Your job is to take this from a rough prototype to something you'd be comfortable shipping to production.

Use a **branch-per-section** workflow. Create a new branch for each section below and open a PR (or be ready to walk through your commits).

### Section 1: Project Setup

Set up the development infrastructure a real project needs. What you choose to include — and what you leave out — will matter.

### Section 2: Database & API

The app currently relies on hardcoded data. Make it real — connect it to the database, and make the API endpoints production-worthy.

### Section 3: User Interface

The `DestinationExplorer` component has problems. Review it thoroughly — there are bugs, code quality issues, and UX shortcomings throughout. Fix what you find and improve the overall component architecture.

### Section 4: Authentication (Bonus)

The API currently has no access control. Add appropriate protections.

### DISCUSSION.md

As you work, keep notes in `DISCUSSION.md`. We want to understand your thought process:

- What tradeoffs did you make and why?
- What would you do differently with more time?
- Any architectural decisions worth calling out?

This is as important as the code. We want to see how you think.

---

## What We're Looking For

- **Code quality** — Clean, readable, well-organized code.
- **Problem-solving** — Can you identify issues and fix them thoughtfully?
- **Full-stack thinking** — Comfortable across the database, API, and UI layers.
- **Laravel proficiency** — Proper use of Eloquent, Livewire, Blade, middleware, and Laravel conventions.
- **Communication** — Your DISCUSSION.md and commit messages tell us how you work.

Good luck. Show us what you've got.

---

## Submission

When you are finished, please submit one of the following:

1. A **zip file** of your completed project
2. A **link to your GitHub repo**

Please send your submission to:
- steve@projectexpedition.com
- eunice@projectexpedition.com
