# Deployment Files Overview

This directory contains all the files needed to deploy the Laravel Chore Tracker application to a LAMP server.

## 📋 Deployment Files

### **DEPLOYMENT_CHECKLIST.md**
Quick reference checklist for deployment steps. Use this to ensure you don't miss any critical steps during deployment.

### **README.md** 
Complete step-by-step deployment guide with detailed instructions, prerequisites, and troubleshooting tips.

### **deploy.sh** *(Executable)*
Automated deployment script that handles:
- Composer dependency installation
- NPM asset building
- Laravel optimization (caching, migrations)
- File permission setup

**Usage:** `./deploy/deploy.sh`

### **env-template.txt**
Template for production `.env` file. Copy this to `.env` and fill in your production values.

**Usage:** `cp deploy/env-template.txt .env`

### **database-setup.sql**
MySQL script to create the database and user for the application.

**Usage:** `mysql -u root -p < deploy/database-setup.sql`

### **apache-vhost.conf**
Apache virtual host configuration for both HTTP and HTTPS.

**Usage:** 
```bash
sudo cp deploy/apache-vhost.conf /etc/apache2/sites-available/chore-tracker.conf
sudo a2ensite chore-tracker.conf
```

### **htaccess-production**
Enhanced `.htaccess` file with security headers, performance optimizations, and HTTPS redirects.

**Usage:** `cp deploy/htaccess-production public/.htaccess`

## 🚀 Quick Start

1. **Read the README**: Start with `README.md` for complete instructions
2. **Use the checklist**: Follow `DEPLOYMENT_CHECKLIST.md` step by step
3. **Run the script**: Execute `./deploy/deploy.sh` for automated setup
4. **Configure server**: Use the provided Apache and database configuration files

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section in `README.md`
2. Review the `DEPLOYMENT_CHECKLIST.md` for missed steps
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Check Apache logs: `tail -f /var/log/apache2/error.log`

## 🔐 Security Notes

- Update all default passwords in the configuration files
- Set proper file permissions as outlined in the README
- Configure SSL certificates for HTTPS
- Enable firewall rules as recommended
- Regularly update dependencies and server software 