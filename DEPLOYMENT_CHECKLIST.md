# 🚀 AWS EC2 Deployment Checklist

Use this checklist for deploying your Research Portal to AWS EC2.

## ☁️ AWS Account Setup

- [ ] Create AWS Free Tier account
- [ ] Verify email and complete registration
- [ ] Set up billing alerts (optional but recommended)

## 📦 S3 Bucket Setup

- [ ] Create S3 bucket (e.g., `research-portal-papers`)
- [ ] Choose region closest to you
- [ ] Uncheck "Block all public access"
- [ ] Add bucket policy for public read access
- [ ] Configure CORS (optional)
- [ ] Note down: **Bucket name** and **Region**

## 👤 IAM User Setup

- [ ] Create IAM user: `research-portal-s3-user`
- [ ] Attach policy: `AmazonS3FullAccess` (or custom policy)
- [ ] Generate access keys
- [ ] Save **Access Key ID** securely
- [ ] Save **Secret Access Key** securely (shown only once!)

## 💻 EC2 Instance Setup

- [ ] Launch EC2 instance
  - [ ] AMI: Ubuntu 24.04 LTS
  - [ ] Instance type: t2.micro (Free Tier)
  - [ ] Create/download key pair (.pem file)
- [ ] Configure Security Group
  - [ ] Allow SSH (port 22) from your IP
  - [ ] Allow HTTP (port 80) from anywhere
  - [ ] Allow HTTPS (port 443) from anywhere
- [ ] Launch instance
- [ ] Note down: **Public IP address**

## 🔐 SSH Connection

- [ ] Change key permissions: `chmod 400 your-key.pem`
- [ ] SSH into instance: `ssh -i your-key.pem ubuntu@your-ec2-ip`
- [ ] Successfully connected?

## 📥 Server Setup

```bash
# Update system
- [ ] sudo apt update
- [ ] sudo apt upgrade -y

# Install Apache
- [ ] sudo apt install -y apache2
- [ ] sudo systemctl status apache2

# Install MySQL
- [ ] sudo apt install -y mysql-server
- [ ] sudo mysql_secure_installation

# Install PHP and extensions
- [ ] sudo apt install -y php php-mysql php-xml php-curl php-mbstring libapache2-mod-php

# Install Composer
- [ ] curl -sS https://getcomposer.org/installer | php
- [ ] sudo mv composer.phar /usr/local/bin/composer

# Install Git
- [ ] sudo apt install -y git unzip
```

## 📁 Project Deployment

```bash
# Navigate to web root
- [ ] cd /var/www/html

# Clone repository
- [ ] sudo git clone <your-repo-url> research-portal
- [ ] cd research-portal

# Install dependencies
- [ ] composer install --no-dev --optimize-autoloader

# Verify vendor folder exists
- [ ] ls -la vendor/
```

## 🗄️ Database Setup

```bash
- [ ] sudo mysql -u root -p
```

```sql
-- Create database and user
- [ ] CREATE DATABASE research_portal;
- [ ] CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'YourSecurePassword123!';
- [ ] GRANT ALL PRIVILEGES ON research_portal.* TO 'portal_user'@'localhost';
- [ ] FLUSH PRIVILEGES;
- [ ] USE research_portal;

-- Create tables (run all CREATE TABLE statements)
- [ ] Users table created
- [ ] Submissions table created
- [ ] Comments table created

-- Verify tables
- [ ] SHOW TABLES;
- [ ] EXIT;
```

## ⚙️ Configuration Files

### Database Configuration
- [ ] Edit: `sudo nano config/db.php`
- [ ] Update database host: `localhost`
- [ ] Update database user: `portal_user`
- [ ] Update database password: (your password)
- [ ] Update database name: `research_portal`
- [ ] Save and exit (Ctrl+X, Y, Enter)

### AWS S3 Configuration
- [ ] Edit: `sudo nano config/aws.php`
- [ ] Update `AWS_ACCESS_KEY_ID`
- [ ] Update `AWS_SECRET_ACCESS_KEY`
- [ ] Update `AWS_REGION` (e.g., us-east-1)
- [ ] Update `AWS_S3_BUCKET` (your bucket name)
- [ ] Save and exit

## 🔧 Permissions

```bash
- [ ] sudo chown -R www-data:www-data /var/www/html/research-portal
- [ ] sudo chmod -R 755 /var/www/html/research-portal
- [ ] sudo chmod -R 777 /var/www/html/research-portal/submissions/uploads
```

## 🌐 Apache Configuration

```bash
# Test Apache configuration
- [ ] sudo apache2ctl configtest

# Restart Apache
- [ ] sudo systemctl restart apache2

# Enable mod_rewrite (if needed)
- [ ] sudo a2enmod rewrite
- [ ] sudo systemctl restart apache2
```

## ✅ Testing

### Test AWS Configuration
```bash
- [ ] php test_aws_config.php
- [ ] All tests passed?
```

### Test Website Access
- [ ] Open browser: `http://your-ec2-ip/research-portal/`
- [ ] Homepage loads correctly?
- [ ] Can register new user?
- [ ] Can login?
- [ ] Can upload paper?
- [ ] Upload success message shows "to S3"?
- [ ] Can view uploaded papers in feed?
- [ ] Can post comments?
- [ ] Can download papers from S3?

### Verify in AWS Console
- [ ] Check S3 bucket
- [ ] Files appear in `papers/2026/01/` folder?
- [ ] Can access file URLs directly?

## 🔒 Security Hardening (Optional but Recommended)

- [ ] Install fail2ban: `sudo apt install fail2ban`
- [ ] Configure UFW firewall
- [ ] Set up automatic security updates
- [ ] Install SSL certificate (Let's Encrypt)
- [ ] Change default SSH port
- [ ] Disable root login
- [ ] Set up database backups

## 📊 Monitoring

- [ ] Set up CloudWatch alarms (Free Tier)
- [ ] Monitor EC2 CPU usage
- [ ] Monitor S3 storage usage
- [ ] Check Apache error logs: `tail -f /var/log/apache2/error.log`
- [ ] Check PHP errors: `tail -f /var/log/apache2/error.log | grep PHP`

## 🎓 College Project Documentation

- [ ] Take screenshots of:
  - [ ] AWS Console (EC2, S3)
  - [ ] Working website
  - [ ] Upload functionality
  - [ ] S3 bucket contents
  - [ ] Database tables
- [ ] Document:
  - [ ] Architecture diagram
  - [ ] Database schema
  - [ ] Technologies used
  - [ ] Features implemented
  - [ ] Security measures

## 💰 Cost Management

- [ ] Set up billing alerts
- [ ] Monitor Free Tier usage
- [ ] Stop EC2 instance when not in use (for demos)
- [ ] Delete test files from S3
- [ ] Review monthly costs

## 🐛 Troubleshooting

### If website doesn't load:
- [ ] Check Apache: `sudo systemctl status apache2`
- [ ] Check error logs: `tail -50 /var/log/apache2/error.log`
- [ ] Check permissions on files
- [ ] Verify Security Group allows HTTP

### If S3 upload fails:
- [ ] Run: `php test_aws_config.php`
- [ ] Verify AWS credentials in config/aws.php
- [ ] Check IAM user permissions
- [ ] Verify bucket name and region

### If database errors:
- [ ] Check MySQL: `sudo systemctl status mysql`
- [ ] Verify database credentials in config/db.php
- [ ] Test connection: `mysql -u portal_user -p research_portal`

## ✨ Completion

- [ ] All features working
- [ ] Documentation complete
- [ ] Screenshots captured
- [ ] Project ready for demonstration!

---

**Congratulations! Your Research Portal is now live on AWS!** 🎉

Access your portal at: `http://your-ec2-public-ip/research-portal/`

Remember to stop your EC2 instance when not in use to save Free Tier hours!
