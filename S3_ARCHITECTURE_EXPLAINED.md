# 🎓 How AWS S3 Integration Works - Developer Guide

## 📊 The Complete Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    RESEARCH PORTAL S3 FLOW                          │
└─────────────────────────────────────────────────────────────────────┘

Step 1: User Upload
┌──────────────┐
│   Browser    │  User selects PDF file (5 MB)
│              │  Fills title: "AI Research Paper"
└──────┬───────┘  Clicks "Upload"
       │
       │ HTTP POST with multipart/form-data
       ▼
┌──────────────────────────────────────────────────────────────────┐
│                    EC2 Instance / Web Server                      │
├──────────────────────────────────────────────────────────────────┤
│  Step 2: PHP Receives Upload                                     │
│  ┌────────────────────────────────────────┐                      │
│  │ submissions/upload.php                  │                      │
│  │                                         │                      │
│  │ - $_FILES['paper'] = temp file         │                      │
│  │ - File stored in: /tmp/php_upload_abc  │                      │
│  │ - Size: 5 MB                           │                      │
│  │ - Type: application/pdf                │                      │
│  └────────────────────────────────────────┘                      │
│                         │                                         │
│                         ▼                                         │
│  Step 3: Generate Unique Filename                                │
│  ┌────────────────────────────────────────┐                      │
│  │ $fileName = uniqid('paper_', true)     │                      │
│  │ → "paper_677566b2d4f5a1.23456789.pdf"  │                      │
│  │                                         │                      │
│  │ $s3Key = "papers/2026/01/" + fileName  │                      │
│  │ → "papers/2026/01/paper_677...pdf"     │                      │
│  └────────────────────────────────────────┘                      │
│                         │                                         │
│                         ▼                                         │
│  Step 4: Upload to S3 (using AWS SDK)                           │
│  ┌────────────────────────────────────────┐                      │
│  │ $s3Client->putObject([                 │                      │
│  │   'Bucket' => 'research-portal-papers',│                      │
│  │   'Key'    => 'papers/2026/01/...',    │                      │
│  │   'SourceFile' => '/tmp/php_upload',   │                      │
│  │   'ACL'    => 'public-read'            │                      │
│  │ ])                                      │                      │
│  └────────────────────────────────────────┘                      │
│                         │                                         │
│                         │ HTTPS Upload (Encrypted)               │
│                         ▼                                         │
└─────────────────────────────────────────────────────────────────┘
                          │
                          │ AWS SDK sends file over internet
                          ▼
┌──────────────────────────────────────────────────────────────────┐
│                         AWS S3 Bucket                             │
│                  (research-portal-papers)                         │
├──────────────────────────────────────────────────────────────────┤
│  Step 5: File Stored in S3                                       │
│                                                                   │
│  Bucket Structure:                                                │
│  research-portal-papers/                                          │
│  └── papers/                                                      │
│      └── 2026/                                                    │
│          └── 01/                                                  │
│              └── paper_677566b2d4f5a1.23456789.pdf  (5 MB)      │
│                                                                   │
│  File Properties:                                                 │
│  - Storage Class: STANDARD                                        │
│  - Encryption: Server-side (automatic)                            │
│  - ACL: public-read                                               │
│  - Content-Type: application/pdf                                  │
│                                                                   │
│  S3 Returns:                                                      │
│  - ObjectURL: "https://research-portal-papers.s3.amazonaws.com/  │
│                papers/2026/01/paper_677566b2d4f5a1.23456789.pdf" │
│  - ETag: "d8e8fca2dc0f896fd7cb4cb0031ba249"                      │
│  - VersionId: (if versioning enabled)                             │
└──────────────────┬───────────────────────────────────────────────┘
                   │
                   │ S3 URL returned to PHP
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│                    EC2 Instance / Web Server                      │
├──────────────────────────────────────────────────────────────────┤
│  Step 6: Save Metadata to MySQL Database                         │
│  ┌────────────────────────────────────────┐                      │
│  │ INSERT INTO submissions                │                      │
│  │ (user_id, title, description,          │                      │
│  │  s3_file_url, file_type, created_at)   │                      │
│  │ VALUES (                               │                      │
│  │   5,                                   │  ← User ID          │
│  │   'AI Research Paper',                 │  ← Title            │
│  │   'Machine learning study',            │  ← Description      │
│  │   'https://research-portal-papers...', │  ← S3 URL ⭐        │
│  │   'pdf',                               │  ← File type        │
│  │   NOW()                                │  ← Timestamp        │
│  │ )                                      │                      │
│  └────────────────────────────────────────┘                      │
│                         │                                         │
│                         ▼                                         │
│  Step 7: Response to User                                        │
│  ┌────────────────────────────────────────┐                      │
│  │ Success message:                       │                      │
│  │ "Paper uploaded successfully to S3!"   │                      │
│  └────────────────────────────────────────┘                      │
└───────────────────────┬──────────────────────────────────────────┘
                        │
                        │ HTTP Response
                        ▼
                  ┌──────────┐
                  │ Browser  │  Shows success message
                  └──────────┘  Redirects to homepage


Step 8: When Another User Views the Paper
┌──────────────┐
│   Browser    │  Visits homepage
└──────┬───────┘
       │
       │ HTTP GET request
       ▼
┌──────────────────────────────────────────────────────────────────┐
│                    EC2 Instance / Web Server                      │
│  ┌────────────────────────────────────────┐                      │
│  │ SELECT s.*, u.name                     │                      │
│  │ FROM submissions s                     │                      │
│  │ JOIN users u ON s.user_id = u.id       │                      │
│  │ ORDER BY created_at DESC               │                      │
│  │                                         │                      │
│  │ Returns:                               │                      │
│  │ - title: "AI Research Paper"           │                      │
│  │ - s3_file_url: "https://research-...   │  ← S3 URL ⭐        │
│  │ - user_name: "John Doe"                │                      │
│  └────────────────────────────────────────┘                      │
└───────────────────────┬──────────────────────────────────────────┘
                        │
                        │ HTML with S3 URL in <a> tag
                        ▼
                  ┌──────────┐
                  │ Browser  │  Displays paper card with Download button
                  └──────┬───┘
                         │
                         │ User clicks "Download"
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│                         AWS S3 Bucket                             │
│  Browser directly downloads from:                                 │
│  https://research-portal-papers.s3.amazonaws.com/papers/2026/...  │
│                                                                   │
│  ⭐ NO traffic through EC2 - Direct download from S3!            │
└──────────────────────────────────────────────────────────────────┘
```

## 🔑 Key Concepts Explained

### 1. **What Actually Gets Stored Where?**

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA STORAGE LOCATIONS                    │
└─────────────────────────────────────────────────────────────┘

AWS S3 (Object Storage)
├── 📄 The actual PDF file (binary data)
├── 📊 File size: 5 MB
├── 🏷️  Metadata: Content-Type, Last-Modified, ETag
└── 🔐 Access permissions (ACL)

MySQL Database (EC2 Instance)
├── 📝 Paper title: "AI Research Paper"
├── 📝 Description: "Machine learning study..."
├── 🔗 S3 URL: "https://research-portal-papers.s3.amazonaws.com/..."
├── 👤 User ID: 5 (reference to users table)
├── 📅 Upload date: "2026-01-01 10:30:45"
└── 🏷️  File type: "pdf"
```

### 2. **Why Store URL in Database?**

The database stores **ONLY THE LINK**, not the file itself:

```sql
-- Database record (lightweight - few KB)
INSERT INTO submissions VALUES (
  8,                                                    -- id
  5,                                                    -- user_id
  'AI Research Paper',                                  -- title (text)
  'Study on machine learning...',                       -- description (text)
  'https://research-portal-papers.s3.amazonaws.com/papers/2026/01/paper_677566b2d4f5a1.23456789.pdf',  -- ⭐ S3 URL
  'pdf',                                                -- file_type
  '2026-01-01 10:30:45'                                 -- created_at
);
```

**Benefits:**
- ✅ Database stays small (only metadata, no files)
- ✅ Fast queries (no large binary data)
- ✅ Files served directly from S3 (faster downloads)
- ✅ EC2 bandwidth saved (S3 handles downloads)
- ✅ Global CDN (S3 has edge locations worldwide)

### 3. **The S3 URL Structure**

```
https://research-portal-papers.s3.amazonaws.com/papers/2026/01/paper_677566b2d4f5a1.23456789.pdf
│      │                         │              │      │    │   │
│      │                         │              │      │    │   └─ File extension
│      │                         │              │      │    └───── Unique ID (prevents overwrites)
│      │                         │              │      └──────────── Year/Month (organization)
│      │                         │              └─────────────────── Folder structure
│      │                         └────────────────────────────────── S3 endpoint
│      └──────────────────────────────────────────────────────────── Bucket name
└─────────────────────────────────────────────────────────────────── HTTPS protocol
```

This URL is:
- **Public** (anyone with link can access)
- **Permanent** (doesn't change)
- **Fast** (served from AWS global network)
- **Scalable** (handles millions of downloads)

## 💻 Code Walkthrough

Let me show you the ACTUAL code from our project:

### File: `config/aws.php`
```php
function uploadToS3($localFilePath, $s3Key, $contentType = 'application/pdf') {
    try {
        $s3Client = getS3Client();  // Creates AWS SDK client
        
        $result = $s3Client->putObject([
            'Bucket'      => AWS_S3_BUCKET,        // 'research-portal-papers'
            'Key'         => $s3Key,                // 'papers/2026/01/paper_xyz.pdf'
            'SourceFile'  => $localFilePath,        // '/tmp/php_upload_abc'
            'ContentType' => $contentType,          // 'application/pdf'
            'ACL'         => S3_ACL,                // 'public-read'
        ]);
        
        // ⭐ Return the URL - this is what gets saved in database!
        return $result['ObjectURL'];
        // Returns: "https://research-portal-papers.s3.amazonaws.com/papers/2026/01/paper_xyz.pdf"
    } catch (Exception $e) {
        error_log('S3 Upload Error: ' . $e->getMessage());
        return false;
    }
}
```

### File: `submissions/upload.php`
```php
// Generate unique filename
$fileName = uniqid('paper_', true) . '.' . $fileType;
$s3Key = 'papers/' . date('Y/m/') . $fileName;

// Upload to S3 and get URL
$fileUrl = uploadToS3($file['tmp_name'], $s3Key, $contentType);
//          ↓ Returns S3 URL string

if ($fileUrl) {
    // ⭐ Save URL to database
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO submissions (user_id, title, description, s3_file_url, file_type, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, 'issss', 
        $_SESSION['user_id'], 
        $title, 
        $description, 
        $fileUrl,        // ← S3 URL stored here
        $fileType
    );
    mysqli_stmt_execute($stmt);
}
```

### File: `index.php` (Display)
```php
// Fetch from database
$result = mysqli_query($conn, 
    "SELECT s.*, u.name as user_name 
     FROM submissions s 
     LEFT JOIN users u ON s.user_id = u.id"
);

while ($row = mysqli_fetch_assoc($result)) {
    // ⭐ URL comes from database
    echo '<a href="' . htmlspecialchars($row['s3_file_url']) . '" target="_blank">';
    echo 'Download</a>';
    //           ↑ Direct link to S3
}
```

## 🔄 Data Flow Comparison

### Traditional Approach (Without S3)
```
User → [Upload] → EC2 Server → Store in /var/www/uploads/ → Database stores path
                                     ↓
User → [Download] → EC2 Server → Read from disk → Send to user
                    (Uses EC2 bandwidth & CPU)
```

### Our S3 Approach
```
User → [Upload] → EC2 → Upload to S3 → Database stores S3 URL
                         ↓
User → [Download] → Click link → DIRECTLY from S3 (bypasses EC2!)
                                  (Uses S3 bandwidth, EC2 untouched)
```

## 🎯 Interview / Class Presentation Points

**Question: "Why not store files in the database?"**
Answer:
1. Databases are designed for **structured data**, not binary files
2. File storage in DB = slower queries, larger backups
3. S3 is optimized for file storage (99.999999999% durability)
4. Cost: S3 storage is $0.023/GB vs Database $0.10/GB

**Question: "What if S3 goes down?"**
Answer:
1. S3 has 99.99% availability SLA (downtime ~4 minutes/month)
2. Our code has fallback to local storage
3. Can implement error handling to show cached version

**Question: "Is the URL permanent?"**
Answer:
- Yes, as long as the file exists in S3
- Only changes if we delete and re-upload
- We can version files in S3 if needed

**Question: "How is this secure?"**
Answer:
1. Files are encrypted in transit (HTTPS)
2. Files are encrypted at rest (S3 server-side encryption)
3. Can use private ACL + pre-signed URLs for sensitive files
4. IAM controls who can upload/delete

## 💰 Cost Breakdown (AWS Free Tier)

```
Storage:       5GB free × 12 months = FREE for small projects
PUT requests:  2,000/month free = ~66 uploads/day
GET requests:  20,000/month free = ~666 downloads/day
Data transfer: 100GB/month free outbound

Example cost after free tier:
- 100 papers × 5MB each = 0.5GB storage = $0.01/month
- 1,000 downloads/month = $0.004/month
TOTAL: ~$0.01/month (essentially free!)
```

## 🎓 Summary for Your Class

1. **Files go to S3** (cloud storage optimized for files)
2. **URLs go to MySQL** (database optimized for structured data)
3. **Users download directly from S3** (fast, scalable, cheap)
4. **EC2 only handles** upload orchestration & metadata
5. **Database stays small** (only text, not binary data)

This is the **industry standard** architecture used by:
- Netflix (video files in S3)
- Spotify (audio files in S3)
- Instagram (images in S3)
- Dropbox (files in S3)
