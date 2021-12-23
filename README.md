# About

This is a Laravel-powered API for the Estimo app.

# Deploying on cPanel

While we are trying to find a better CI/CD solution, you can follow the instructions below:

* Run `$ git clone https://.....`
* Create a database and user credentials. If you are using a shared server you might need to do this via the cPanel GUI.
* `cd` into project folder and create a `.env` file.
* Run `$ composer install`
* Run `$ php artisan key:generate`
* Run `$ php artisan migrate`


# Testing

## Automated Tests

Run `php artisan test`

## Testing with Postman

1. Open Postman and import `postman.json`
2. Send a `POST login` request. The server should respond with an API token.
3. Use the retrieved token to set the Bearer Token for the estimo collection. All requests to protected routes are set up so that they inherit auth from their parent.

Some requests need to include the header `Accept:application/json`.

For protected routes (i.e., those requiring authentication), the user's PAT should be included in the Authorization header as a Bearer token. The PAT can be acquired upon registration via `POST /api/register` or by logging in via `POST /api/login`.


Note that when hitting `PATCH` endpoints, the resource will not be updated unless the data is sent as `x-www-form-urlencoded` data.


# Developer's Journal

This section can be ignored by the reader.

My personal project *Koolabo* is being used as a reference point.
Extensively reused code from *Koolabo* which features a similar `Project` resource.
There were a couple of oversights/bugs in *Koolabo* which have now been fixed in this project but are yet to be fixed in *Koolabo*.

## Hours

This is where I keep track of the hours expended on this project.

December
21  22  23  24  25  26  27  28  29  30  31
?	8   4

January
01  02  03  04  05

## Issues

Note that there is redundant data as manager is stored both in the `projects.managed_by` column as well as the `project_members.role` column
