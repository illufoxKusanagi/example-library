# Full-Stack Laravel Architecture & Developer Playbook

A complete handbook for understanding the directory structure, knowing what is safe to modify versus off-limits, writing features and pages from scratch, and building production-ready modern Laravel 12/13 applications with Livewire 4, Flux UI, Tailwind CSS v4, Supabase, and Docker.

---

## Table of Contents

1. [Tech Stack Overview](#1-tech-stack-overview)
2. [Directory Anatomy & Permission Matrix](#2-directory-anatomy--permission-matrix)
3. [File Classification: Safe to Edit vs. Boilerplate](#3-file-classification-safe-to-edit-vs-boilerplate)
4. [How to Generate Pages & Components Manually](#4-how-to-generate-pages--components-manually)
   * [Pattern A: Standard Laravel Page (Controller + Blade + Route)](#pattern-a-standard-laravel-page-controller--blade--route)
   * [Pattern B: Reusable Blade Components (`<x-*>`)](#pattern-b-reusable-blade-components-x-)
   * [Pattern C: Livewire Reactive Components (`wire:*`)](#pattern-c-livewire-reactive-components-wire)
   * [Pattern D: Flux UI Components (`<flux:*>`)](#pattern-d-flux-ui-components-flux)
5. [Step-by-Step: Building an App From Scratch](#5-step-by-step-building-an-app-from-scratch)
6. [The 4-Step Feature Development Workflow (TODO Checklist)](#6-the-4-step-feature-development-workflow-todo-checklist)
7. [Essential Artisan CLI Cheatsheet](#7-essential-artisan-cli-cheatsheet)
8. [Testing & Quality Assurance (Pest & Pint)](#8-testing--quality-assurance-pest--pint)
9. [Docker, Render & Cloudflare Deployment](#9-docker-render--cloudflare-deployment)
10. [Hard-Earned Production Lessons & Traps Avoided](#10-hard-earned-production-lessons--traps-avoided)

---

## 1. Tech Stack Overview

* **Backend Framework**: Laravel 12/13 (PHP 8.5)
* **Reactive UI**: Livewire 4 & Flux UI v2 (headless, accessible Blade components)
* **Styling**: Tailwind CSS v4 with `@tailwindcss/vite`
* **Authentication**: Laravel Fortify (headless backend with Passkeys / WebAuthn & 2FA)
* **Database**: PostgreSQL (hosted on Supabase Singapore `ap-southeast-1`)
* **Web Server & Runtime**: FrankenPHP (Caddy Go web server embedded with PHP worker)
* **Containerization**: Multi-stage Docker image deployed on Render Free Tier
* **DNS & CDN**: Cloudflare (CNAME subdomain routing with Full SSL/TLS)

---

## 2. Directory Anatomy & Permission Matrix

Use this matrix as your reference for where you can create/edit files and what you must leave alone.

### Permission Legend
* 🟢 **CAN MODIFY / CREATE**: Safe for everyday development. This is where your application logic lives.
* 🟡 **MODIFY WITH CAUTION & INTENT**: System configuration, database migrations, and build files. Only change when intentionally configuring the environment or schema.
* 🔴 **NEVER TOUCH / OFF-LIMITS**: Managed by package managers, build tools, or framework internals. Manual edits will be overwritten, break auto-loading, or crash the application.

---

### Complete Project Directory Matrix

| Directory / File              | Status | What It Does                                              | Why You Can / Cannot Touch It                                                                                                      |
| :---------------------------- | :----: | :-------------------------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/`       |   🟢    | HTTP request controllers (`BookController.php`)           | **Safe.** Create new controllers or methods to handle user requests and return views or JSON.                                      |
| `app/Models/`                 |   🟢    | Eloquent ORM models (`Book.php`, `User.php`)              | **Safe.** Define database relationships, query scopes, and data mutators here.                                                     |
| `app/Http/Requests/`          |   🟢    | Form validation request classes                           | **Safe.** Keep controllers clean by placing validation rules and authorization checks here.                                        |
| `app/View/Components/`        |   🟢    | Class-based Blade components                              | **Safe.** Create backend logic for complex reusable Blade components.                                                              |
| `app/Actions/Fortify/`        |   🟢    | User authentication & lifecycle actions                   | **Safe.** Customize user registration, password updates, and 2FA handling.                                                         |
| `app/Providers/`              |   🟡    | Service registration & boot logic                         | **Edit with intent.** Use to register global listeners, force HTTPS, or bind singletons in the service container.                  |
| `bootstrap/app.php`           |   🟡    | Application bootstrapper & middleware configuration       | **Edit with intent.** Configure global middleware, exception handling, and routing paths here.                                     |
| `bootstrap/cache/`            |   🔴    | Framework runtime caches (`packages.php`, `services.php`) | **NEVER TOUCH.** Automatically created by Artisan. Clear using `php artisan optimize:clear`.                                       |
| `config/*.php`                |   🟡    | Configuration files (`database.php`, `fortify.php`, etc.) | **Edit with intent.** Read values via `config('app.name')`. Keep secrets in `.env`, never hardcode them here.                      |
| `database/migrations/`        |   🟡    | Timestamped database schema evolution                     | **Create new files.** Never edit past migrations that have already run in staging or production. Run `php artisan make:migration`. |
| `database/seeders/`           |   🟡    | Test and initial database data population                 | **Edit with intent.** Create seeders to fill tables with sample or default data (`DatabaseSeeder.php`).                            |
| `database/factories/`         |   🟢    | Fake data generators for automated tests                  | **Safe.** Create factories (`BookFactory.php`) to generate fake model instances for Pest tests.                                    |
| `public/`                     |   🟡    | Webroot containing entrypoint and public assets           | Contains `index.php` and `.htaccess`. Do not edit `index.php`.                                                                     |
| `public/build/`               |   🔴    | Vite compiled CSS & JS assets and `manifest.json`         | **NEVER TOUCH.** Generated automatically by `npm run build`. Any manual edit will be wiped out.                                    |
| `public/storage/`             |   🔴    | Symlink pointing to `storage/app/public/`                 | **NEVER TOUCH.** Created by `php artisan storage:link`. Do not manually create or edit files here.                                 |
| `resources/views/`            |   🟢    | Blade templates and layouts                               | **Safe.** Create all HTML views, partials, and layouts here.                                                                       |
| `resources/views/components/` |   🟢    | Custom reusable Blade components (`<x-*>`)                | **Safe.** Create your custom Blade components here (e.g., `app-logo.blade.php`).                                                   |
| `resources/views/flux/`       |   🟡    | Published Flux UI components                              | **Edit with caution.** Created by `php artisan flux:publish`. You can tweak styles here, but prefer standard Flux props.           |
| `resources/views/pages/`      |   🟢    | Livewire 4 Single-File Components (SFC)                   | **Safe.** Create reactive Livewire views with inline PHP (e.g., `⚡profile.blade.php`).                                             |
| `resources/css/`              |   🟢    | Source CSS (`app.css`)                                    | **Safe.** Configure Tailwind directives and custom CSS styles here.                                                                |
| `resources/js/`               |   🟢    | Client-side JavaScript (`app.js`)                         | **Safe.** Register custom client-side libraries, passkey scripts, or Alpine plugins here.                                          |
| `routes/web.php`              |   🟢    | Web route definitions                                     | **Safe.** Register all browser URLs, controller mappings, and middleware groups here.                                              |
| `routes/console.php`          |   🟢    | Artisan CLI command routes                                | **Safe.** Register custom scheduled tasks and terminal commands here.                                                              |
| `routes/settings.php`         |   🟢    | User settings & passkey endpoints                         | **Safe.** Custom settings routing for profile, appearance, and security.                                                           |
| `storage/app/public/`         |   🟢    | User uploads (e.g. `covers/`)                             | **Safe.** File storage directory accessible through `public/storage/`.                                                             |
| `storage/framework/`          |   🔴    | Sessions, caches, and compiled views                      | **NEVER TOUCH.** Managed entirely by Laravel runtime. Clear using `php artisan view:clear`.                                        |
| `storage/logs/`               |   🟡    | Application log files (`laravel.log`)                     | Read for debugging. Do not write code here.                                                                                        |
| `tests/`                      |   🟢    | Pest automated tests (`tests/Feature/`, `tests/Unit/`)    | **Safe.** Write tests for every feature to guarantee zero regressions.                                                             |
| `vendor/`                     |   🔴    | Composer PHP packages                                     | **NEVER TOUCH.** Managed by Composer. Edits are lost on `composer install` or `composer update`.                                   |
| `node_modules/`               |   🔴    | NPM JavaScript & CSS packages                             | **NEVER TOUCH.** Managed by NPM. Edits are lost on `npm install`.                                                                  |
| `Dockerfile`                  |   🟡    | Production container definition                           | **Edit with intent.** Only touch when adding system packages or changing build stages.                                             |
| `docker/entrypoint.sh`        |   🟡    | Container boot script                                     | **Edit with intent.** Manages migrations, caching, and server startup on Render.                                                   |
| `render.yaml`                 |   🟡    | Render Cloud blueprint                                    | **Edit with intent.** Specifies environment variables, health checks, and build commands.                                          |
| `.env`                        |   🟡    | Local environment variables & secrets                     | **Local only.** Never commit to git. Contains database credentials and API keys.                                                   |
| `.env.example`                |   🟡    | Template for `.env`                                       | **Edit with intent.** Update whenever a new environment variable is introduced.                                                    |

---

## 3. File Classification: Safe to Edit vs. Boilerplate

### 🟢 1. Application Code (Safe to create, modify, and delete)
These files represent your unique business logic:
* `routes/web.php` — URL routes.
* `app/Http/Controllers/*.php` — Controller actions.
* `app/Http/Requests/*.php` — Form Request validation classes.
* `app/Models/*.php` — Eloquent models and relationships.
* `resources/views/**/*.blade.php` — Blade templates, partials, and layouts.
* `resources/css/app.css` — Styling and theme configuration.
* `resources/js/app.js` — Client-side scripts.
* `tests/Feature/*.php` — Feature tests.

### 🟡 2. Configuration & Infrastructure (Edit deliberately)
Touch these files only when making structural changes:
* `config/*.php` — Framework configuration.
* `database/migrations/*.php` — Schema migrations. **Rule:** Only create NEW migrations. Never edit migrations that have already run in production.
* `database/seeders/*.php` — Database seeding.
* `Dockerfile` & `docker/entrypoint.sh` — Multi-stage build and container runtime logic.
* `render.yaml` — Infrastructure as Code specification for Render.
* `composer.json` & `package.json` — Dependency management. Use `composer require` or `npm install` rather than editing manually when possible.

### 🔴 3. Boilerplate & Framework Core (Never touch)
* `public/index.php` — The HTTP request entry point for PHP.
* `public/build/*` — Generated Vite assets (CSS, JS, manifest).
* `vendor/*` — Upstream PHP packages.
* `node_modules/*` — Upstream Node.js packages.
* `bootstrap/cache/*` — Compiled PHP files for bootstrapping.

---

## 4. How to Generate Pages & Components Manually

This section provides practical, copy-pasteable patterns for generating pages and components from scratch.

---

### Pattern A: Standard Laravel Page (Controller + Blade + Route)

Follow these 4 steps whenever you need a new page in your application (e.g. an "Authors" directory):

#### 1. Generate the Controller
Run the Artisan CLI command:
```bash
php artisan make:controller AuthorController
```
> [!TIP]
> Use `--resource` to automatically scaffold all CRUD methods (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`):
> ```bash
> php artisan make:controller AuthorController --resource
> ```
> Use `--invokable` for single-action controllers:
> ```bash
> php artisan make:controller DashboardController --invokable
> ```

#### 2. Implement the Controller Action
In `app/Http/Controllers/AuthorController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors.
     */
    public function index(): View
    {
        $authors = Author::query()
            ->withCount('books')
            ->orderBy('name')
            ->paginate(12);

        return view('authors.index', compact('authors'));
    }
}
```

#### 3. Register the Route
In `routes/web.php`:
```php
use App\Http\Controllers\AuthorController;

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
```

#### 4. Create the Blade View
Create `resources/views/authors/index.blade.php`:
```blade
<x-layouts::app :title="__('Authors Directory')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Authors') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Discover authors and their published works.') }}</flux:text>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($authors as $author)
                <flux:card class="flex flex-col justify-between">
                    <div>
                        <flux:heading size="lg">{{ $author->name }}</flux:heading>
                        <flux:badge size="sm" color="zinc" class="mt-2">
                            {{ $author->books_count }} {{ trans_choice('Book|Books', $author->books_count) }}
                        </flux:badge>
                    </div>
                    <div class="mt-4">
                        <flux:button :href="route('authors.show', $author)" variant="subtle" size="sm">
                            {{ __('View Profile') }}
                        </flux:button>
                    </div>
                </flux:card>
            @empty
                <div class="col-span-full py-12 text-center">
                    <flux:text>{{ __('No authors found.') }}</flux:text>
                </div>
            @endforelse
        </div>

        <div>
            {{ $authors->links() }}
        </div>
    </div>
</x-layouts::app>
```

---

### Pattern B: Reusable Blade Components (`<x-*>`)

Blade components let you encapsulate reusable HTML chunks without any JavaScript.

#### 1. Anonymous Blade Components (Fastest & Simplest)
Place a Blade file directly in `resources/views/components/`. The file name determines the component tag name.

Example: Create `resources/views/components/status-pill.blade.php`:
```blade
@props([
    'status' => 'pending', // default value
])

@php
$classes = match ($status) {
    'active', 'returned' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
    'borrowed' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    'overdue' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
    default => 'bg-zinc-50 text-zinc-700 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-700',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {$classes}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>
    {{ $slot }}
</span>
```

#### How to use it in any view:
```blade
<x-status-pill status="borrowed">
    {{ __('Borrowed') }}
</x-status-pill>

{{-- Additional classes can be passed and are automatically merged --}}
<x-status-pill status="overdue" class="shadow-xs">
    {{ __('Overdue by 3 days') }}
</x-status-pill>
```

#### 2. Class-Based Blade Components (For Complex Logic)
Use when the component needs to fetch data from the database or run complex PHP logic:
```bash
php artisan make:component BookCard
```
This generates:
* `app/View/Components/BookCard.php` (PHP class)
* `resources/views/components/book-card.blade.php` (Blade template)

In `app/View/Components/BookCard.php`:
```php
namespace App\View\Components;

use App\Models\Book;
use Illuminate\View\Component;
use Illuminate\View\View;

class BookCard extends Component
{
    public function __construct(public Book $book) {}

    public function render(): View
    {
        return view('components.book-card');
    }
}
```

Use it in Blade:
```blade
<x-book-card :book="$book" />
```

---

### Pattern C: Livewire Reactive Components (`wire:*`)

Livewire allows you to build reactive interfaces with PHP instead of writing custom JavaScript.

#### Method 1: Livewire 4 Single-File Components (SFC)
Livewire 4 supports Single-File Components where PHP logic and Blade markup live in one file.
In this app, settings pages use this convention (e.g. `resources/views/pages/settings/⚡profile.blade.php`).

Create `resources/views/pages/⚡book-search.blade.php`:
```blade
<?php

use App\Models\Book;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    public function render()
    {
        $results = strlen($this->query) >= 2
            ? Book::where('title', 'ilike', "%{$this->query}%")->take(5)->get()
            : collect();

        return view('pages.⚡book-search', [
            'results' => $results,
        ]);
    }
};
?>

<div class="relative">
    <flux:input
        wire:model.live.debounce.300ms="query"
        placeholder="Type to search books instantly..."
        icon="magnifying-glass"
        clearable
    />

    @if ($results->isNotEmpty())
        <div class="absolute z-50 mt-2 w-full rounded-xl border border-zinc-200 bg-white p-2 shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            @foreach ($results as $book)
                <a href="{{ route('books.show', $book) }}" class="flex items-center justify-between rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    <span class="font-medium text-sm">{{ $book->title }}</span>
                    <flux:badge size="sm">{{ $book->stock }} left</flux:badge>
                </a>
            @endforeach
        </div>
    @endif
</div>
```

#### Method 2: Standard Livewire Class + View
Scaffold with Artisan:
```bash
php artisan make:livewire BookFilter
```
This generates:
* `app/Livewire/BookFilter.php`
* `resources/views/livewire/book-filter.blade.php`

Render it inside any Blade view:
```blade
<livewire:book-filter :category-id="$category->id" />
```

---

### Pattern D: Flux UI Components (`<flux:*>`)

Flux UI provides pre-built, accessible, beautifully styled components configured with Tailwind CSS v4.

#### Essential Flux Component Cheatsheet

| Component   | Example Code                                                                               | Use Case                                                          |
| :---------- | :----------------------------------------------------------------------------------------- | :---------------------------------------------------------------- |
| **Button**  | `<flux:button variant="primary" icon="plus">Submit</flux:button>`                          | Primary and secondary buttons, actions, or links (`:href="..."`). |
| **Input**   | `<flux:input name="title" label="Title" placeholder="Book title" clearable />`             | Text fields with automatic label, error, and icon binding.        |
| **Select**  | `<flux:select name="role"><option value="admin">Admin</option></flux:select>`              | Native and custom styled dropdowns.                               |
| **Modal**   | `<flux:modal name="confirm-modal"><flux:heading>Are you sure?</flux:heading></flux:modal>` | Dialogs and popups controlled via `flux:modal.open`.              |
| **Card**    | `<flux:card class="space-y-4"><flux:heading>Info</flux:heading></flux:card>`               | Content containers with unified border and dark mode colors.      |
| **Badge**   | `<flux:badge color="emerald" size="sm">Available</flux:badge>`                             | Category tags, counts, and status labels.                         |
| **Heading** | `<flux:heading size="xl" level="1">Catalog</flux:heading>`                                 | Semantic, accessible headings with pre-tuned font sizes.          |
| **Icon**    | `<flux:icon name="book-open" class="size-5" />`                                            | Heroicons/Lucide icons integrated directly.                       |

#### Complete Flux Form & Modal Example:
```blade
{{-- Button that triggers the modal --}}
<flux:button variant="primary" icon="arrow-path" x-on:click="$flux.modal('return-modal').show()">
    {{ __('Return Book') }}
</flux:button>

{{-- Modal definition --}}
<flux:modal name="return-modal" class="md:w-96">
    <form method="POST" action="{{ route('books.return', $book) }}" class="space-y-4">
        @csrf
        <div>
            <flux:heading size="lg">{{ __('Confirm Return') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Are you sure you want to mark ":title" as returned?', ['title' => $book->title]) }}
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="subtle" x-on:click="$flux.modal('return-modal').close()">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Confirm Return') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
```

---

## 5. Step-by-Step: Building an App From Scratch

If starting this project from a blank directory, follow these sequential phases:

### Phase 1: Initialize Project & Frontend
```bash
# 1. Create a fresh Laravel 12/13 project
composer create-project laravel/laravel example-app
cd example-app

# 2. Install Livewire & Flux UI
composer require livewire/livewire livewire/flux

# 3. Publish Flux icons and components
php artisan flux:publish

# 4. Install Tailwind CSS v4 and Vite dependencies
npm install tailwindcss @tailwindcss/vite @laravel/passkeys
```

Configure `vite.config.js`:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

Import Flux in `resources/css/app.css`:
```css
@import "tailwindcss";
@import "../../vendor/livewire/flux/dist/flux.css";
```

### Phase 2: Database Schema & Models
Generate the models, migrations, factories, and controllers in one step:
```bash
# -m: migration, -s: seeder, -f: factory, -c: controller, -r: resource
php artisan make:model Category -ms
php artisan make:model Book -mscr
php artisan make:model RentLog -m
```

1. **Define Columns in Migrations** (`database/migrations/`):
   ```php
   Schema::create('books', function (Blueprint $table) {
       $table->id();
       $table->string('code')->unique();
       $table->string('title');
       $table->string('author');
       $table->integer('stock')->default(0);
       $table->string('cover_image')->nullable();
       $table->timestamps();
   });
   ```
2. **Define Relationships in Models** (`app/Models/`):
   ```php
   // Book.php
   public function categories(): BelongsToMany
   {
       return $this->belongsToMany(Category::class);
   }

   public function rentLogs(): HasMany
   {
       return $this->hasMany(RentLog::class);
   }
   ```
3. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

### Phase 3: Authentication & Roles
1. **Configure Fortify** (`config/fortify.php`):
   * Enable features: `Features::registration()`, `Features::resetPasswords()`, `Features::twoFactorAuthentication()`.
2. **Add Roles to User Model**:
   * Add a `role` column (`string`, default `'client'`) in a migration.
   * Add helper methods on `User`:
     ```php
     public function isAdmin(): bool { return $this->role === 'admin'; }
     public function isClient(): bool { return $this->role === 'client'; }
     ```

### Phase 4: Business Logic & Controllers
1. **Form Validation**: Create dedicated Form Requests:
   ```bash
   php artisan make:request StoreBookRequest
   ```
2. **Database Transactions**: Wrap multi-table operations inside `DB::transaction()`:
   ```php
   DB::transaction(function () use ($book, $user) {
       RentLog::create([
           'user_id' => $user->id,
           'book_id' => $book->id,
           'rent_date' => now(),
           'return_date' => now()->addDays(7),
       ]);
       $book->decrement('stock');
   });
   ```

---

## 6. The 4-Step Feature Development Workflow (TODO Checklist)

Whenever adding any new feature (e.g. "Add Book Reviews"), copy and check off this workflow:

```text
[ ] STEP 1: DATABASE ARCHITECTURE
    ├── [ ] Create migration: php artisan make:migration create_reviews_table
    ├── [ ] Define columns, foreign keys, and indexes in migration file
    ├── [ ] Create Eloquent Model: php artisan make:model Review
    ├── [ ] Set up relationships (e.g., $book->reviews(), $user->reviews())
    └── [ ] Run migration: php artisan migrate

[ ] STEP 2: BUSINESS LOGIC & ROUTING
    ├── [ ] Create Form Request: php artisan make:request StoreReviewRequest
    ├── [ ] Define validation rules (e.g. rating min 1 max 5, comment required)
    ├── [ ] Create Controller action: ReviewController::store()
    └── [ ] Register named route in routes/web.php with auth middleware

[ ] STEP 3: USER INTERFACE (VIEWS & COMPONENTS)
    ├── [ ] Create Blade view or Livewire component: resources/views/books/_reviews.blade.php
    ├── [ ] Use <flux:card>, <flux:field>, <flux:textarea>, <flux:button>
    └── [ ] Ensure validation errors render cleanly with <flux:error>

[ ] STEP 4: VERIFICATION & QUALITY ASSURANCE
    ├── [ ] Create Pest test: php artisan make:test --pest ReviewTest
    ├── [ ] Test happy path (review created) and failure path (validation error)
    ├── [ ] Run tests: php artisan test --filter=ReviewTest
    └── [ ] Format code: vendor/bin/pint --dirty --format agent
```

---

## 7. Essential Artisan CLI Cheatsheet

Keep this list handy for generating code instead of creating files by hand:

### Generating Code (`make:*`)
```bash
# Controllers
php artisan make:controller BookController             # Basic controller
php artisan make:controller BookController --resource  # Full CRUD controller
php artisan make:controller ShowDashboard --invokable  # Single-action controller

# Models & Database
php artisan make:model Book -mscr                      # Model + Migration + Seeder + Controller
php artisan make:migration add_isbn_to_books_table     # Schema migration
php artisan make:seeder CategorySeeder                 # Seeder class
php artisan make:factory BookFactory --model=Book      # Test model factory

# Requests & Validation
php artisan make:request StoreBookRequest              # Form request validation class

# Views & Components
php artisan make:component Alert                       # Class-based Blade component
php artisan make:livewire SearchBooks                  # Livewire component (Class + View)

# Testing
php artisan make:test --pest BookCatalogTest           # Pest Feature test
php artisan make:test --pest BookUnitTest --unit       # Pest Unit test
```

### Clearing & Caching for Production
```bash
# Clear all caches during development
php artisan optimize:clear

# Re-cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 8. Testing & Quality Assurance (Pest & Pint)

All 60 tests in this project must pass before deploying.

### Running Tests
```bash
# Run all tests with compact output
php artisan test --compact

# Run a specific test file
php artisan test tests/Feature/RentSystemTest.php

# Filter by a specific test name
php artisan test --filter="user_can_borrow_an_available_book"
```

### Writing a Pest Feature Test
In `tests/Feature/RentSystemTest.php`:
```php
test('authenticated user can borrow an available book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create(['stock' => 5]);

    $response = $this->actingAs($user)
        ->post(route('books.borrow', $book));

    $response->assertRedirect();
    expect($book->fresh()->stock)->toBe(4);
    $this->assertDatabaseHas('rent_logs', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);
});
```

### Code Formatting with Pint
Always run Pint before committing:
```bash
vendor/bin/pint --dirty --format agent
```

---

## 9. Docker, Render & Cloudflare Deployment

### Multi-Stage Dockerfile Strategy
To keep the container lightweight and secure:
1. **Stage 1 (`composer:2`)**: Installs PHP vendor dependencies with `--no-dev` and `--optimize-autoloader`.
2. **Stage 2 (`node:22-bookworm-slim`)**: Copies `vendor/` from Stage 1 (required because Flux CSS lives in `vendor/livewire/flux/dist/flux.css`) and runs `npm run build`.
3. **Stage 3 (`dunglas/frankenphp:1-php8.5-bookworm`)**: The final production runner. Installs PHP extensions (`pdo_pgsql`, `opcache`), strips file capabilities with `setcap -r`, copies production assets, and binds to `0.0.0.0:10000`.

### Container Entrypoint (`docker/entrypoint.sh`)
When the container boots on Render:
1. Binds FrankenPHP to `0.0.0.0:${PORT:-10000}`.
2. Disables Caddy Admin API (`CADDY_GLOBAL_OPTIONS="admin off"`).
3. Generates a fallback `APP_KEY` if missing from environment variables.
4. Discovers packages: `php artisan package:discover --ansi`.
5. Links storage: `php artisan storage:link --force`.
6. Migrates database: `php artisan migrate --force`.
7. Caches config, routes, and views in production (clears them in local development).
8. Launches `frankenphp run`.

---

## 10. Hard-Earned Production Lessons & Traps Avoided

Save yourself days of debugging by remembering these 8 real-world solutions:

### Trap 1: Missing Vendor in Frontend Docker Build
* **Problem**: `resources/css/app.css` imports `@import '../../vendor/livewire/flux/dist/flux.css'`. If the Node stage runs before Composer, `npm run build` crashes because `vendor/` does not exist yet.
* **Solution**: Stage 1 must run `composer install`. Stage 2 (Node) copies `--from=composer /app/vendor ./vendor` *before* running `npm run build`.

### Trap 2: Vite-Plus / Rolldown Crash in Linux Containers
* **Problem**: Using `"build": "vp build"` (vite-plus/rolldown) throws `aggregateBindingErrorsIntoJsError` in Linux containers.
* **Solution**: Use standard `"build": "vite build"` with `import { defineConfig } from 'vite'` in `vite.config.js`.

### Trap 3: FrankenPHP `Operation not permitted` (Exit Status 126)
* **Problem**: Official FrankenPHP image bakes `CAP_NET_BIND_SERVICE` into the binary. Render runs containers with `PR_SET_NO_NEW_PRIVS`. Linux kernel blocks execution of setcap binaries under `no_new_privs`.
* **Solution**: Add `RUN setcap -r /usr/local/bin/frankenphp` in the Dockerfile to strip the capability.

### Trap 4: Render Port Detection Failing on `0.0.0.0`
* **Problem**: FrankenPHP default binding listens on IPv6 wildcard `[::]:10000`. Render's port scanner scans IPv4 `0.0.0.0`, finds no IPv4 socket, and times out with `No open HTTP ports detected on 0.0.0.0`.
* **Solution**: Force IPv4 binding with `ENV CADDY_SERVER_EXTRA_DIRECTIVES="bind 0.0.0.0"` and declare `EXPOSE 10000 80`.

### Trap 5: Caddy Admin Port 2019 Conflict
* **Problem**: Caddy opens an internal Admin API on port 2019. Render's port scanner probed port 2019, received `403 Forbidden` (`host not allowed`), and failed the deployment.
* **Solution**: Add `ENV CADDY_GLOBAL_OPTIONS="admin off"` to disable port 2019.

### Trap 6: Missing `APP_KEY` Fatal Crash
* **Problem**: If Render does not have `APP_KEY` set in its dashboard, Laravel crashes on boot with `MissingAppKeyException`.
* **Solution**: In `docker/entrypoint.sh`, automatically generate a fallback key on container boot if `APP_KEY` is empty:
  ```bash
  if [ -z "$APP_KEY" ]; then
      export APP_KEY=$(php artisan key:generate --show --no-interaction)
  fi
  ```

### Trap 7: Missing CSS on Live Site (Mixed Content Block)
* **Problem**: Render's reverse proxy terminates SSL and forwards requests to the container over HTTP. Laravel generates `http://...` asset links, which browsers block as Mixed Content on an HTTPS site.
* **Solution**:
  1. Add `$middleware->trustProxies(at: '*');` in `bootstrap/app.php`.
  2. Add `URL::forceScheme('https');` in `app/Providers/AppServiceProvider.php`.

### Trap 8: Cloudflare SSL Redirect Loop (`ERR_TOO_MANY_REDIRECTS`)
* **Problem**: Setting Cloudflare SSL to "Flexible" mode encrypts traffic to the user but contacts Render over plain HTTP, causing an infinite redirect loop.
* **Solution**: Always set Cloudflare SSL/TLS encryption mode to **Full** or **Full (strict)**.
