# API Testing Guide

This guide provides curl commands and examples for testing the JWT Authentication API.

## Base URL
```
http://localhost:8000/api
```

## Table of Contents
- [1. Register User](#1-register-user)
- [2. Login User](#2-login-user)
- [3. Get User Profile](#3-get-user-profile)
- [4. Logout User](#4-logout-user)
- [5. Request Password Reset](#5-request-password-reset)
- [6. Confirm Password Reset](#6-confirm-password-reset)

---

## 1. Register User

**Endpoint:** `POST /auth/register`

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "confirmed_password": "password123"
  }'
```

**Success Response (201):**
```json
{
  "status": "success",
  "message": "User registered successfully.",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Error Response - Email Already Registered (409):**
```json
{
  "status": "error",
  "message": "Email is already registered.",
  "errors": {}
}
```

**Error Response - Password Mismatch (400):**
```json
{
  "status": "error",
  "message": "Password and confirm password do not match.",
  "errors": {}
}
```

---

## 2. Login User

**Endpoint:** `POST /auth/login`

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Login successful.",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**JWT Token Contains Custom Claims:**
- `email`: User's email address
- `user_id`: User's ID
- `login_time`: Timestamp when token was created

**Error Response - Invalid Credentials (401):**
```json
{
  "status": "error",
  "message": "Invalid credentials."
}
```

---

## 3. Get User Profile

**Endpoint:** `GET /auth/me`

**Note:** Replace `YOUR_JWT_TOKEN` with the actual token received from login/register.

**Request:**
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "User profile retrieved successfully.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-10-07T10:30:00.000000Z",
    "updated_at": "2025-10-07T10:30:00.000000Z"
  }
}
```

**Error Response - Token Expired (401):**
```json
{
  "status": "error",
  "message": "Token has expired."
}
```

**Error Response - Invalid Token (401):**
```json
{
  "status": "error",
  "message": "Token is invalid."
}
```

**Error Response - Missing Token (401):**
```json
{
  "status": "error",
  "message": "Token not provided or authorization header is missing."
}
```

---

## 4. Logout User

**Endpoint:** `POST /auth/logout`

**Note:** Replace `YOUR_JWT_TOKEN` with the actual token.

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Successfully logged out."
}
```

**Error Response - Token Blacklisted (401):**
```json
{
  "status": "error",
  "message": "Token has been blacklisted."
}
```

---

## 5. Request Password Reset

**Endpoint:** `POST /auth/password-reset/request`

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/password-reset/request \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Password reset OTP has been sent to your email."
}
```

**Note:** A 6-digit OTP will be sent to the user's email. The OTP expires in 15 minutes.

**Note:** For security reasons, the API always returns a success message even if the email doesn't exist. This prevents email enumeration attacks.

---

## 6. Confirm Password Reset

**Endpoint:** `POST /auth/password-reset/confirm`

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/password-reset/confirm \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "confirmed_password": "newpassword123"
  }'
```

**Success Response (202):**
```json
{
  "status": "success",
  "message": "Password has been reset successfully."
}
```

**Error Response - Invalid/Expired OTP (400):**
```json
{
  "status": "error",
  "message": "Invalid or expired OTP."
}
```

**Error Response - Password Mismatch (400):**
```json
{
  "status": "error",
  "message": "Password and confirm password do not match.",
  "errors": {}
}
```

---

## Complete Test Flow

### 1. Register a new user
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "confirmed_password": "password123"
  }'
```

### 2. Copy the token from response and get profile
```bash
TOKEN="your-token-here"

curl -X GET http://localhost:8000/api/auth/me \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Logout
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Login again
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 5. Request password reset
```bash
curl -X POST http://localhost:8000/api/auth/password-reset/request \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com"
  }'
```

### 6. Check email for OTP and reset password
```bash
curl -X POST http://localhost:8000/api/auth/password-reset/confirm \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "otp": "123456",
    "password": "newpassword456",
    "confirmed_password": "newpassword456"
  }'
```

---

## Postman Collection

Import the following JSON into Postman:

```json
{
  "info": {
    "name": "JWT Auth API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api",
      "type": "string"
    },
    {
      "key": "token",
      "value": "",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"John Doe\",\n  \"email\": \"john@example.com\",\n  \"password\": \"password123\",\n  \"confirmed_password\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/auth/register",
              "host": ["{{base_url}}"],
              "path": ["auth", "register"]
            }
          }
        },
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"john@example.com\",\n  \"password\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/auth/login",
              "host": ["{{base_url}}"],
              "path": ["auth", "login"]
            }
          }
        },
        {
          "name": "Get Profile",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/auth/me",
              "host": ["{{base_url}}"],
              "path": ["auth", "me"]
            }
          }
        },
        {
          "name": "Logout",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/auth/logout",
              "host": ["{{base_url}}"],
              "path": ["auth", "logout"]
            }
          }
        }
      ]
    },
    {
      "name": "Password Reset",
      "item": [
        {
          "name": "Request OTP",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"john@example.com\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/auth/password-reset/request",
              "host": ["{{base_url}}"],
              "path": ["auth", "password-reset", "request"]
            }
          }
        },
        {
          "name": "Confirm Reset",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"john@example.com\",\n  \"otp\": \"123456\",\n  \"password\": \"newpassword123\",\n  \"confirmed_password\": \"newpassword123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/auth/password-reset/confirm",
              "host": ["{{base_url}}"],
              "path": ["auth", "password-reset", "confirm"]
            }
          }
        }
      ]
    }
  ]
}
```

---

## Notes

- **JWT Token Expiry:** Default is 60 minutes (configurable in `.env` with `JWT_TTL`)
- **OTP Expiry:** 15 minutes
- **Blacklist:** Tokens are blacklisted on logout and cannot be reused
- **Custom Claims:** JWT includes `email`, `user_id`, and `login_time`
- **Email Configuration:** Configure your mail settings in `.env` for password reset OTPs to work

## Running the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`
