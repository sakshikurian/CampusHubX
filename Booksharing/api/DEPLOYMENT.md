# BookSharing API - Deployment & Security Guide

## 📋 Table of Contents
1. [Production Deployment](#production-deployment)
2. [Security Checklist](#security-checklist)
3. [Configuration for Hosting](#configuration-for-hosting)
4. [Testing](#testing)
5. [Monitoring & Logs](#monitoring--logs)
6. [Troubleshooting](#troubleshooting)

---

## 🚀 Production Deployment

### Prerequisites
- PHP 7.4+ (preferably 8.0+)
- MySQL 5.7+ or MariaDB 10.2+
- Apache 2.4+ with mod_rewrite enabled
- SSL/TLS certificate (HTTPS)

### Step 1: Prepare Server
```bash
# SSH into your server
ssh user@yourdomain.com

# Update system packages
sudo apt-get update && sudo apt-get upgrade -y

# Install required packages
sudo apt-get install -y apache2 php php-mysql php-curl php-json
```

### Step 2: Upload Files
```bash
# Upload all BookSharing files to your web root
# Ensure correct permissions:

chmod 755 /var/www/html/booksharing/
chmod 755 /var/www/html/booksharing/api/
chmod 755 /var/www/html/booksharing/uploads/
chmod 600 /var/www/html/booksharing/db_connect.php
chmod 600 /var/www/html/booksharing/api/.htaccess
```

### Step 3: Configure Database
```bash
# Update db_connect.php with production credentials
nano /var/www/html/booksharing/db_connect.php

# Ensure database exists and users table has proper structure
mysql -u root -p < /var/www/html/booksharing/setup.sql
```

### Step 4: Enable HTTPS
```bash
# Install Let's Encrypt SSL
sudo apt-get install -y certbot python3-certbot-apache

# Generate certificate
sudo certbot certonly --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renew certificates
sudo systemctl enable certbot.timer
```

### Step 5: Configure Apache
Create `/etc/apache2/sites-available/booksharing.conf`:

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/booksharing
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
    
    # Security Headers
    <IfModule mod_headers.c>
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
    </IfModule>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/booksharing_error.log
    CustomLog ${APACHE_LOG_DIR}/booksharing_access.log combined
    
    # Rewrite rules
    <Directory /var/www/html/booksharing>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{HTTPS} off
            RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
        </IfModule>
    </Directory>
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite booksharing.conf
sudo a2enmod ssl rewrite headers
sudo systemctl restart apache2
```

---

## 🔒 Security Checklist

### Before Going Live

- [ ] **Database Security**
  - [ ] Change default MySQL root password
  - [ ] Create specific database user with limited privileges
  - [ ] Enable encrypted connections
  - [ ] Regular backups scheduled

- [ ] **File Permissions**
  - [ ] api/ directory: 755
  - [ ] uploads/ directory: 755 (writable)
  - [ ] db_connect.php: 600 (read-only)
  - [ ] logs/ directory: 755

- [ ] **API Security**
  - [ ] Update ALLOWED_ORIGINS in api/config.php
  - [ ] Enable HTTPS only (remove HTTP)
  - [ ] .htaccess enabled for API protection
  - [ ] Error reporting disabled in production

- [ ] **Server Security**
  - [ ] SSH key authentication enabled
  - [ ] Firewall configured (UFW/IPTables)
  - [ ] Only necessary ports open (80, 443, 22)
  - [ ] Automatic security updates enabled

- [ ] **Application Security**
  - [ ] Update all API endpoints for production
  - [ ] Rate limiting enabled (if needed)
  - [ ] CORS properly configured
  - [ ] All debug files removed

---

## ⚙️ Configuration for Hosting

### Update api/config.php

```php
// Set environment
define('ENVIRONMENT', 'production');

// Allow your domain
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',
    'https://www.yourdomain.com',
    // Add any third-party domains that need access
]);

// Enable error logging
define('LOG_ERRORS', true);
define('LOG_DIR', '/var/log/booksharing/'); // Use system log directory
```

### Update api/resources.php

Change line 10-12 to hide database errors:

```php
// In production, log errors instead of showing them
// Database errors should never be exposed to API clients
```

### Update api/download.php

Ensure line 9 uses secure paths:

```php
$uploadsDir = realpath(__DIR__ . "/../uploads/");
// Verify this path is correct for your hosting setup
```

---

## ✅ Testing

### Test API Endpoints

```bash
# Test 1: Get resources
curl -H "Accept: application/json" https://yourdomain.com/api/resources.php

# Test 2: Search resources
curl "https://yourdomain.com/api/resources.php?search=test&limit=5"

# Test 3: Download file (replace ID with real resource ID)
curl -O "https://yourdomain.com/api/download.php?id=1"

# Test 4: Invalid request (should return 404)
curl "https://yourdomain.com/api/download.php?id=99999"
```

### Performance Testing

```bash
# Using Apache Bench (load testing)
ab -n 100 -c 10 https://yourdomain.com/api/resources.php

# Using siege (load testing)
siege -c 10 -r 10 https://yourdomain.com/api/resources.php
```

---

## 📊 Monitoring & Logs

### Check Error Logs

```bash
# API errors
tail -f /var/log/booksharing/api_errors.log

# Apache access logs
tail -f /var/log/apache2/booksharing_access.log

# PHP errors
tail -f /var/log/php_errors.log
```

### Monitor Disk Space

```bash
# Check uploads directory size
du -sh /var/www/html/booksharing/uploads/

# Clean old logs (monthly)
find /var/log/booksharing/ -name "*.log" -mtime +30 -delete
```

### Database Maintenance

```bash
# Backup database (daily)
mysqldump -u user -p database > /backups/booksharing_$(date +%Y%m%d).sql

# Optimize tables (monthly)
mysql -u user -p -e "OPTIMIZE TABLE resources, users, queries, comments;"
```

---

## 🔧 Troubleshooting

### API Returns 500 Error

1. Check error logs:
   ```bash
   tail -f /var/log/booksharing/api_errors.log
   ```

2. Verify database connection:
   ```bash
   mysql -u user -p -e "SELECT 1;"
   ```

3. Check file permissions:
   ```bash
   ls -la /var/www/html/booksharing/db_connect.php
   ```

### Download Not Working

1. Verify file exists in uploads:
   ```bash
   ls -la /var/www/html/booksharing/uploads/
   ```

2. Check permissions:
   ```bash
   chmod 644 /var/www/html/booksharing/uploads/*
   ```

3. Verify database record exists:
   ```bash
   mysql -u user -p -e "SELECT * FROM resources WHERE id=1;"
   ```

### CORS Issues

1. Verify ALLOWED_ORIGINS in config.php
2. Check browser console for specific errors
3. Test with curl:
   ```bash
   curl -H "Origin: https://yourdomain.com" https://yourdomain.com/api/resources.php
   ```

### Performance Issues

1. Check server resources:
   ```bash
   top
   df -h
   free -m
   ```

2. Optimize database queries:
   ```bash
   mysql -u user -p -e "ANALYZE TABLE resources;"
   ```

3. Enable caching (Redis/Memcached recommended)

---

## 📝 Additional Notes

- Always keep backups of database and uploads
- Monitor server logs regularly for suspicious activity
- Update PHP and server packages regularly
- Consider implementing CDN for large file downloads
- Use database connection pooling for high traffic
- Implement rate limiting if needed

---

## 🆘 Support

For issues or questions:
1. Check the error logs
2. Review this guide's troubleshooting section
3. Test endpoints using curl or Postman
4. Verify all configuration files are correct

Happy hosting! 🚀
