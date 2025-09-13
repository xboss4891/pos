# Hướng dẫn Deploy Bhojon trên Coolify

## Chuẩn bị

1. **Tạo repository trên Git** (GitHub, GitLab, hoặc Bitbucket)
2. **Push code lên repository**
3. **Cài đặt Coolify** trên server của bạn

## Cấu hình Coolify

### 1. Tạo Application mới

1. Đăng nhập vào Coolify dashboard
2. Click "New Application"
3. Chọn "Docker Compose"
4. Kết nối với Git repository của bạn

### 2. Cấu hình Environment Variables

Trong Coolify, thêm các biến môi trường sau:

```env
# Container Names
CONTAINER_NAME=bhojon

# Port Configuration  
PORT=80
MYSQL_PORT=3306
PHPMYADMIN_PORT=8081

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
   - Build Docker image
   - Tạo containers
   - Cấu hình network
   - Start services

## Các tính năng đã được tối ưu cho Coolify

### 1. Health Checks
- MySQL có health check để đảm bảo database sẵn sàng
- Application sẽ chờ MySQL khởi động xong mới start

### 2. Environment Variables
- Tất cả cấu hình đều sử dụng environment variables
- Dễ dàng thay đổi mà không cần rebuild image

### 3. Volume Management
- Persistent volumes cho database
- Application files được mount để dễ update

### 4. Network Configuration
- Internal network cho services
- External ports có thể cấu hình qua environment variables

### 5. Labels
- Coolify labels để quản lý tốt hơn
- Phân loại services (application, database, service)

## Troubleshooting

### 1. Database Connection Issues
- Kiểm tra environment variables DB_HOST, DB_USER, DB_PASS
- Đảm bảo MySQL container đã khởi động hoàn toàn

### 2. Permission Issues
- Kiểm tra file permissions trong application/cache
- Đảm bảo www-data có quyền write

### 3. Build Issues
- Kiểm tra Dockerfile có đúng syntax
- Xem logs trong Coolify để debug

### 4. Port Conflicts
- Thay đổi PORT, MYSQL_PORT, PHPMYADMIN_PORT nếu cần
- Đảm bảo ports không bị conflict với services khác

## Monitoring

Coolify sẽ cung cấp:
- Container logs
- Resource usage
- Health status
- Automatic restarts

## Backup

1. **Database**: Coolify có thể backup MySQL volumes
2. **Application**: Code được lưu trong Git repository
3. **Uploads**: Files trong assets/ và dummyimage/ được mount từ host

## Updates

1. Push code mới lên Git repository
2. Coolify sẽ tự động detect changes
3. Click "Deploy" để update
4. Database sẽ được preserve

## Security Notes

- Thay đổi tất cả passwords mặc định
- Sử dụng strong passwords cho database
- Enable SSL/HTTPS
- Cấu hình firewall nếu cần
- Regular updates cho base images
