

# ☁️ **Research Portal**

**A modern, cloud-native web application for academic research paper submission, peer review, and AI-powered analysis.**

This platform enables researchers to securely upload, share, and review academic papers online. Built with PHP, MySQL, AWS S3, and Tailwind CSS, and designed for seamless deployment on AWS EC2. It supports scalable, secure, and collaborative research workflows with real-time commenting and beautiful modern UI.

**Cloud-Based Paper Submission & Review Platform**

![PHP](https://img.shields.io/badge/PHP-8.x-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.x-blue?logo=mysql)
![AWS](https://img.shields.io/badge/AWS-EC2%20|%20S3-orange?logo=amazon-aws)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?logo=tailwindcss)

Modern, scalable, and AI-ready research platform for the cloud era.

---


## 🚀 Features

- ☁️ **AWS S3 Integration** - Automatic cloud storage for all research papers
- 🔐 **Secure Authentication** - User registration/login with password hashing
- 💬 **Real-time Comments** - AJAX-powered commenting system
- 📊 **Community Stats** - Live statistics dashboard
- 🎨 **Modern UI** - Glassmorphism design with smooth animations
- 📱 **Responsive Design** - Works perfectly on all devices
- 🗄️ **MySQL Database** - Relational database with proper foreign keys
- 🚀 **EC2 Ready** - Optimized for AWS Free Tier deployment
- 🔄 **Fallback Storage** - Local storage when AWS not configured
- 🎯 **Production Ready** - Follows best security practices

---

## 🧠 How It Works

### 1. User Registration & Login
- Users register with their name, email, and password (securely hashed).
- Login is required to submit papers or post comments.

### 2. Paper Submission with S3
- Authenticated users can upload research papers (PDF, DOC, DOCX, TXT).
- Files are automatically uploaded to AWS S3 (or stored locally as fallback).
- Organized by date: `papers/2026/01/paper_abc123.pdf`
- Only metadata and S3 URL are saved in MySQL.
- Each submission includes title, description, file type, and upload timestamp.

### 3. Feed & Discovery
- The homepage displays a feed of recent submissions in a modern card layout.
- Each card shows the paper title, author, file type, description, and comment count.
- Users can download/view papers directly from S3.
- Glassmorphism effects and smooth hover animations.

### 4. Real-time Comments
- Any registered user can comment on any submission.
- AJAX-powered posting without page reload.
- Comments display with user avatars and timestamps.
- Dynamic comment count updates.

- Reviews are stored in the `reviews` table, linked to both the user and the submission.

### 5. AI/ML Analysis (Optional)
- External or offline AI/ML models can analyze submissions and POST results as JSON.
- Results are stored in the `analysis_results` table and can be displayed alongside submissions.
- Example: Topic classification, quality scoring, keyword extraction, etc.

### 6. Security & Best Practices
- Passwords are always hashed.
- No files are stored in the repo or on the web server in production (S3 only).
- Database credentials and sensitive config are kept out of version control.

---

## 🏗️ Project Structure

```text
research-portal/
├── index.php
├── auth/
│   ├── login.php
│   └── register.php
├── config/
│   ├── db.php
│   ├── aws.php (AWS S3 configuration)
│   ├── header.php
│   └── footer.php
├── submissions/
│   ├── upload.php
│   └── uploads/ (fallback local storage)
├── api/
│   └── add_comment.php
├── assets/
│   └── css/
│       └── tailwind.php
├── vendor/ (Composer dependencies)
├── composer.json
├── .gitignore
├── AWS_SETUP_GUIDE.md
└── README.md
```

---

## ⚙️ Quick Setup

### Local Development
```bash
# 1. Clone repository
git clone <your-repo-url>
cd research-portal

# 2. Install PHP dependencies
php composer.phar install

# 3. Configure database
mysql -u root -p
CREATE DATABASE research_portal;
USE research_portal;
# Run schema (see Database Schema section)

# 4. Update database config
# Edit config/db.php with your credentials

# 5. Start local server
php -S localhost:8000

# 6. Access: http://localhost:8000
```

### AWS S3 Configuration
```bash
# 1. See detailed guide
cat AWS_SETUP_GUIDE.md

# 2. Quick steps:
# - Create S3 bucket in AWS Console
# - Create IAM user with S3 permissions
# - Generate access keys
# - Update config/aws.php with credentials

# 3. Test upload - should show:
# "Paper uploaded successfully to S3!"
```

---

## 🛠️ Deployment on AWS EC2

### Step-by-Step EC2 Deployment

1. **Launch EC2 Instance**
   - AMI: Ubuntu 24.04 LTS
   - Instance Type: t2.micro (Free Tier)
   - Security Group: Allow HTTP (80), HTTPS (443), SSH (22)

2. **SSH into Instance**
   ```bash
   ssh -i your-key.pem ubuntu@your-ec2-ip
   ```

3. **Install Dependencies**
   ```bash
   sudo apt update
   sudo apt install -y apache2 mysql-server php php-mysql \
                       php-xml php-curl php-mbstring git unzip
   
   # Install Composer
   cd /tmp
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

4. **Clone Project**
   ```bash
   cd /var/www/html
   sudo git clone <your-repo-url> research-portal
   cd research-portal
   ```

5. **Install PHP Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

6. **Configure Database**
   ```bash
   sudo mysql -u root
   ```
   ```sql
   CREATE DATABASE research_portal;
   CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON research_portal.* TO 'portal_user'@'localhost';
   FLUSH PRIVILEGES;
   USE research_portal;
   -- Run your schema here
   ```

7. **Update Configuration Files**
   ```bash
   # Database
   sudo nano config/db.php
   # Update: host, user, password, database
   
   # AWS S3 (Optional but recommended)
   sudo nano config/aws.php
   # Update: access key, secret key, region, bucket name
   ```

8. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/research-portal
   sudo chmod -R 755 /var/www/html/research-portal
   sudo chmod -R 777 /var/www/html/research-portal/submissions/uploads
   ```

9. **Configure Apache**
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
   Update DocumentRoot to: `/var/www/html`
   
   ```bash
   sudo systemctl restart apache2
   ```

10. **Access Your Site**
    - `http://your-ec2-public-ip/research-portal/`

### Security Best Practices for EC2
- Use IAM Role instead of hardcoded AWS keys
- Enable HTTPS with Let's Encrypt
- Configure firewall rules (Security Groups)
- Regular security updates
- Backup database regularly

---

## 🗄️ Database Schema

<details>
<summary>Click to expand</summary>

```sql
-- Users table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Submissions table
CREATE TABLE submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  s3_file_url TEXT NOT NULL,
  file_type VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table
CREATE TABLE comments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_submission_id (submission_id),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
</details>

---

## 💰 AWS Free Tier Limits

This project is optimized for AWS Free Tier:
- **EC2**: 750 hours/month of t2.micro instance (12 months free)
- **S3**: 5GB storage, 20,000 GET requests, 2,000 PUT requests/month (12 months free)
- **Data Transfer**: 100GB outbound/month (12 months free)

Perfect for college projects and demonstrations!

---

## 📚 Additional Resources

- **AWS Setup Guide**: See `AWS_SETUP_GUIDE.md` for detailed S3 configuration
- **Composer Dependencies**: AWS SDK, Guzzle HTTP client
- **Security**: Password hashing, prepared statements, input validation

---

## 👨‍💻 For Students

This is a complete college project demonstrating:
- ✅ Cloud deployment (AWS EC2 + S3)
- ✅ Full-stack development (PHP, MySQL, JavaScript)
- ✅ Modern UI/UX design
- ✅ RESTful API patterns
- ✅ Database relationships and foreign keys
- ✅ Security best practices
- ✅ Version control (Git)

---



## 🤝 Contributing

Pull requests and suggestions are welcome!

---

## 📄 License

MIT
