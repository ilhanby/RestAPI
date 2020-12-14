# RestAPI
Symfony **4.4** and MySql - RestFul Service

# **Docker**

### In Order
```sh
1. docker-compose build
```
**If php: 7.4-fpm fails, try again. because it may expire**
```sh
1. docker-compose build
```

```sh
2. docker-compose up -d
```
```sh
3. docker-compose exec php composer install
```
```sh
4. docker-compose exec php bin/console doctrine:schema:create
```
```sh
5. docker-compose exec php bin/console doctrine:migrations:migrate
```
```sh
6. docker-compose exec php bin/console doctrine:fixtures:load
```
```sh
7. docker-compose exec php bin/console assets:install –symlink public/
```


## Click on the image below for postman documentation

[![N|Solid](https://res.cloudinary.com/postman/image/upload/t_team_logo_pubdoc/v1/team/768118b36f06c94b0306958b980558e6915839447e859fe16906e29d683976f0)](https://documenter.getpostman.com/view/10240903/TVsoGVyV)
##### By clicking 'RUN IN POSTMAN' at the top right of the page that opens, the collection is recorded.
