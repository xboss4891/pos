# Hướng dẫn Deploy Bhojon trên Coolify

## ⚠️ Lưu ý quan trọng

Coolify không hỗ trợ mount local directories (`./folder`), chỉ hỗ trợ Docker volumes. Do đó:

1. **Sử dụng file `docker-compose.coolify.yml`** thay vì `docker-compose.yml`
2. **Tất cả application files sẽ được copy vào container** khi build
3. **Chỉ cache directory được mount** để persist data

## Chuẩn bị

1. **Tạo repository trên Git** (GitHub, GitLab, hoặc Bitbucket)
2. **Push code lên repository** (bao gồm cả file `docker-compose.coolify.yml`)
3. **Cài đặt Coolify** trên server của bạn

## Cấu hình Coolify

### 1. Tạo Application mới

1. Đăng nhập vào Coolify dashboard
2. Click "New Application"
3. Chọn "Docker Compose"
4. Kết nối với Git repository của bạn
5. **Chọn file `docker-compose.coolify.yml`** làm compose file

### 2. Cấu hình Environment Variables

Trong Coolify, thêm các biến môi trường sau:

```env
# Container Names
CONTAINER_NAME=bhojon

# Port Configuration  
PORT=80
MYSQL_PORT=3306

# Database Configuration
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_DATABASE=bhojon_db
MYSQL_USER=bhojon_user
MYSQL_PASSWORD=your_secure_password

# Application Database Settings
DB_HOST=mysql
DB_PORT=3306
DB_NAME=bhojon_db
DB_USER=bhojon_user
DB_PASS=your_secure_password

# PHP Configuration
PHP_MEMORY_LIMIT=256M
PHP_UPLOAD_MAX_FILESIZE=50M
PHP_POST_MAX_SIZE=50M
PHP_MAX_EXECUTION_TIME=300

# Application Environment
APP_ENV=production
APP_DEBUG=false
```

### 3. Cấu hình Domain và SSL

1. Trong Coolify, cấu hình domain cho ứng dụng
2. Enable SSL certificate (Let's Encrypt)
3. Cấu hình reverse proxy nếu cần

### 4. Deploy

1. Click "Deploy" trong Coolify
2. Coolify sẽ tự động:
   - Build Docker image (copy tất cả files vào container)
   - Tạo containers
   - Cấu hình network và volumes
   - Start services

## Khác biệt với Local Development

### Local Development (docker-compose.yml)
- Mount local directories để development
- Files thay đổi real-time
- Có phpMyAdmin

### Coolify Production (docker-compose.coolify.yml)
- Files được copy vào container khi build
- Chỉ cache directory được persist
- Không có phpMyAdmin (có thể thêm nếu cần)

## Cập nhật Application

Khi cần update:

1. **Push code mới lên Git**
2. **Coolify sẽ detect changes**
3. **Click "Deploy" để rebuild**
4. **Database data sẽ được preserve**

## Troubleshooting

### 1. Build Issues
- Kiểm tra `.dockerignore` không exclude files cần thiết
- Xem build logs trong Coolify

### 2. Database Connection
- Đảm bảo `DB_HOST=mysql` (service name)
- Kiểm tra MySQL health check

### 3. File Permissions
- Cache directory sẽ có quyền write
- Application files có quyền read

### 4. Performance
- Enable OPcache trong PHP
- Optimize Composer autoloader
- Sử dụng CDN cho static assets nếu cần

## Monitoring

Coolify cung cấp:
- Container logs
- Resource usage
- Health status
- Automatic restarts

## Backup Strategy

1. **Code**: Git repository
2. **Database**: MySQL volume backup
3. **Uploads**: Files trong assets/ được copy vào container
4. **Cache**: Persist trong Docker volume

## Security Best Practices

- Thay đổi tất cả passwords mặc định
- Sử dụng strong passwords
- Enable SSL/HTTPS
- Regular security updates
- Monitor logs
