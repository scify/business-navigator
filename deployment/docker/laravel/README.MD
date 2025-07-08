# Overview

Welcome to the documentation for the Laravel Docker Compose template. This template is meticulously designed to streamline the process of building and serving Laravel applications in production environments.

## Exposed Services

1. **NGINX**: NGINX is published on local port 8080 (HTTP) by default.
2. **PHPMyAdmin**: PHPMyAdmin is published on local port 8089 by default.

## Docker Compose Containers

1. **MySQL**: Provides a robust database solution.
2. **PHP-FPM with NGINX**: Offers a high-performance PHP environment with NGINX as the web server.
3. **PHPMyAdmin** (optional): A convenient web-based MySQL database management tool.

## Features

### MySQL

1. **Customizable MySQL Version**: Users are prompted to specify the MySQL version during template initialization.
2. **Health Check**: Configured to verify MySQL's TCP connectivity, ensuring smooth operation.

### PHP-FPM with NGINX

1. **Automatic Application Key Generation**: Generates an application key during the build process for enhanced security.
2. **Flexible Environment Configuration**: Users can customize environment variables through the Docker Compose file using the `environments` field.
3. **Automated Startup Commands**: Utilizing the `AUTORUN_ENABLED` environment variable, users can trigger a series of commands, such as `php artisan migrate`, at container startup for seamless deployment (Refer to: [Environment Variable Specification](https://serversideup.net/open-source/docker-php/docs/reference/environment-variable-specification)). 

## Running Docker Compose

To execute the Docker Compose setup, simply navigate to your source code directory and run the following command in your terminal:

```bash
docker compose up -f docker-interactive-landscape-tool/laravel/laravel-compose.yml
```

**NOTE:** Ensure that you're within the appropriate directory before executing the Docker Compose command. Your folder structure should resemble the structure bellow.


```
.
├── docker-interactive-landscape-tool
│   └── laravel
│       ├── laravel-compose.yml
|       ├── ....
├── public
│   └── ...
├── routes
│   └── ...
├── resources
│   └── ...
...
```
