# Contact Form API - Deployment Guide

This directory contains the backend API for the contact form on peterebenhoch.com.

## Files

- `submit-contact.php` - Main form handler (processes submissions via Mailbox.org SMTP)
- `composer.json` - PHP dependencies (PHPMailer, dotenv)
- `.env.example` - Template for environment variables
- `.env` - Actual credentials (DO NOT commit to git!)

## Deployment Steps

### 1. Install on Hetzner Server

```bash
# SSH into your server
ssh user@your-server

# Create API directory
sudo mkdir -p /var/www/peterebenhoch.com/api
cd /var/www/peterebenhoch.com/api

# Upload files (from your local Mac)
# Run this from your local machine:
scp -r /path/to/project/api/* user@your-server:/var/www/peterebenhoch.com/api/
```

### 2. Install Dependencies

```bash
# On the server, install Composer if not present
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
cd /var/www/peterebenhoch.com/api
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment Variables

```bash
# Copy example to actual .env file
cp .env.example .env

# Edit with your Mailbox.org credentials
nano .env

# Fill in:
# - SMTP_USERNAME: Your Mailbox.org email
# - SMTP_PASSWORD: Your Mailbox.org password
# - TO_EMAIL: pe@peterebenhoch.com
```

### 4. Set Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/peterebenhoch.com/api

# Secure .env file (read-only, owner only)
sudo chmod 600 .env

# Make PHP files readable
sudo chmod 644 *.php
```

### 5. Configure Web Server

#### For Nginx:

```nginx
# In /etc/nginx/sites-available/peterebenhoch.com

location /api/ {
    alias /var/www/peterebenhoch.com/api/;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        include fastcgi_params;
    }
    
    # Protect .env file
    location ~ /\.env {
        deny all;
        return 404;
    }
}
```

#### For Apache:

Create `.htaccess` in the api directory:

```apache
# Protect .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 6. Restart Web Server

```bash
# For Nginx
sudo systemctl restart nginx php-fpm

# For Apache
sudo systemctl restart apache2
```

## Testing

### 1. Test PHP Syntax

```bash
php -l submit-contact.php
```

### 2. Test Form Submission

Visit: https://peterebenhoch.com/contact.html

Fill out the form and submit. Check pe@peterebenhoch.com for the email.

### 3. Check Logs

```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Apache error log
sudo tail -f /var/log/apache2/error.log

# PHP error log
sudo tail -f /var/log/php-fpm/error.log
```

## Troubleshooting

### SMTP Connection Failed

- Verify Mailbox.org credentials in `.env`
- Check if port 587 is open: `telnet smtp.mailbox.org 587`
- Verify SMTP_FROM_EMAIL is authorized in Mailbox.org

### Permission Denied

```bash
sudo chown -R www-data:www-data /var/www/peterebenhoch.com/api
sudo chmod 755 /var/www/peterebenhoch.com/api
```

### 500 Internal Server Error

Check PHP error logs and ensure:
- Composer dependencies are installed
- `.env` file exists and is readable by www-data
- PHP version >= 7.4

### Rate Limiting Issues

If legitimate users are blocked:
- Increase `MAX_REQUESTS_PER_HOUR` in `.env`
- Or clear PHP sessions: `rm -rf /var/lib/php/sessions/*`

## Security Checklist

- ✅ `.env` file is in `.gitignore`
- ✅ `.env` has 600 permissions (owner read/write only)
- ✅ HTTPS is enabled on peterebenhoch.com
- ✅ CORS is restricted to peterebenhoch.com domain
- ✅ Rate limiting is active (5 requests/hour per IP)
- ✅ Input validation and sanitization is implemented

## Maintenance

### Update Dependencies

```bash
cd /var/www/peterebenhoch.com/api
composer update
```

### Monitor Submission Volume

Check email volume at pe@peterebenhoch.com to ensure no spam.

### Backup

Important files to backup:
- `.env` (credentials)
- `submit-contact.php` (if customized)

## Next Steps (Future Enhancements)

- Add SQLite database to store submissions
- Implement Attio CRM integration
- Create admin dashboard to view submissions
- Add email confirmation to users
- Implement honeypot for additional spam protection
