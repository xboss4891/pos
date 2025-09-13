#!/bin/bash
# MySQL setup script for Docker

echo "Waiting for MySQL to be ready..."
until mysqladmin ping -h"mysql" -u"root" -p"${MYSQL_ROOT_PASSWORD:-rootpassword}" --silent; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done

echo "MySQL is ready! Setting up database and user..."

# Create database if it doesn't exist
mysql -h"mysql" -u"root" -p"${MYSQL_ROOT_PASSWORD:-rootpassword}" -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE:-bhojon_db};"

# Create user if it doesn't exist and grant privileges
mysql -h"mysql" -u"root" -p"${MYSQL_ROOT_PASSWORD:-rootpassword}" -e "
CREATE USER IF NOT EXISTS '${MYSQL_USER:-bhojon_user}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD:-bhojon_password}';
GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE:-bhojon_db}.* TO '${MYSQL_USER:-bhojon_user}'@'%';
FLUSH PRIVILEGES;
"

echo "Database setup completed!"
