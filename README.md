# projectDOC

## Deploy with Docker

Set environment variable in docker-compose and .env

### Build image

```sh
docker-compose build
```

### Run containers

```sh
docker-compose up -d
```

### Stop containers

```sh
docker-compose stop
```

### Initialize php project in container


```sh
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:diff
```
