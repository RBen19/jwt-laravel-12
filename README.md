# JWT Authentication API

A secure and robust RESTful API built with Laravel 12 that provides complete JWT-based authentication functionality including user registration, login, profile management, and password reset with OTP verification.

## About This Project

this project is a Laravel-based authentication API that implements industry-standard security practices to prevent common vulnerabilities like user enumeration attacks. The API uses JSON Web Tokens (JWT) for stateless authentication and includes features such as:

- **User Registration** - Create new user accounts with validated credentials
- **User Login** - Authenticate users and issue JWT tokens with custom claims
- **Profile Management** - Retrieve authenticated user information
- **Secure Logout** - Token invalidation and blacklisting
- **Password Reset** - OTP-based password recovery via email
- **Security-First Design** - Prevents email enumeration and follows OWASP best practices

## Key Features

- ✅ JWT Authentication with custom claims (email, user_id, login_time)
- ✅ Token blacklisting on logout
- ✅ Configurable token expiry (default: 60 minutes)
- ✅ OTP-based password reset (6-digit code, 15-minute expiry)
- ✅ Secure error handling (prevents user enumeration attacks)
- ✅ API documentation with Swagger/OpenAPI
- ✅ Request validation with custom error messages
- ✅ Service-based architecture for clean code separation

## Technology Stack

- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Authentication:** tymon/jwt-auth (v2.2)
- **API Documentation:** darkaonline/l5-swagger (v9.0)
- **Database:** MySQL/PostgreSQL/SQLite (configurable)

## Requirements

- PHP >= 8.2
- Composer
- MySQL/PostgreSQL/SQLite
- Mail server configuration (for password reset OTP)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/RBen19/jwt-laravel-12.git
cd jwt-laravel-12
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` and configure the following:

```env
# Application
APP_NAME=jwtAuth
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jwtAuth
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your-secret-key-here
JWT_TTL=60

# Mail Configuration (for OTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Generate JWT Secret

```bash
php artisan jwt:secret
```

This will add `JWT_SECRET` to your `.env` file automatically.

### 6. Create Database


Or use SQLite for quick setup:

```bash
touch database/database.sqlite
```

Then update `.env`:
```env
DB_CONNECTION=sqlite
```

### 7. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `users` - User accounts
- `password_reset_otps` - OTP storage for password reset
- JWT blacklist tables

### 8. Publish Swagger Configuration (Optional)

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 9. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| GET | `/api/auth/me` | Get user profile | Yes |
| POST | `/api/auth/logout` | Logout user | Yes |

### Password Reset

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/password-reset/request` | Request OTP | No |
| POST | `/api/auth/password-reset/confirm` | Reset password with OTP | No |

## Quick Start Testing

### Using cURL

```bash
# 1. Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "confirmed_password": "password123"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# 3. Get profile (replace YOUR_TOKEN with actual token)
curl -X GET http://localhost:8000/api/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Postman

Import the provided Postman collection:

```bash
# File location
postman_collection.json
```

The collection includes:
- Pre-configured requests for all endpoints
- Auto-save token functionality
- Environment variables for base URL and token

## Documentation

- **API Testing Guide:** See [API_TESTING.md](API_TESTING.md) for detailed curl examples and response formats
- **Swagger Documentation:** Visit `/api/documentation` after running `php artisan l5-swagger:generate`
- **Postman Collection:** Import `postman_collection.json` for easy testing

## Security Features

### 1. User Enumeration Prevention

The API returns identical responses for both existing and non-existing emails to prevent attackers from discovering registered users:

**Login:**
- Email not found → 401 "Invalid credentials"
- Wrong password → 401 "Invalid credentials"

**Password Reset:**
- Email exists → 200 "OTP sent" (OTP actually sent)
- Email doesn't exist → 200 "OTP sent" (nothing sent)

### 2. Token Security

- JWT tokens are signed with a secret key
- Tokens include custom claims for additional validation
- Expired tokens are automatically rejected
- Logout blacklists tokens to prevent reuse

### 3. Password Security

- Passwords are hashed using bcrypt
- OTPs are hashed before database storage
- OTP expiry enforced at 15 minutes
- Password confirmation required for reset

## Configuration

### JWT Token Expiry

Default is 60 minutes. Change in `.env`:

```env
JWT_TTL=120  # 2 hours
```

### OTP Expiry

Default is 15 minutes. Change in `app/Services/AuthService.php`:

```php
'expires_at' => Carbon::now()->addMinutes(30), // 30 minutes
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AuthController.php      # API endpoints
│   ├── Requests/
│   │   ├── LoginRequest.php        # Login validation
│   │   ├── RegisterRequest.php     # Registration validation
│   │   ├── PasswordResetRequest.php
│   │   └── PasswordResetConfirmRequest.php
│   └── Middleware/
│       └── JWTMiddleware.php       # JWT authentication
├── Services/
│   └── AuthService.php             # Business logic
├── Traits/
│   └── ApiResponse.php             # Standardized responses
└── Models/
    └── User.php                    # User model

database/
└── migrations/
    ├── create_users_table.php
    └── create_password_reset_otps_table.php

routes/
└── api.php                         # API routes
```



## Common Issues & Troubleshooting

### 1. JWT Secret Not Found

**Error:** `The JWT secret key is not set.`

**Solution:**
```bash
php artisan jwt:secret
```

### 2. Migration Errors

**Error:** `Database does not exist`

**Solution:** Create the database first


### 3. Mail Configuration

**Error:** OTP emails not sending

**Solution:** Use Mailtrap for development:
1. Sign up at [mailtrap.io](https://mailtrap.io)
2. Copy SMTP credentials to `.env`
3. Test with password reset endpoint

### 4. Token Expired

**Error:** `Token has expired`

**Solution:** Login again to get a new token or increase `JWT_TTL` in `.env`

## API Response Format

All API responses follow a consistent format:

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error description",
  "errors": { ... }
}
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues, questions, or contributions, please open an issue on the GitHub repository.

## Credits

Built with:
- [Laravel](https://laravel.com) - The PHP Framework
- [JWT Auth](https://github.com/tymondesigns/jwt-auth) - JWT Authentication for Laravel
- [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger) - OpenAPI/Swagger Integration
