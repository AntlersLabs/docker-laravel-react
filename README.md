# Laravel + React Docker Application

A modern, production-ready Laravel application with React frontend, containerized with Docker for optimal performance and scalability.

## 🏗️ Architecture

This application uses a multi-service Docker architecture:

- **Laravel 12** backend with PHP 8.4-FPM
- **React 19** frontend with TypeScript and Tailwind CSS
- **Inertia.js** for seamless SPA experience
- **Nginx** web server with optimized configuration
- **MySQL 8.0** database
- **Redis** for caching and sessions
- **Supervisor** for process management
- **Multi-stage Docker build** for optimized production images

## 🚀 Quick Start

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

### Environment Setup

1. **Clone the repository:**
   ```bash
   https://github.com/AntlersLabs/docker-laravel-react.git
   cd docker-laravel-react
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Generate application key:**
   ```bash
   docker-compose run --rm app php artisan key:generate
   ```

4. **Start the application:**
   ```bash
   docker-compose up -d
   ```

5. **Access the application:**
   - Web Application: http://localhost:8000
   - Database: localhost:3306
   - Redis: localhost:6379

## 🐳 Docker Services

### Application Container (`app`)
- **Base Image:** PHP 8.4-FPM Alpine
- **Port:** 80 (mapped to host 8000)
- **Features:**
  - Multi-stage build for optimization
  - Nginx + PHP-FPM + Supervisor
  - Automatic migrations and optimizations
  - Built-in queue worker
  - Inertia SSR support

### Database Container (`mysql`)
- **Image:** MySQL 8.0
- **Port:** 3306
- **Database:** laravel
- **Credentials:** laravel/secret

### Cache Container (`redis`)
- **Image:** Redis 7 Alpine
- **Port:** 6379
- **Usage:** Sessions, cache, and queue

## 🔧 Configuration

### Environment Variables

Create a `.env` file with the following variables:

```env
# Application
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (configure for production)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Production Security

For production deployment, ensure:

1. **Generate a secure APP_KEY:**
   ```bash
   docker-compose run --rm app php artisan key:generate
   ```

2. **Set strong database passwords:**
   ```bash
   # Update docker-compose.yml
   MYSQL_ROOT_PASSWORD=your-secure-root-password
   MYSQL_PASSWORD=your-secure-password
   ```

3. **Configure proper file permissions:**
   ```bash
   sudo chown -R $USER:$USER storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## 📦 Development

### Local Development

```bash
# Start development environment
docker-compose up -d

# View logs
docker-compose logs -f app

# Run commands inside container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Build assets
docker-compose exec app npm run build
```

### Available Commands

```bash
# Database operations
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan db:seed

# Cache operations
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Queue operations
docker-compose exec app php artisan queue:work
docker-compose exec app php artisan queue:restart

# Testing
docker-compose exec app php artisan test
docker-compose exec app php artisan test --coverage
```

## 🚀 Production Deployment

### Docker Swarm Deployment

1. **Initialize Docker Swarm:**
   ```bash
   docker swarm init
   ```

2. **Deploy stack:**
   ```bash
   docker stack deploy -c docker-compose.yml laravel-app
   ```

### Cloud Deployment

#### AWS ECS/Fargate
- Use the provided Dockerfile
- Configure ECS task definitions
- Set up RDS for MySQL and ElastiCache for Redis
- Use Application Load Balancer

#### Google Cloud Run
- Push image to Google Container Registry
- Configure Cloud SQL and Memorystore
- Set up Cloud Load Balancing

#### DigitalOcean App Platform
- Connect GitHub repository
- Configure environment variables
- Use managed databases

### Performance Optimization

#### Production Dockerfile Features:
- Multi-stage build reduces image size
- PHP OPcache enabled for performance
- Nginx gzip compression
- Optimized PHP-FPM configuration
- Supervisor for process management

#### Recommended Production Settings:
```env
# Performance
APP_DEBUG=false
APP_ENV=production

# Cache everything
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Optimized PHP settings
OPCACHE_ENABLE=1
OPCACHE_MEMORY_CONSUMPTION=256
```

## 📊 Monitoring & Logging

### Application Logs
```bash
# View application logs
docker-compose logs -f app

# View specific service logs
docker-compose logs -f mysql
docker-compose logs -f redis
```

### Health Checks
The application includes built-in health checks:
- Database connectivity
- Redis connectivity
- Application startup verification

### Performance Monitoring
- Nginx access logs: `/var/log/nginx/access.log`
- PHP-FPM logs: Available via Docker logs
- Application logs: `storage/logs/laravel.log`

## 🔒 Security

### SSL/TLS Configuration
For production, configure SSL certificates:

```nginx
# Add to docker/nginx/default.conf
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    # ... rest of configuration
}
```

### Security Headers
The Nginx configuration includes security headers:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`

### Firewall Configuration
```bash
# Allow only necessary ports
ufw allow 22    # SSH
ufw allow 80    # HTTP
ufw allow 443   # HTTPS
ufw enable
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test suite
docker-compose exec app php artisan test --testsuite=Feature

# Run with coverage
docker-compose exec app php artisan test --coverage
```

### Test Database
Tests use a separate SQLite database configured in `phpunit.xml`.

## 📈 Scaling

### Horizontal Scaling
```bash
# Scale application containers
docker-compose up -d --scale app=3

# Use load balancer (nginx/haproxy)
# Configure in docker-compose.yml
```

### Database Scaling
- Use read replicas for MySQL
- Implement Redis clustering
- Consider managed database services

## 🔧 Troubleshooting

### Common Issues

1. **Permission Denied Errors:**
   ```bash
   sudo chown -R $USER:$USER storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection Issues:**
   ```bash
   # Check if MySQL is running
   docker-compose ps mysql
   
   # Check MySQL logs
   docker-compose logs mysql
   ```

3. **Asset Build Issues:**
   ```bash
   # Rebuild assets
   docker-compose exec app npm run build
   ```

4. **Cache Issues:**
   ```bash
   # Clear all caches
   docker-compose exec app php artisan optimize:clear
   ```

### Debug Mode
Enable debug mode for development:
```env
APP_DEBUG=true
APP_ENV=local
```

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Docker Documentation](https://docs.docker.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `docker-compose exec app php artisan test`
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review Docker and Laravel documentation

---

**Built with ❤️ by Antlers Labs using Laravel, React, and Docker**
