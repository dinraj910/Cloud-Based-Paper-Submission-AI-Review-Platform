

# ☁️ **Research Portal**

**A modern, cloud-native web application for academic research paper submission, peer review, and AI-powered analysis.**

This platform enables researchers to securely upload, share, and review academic papers online. Built with PHP, MySQL, and Tailwind CSS, and designed for seamless deployment on AWS EC2 and S3, it supports scalable, secure, and collaborative research workflows. The system is modular, AI/ML-ready, and follows best practices for cloud and academic environments.

**Cloud-Based Paper Submission & Review Platform**

![PHP](https://img.shields.io/badge/PHP-8.x-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.x-blue?logo=mysql)
![AWS](https://img.shields.io/badge/AWS-EC2%20|%20S3-orange?logo=amazon-aws)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?logo=tailwindcss)

Modern, scalable, and AI-ready research platform for the cloud era.

---


## 🚀 Features

- **Cloud-Native Submission & Review**
- Secure user authentication (register/login, hashed passwords)
- MySQL relational database (users, submissions, reviews, analysis)
- Peer review & commenting
- Modern, responsive UI (Tailwind CSS, card-based)
- AWS EC2 & S3 ready (Free Tier friendly)
- AI/ML integration ready (analysis results, JSON)
- Best security practices (no local file storage, centralized config)
---

## 🧠 How It Works

### 1. User Registration & Login
- Users register with their name, email, and password (securely hashed).
- Login is required to submit or review papers.

### 2. Paper Submission
- Authenticated users can upload research papers (PDF, DOC, DOCX, TXT).
- Files are stored (locally or on S3 in production); only metadata and S3 URL are saved in MySQL.
- Each submission includes title, description, file type, and upload timestamp.

### 3. Feed & Discovery
- The homepage displays a feed of recent submissions in a card-based layout.
- Each card shows the paper title, author, file type, and description.
- Users can download/view papers directly from the feed.

### 4. Peer Review & Comments
- Any registered user can leave reviews/comments on any submission.
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
│   ├── header.php
│   └── footer.php
├── submissions/
│   ├── upload.php
│   └── uploads/ (ignored)
├── assets/
│   └── css/
│       └── tailwind.php
├── reviews/
├── analysis/
├── .gitignore
└── README.md
```

---

## 🛠️ Deployment on AWS EC2

1. **Launch an EC2 Instance** (Ubuntu, Free Tier)
2. Install dependencies:
   ```sh
   sudo apt update && sudo apt install apache2 php libapache2-mod-php php-mysqli git
   ```
3. **Clone this repo:**
   ```sh
   git clone https://github.com/yourusername/research-portal.git
   cd research-portal
   ```
4. **Configure MySQL:**
   - Create the database and tables (see schema below)
   - Update `config/db.php` with your credentials
5. **Set up S3 (optional):**
   - Add AWS SDK and credentials for file uploads
6. **Set permissions:**
   ```sh
   sudo chmod -R 777 submissions/uploads
   ```
7. **Access your site:**
   - `http://<EC2-PUBLIC-IP>/research-portal/`

---

## 🗄️ Database Schema

<details>
<summary>Click to expand</summary>

<pre>
users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  s3_file_url TEXT,
  file_type VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

reviews (
  id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT,
  user_id INT,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

analysis_results (
  id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT,
  model_name VARCHAR(100),
  summary TEXT,
  score FLOAT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
</pre>
</details>

---


## 👨‍💻 Author & Expertise

**AWS Certified Solutions Architect**  
**Expert in Cloud, Virtualization, and DevOps**  
5+ years deploying scalable, secure web apps on AWS  
Passionate about academic technology, automation, and cloud-native design  

---



## 🤝 Contributing

Pull requests and suggestions are welcome!

---

## 📄 License

MIT
