# Laravel User CRUD

A Laravel 12 package that provides complete user management with authentication and CRUD operations out of the box. Supports both web and API interfaces with multiple authentication providers.

## Requirements

- PHP 8.2+
- Laravel 12.x

## Features

- Complete user management (CRUD)
- Authentication (login, register, logout)
- Works with both web and API interfaces
- API support for multiple authentication providers:
  - Laravel Sanctum
  - Laravel Passport
  - JWT Auth

## Installation

You can install this package via composer:

```bash
composer require mubeen/laravel-user-crud
```

After installing the package, run the installation command which will prompt you to choose your preferred interface (web or API) and authentication provider:

```bash
php artisan laravel-user-crud:install
```

The installer will create the necessary configuration, publish resources, and guide you through setting up any additional dependencies.

## Configuration

The installation command generates a configuration file, but you can also publish it manually:

```bash
php artisan vendor:publish --provider="Mubeen\LaravelUserCrud\LaravelUserCrudServiceProvider" --tag="laravel-user-crud"
```

This will publish the following files:
- Configuration file: `config/laravel-user-crud.php`
- Migrations: `database/migrations/`
- Views (if web interface): `resources/views/vendor/laravel-user-crud/`

## Usage

### Web Interface

If you chose the web interface during installation, you'll have access to these routes:

#### Authentication

- Login: `/login`
- Register: `/register`
- Logout: POST to `/logout`

#### User Management

User management routes are prefixed with `/admin` by default (configurable):

- List users: `/admin/users`
- Create user: `/admin/users/create`
- Show user: `/admin/users/{user}`
- Edit user: `/admin/users/{user}/edit`
- Delete user: DELETE to `/admin/users/{user}`

### API Interface

If you chose the API interface during installation, you'll have access to these endpoints:

#### Authentication

- Login: POST to `/auth/login`
- Register: POST to `/auth/register`
- Logout: POST to `/auth/logout` (requires authentication)
- Get profile: GET to `/auth/profile` (requires authentication)
- Update profile: PUT to `/auth/profile` (requires authentication)

#### User Management

User management endpoints are prefixed with `/api` by default (configurable):

- List users: GET to `/api/users`
- Create user: POST to `/api/users`
- Show user: GET to `/api/users/{user}`
- Update user: PUT/PATCH to `/api/users/{user}`
- Delete user: DELETE to `/api/users/{user}`

### Authentication Providers

When using the API interface, you can choose from three authentication providers:

#### Laravel Sanctum

Ideal for SPAs, mobile applications, and simple API tokens. The package will automatically add necessary authentication setup.

#### Laravel Passport

More feature-rich OAuth2 server. Suitable for creating OAuth2 APIs with personal access tokens, password grants, and more.

#### JWT Auth

JSON Web Token authentication for stateless API authentication.

## Customization

You can customize the behavior in the configuration file:

```php
// config/laravel-user-crud.php

return [
    // Interface type: 'web' or 'api'
    'interface_type' => 'web',
    
    // Auth provider for API: 'sanctum', 'passport', or 'jwt'
    'auth_provider' => null,
    
    // Routes prefix for all the package routes
    'route_prefix' => 'admin',
    
    // Middleware to apply to the routes
    'middleware' => ['web', 'auth'],
    
    // Admin routes middleware 
    'admin_middleware' => ['web', 'auth'],
    
    // View layout to extend (for web interface)
    'layout' => 'layouts.app',
    
    // Additional user fields
    'additional_user_fields' => [],
    
    // API response settings
    'api_pagination_limit' => 15,
    
    // Token settings (for Sanctum/Passport)
    'token_name' => 'User Management',
    'token_expiration_days' => 30,
];
```

## Testing

```bash
composer test
```

## Credits

- [Mubeen](https://github.com/mubeen)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 