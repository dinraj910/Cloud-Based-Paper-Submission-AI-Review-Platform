# 🗄️ Database Configuration Guide

## Current Database Setup

**Database Name:** `research_portal`  
**Username:** `studentuser`  
**Password:** `student123`  
**Host:** `localhost`

## Database Statistics

```bash
# Check current data
mysql -u studentuser -pstudent123 research_portal -e "
SELECT 'Users' as Table_Name, COUNT(*) as Count FROM users
UNION ALL SELECT 'Submissions', COUNT(*) FROM submissions
UNION ALL SELECT 'Comments', COUNT(*) FROM comments
UNION ALL SELECT 'Reviews', COUNT(*) FROM reviews;
"
```

Current data as of setup:
- **Users:** 4
- **Submissions:** 7
- **Comments:** 2
- **Reviews:** 0

## Database Schema

### Tables Overview

1. **users** - User accounts
2. **submissions** - Research papers
3. **comments** - Comments on submissions
4. **reviews** - Peer reviews (for future use)
5. **analysis_results** - AI analysis results (for future use)

### Table Relationships

```
users (1) ──< (M) submissions
  │
  └──< (M) comments
  │
  └──< (M) reviews

submissions (1) ──< (M) comments
  │
  └──< (M) reviews
  │
  └──< (M) analysis_results
```

## Common Database Commands

### Connect to Database
```bash
mysql -u studentuser -pstudent123 research_portal
```

### View All Tables
```sql
SHOW TABLES;
```

### View Table Structure
```sql
DESCRIBE users;
DESCRIBE submissions;
DESCRIBE comments;
```

### View Recent Submissions
```sql
SELECT s.id, s.title, u.name as author, s.created_at
FROM submissions s
LEFT JOIN users u ON s.user_id = u.id
ORDER BY s.created_at DESC
LIMIT 10;
```

### View Recent Comments
```sql
SELECT c.id, u.name as commenter, s.title as paper, c.comment, c.created_at
FROM comments c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN submissions s ON c.submission_id = s.id
ORDER BY c.created_at DESC
LIMIT 10;
```

### Get User Statistics
```sql
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(DISTINCT s.id) as papers_uploaded,
    COUNT(DISTINCT c.id) as comments_posted
FROM users u
LEFT JOIN submissions s ON u.id = s.user_id
LEFT JOIN comments c ON u.id = c.user_id
GROUP BY u.id, u.name, u.email;
```

## Backup & Restore

### Create Backup
```bash
# Using the backup script
./database/backup.sh

# Or manually
mysqldump -u studentuser -pstudent123 research_portal > backup_$(date +%Y%m%d).sql
```

### Restore from Backup
```bash
mysql -u studentuser -pstudent123 research_portal < backup_20260101.sql
```

### Export Specific Table
```bash
mysqldump -u studentuser -pstudent123 research_portal users > users_backup.sql
```

## Database Maintenance

### Check Database Size
```sql
SELECT 
    table_schema as 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'research_portal'
GROUP BY table_schema;
```

### Check Table Sizes
```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows AS 'Rows'
FROM information_schema.TABLES
WHERE table_schema = 'research_portal'
ORDER BY (data_length + index_length) DESC;
```

### Optimize Tables
```sql
OPTIMIZE TABLE users;
OPTIMIZE TABLE submissions;
OPTIMIZE TABLE comments;
```

## For AWS EC2 Deployment

### Create Production Database User
```sql
-- On EC2 instance
CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON research_portal.* TO 'portal_user'@'localhost';
FLUSH PRIVILEGES;
```

### Update config/db.php on EC2
```php
$conn = mysqli_connect(
    "localhost",
    "portal_user",
    "your_secure_password",
    "research_portal"
);
```

### Import Schema on EC2
```bash
# Upload schema.sql to EC2, then:
mysql -u portal_user -p research_portal < database/schema.sql
```

### Import Data from Development
```bash
# On development machine:
mysqldump -u studentuser -pstudent123 research_portal > data_export.sql

# Upload to EC2:
scp -i your-key.pem data_export.sql ubuntu@ec2-ip:/tmp/

# On EC2:
mysql -u portal_user -p research_portal < /tmp/data_export.sql
```

## Troubleshooting

### Cannot connect to database
```bash
# Check MySQL is running
sudo systemctl status mysql

# Check MySQL error log
sudo tail -50 /var/log/mysql/error.log
```

### Foreign key constraint fails
```bash
# Check foreign key relationships
SELECT * FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'research_portal' 
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### Database connection in PHP fails
```php
// Add error reporting in config/db.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
```

## Security Notes

⚠️ **For Production (EC2):**
- Change default passwords
- Use strong passwords (min 16 characters)
- Restrict database user permissions
- Enable MySQL SSL connections
- Regular backups to S3
- Monitor database access logs

## Quick Reference

| Command | Description |
|---------|-------------|
| `SHOW TABLES;` | List all tables |
| `DESCRIBE table_name;` | Show table structure |
| `SELECT * FROM users LIMIT 10;` | View first 10 users |
| `DELETE FROM table_name WHERE id=1;` | Delete specific record |
| `TRUNCATE TABLE comments;` | Delete all comments |
| `DROP TABLE table_name;` | Delete entire table |

## File Locations

- Schema: `/database/schema.sql`
- Backup Script: `/database/backup.sh`
- Backups: `/database/backups/`
- Config: `/config/db.php`
