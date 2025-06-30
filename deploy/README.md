# Laravel Chore Tracker - LAMP Server Deployment Guide

This guide will help you deploy the Laravel Chore Tracker application to a LAMP (Linux, Apache, MySQL, PHP) server.

## Prerequisites

- Linux server (Ubuntu 20.04+ or CentOS 7+ recommended)
- Apache 2.4+
- MySQL 8.0+ or MariaDB 10.3+
- PHP 8.1+ with required extensions
- Composer
- Node.js and NPM (for building assets)

## PHP Extensions Required

Ensure these PHP extensions are installed:
```bash
# Ubuntu/Debian
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-intl php8.1-bcmath

# CentOS/RHEL
sudo yum install php81-php php81-php-cli php81-php-mysql php81-php-xml php81-php-mbstring php81-php-curl php81-php-zip php81-php-gd php81-php-intl php81-php-bcmath
```

## Step 1: Server Preparation

### Enable Required Apache Modules
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Install Node.js and NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## Step 2: Database Setup

1. **Login to MySQL as root:**
   ```bash
   mysql -u root -p
   ```

2. **Run the database setup script:**
   ```sql
   source /path/to/your/project/deploy/database-setup.sql
   ```

3. **Update the password in the script before running it!**

## Step 3: Application Deployment

1. **Upload your project to the server:**
   ```bash
   # Upload to /var/www/html/chore-tracker/
   rsync -avz --progress ./chore-tracker/ user@server:/var/www/html/chore-tracker/
   ```

2. **Set up environment file:**
   ```bash
   cd /var/www/html/chore-tracker
   cp deploy/env-template.txt .env
   # Edit .env with your production values
   nano .env
   ```

3. **Make deployment script executable and run it:**
   ```bash
   chmod +x deploy/deploy.sh
   ./deploy/deploy.sh
   ```

## Step 4: Apache Virtual Host Configuration

1. **Copy the virtual host configuration:**
   ```bash
   sudo cp deploy/apache-vhost.conf /etc/apache2/sites-available/chore-tracker.conf
   ```

2. **Edit the configuration file:**
   ```bash
   sudo nano /etc/apache2/sites-available/chore-tracker.conf
   ```
   
   Update these values:
   - `yourdomain.com` → your actual domain
   - `/var/www/html/chore-tracker` → your actual path
   - SSL certificate paths (if using HTTPS)

3. **Enable the site:**
   ```bash
   sudo a2ensite chore-tracker.conf
   sudo systemctl reload apache2
   ```

## Step 5: File Permissions

Set proper ownership and permissions:
```bash
cd /var/www/html/chore-tracker
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 775 public/storage
```

## Step 6: SSL Certificate (Recommended)

### Using Let's Encrypt (Free):
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### Using existing certificates:
Update the SSL paths in your virtual host configuration.

## Step 7: Final Testing

1. **Test the application:**
   - Visit your domain in a browser
   - Check that all pages load correctly
   - Test user registration and login
   - Verify admin functionality

2. **Check logs for errors:**
   ```bash
   tail -f storage/logs/laravel.log
   tail -f /var/log/apache2/chore-tracker_error.log
   ```

## Step 8: Create Default Admin User

Run this command to create an admin user:
```bash
php artisan tinker
```

Then in the tinker console:
```php
\App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('your-secure-password'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
```

## Security Considerations

1. **Hide sensitive files:**
   ```bash
   # Add to .htaccess in document root
   echo "RewriteRule ^\.env$ - [F,L]" >> public/.htaccess
   ```

2. **Set restrictive file permissions:**
   ```bash
   chmod 600 .env
   chmod -R 644 config/
   ```

3. **Configure firewall:**
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

4. **Regular updates:**
   - Keep Laravel dependencies updated
   - Apply server security updates regularly
   - Monitor application logs

## Maintenance Commands

### Update application:
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci --only=production && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Backup database:
```bash
mysqldump -u chore_user -p chore_tracker > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error:**
   - Check Apache error logs
   - Verify file permissions
   - Check .env configuration

2. **Database Connection Error:**
   - Verify database credentials in .env
   - Check MySQL service status
   - Test database connection manually

3. **Assets not loading:**
   - Run `npm run build`
   - Check public directory permissions
   - Verify Apache configuration

4. **Session/Cache Issues:**
   - Clear all caches
   - Check storage directory permissions
   - Verify session driver configuration

For additional support, check the Laravel documentation at https://laravel.com/docs 