# StoreFlow - Phase 1 Setup Complete

## Overview

Phase 1 of StoreFlow has been successfully implemented! This includes:
- Core architecture (merchants, stores, users, multi-tenancy)
- Authentication system with username/password
- Role-based access control (Owner, Manager, Staff)
- Store selection middleware
- Dashboard UI with Vue 3 + TailwindCSS

## What's Been Built

### Backend (Laravel)
1. **Database Migrations**
   - `merchants` - Merchant accounts
   - `stores` - Store locations
   - `users` - System users with roles
   - `store_users` - User-store assignments

2. **Eloquent Models**
   - `Merchant` - With relationships to users and stores
   - `Store` - With relationships to merchant and users
   - `User` - With role-based helper methods
   - `StoreUser` - Pivot model for many-to-many

3. **Authentication**
   - Username/password login
   - Store selection for multi-store users
   - Session-based authentication
   - Automatic redirect logic

4. **Middleware**
   - `HandleInertiaRequests` - Inertia.js integration
   - `EnsureStoreSelected` - Store context enforcement

5. **Policies**
   - `StorePolicy` - Role-based authorization for store operations

### Frontend (Vue 3 + Inertia.js)
1. **Authentication Pages**
   - `Auth/Login.vue` - Login form
   - `Auth/StoreSelection.vue` - Store picker

2. **Dashboard**
   - `Layouts/DashboardLayout.vue` - Main layout with sidebar
   - `Dashboard/Index.vue` - Dashboard home

3. **Styling**
   - TailwindCSS configured
   - Responsive design
   - Clean, modern UI

## Getting Started

### 1. Create Database
```bash
# Using MySQL command line or phpMyAdmin
CREATE DATABASE storeflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Demo Data
```bash
php artisan db:seed --class=DemoSeeder
```

This creates:
- 1 merchant: "Demo Merchant"
- 2 stores: "Main Store" and "Second Location"
- 3 users with different roles

### 4. Install NPM Dependencies & Build
```bash
npm install
npm run dev
```

Keep this running in a separate terminal for hot reload during development.

### 5. Start Laravel Server
```bash
php artisan serve
```

### 6. Access the Application
Open your browser to: http://localhost:8000

## Demo Login Credentials

### Owner Account
- **Username:** `owner`
- **Password:** `password`
- **Access:** All stores, full management rights

### Manager Account
- **Username:** `manager`
- **Password:** `password`
- **Access:** Main Store only, can manage products/orders

### Staff Account
- **Username:** `staff`
- **Password:** `password`
- **Access:** Both stores, operations only

## Role Permissions

| Feature | Owner | Manager | Staff |
|---------|-------|---------|-------|
| View all stores | ✅ | ❌ | ❌ |
| Create stores | ✅ | ❌ | ❌ |
| Update store settings | ✅ | ✅* | ❌ |
| Delete stores | ✅ | ❌ | ❌ |
| View orders | ✅ | ✅ | ✅ |
| Manage products | ✅ | ✅ | ❌ |

*Managers can only manage stores they're assigned to

## Architecture Highlights

### Multi-Tenancy
- All operations scoped by `merchant_id`
- Store-level isolation via `store_id` in session
- Strong foreign key constraints

### Authentication Flow
1. User logs in with username/password
2. System checks store access
3. If multiple stores → Store selection screen
4. If single store → Auto-select and redirect to dashboard
5. Session stores `store_id` for all subsequent requests

### Security
- Passwords hashed with bcrypt
- CSRF protection enabled
- Session regeneration on login
- Middleware enforcement of store access

## Project Structure

```
StoreFlow/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── LogoutController.php
│   │   │   ├── DashboardController.php
│   │   │   └── StoreSelectionController.php
│   │   └── Middleware/
│   │       ├── EnsureStoreSelected.php
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   ├── Merchant.php
│   │   ├── Store.php
│   │   ├── StoreUser.php
│   │   └── User.php
│   └── Policies/
│       └── StorePolicy.php
├── database/
│   ├── migrations/
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── 2025_12_02_085930_create_merchants_table.php
│   │   ├── 2025_12_02_085931_create_stores_table.php
│   │   └── 2025_12_02_085931_create_store_users_table.php
│   └── seeders/
│       └── DemoSeeder.php
├── resources/
│   ├── js/
│   │   ├── Layouts/
│   │   │   └── DashboardLayout.vue
│   │   └── Pages/
│   │       ├── Auth/
│   │       │   ├── Login.vue
│   │       │   └── StoreSelection.vue
│   │       └── Dashboard/
│   │           └── Index.vue
│   └── views/
│       └── app.blade.php
└── routes/
    └── web.php
```

## Next Steps (Phase 2)

The foundation is ready! Here's what to build next:

1. **Products Module**
   - Product CRUD
   - Categories
   - Product customizations
   - Image upload

2. **Orders Module**
   - Order creation
   - Status management
   - Real-time updates (WebSockets)
   - Order items with customizations

3. **Customers Module**
   - Customer management
   - Guest checkout
   - Account creation

4. **Shipping Module**
   - Zones configuration
   - Shipping methods
   - Rate calculation engine
   - Address validation

5. **Additional Features**
   - Loyalty program
   - Audit logging
   - Reports/Analytics
   - Email notifications

## Troubleshooting

### Database Connection Error
- Check `.env` file has correct database credentials
- Ensure MySQL is running
- Verify database `storeflow` exists

### NPM Build Errors
- Delete `node_modules` and `package-lock.json`
- Run `npm install` again
- Ensure Node.js version is 16+

### Login Not Working
- Run migrations: `php artisan migrate:fresh`
- Run seeder: `php artisan db:seed --class=DemoSeeder`
- Clear cache: `php artisan cache:clear`

### Page Not Found (404)
- Ensure `npm run dev` is running
- Check routes: `php artisan route:list`
- Clear config: `php artisan config:clear`

## Technical Stack

- **Backend:** Laravel 10, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js, TailwindCSS
- **Build:** Vite
- **Database:** MySQL 8
- **Authentication:** Laravel Session-based

## Need Help?

This is a solid foundation. All core tenancy, authentication, and authorization patterns are in place. You can now build features on top of this architecture!
