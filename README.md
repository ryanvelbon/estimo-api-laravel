# About

This is a Laravel-powered API for the Estimo app.

# Deploying on cPanel


# Consuming the API

Some requests need to include the header `Accept:application/json`.

For protected routes which require authentication, the request must include the user's `bearer` token which can be acquired by logging in at `/api/login`.

## Protected Routes

The token should be included in the Authorization header as a Bearer token.


# Testing

Run `php artisan test`