# EC2 Setup Guide for Research Portal

## Table of Contents
1. [EC2 Instance Requirements](#ec2-instance-requirements)
2. [Initial EC2 Setup](#initial-ec2-setup)
3. [Install Required Software](#install-required-software)
4. [Configure Apache Web Server](#configure-apache-web-server)
5. [Setup MySQL Database](#setup-mysql-database)
6. [Deploy Application](#deploy-application)
7. [Configure AWS Credentials](#configure-aws-credentials)
8. [Set File Permissions](#set-file-permissions)
9. [Configure SSL/HTTPS (Optional)](#configure-sslhttps-optional)
10. [Security Configuration](#security-configuration)
11. [Testing & Verification](#testing--verification)
12. [Maintenance Commands](#maintenance-commands)

---

## EC2 Instance Requirements

### Recommended Specifications
- **Instance Type**: t2.medium or t3.medium (minimum t2.small)
- **Operating System**: Ubuntu 22.04 LTS or Amazon Linux 2023
- **Storage**: 20 GB General Purpose SSD (gp3)
- **vCPUs**: 2
- **RAM**: 4 GB

### Security Group Configuration
Create a security group with the following inbound rules:

| Type | Protocol | Port Range | Source | Description |
|------|----------|------------|--------|-------------|
| SSH | TCP | 22 | Your IP/0.0.0.0/0 | SSH access |
| HTTP | TCP | 80 | 0.0.0.0/0 | Web traffic |
| HTTPS | TCP | 443 | 0.0.0.0/0 | Secure web traffic |
| MySQL | TCP | 3306 | Security Group ID | Internal MySQL (optional) |

### IAM Role Configuration
Create an IAM role with the following policies:
- `AmazonS3FullAccess` (or custom S3 policy for your bucket)
- Attach this role to your EC2 instance

---

## Initial EC2 Setup

### 1. Connect to EC2 Instance
```bash
# SSH into your EC2 instance
ssh -i /path/to/your-key.pem ubuntu@your-ec2-public-ip

# Or for Amazon Linux
ssh -i /path/to/your-key.pem ec2-user@your-ec2-public-ip
```

### 2. Update System Packages
```bash
# For Ubuntu
sudo apt update && sudo apt upgrade -y

# For Amazon Linux
sudo yum update -y
```

### 3. Set Hostname (Optional)
```bash
sudo hostnamectl set-hostname research-portal
```

---

## Install Required Software

### For Ubuntu 22.04 LTS

#### 1. Install Apache Web Server
```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
sudo systemctl status apache2
```

#### 2. Install PHP 8.1 and Required Extensions
```bash
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mysql \
  php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip \
  php8.1-gd php8.1-intl libapache2-mod-php8.1 -y

# Verify PHP installation
php -v
```

#### 3. Install MySQL Server
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql
sudo systemctl status mysql
```

#### 4. Install Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
rm composer-setup.php
```

#### 5. Install Git
```bash
sudo apt install git -y
git --version
```

#### 6. Install Additional Tools
```bash
sudo apt install unzip curl wget vim -y
```

### For Amazon Linux 2023

#### 1. Install Apache Web Server
```bash
sudo yum install httpd -y
sudo systemctl start httpd
sudo systemctl enable httpd
sudo systemctl status httpd
```

#### 2. Install PHP 8.1 and Extensions
```bash
sudo yum install php8.1 php8.1-cli php8.1-mysqlnd php8.1-xml \
  php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd -y

# Verify PHP installation
php -v
```

#### 3. Install MySQL (MariaDB)
```bash
sudo yum install mariadb105-server -y
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo systemctl status mariadb
```

#### 4. Install Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
rm composer-setup.php
```

#### 5. Install Git
```bash
sudo yum install git -y
git --version
```

---

## Configure Apache Web Server

### 1. Create Virtual Host Configuration

#### For Ubuntu
```bash
sudo nano /etc/apache2/sites-available/research-portal.conf
```

Add the following configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    ServerAdmin admin@your-domain.com
    
    DocumentRoot /var/www/html/research-portal
    
    <Directory /var/www/html/research-portal>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/research-portal-error.log
    CustomLog ${APACHE_LOG_DIR}/research-portal-access.log combined
    
    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    # Security Headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

Save and exit (Ctrl+X, Y, Enter)

#### For Amazon Linux
```bash
sudo nano /etc/httpd/conf.d/research-portal.conf
```

Use the same configuration as above, but adjust log paths:
```apache
ErrorLog /var/log/httpd/research-portal-error.log
CustomLog /var/log/httpd/research-portal-access.log combined
```

### 2. Enable Required Apache Modules

#### Ubuntu
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2ensite research-portal.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

#### Amazon Linux
```bash
# Edit main httpd.conf to ensure mod_rewrite and mod_headers are loaded
sudo systemctl restart httpd
```

### 3. Configure PHP Settings
```bash
# For Ubuntu
sudo nano /etc/php/8.1/apache2/php.ini

# For Amazon Linux
sudo nano /etc/php.ini
```

Update these settings:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
date.timezone = America/New_York
```

Restart Apache:
```bash
# Ubuntu
sudo systemctl restart apache2

# Amazon Linux
sudo systemctl restart httpd
```

---

## Setup MySQL Database

### 1. Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

Follow the prompts:
- Set root password: **YES** (choose a strong password)
- Remove anonymous users: **YES**
- Disallow root login remotely: **YES**
- Remove test database: **YES**
- Reload privilege tables: **YES**

### 2. Create Database and User
```bash
sudo mysql -u root -p
```

Execute the following SQL commands:
```sql
-- Create database
CREATE DATABASE IF NOT EXISTS research_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Create user with strong password
CREATE USER 'studentuser'@'localhost' IDENTIFIED BY 'student123';

-- Grant privileges
GRANT ALL PRIVILEGES ON research_portal.* TO 'studentuser'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User = 'studentuser';

-- Exit
EXIT;
```

### 3. Test Database Connection
```bash
mysql -u studentuser -pstudent123 -e "USE research_portal; SHOW TABLES;"
```

---

## Deploy Application

### 1. Create Web Directory
```bash
sudo mkdir -p /var/www/html
cd /var/www/html
```

### 2. Clone or Upload Project

#### Option A: Clone from Git Repository
```bash
# If using Git
sudo git clone https://github.com/yourusername/research-portal.git
cd research-portal
```

#### Option B: Upload via SCP
```bash
# From your local machine
scp -i /path/to/your-key.pem -r /path/to/research-portal ubuntu@your-ec2-ip:/tmp/

# On EC2 instance
sudo mv /tmp/research-portal /var/www/html/
cd /var/www/html/research-portal
```

#### Option C: Manual Upload
```bash
# Create directory
sudo mkdir -p /var/www/html/research-portal
cd /var/www/html/research-portal

# Upload files using FileZilla or similar tool
```

### 3. Install PHP Dependencies
```bash
cd /var/www/html/research-portal
sudo composer install --no-dev --optimize-autoloader
```

### 4. Import Database Schema
```bash
cd /var/www/html/research-portal/database
mysql -u studentuser -pstudent123 research_portal < schema.sql

# Verify tables were created
mysql -u studentuser -pstudent123 -e "USE research_portal; SHOW TABLES;"
```

### 5. Configure Database Connection
```bash
cd /var/www/html/research-portal/config
sudo nano db.php
```

Verify the configuration:
```php
<?php

$conn = mysqli_connect(
    "localhost",
    "studentuser",
    "student123",
    "research_portal"
);

if(!$conn){
    die("Database connection failed: " . mysqli_connect_error());
}

?>
```

---

## Configure AWS Credentials

### 1. Setup AWS Configuration Files

#### Option A: Use IAM Role (Recommended for EC2)
The instance will automatically use the attached IAM role - no credentials needed!

Just verify the config file:
```bash
cd /var/www/html/research-portal/config
sudo cp aws.php.example aws.php
sudo nano aws.php
```

Ensure it looks like this for IAM role usage:
```php
<?php
require '../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// S3 Client Configuration - Uses IAM Role automatically
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1',  // Change to your region
    // No credentials needed - uses EC2 instance IAM role
]);

// Your S3 bucket name
define('S3_BUCKET', 'your-bucket-name');
define('S3_REGION', 'us-east-1');

?>
```

#### Option B: Use AWS Credentials File
```bash
# Create AWS directory for www-data user
sudo mkdir -p /var/www/.aws
sudo nano /var/www/.aws/credentials
```

Add your credentials:
```ini
[default]
aws_access_key_id = YOUR_ACCESS_KEY_ID
aws_secret_access_key = YOUR_SECRET_ACCESS_KEY
```

Create config file:
```bash
sudo nano /var/www/.aws/config
```

Add:
```ini
[default]
region = us-east-1
output = json
```

Set proper ownership:
```bash
sudo chown -R www-data:www-data /var/www/.aws
sudo chmod 600 /var/www/.aws/credentials
sudo chmod 644 /var/www/.aws/config
```

### 2. Update aws.php Configuration
```bash
sudo nano /var/www/html/research-portal/config/aws.php
```

Update with your S3 bucket details:
```php
// Your S3 bucket name
define('S3_BUCKET', 'research-portal-uploads-bucket');
define('S3_REGION', 'us-east-1');
```

### 3. Test AWS Configuration
```bash
cd /var/www/html/research-portal
php test_aws_config.php
```

---

## Set File Permissions

### 1. Set Ownership
```bash
# Set ownership to Apache user
# Ubuntu uses www-data
sudo chown -R www-data:www-data /var/www/html/research-portal

# Amazon Linux uses apache
# sudo chown -R apache:apache /var/www/html/research-portal
```

### 2. Set Directory Permissions
```bash
cd /var/www/html/research-portal

# Set base permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# Make uploads directory writable
sudo chmod 775 submissions/uploads
sudo chown www-data:www-data submissions/uploads

# Protect sensitive files
sudo chmod 640 config/db.php
sudo chmod 640 config/aws.php
```

### 3. Create Required Directories
```bash
# Create any missing directories
sudo mkdir -p /var/www/html/research-portal/submissions/uploads
sudo mkdir -p /var/www/html/research-portal/assets/css
sudo chown -R www-data:www-data /var/www/html/research-portal
```

---

## Configure SSL/HTTPS (Optional)

### Using Let's Encrypt (Free SSL)

#### 1. Install Certbot
```bash
# Ubuntu
sudo apt install certbot python3-certbot-apache -y

# Amazon Linux
sudo yum install certbot python3-certbot-apache -y
```

#### 2. Obtain SSL Certificate
```bash
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

Follow the prompts:
- Enter email address
- Agree to terms
- Choose redirect option (recommended: redirect HTTP to HTTPS)

#### 3. Test Auto-renewal
```bash
sudo certbot renew --dry-run
```

#### 4. Verify SSL Configuration
```bash
# Ubuntu
sudo nano /etc/apache2/sites-available/research-portal-le-ssl.conf

# Amazon Linux
sudo nano /etc/httpd/conf.d/research-portal-le-ssl.conf
```

---

## Security Configuration

### 1. Configure Firewall (UFW for Ubuntu)
```bash
# Ubuntu
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

### 2. Disable Directory Listing
```bash
sudo nano /etc/apache2/apache2.conf  # Ubuntu
# or
sudo nano /etc/httpd/conf/httpd.conf  # Amazon Linux
```

Ensure `Options -Indexes` is set in Directory directives.

### 3. Hide PHP Version
```bash
# Ubuntu
sudo nano /etc/php/8.1/apache2/php.ini

# Amazon Linux
sudo nano /etc/php.ini
```

Set:
```ini
expose_php = Off
```

### 4. Protect Sensitive Files
Create/update `.htaccess` in project root:
```bash
sudo nano /var/www/html/research-portal/.htaccess
```

Add:
```apache
# Protect sensitive files
<FilesMatch "^(composer\.json|composer\.lock|\.git)">
    Require all denied
</FilesMatch>

# Protect config directory
<Directory "/var/www/html/research-portal/config">
    <FilesMatch "\.(php|example)$">
        Require all denied
    </FilesMatch>
</Directory>

# Allow only specific PHP files to be accessed
<Files "*.php">
    Require all denied
</Files>

<FilesMatch "^(index|login|register|logout|upload|add_comment)\.php$">
    Require all granted
</FilesMatch>
```

### 5. Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/research-portal
```

Add:
```
/var/log/apache2/research-portal-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 640 root adm
    sharedscripts
    postrotate
        /etc/init.d/apache2 reload > /dev/null
    endscript
}
```

---

## Testing & Verification

### 1. Test Apache Configuration
```bash
# Ubuntu
sudo apache2ctl configtest

# Amazon Linux
sudo httpd -t
```

Should return: `Syntax OK`

### 2. Test PHP
```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/research-portal/info.php
```

Visit: `http://your-ec2-ip/info.php`

**Important:** Delete this file after testing:
```bash
sudo rm /var/www/html/research-portal/info.php
```

### 3. Test Database Connection
```bash
cd /var/www/html/research-portal
php -r "require 'config/db.php'; echo 'Database connected successfully!';"
```

### 4. Test AWS S3 Connection
```bash
cd /var/www/html/research-portal
php test_aws_config.php
```

### 5. Verify Database Schema
```bash
cd /var/www/html/research-portal/database
bash verify_database.sh
```

### 6. Test Application Access
Visit the following URLs:
- `http://your-ec2-ip/` - Main page
- `http://your-ec2-ip/auth/login.php` - Login page
- `http://your-ec2-ip/auth/register.php` - Registration page

### 7. Check Apache Logs
```bash
# Ubuntu
sudo tail -f /var/log/apache2/research-portal-error.log
sudo tail -f /var/log/apache2/research-portal-access.log

# Amazon Linux
sudo tail -f /var/log/httpd/research-portal-error.log
sudo tail -f /var/log/httpd/research-portal-access.log
```

### 8. Check PHP Error Logs
```bash
sudo tail -f /var/log/php_errors.log
```

---

## Maintenance Commands

### Service Management

#### Apache
```bash
# Ubuntu
sudo systemctl start apache2
sudo systemctl stop apache2
sudo systemctl restart apache2
sudo systemctl reload apache2
sudo systemctl status apache2

# Amazon Linux
sudo systemctl start httpd
sudo systemctl stop httpd
sudo systemctl restart httpd
sudo systemctl reload httpd
sudo systemctl status httpd
```

#### MySQL
```bash
sudo systemctl start mysql    # Ubuntu
sudo systemctl start mariadb  # Amazon Linux
sudo systemctl stop mysql
sudo systemctl restart mysql
sudo systemctl status mysql
```

### Database Operations

#### Backup Database
```bash
cd /var/www/html/research-portal/database
bash backup.sh

# Or manually
mysqldump -u studentuser -pstudent123 research_portal > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### Restore Database
```bash
mysql -u studentuser -pstudent123 research_portal < backup_file.sql
```

### File Operations

#### Pull Latest Code (if using Git)
```bash
cd /var/www/html/research-portal
sudo git pull origin main
sudo composer install --no-dev --optimize-autoloader
sudo chown -R www-data:www-data .
sudo systemctl restart apache2
```

#### Clear Cache (if applicable)
```bash
cd /var/www/html/research-portal
sudo rm -rf cache/*
sudo rm -rf tmp/*
```

### Monitoring

#### Disk Usage
```bash
df -h
du -sh /var/www/html/research-portal
```

#### Memory Usage
```bash
free -h
```

#### Process Monitoring
```bash
htop
# or
top
```

#### Active Connections
```bash
# Ubuntu
sudo netstat -tulpn | grep apache2

# Amazon Linux
sudo netstat -tulpn | grep httpd
```

### System Updates
```bash
# Ubuntu
sudo apt update
sudo apt upgrade -y
sudo apt autoremove -y

# Amazon Linux
sudo yum update -y
sudo yum clean all
```

---

## Troubleshooting

### Common Issues

#### 1. Permission Denied Errors
```bash
sudo chown -R www-data:www-data /var/www/html/research-portal
sudo chmod -R 755 /var/www/html/research-portal
sudo chmod 775 submissions/uploads
```

#### 2. Database Connection Errors
```bash
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u studentuser -pstudent123 -e "SHOW DATABASES;"

# Check error logs
sudo tail -f /var/log/mysql/error.log
```

#### 3. Apache Won't Start
```bash
# Check configuration
sudo apache2ctl configtest

# Check logs
sudo tail -f /var/log/apache2/error.log

# Check port conflicts
sudo netstat -tulpn | grep :80
```

#### 4. PHP Files Download Instead of Execute
```bash
# Ubuntu
sudo a2enmod php8.1
sudo systemctl restart apache2

# Verify PHP module
apache2ctl -M | grep php
```

#### 5. S3 Upload Failures
```bash
# Check IAM role
aws sts get-caller-identity

# Test S3 access
aws s3 ls s3://your-bucket-name

# Check PHP error logs
sudo tail -f /var/log/php_errors.log
```

---

## Performance Optimization

### 1. Enable PHP OPcache
```bash
# Ubuntu
sudo nano /etc/php/8.1/apache2/php.ini

# Amazon Linux
sudo nano /etc/php.ini
```

Add/update:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### 2. Enable Apache Compression
```bash
# Ubuntu
sudo a2enmod deflate
sudo systemctl restart apache2
```

Add to virtual host:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Enable Browser Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Quick Reference Commands

```bash
# Restart everything
sudo systemctl restart apache2 mysql

# View all logs
sudo tail -f /var/log/apache2/* /var/log/php_errors.log

# Check all services
sudo systemctl status apache2 mysql

# Update application
cd /var/www/html/research-portal && sudo git pull && sudo composer install

# Backup everything
mysqldump -u studentuser -pstudent123 research_portal > ~/backup.sql
sudo tar -czf ~/research-portal-backup.tar.gz /var/www/html/research-portal

# Check disk space
df -h && du -sh /var/www/html/research-portal
```

---

## Security Checklist

- [ ] EC2 security group configured with minimal required ports
- [ ] IAM role attached to EC2 instance for S3 access
- [ ] MySQL secure installation completed
- [ ] Strong database passwords set
- [ ] File permissions properly configured (755/644)
- [ ] Sensitive files protected (600/640)
- [ ] SSL/HTTPS enabled
- [ ] Firewall configured (UFW/Security Groups)
- [ ] PHP version hidden (expose_php = Off)
- [ ] Directory listing disabled
- [ ] Error display disabled in production
- [ ] Log rotation configured
- [ ] Regular backups scheduled
- [ ] System updates automated or scheduled

---

## Next Steps

1. ✅ Complete EC2 setup following this guide
2. ✅ Test all functionality (login, upload, S3 integration)
3. 📝 Configure custom domain name (Route 53)
4. 🔒 Setup SSL certificate (Let's Encrypt)
5. 📊 Configure monitoring (CloudWatch)
6. 🔄 Setup automated backups
7. 📧 Configure email notifications (SES)
8. 🚀 Optimize for production (caching, CDN)

---

**Last Updated**: January 3, 2026
**Project**: Research Portal
**Environment**: AWS EC2 (Ubuntu 22.04 / Amazon Linux 2023)
