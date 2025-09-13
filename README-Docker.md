# Bhojon Restaurant Management System - Docker Deployment

This project is a PHP-based restaurant management system built with CodeIgniter framework. This Docker setup provides a complete development and production environment with PHP 8.2 and MySQL 8.0.

## Prerequisites

- Docker
- Docker Compose
- Git (optional)

## Quick Start

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd Bhojon_main
   ```

2. **Configure environment variables**
   ```bash
   cp env.example .env
   # Edit .env file with your preferred database credentials
   ```

3. **Start the application**
   ```bash
   docker-compose up -d
   ```

4. **Access the application**
   - Main Application: http://localhost:8080
   - phpMyAdmin: http://localhost:8081
   - MySQL: localhost:3306

## Services

### PHP Application (app)
- **Port**: 8080
- **Image**: Custom PHP 8.2 with Apache
- **Features**:
  - PHP 8.2 with Apache
  - All required PHP extensions (mysqli, gd, zip, etc.)
  - Composer for dependency management
  - Apache mod_rewrite enabled
  - Optimized PHP configuration

### MySQL Database (mysql)
- **Port**: 3306
- **Image**: MySQL 8.0
- **Features**:
  - Persistent data storage
  - Automatic database initialization
  - Native password authentication

### phpMyAdmin (phpmyadmin)
- **Port**: 8081
- **Image**: phpMyAdmin latest
- **Features**:
  - Web-based MySQL administration
  - Pre-configured connection to MySQL service

## Configuration

### Database Configuration
Update the database configuration in `application/config/database.php`:

```php
$db['default'] = array(
    'hostname' => 'mysql',  // Use service name
    'username' => 'bhojon_user',
    'password' => 'bhojon_password',
    'database' => 'bhojon_db',
    'dbdriver' => 'mysqli',
    // ... other settings
);
```

### Environment Variables
Create a `.env` file based on `env.example`:

```env
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=bhojon_db
MYSQL_USER=bhojon_user
MYSQL_PASSWORD=your_password
```

## Development

### Building the Application
```bash
docker-compose build
```

### Viewing Logs
```bash
docker-compose logs -f app
docker-compose logs -f mysql
```

### Accessing Container Shell
```bash
docker-compose exec app bash
docker-compose exec mysql bash
```

### Running Composer Commands
```bash
docker-compose exec app composer install
docker-compose exec app composer update
```

## Production Deployment

1. **Update environment variables for production**
   ```bash
   # Edit .env file
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Build and start services**
   ```bash
   docker-compose up -d --build
   ```

3. **Set up SSL (optional)**
   - Use a reverse proxy like Nginx
   - Configure SSL certificates
   - Update port mappings

## Troubleshooting

### Common Issues

1. **Permission Issues**
   ```bash
   docker-compose exec app chown -R www-data:www-data /var/www/html
   docker-compose exec app chmod -R 755 /var/www/html
   ```

2. **Database Connection Issues**
   - Ensure MySQL service is running: `docker-compose ps`
   - Check database credentials in `.env`
   - Verify database configuration in `application/config/database.php`

3. **Application Not Loading**
   - Check Apache error logs: `docker-compose logs app`
   - Verify file permissions
   - Ensure all required PHP extensions are installed

### Useful Commands

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: This will delete database data)
docker-compose down -v

# Restart specific service
docker-compose restart app

# View service status
docker-compose ps

# Execute commands in running container
docker-compose exec app php -v
docker-compose exec mysql mysql -u root -p
```

## File Structure

```
├── Dockerfile              # PHP application container definition
├── docker-compose.yml      # Multi-container application definition
├── .dockerignore          # Files to ignore during Docker build
├── env.example            # Environment variables template
├── README.md              # This file
├── application/           # CodeIgniter application files
├── assets/                # Static assets (CSS, JS, images)
├── system/                # CodeIgniter system files
└── vendor/                # Composer dependencies
```

## Support

For issues related to:
- Docker configuration: Check this README
- Application functionality: Refer to the main application documentation
- Database issues: Check MySQL logs and phpMyAdmin

## License

This Docker configuration is provided as-is for the Bhojon Restaurant Management System.

