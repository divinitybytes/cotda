# Laravel Chore Tracker - Deployment Checklist

## Pre-Deployment Checklist

- [ ] Server meets minimum requirements (PHP 8.1+, MySQL 8.0+, Apache 2.4+)
- [ ] Required PHP extensions installed
- [ ] Composer installed on server
- [ ] Node.js and NPM installed
- [ ] Apache modules enabled (rewrite, ssl, headers)
- [ ] Domain DNS pointing to server
- [ ] SSL certificate obtained (if using HTTPS)

## Deployment Steps

### 1. Database Setup
- [ ] MySQL server running
- [ ] Database and user created (`deploy/database-setup.sql`)
- [ ] Database password updated in setup script
- [ ] Database connection tested

### 2. File Upload
- [ ] Project files uploaded to `/var/www/html/chore-tracker/`
- [ ] Environment file created (`.env` from `deploy/env-template.txt`)
- [ ] All production values filled in `.env`
- [ ] APP_KEY generated

### 3. Dependencies & Build
- [ ] Composer dependencies installed (`composer install --optimize-autoloader --no-dev`)
- [ ] NPM dependencies installed (`npm ci --only=production`)
- [ ] Assets built (`npm run build`)

### 4. Laravel Configuration
- [ ] Database migrations run (`php artisan migrate --force`)
- [ ] Configuration cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Storage symlink created (`php artisan storage:link`)

### 5. File Permissions
- [ ] Ownership set (`chown -R www-data:www-data`)
- [ ] Directory permissions set (`chmod -R 755`)
- [ ] Storage permissions set (`chmod -R 775 storage bootstrap/cache`)
- [ ] Environment file secured (`chmod 600 .env`)

### 6. Apache Configuration
- [ ] Virtual host configuration copied
- [ ] Domain name updated in vhost
- [ ] Document root path verified
- [ ] SSL certificate paths configured (if using HTTPS)
- [ ] Site enabled (`a2ensite chore-tracker.conf`)
- [ ] Apache restarted

### 7. Security (Optional but Recommended)
- [ ] Production .htaccess copied (`deploy/htaccess-production`)
- [ ] Firewall configured
- [ ] Sensitive files access blocked
- [ ] Security headers enabled

### 8. Testing
- [ ] Website loads correctly
- [ ] User registration works
- [ ] User login works
- [ ] Admin login works
- [ ] Database operations work
- [ ] File uploads work
- [ ] All major features tested

### 9. Post-Deployment
- [ ] Admin user created
- [ ] Application logs monitored
- [ ] Backup procedures established
- [ ] Update procedures documented

## Quick Commands Reference

```bash
# Run deployment script
./deploy/deploy.sh

# Create admin user
php artisan tinker
\App\Models\User::create(['name' => 'Admin', 'email' => 'admin@domain.com', 'password' => bcrypt('password'), 'role' => 'admin', 'email_verified_at' => now()]);

# Clear caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/apache2/chore-tracker_error.log

# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

## Troubleshooting Quick Fixes

- **500 Error**: Check file permissions and Apache error logs
- **Database Error**: Verify .env database credentials
- **Assets Not Loading**: Run `npm run build` and check permissions
- **Routes Not Working**: Enable mod_rewrite and check .htaccess 