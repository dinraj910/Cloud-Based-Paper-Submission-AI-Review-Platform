# AWS S3 Integration Setup Guide

## 🎯 Overview
Your research portal now supports AWS S3 for storing uploaded research papers. This is perfect for AWS EC2 deployment and AWS Free Tier.

## 📋 AWS Setup Steps

### Step 1: Create S3 Bucket
1. Login to **AWS Console** → Navigate to **S3**
2. Click **Create bucket**
3. Configure:
   - **Bucket name**: `research-portal-papers` (must be globally unique)
   - **Region**: Choose closest region (e.g., `us-east-1` or `ap-south-1` for Mumbai)
   - **Block Public Access**: Uncheck "Block all public access" (for public downloads)
   - **Bucket Versioning**: Optional (Enable for version history)
4. Click **Create bucket**

### Step 2: Configure Bucket Policy (Public Read Access)
1. Go to your bucket → **Permissions** tab
2. Scroll to **Bucket policy** → Click **Edit**
3. Add this policy (replace `YOUR-BUCKET-NAME`):

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
        }
    ]
}
```

### Step 3: Configure CORS (Optional - for browser uploads)
1. Go to bucket → **Permissions** → **CORS**
2. Add this configuration:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": ["ETag"]
    }
]
```

### Step 4: Create IAM User
1. Go to **IAM** → **Users** → **Create user**
2. User name: `research-portal-s3-user`
3. Select **Attach policies directly**
4. Attach policy: **AmazonS3FullAccess** (or create custom policy below)
5. Click **Create user**

#### Custom IAM Policy (Recommended - More Secure)
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::YOUR-BUCKET-NAME",
                "arn:aws:s3:::YOUR-BUCKET-NAME/*"
            ]
        }
    ]
}
```

### Step 5: Generate Access Keys
1. Go to **IAM** → **Users** → Select `research-portal-s3-user`
2. Click **Security credentials** tab
3. Scroll to **Access keys** → Click **Create access key**
4. Select **Application running outside AWS** → Click **Next**
5. **IMPORTANT**: Save both:
   - Access Key ID
   - Secret Access Key (shown only once!)

### Step 6: Configure Your Application
1. Edit file: `/var/www/html/research-portal/config/aws.php`
2. Update these values:

```php
define('AWS_ACCESS_KEY_ID', 'YOUR_ACCESS_KEY_ID_HERE');
define('AWS_SECRET_ACCESS_KEY', 'YOUR_SECRET_ACCESS_KEY_HERE');
define('AWS_REGION', 'us-east-1'); // Your bucket region
define('AWS_S3_BUCKET', 'research-portal-papers'); // Your bucket name
```

## 🔒 Security Best Practices

### For Production Deployment on EC2:
Instead of hardcoding credentials in `aws.php`, use **IAM Role**:

1. Create IAM Role with S3 permissions
2. Attach role to EC2 instance
3. Update `config/aws.php` to use instance metadata:

```php
function getS3Client() {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    return new Aws\S3\S3Client([
        'version' => AWS_S3_VERSION,
        'region'  => AWS_REGION,
        // Credentials automatically loaded from EC2 instance role
    ]);
}
```

### Environment Variables (Alternative)
Create `.env` file (add to .gitignore):
```
AWS_ACCESS_KEY_ID=your_key_here
AWS_SECRET_ACCESS_KEY=your_secret_here
AWS_REGION=us-east-1
AWS_S3_BUCKET=research-portal-papers
```

## 💰 AWS Free Tier Limits
- **S3**: 5GB storage, 20,000 GET requests, 2,000 PUT requests/month
- **EC2**: 750 hours/month of t2.micro instance
- **Data Transfer**: 100GB outbound/month

## 🧪 Testing

### Test Upload:
1. Login to your portal
2. Upload a research paper
3. Check AWS S3 Console → Your bucket → `papers/` folder
4. Success message should say: "Paper uploaded successfully to S3!"

### Fallback Mode:
If AWS is NOT configured (default state):
- Files are stored locally in `/submissions/uploads/`
- Success message: "Paper uploaded successfully (stored locally - AWS not configured)!"

## 📁 File Organization in S3
Files are organized by date:
```
research-portal-papers/
└── papers/
    └── 2026/
        └── 01/
            ├── paper_abc123.pdf
            ├── paper_def456.pdf
            └── ...
```

## 🐛 Troubleshooting

### Error: "AWS is not configured"
- Check if you updated `config/aws.php` with real credentials

### Error: "Failed to upload file to S3"
- Verify AWS credentials are correct
- Check bucket name and region match
- Ensure IAM user has S3 permissions
- Check PHP error logs: `tail -f /var/log/apache2/error.log`

### Error: "Access Denied"
- Verify bucket policy allows public read
- Check IAM user permissions
- Ensure bucket name in config matches actual bucket

### Files not accessible
- Add bucket policy for public read access
- Or use S3 pre-signed URLs for private access

## 🚀 Deploy to EC2

### Quick EC2 Setup:
```bash
# 1. SSH into EC2 instance
ssh -i your-key.pem ubuntu@your-ec2-ip

# 2. Install dependencies
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-xml php-curl php-mbstring

# 3. Clone/upload your project
cd /var/www/html
sudo git clone your-repo.git research-portal
# OR upload files via SCP

# 4. Install Composer dependencies
cd research-portal
php composer.phar install --no-dev

# 5. Configure database
mysql -u root -p < database/schema.sql

# 6. Set permissions
sudo chown -R www-data:www-data /var/www/html/research-portal
sudo chmod -R 755 /var/www/html/research-portal

# 7. Restart Apache
sudo systemctl restart apache2
```

## 📞 Support
For issues, check:
- AWS S3 Console → Bucket properties
- PHP error logs
- Browser console for JavaScript errors

Good luck with your college project! 🎓
