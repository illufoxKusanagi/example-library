# Missing Features — example-app vs. library_project

Gap analysis comparing `example-app` (modern Laravel 12/13 + Livewire 4 + Flux UI) against the reference `library_project`. Every item below needs to be implemented to reach full feature parity.

---

## 1. Database Schema

### Add columns to `users` table
- `phone` — `string`, nullable
- `address` — `text`, nullable

### Add soft-delete to `books` table
- `deleted_at` — `timestamp`, nullable (enable `SoftDeletes` on `Book` model)
- Currently the app hard-deletes books; the reference soft-deletes so they can be restored

### Add soft-delete to `categories` table
- `deleted_at` — `timestamp`, nullable (enable `SoftDeletes` on `Category` model)

### New table: `book_requests`
The core request/approval borrow flow requires this table.

| Column                     | Type            | Notes                                   |
| :------------------------- | :-------------- | :-------------------------------------- |
| `id`                       | bigint          | PK                                      |
| `book_id`                  | FK → `books.id` |                                         |
| `user_id`                  | FK → `users.id` |                                         |
| `request_date`             | timestamp       | nullable; set to `now()` on creation    |
| `type`                     | string          | `'loan'` or `'return'`                  |
| `status`                   | string          | `'pending'`, `'accepted'`, `'rejected'` |
| `created_at`, `updated_at` | timestamps      |                                         |

---

## 2. Models

| File                         | Change                                                                      |
| :--------------------------- | :-------------------------------------------------------------------------- |
| `app/Models/User.php`        | Add `SoftDeletes`. Add `phone`, `address` to `$fillable`                    |
| `app/Models/Book.php`        | Add `SoftDeletes`. Add `hasMany(BookRequest::class)`                        |
| `app/Models/Category.php`    | Add `SoftDeletes`                                                           |
| `app/Models/BookRequest.php` | **[NEW]** — `table = 'book_requests'`, `belongsTo(User)`, `belongsTo(Book)` |

---

## 3. Controllers

| File                                             | Change                                                                                                                |
| :----------------------------------------------- | :-------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/CategoryController.php`    | **[NEW]** — `index`, `create`, `store`, `edit`, `update`, `destroy` (soft), `trashed`, `restore`                      |
| `app/Http/Controllers/UserController.php`        | **[NEW]** — `index`, `show`, `destroy` (soft/ban), `trashed`, `restore`                                               |
| `app/Http/Controllers/BookRequestController.php` | **[NEW]** — `index` (admin), `store` (client loan request), `storeReturn` (client return request), `accept`, `reject` |
| `app/Http/Controllers/BookController.php`        | Change `destroy()` from hard-delete → soft-delete. Add `trashed()` and `restore()`                                    |

---

## 4. Routes

Add to `routes/web.php` (all under `auth` middleware):

```php
// Admin-only
Route::middleware(['auth', 'admin'])->group(function () {
    // Categories CRUD + trash/restore
    Route::resource('categories', CategoryController::class)->except('show');
    Route::get('categories/trashed', [CategoryController::class, 'trashed'])->name('categories.trashed');
    Route::put('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');

    // Users management + trash/restore
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Books trash/restore (hard-delete already existed)
    Route::get('books/trashed', [BookController::class, 'trashed'])->name('books.trashed');
    Route::put('books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');

    // Request approvals
    Route::get('requests', [BookRequestController::class, 'index'])->name('requests.index');
    Route::put('requests/{bookRequest}/accept', [BookRequestController::class, 'accept'])->name('requests.accept');
    Route::put('requests/{bookRequest}/reject', [BookRequestController::class, 'reject'])->name('requests.reject');
});

// Client: submit requests
Route::middleware('auth')->group(function () {
    Route::post('books/{book}/request-loan', [BookRequestController::class, 'store'])->name('books.request-loan');
    Route::post('books/{book}/request-return', [BookRequestController::class, 'storeReturn'])->name('books.request-return');
    Route::get('my-requests', [BookRequestController::class, 'myRequests'])->name('requests.my');
});
```

---

## 5. Views (New Pages)

| Path                                             | Who    | Description                                                                                                       |
| :----------------------------------------------- | :----- | :---------------------------------------------------------------------------------------------------------------- |
| `resources/views/categories/index.blade.php`     | Admin  | Category list table — No \| Name \| Action (Edit, Delete). Buttons: "Add Category", "View Deleted"                |
| `resources/views/categories/create.blade.php`    | Admin  | Form: single "Category Name" field                                                                                |
| `resources/views/categories/edit.blade.php`      | Admin  | Form: pre-filled name field                                                                                       |
| `resources/views/categories/trashed.blade.php`   | Admin  | Table: No \| Name \| Restore button                                                                               |
| `resources/views/users/index.blade.php`          | Admin  | User list table — No \| Name \| Email \| Phone \| Address \| Action (Detail, Ban)                                 |
| `resources/views/users/show.blade.php`           | Admin  | Left: profile card (name, email, phone, address). Right: rent history (book cover, title, rent date, return date) |
| `resources/views/users/trashed.blade.php`        | Admin  | Table of banned users + Restore button                                                                            |
| `resources/views/books/trashed.blade.php`        | Admin  | Table: No \| Code \| Title \| Restore button                                                                      |
| `resources/views/requests/index.blade.php`       | Admin  | All `book_requests` — No \| Member \| Book \| Type \| Requested On \| Status \| Action (Accept ✅ / Reject ❌)      |
| `resources/views/requests/my-requests.blade.php` | Client | Own requests read-only — No \| Book \| Type \| Requested On \| Status                                             |

---

## 6. Views (Modified Pages)

| File                                                | Change                                                                                                        |
| :-------------------------------------------------- | :------------------------------------------------------------------------------------------------------------ |
| `resources/views/layouts/app/sidebar.blade.php`     | Add admin links: Categories, Users, Request Approvals. Add client link: My Requests                           |
| `resources/views/books/show.blade.php`              | Client "Borrow" button → submit loan **request** (`POST /books/{book}/request-loan`) instead of direct borrow |
| `resources/views/books/show.blade.php`              | Client on active-loan book → show "Request Return" button (`POST /books/{book}/request-return`)               |
| `resources/views/books/index.blade.php`             | Client borrow button on catalog cards → submit loan request                                                   |
| `resources/views/books/_card.blade.php`             | Same — borrow action → loan request                                                                           |
| `resources/views/dashboard.blade.php`               | Admin: add "Pending Requests" stat card. Add link to `/requests`                                              |
| `resources/views/pages/settings/⚡profile.blade.php` | Add `phone` and `address` fields                                                                              |
| `resources/views/pages/auth/register.blade.php`     | Optionally add `phone` and `address` at registration                                                          |

---

## 7. Borrow / Return Flow Change

### Current flow (direct):
```
Client → clicks "Borrow" → rent_log created immediately → book.status = 'unavailable'
Client → clicks "Return"  → actual_return_date set immediately → book.status = 'available'
```

### New flow (request-based, matching reference):
```
Client → clicks "Loan"           → book_request (type='loan',   status='pending')
Admin  → accepts loan request    → rent_log created + book.status = 'unavailable'
Admin  → rejects loan request    → book_request.status = 'rejected' (no side effects)

Client → clicks "Request Return" → book_request (type='return', status='pending')
Admin  → accepts return request  → actual_return_date set + book.status = 'available'
Admin  → rejects return request  → book_request.status = 'rejected'
```

---

## 8. Sidebar Navigation Updates

### Admin sidebar (add):
- **Categories** (`/categories`) — `folder-open` icon
- **Users** (`/users`) — `users` icon
- **Request Approvals** (`/requests`) — `inbox-arrow-down` icon (with pending count badge)

### Client sidebar (add):
- **My Requests** (`/my-requests`) — `clock` icon (with pending count badge)

---

## 9. Profile / Registration Fields

- Registration form: add optional `phone` and `address` inputs
- Settings Profile page: add `phone` and `address` fields
- `CreateNewUser` action (`app/Actions/Fortify/CreateNewUser.php`): save `phone` and `address`
- Profile update validation: allow `phone` (nullable, string, max 20) and `address` (nullable, string, max 500)

---

## Priority Order (Suggested)

1. **Migrations** — schema changes first (phone/address, soft-deletes, book_requests)
2. **Models** — SoftDeletes + BookRequest model
3. **Category CRUD** — fully self-contained, no dependencies
4. **User Management** — depends on User model update (phone/address)
5. **Book soft-delete + trash/restore** — change destroy() in BookController
6. **BookRequest model + controller** — core of the new borrow flow
7. **Update borrow/return buttons** in views to use request flow
8. **Request Approvals page** — admin review + accept/reject
9. **My Requests page** — client read-only view
10. **Sidebar navigation** — add all missing links
11. **Dashboard** — add Pending Requests stat card
12. **Profile/Registration** — add phone and address fields
13. **Tests** — update existing + add new tests for all above

