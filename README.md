# lumen54-api-demo

This is a demo for lumen5.4. if you are using lumen to write REST api it will help you.

This demo use `dingo/api`  `tymon/jwt-auth` and write some easy APIs and PHPUNIT

## FEATURE

```

Using JWT authentication
Login, Register
CRUD example

```


## USAGE

```
$ composer install
$ cp .env.example .env
$ vim .env
        DB_*
            config  uration your database
	    JWT_SECRET
            php artisan jwt:secret
	    APP_KEY
            key:generate is abandoned in lumen, so do it yourself
            md5(uniqid())，str_random(32) etc.，maybe use jwt:secret and copy it

$ php artisan migrate
$ php artisan db:seed

```
## REST API DESIGN

just a demo for rest api design

```
    demo： user, post
    
    post   /api/register              	 register a new user
    post   /api/login              	 login
    put    /api/authorizations           refresh token
    delete /api/logout            	 logout
    
    post   /api/posts              	 create a post
    get    /api/posts/5            	 post detail
    put    /api/posts/5            	 replace a post
    patch  /api/posts/5            	 update part of a post
    delete /api/posts/5            	 delete a post
    get    /api/users/4/posts            post list of a user
    get    /api/user/posts               post list of current user
```

