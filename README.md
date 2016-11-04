#TD Test - Karl Lurman

## Installation

- Make sure you are running Docker (native on MacOS) and that you are not running any webservers on port 80 or 8080.
- Git clone the entire repo to your local machine
- Bring up the environment:

```sh
cd laradock
docker-compose up -d
```

- You probably need to do a Laravel install and database migrate.

```sh
docker exec -ti laradock_workspace_1 bash
cd api
composer install
php artisan migrate
```

- app/clm-api folder in the repo contains the Laravel API
- app/clm-frontend folder in the repo contains the static frontend folder
- laradock contains all the Laradock docker configuration

## API Usage

An API method exists to signup a user. Use Postman to create a user with a POST to the api/signup endpoint:

``http://localhost:8080/api/auth/signup?name=karl&password=karl1234&secret_information=mysecret``

This should create a new user in the database on laradock_mysql_1:

```
mysql> select * from users\G

 *************************** 1. row ***************************
                 id: 1
               name: karl
           password: $2y$10$ubMOc9dVNbr8f0/HRnC/g.TVxexHlkk54DuaGolv2poPPRlMtdA2a
 secret_information: eyJpdiI6IlhGMEN6cXVQUFhtalpIUXdOcldXaUE9PSIsInZhbHVlIjoiNWZ2ektxXC92V0FqTEwzUVwvYmpFZlpnPT0iLCJtYWMiOiI0NjFiNmNjNjBjNjY3MzljOWRmYzM4NWIzNTBhZmY1ZTJkOTVmYTk0ZjA1ODA1N2U5N2EzNzAyM2JkZjEwOGI0In0=
     remember_token: NULL
         created_at: 2016-11-04 01:58:49
         updated_at: 2016-11-04 01:58:49         
```

You should also get back a JWT for use in subsequent requests:

``{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwXC9hcGlcL2F1dGhcL3NpZ251cCIsImlhdCI6MTQ3ODIyNDcyOSwiZXhwIjoxNDc4MjI4MzI5LCJuYmYiOjE0NzgyMjQ3MjksImp0aSI6Ijk1ZTFhN2IyZDQ0Zjc1ZTU2OWExNDQyOGRhMzUwMWIyIn0.wQHGhoqchzyJnPCj-xqgmN6fq03qU57v519kROg1m9A"}``

Here's the token being used to make a GET to the api/user endpoint:

``http://localhost:8080/api/user?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwXC9hcGlcL2F1dGhcL3NpZ251cCIsImlhdCI6MTQ3ODIyNDcyOSwiZXhwIjoxNDc4MjI4MzI5LCJuYmYiOjE0NzgyMjQ3MjksImp0aSI6Ijk1ZTFhN2IyZDQ0Zjc1ZTU2OWExNDQyOGRhMzUwMWIyIn0.wQHGhoqchzyJnPCj-xqgmN6fq03qU57v519kROg1m9A``

Returns our user data, including decrypted secret_information:

``{"user":{"id":1,"name":"karl","secret_information":"mysecret","created_at":"2016-11-04 01:58:49","updated_at":"2016-11-04 01:58:49"}}``

Obviously a POST to the api/auth/login with the correct user credentials will return a JWT without creating a new User.

## Frontend Usage

- If you visit http://localhost, you should be presented with two forms: Login and Signup.
- Use the Signup form to create a user with password and a secret_informaton. Please note name is unique. Successful signup will result in a JWT being displayed under the submit button.
- Use the Login form to login and retrieve a users Secret Information.

## Approach

Focus has been on setting up a protyping environment with separate frontend and API servers in Docker. From there setting up and authenticating against the API itself, was explored. Decision was made to use (stateless) JWT without the OAuth  dancing of say, Laravel Passport. Removed web routes. Basic changes to the existing Eloquent User model were made to remove email and add in secret_information field. Work was done to ensure secret_information was encrypted on create and decrypted on load - some mutators. The user api endpoint retrieves the user for the supplied JWT, returning the user (and only that user) in a JSON response. With the time remaining, I slapped together a quick jQuery frontend which contains some forms for creating and logging in a user.

## Technical Details

- Laradock has been used as a basis for my docker setup... It's a bit overkill with the containers, I have removed a bunch of them to keep it more minimal for the prototype. 
- Instead of a simple two container architecture, my prototype makes use of a separate web data volume container, along with separate mysql and php-fpm containers. It does move away from the brief, but it's closer to how it would be done in a production container-based architecture anyway.
- https://github.com/francescomalatesta/laravel-api-boilerplate-jwt has been used as basis for my Laravel API.
- VerifyCsrfToken middleware is disabled with this boilerplate - which may or may not be an issue in production.
- I had some troubles with setting up the SSL side of nginx with the laradock Dockerfile, so I abandoned this and went with a simple port 8080 for the API... Ideally all requests to the API should be made via SSL. Safe to say that passwords and usernames are being communicated without encryption right now.
- Two nginx servers are started, one that is hooked up to php-fpm for the api (port 8080), the other without php used to serve the html/javascript only frontend (port 80). The two servers do share the same configuration files, which isn't ideal, but for purposes of this prototype it will suffice.
- CORS is enabled for all API routes.

## TODO and Comments

- A decent frontend, probably with Vue instead of jQuery - I simply ran out of time and I don't know Vue that well anyway. 
The Javascript here is not namespaced, procedural, and just to show JWT and CORS in effect.
- SSL security and better CORS config to ensure only specific domains can access the API in an encrypted protocol.
- Use of DatabaseSeeder to generate some Users - The brief doesn't require a signup process, but it served the purpose
of creating test data.
- Token expiration is currently 24 hours... This should be a lot smaller for 'production' usage
- Logging is pretty verbose in the current setup... lots of information is being leaked to the frontend via the API error
messages. Turning this down or handling errors better in the code is required to eliminate this.