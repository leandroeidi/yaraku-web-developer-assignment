## Requirements
- [Docker](https://docs.docker.com/install)
- [Docker Compose](https://docs.docker.com/compose/install)

## Setup
1. Clone the repository.
1. Start the containers by running `docker-compose up -d` in the project root.
1. Install the composer packages by running `docker-compose exec laravel composer install`.
1. Access the Laravel instance on `http://localhost` (If there is a "Permission denied" error, run `docker-compose exec laravel chown -R www-data storage`).

Note that the changes you make to local files will be automatically reflected in the container. 

## Persistent database
If you want to make sure that the data in the database persists even if the database container is deleted, add a file named `docker-compose.override.yml` in the project root with the following contents.
```
version: "3.7"

services:
  mysql:
    volumes:
    - mysql:/var/lib/mysql

volumes:
  mysql:
```
Then run the following.
```
docker-compose stop \
  && docker-compose rm -f mysql \
  && docker-compose up -d
``` 

## Deploy
1. If it's your first time deploying the project, access your server's terminal and clone the git repository with the command: `git clone https://github.com/leandroeidi/yaraku-web-developer-assignment.git`
1. Create a new database through your server's database manager.
1. Access the project folder by `cd yaraku-web-developer-assignment/src`
1. Create a .env file for the project. The easiest way is to duplicate the .env.example file with `cp .env.example .env`
1. For this project, you only need to change the fields in the .env file related to the database so the site can connect to it (DB_CONNECTION, DB_DATABASE, DB_USERNAME...)
1. Run `composer install`. If an error occurs, run `composer update`.
1. Run `php artisan key:generate`
1. Run `php artisan migrate`
1. Make you domain's root be the project's public folder. Eg.: /yaraku-web-developer-assignment/src/public
1. The site should now be accessible.