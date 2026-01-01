#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║         S3 vs Local Storage - Live Demonstration                 ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══ CURRENT STATE (Local Storage) ═══${NC}"
echo ""

echo "Database Query:"
echo "SELECT id, title, s3_file_url FROM submissions LIMIT 3;"
echo ""

mysql -u studentuser -pstudent123 research_portal -t -e "
SELECT 
    id,
    LEFT(title, 30) as title,
    LEFT(s3_file_url, 50) as file_url,
    CASE 
        WHEN s3_file_url LIKE 'https://%.s3.%' THEN '✓ S3'
        WHEN s3_file_url LIKE 'https://%.amazonaws.com%' THEN '✓ S3'
        ELSE '✗ Local'
    END as storage_type
FROM submissions 
ORDER BY created_at DESC 
LIMIT 3;
" 2>/dev/null

echo ""
echo -e "${YELLOW}═══ HOW IT WORKS ═══${NC}"
echo ""

echo "1. LOCAL STORAGE (Current):"
echo "   Upload Flow:"
echo "   Browser → EC2 → /var/www/html/research-portal/submissions/uploads/"
echo "   Database stores: /research-portal/submissions/uploads/paper_xyz.pdf"
echo ""
echo "   Download Flow:"
echo "   User → EC2 reads file from disk → Sends to user"
echo "   ❌ Problem: Uses EC2 bandwidth and disk I/O"
echo ""

echo "2. S3 STORAGE (After configuration):"
echo "   Upload Flow:"
echo "   Browser → EC2→ AWS S3 (cloud)"
echo "   Database stores: https://research-portal-papers.s3.amazonaws.com/papers/2026/01/paper_xyz.pdf"
echo ""
echo "   Download Flow:"
echo "   User → Directly from S3 (bypasses EC2!)"
echo "   ✓ Benefit: EC2 untouched, faster downloads, global CDN"
echo ""

echo -e "${BLUE}═══ DATABASE STORAGE COMPARISON ═══${NC}"
echo ""

echo "What's stored in the database:"
echo ""
echo "┌─────────────────────────────────────────────────────────────┐"
echo "│                    submissions table                        │"
echo "├────┬─────────────┬────────────────────────────────┬─────────┤"
echo "│ id │ title       │ s3_file_url                    │ file_   │"
echo "│    │             │                                │ type    │"
echo "├────┼─────────────┼────────────────────────────────┼─────────┤"
echo "│ 1  │ AI Paper    │ https://bucket.s3.amazon...    │ pdf     │ ← S3 URL (just text)"
echo "│    │             │ papers/2026/01/paper_abc.pdf   │         │"
echo "├────┼─────────────┼────────────────────────────────┼─────────┤"
echo "│ 2  │ ML Study    │ /uploads/paper_xyz.pdf         │ pdf     │ ← Local path"
echo "└────┴─────────────┴────────────────────────────────┴─────────┘"
echo ""
echo "Notice: Database only stores TEXT (URLs/paths), NOT the actual file!"
echo ""

echo -e "${YELLOW}═══ REAL EXAMPLE FROM YOUR DATABASE ═══${NC}"
echo ""

# Get actual example
SAMPLE=$(mysql -u studentuser -pstudent123 research_portal -sN -e "
SELECT s3_file_url FROM submissions ORDER BY created_at DESC LIMIT 1;
" 2>/dev/null)

echo "Latest submission URL in database:"
echo "\"$SAMPLE\""
echo ""
echo "URL length: $(echo -n "$SAMPLE" | wc -c) bytes (just text!)"
echo ""

if [ -f "/var/www/html/research-portal/submissions/uploads/"* ]; then
    ACTUAL_FILE=$(ls -lh /var/www/html/research-portal/submissions/uploads/ | tail -1 | awk '{print $5, $9}')
    echo "Actual file on disk: $ACTUAL_FILE"
    echo ""
fi

echo -e "${GREEN}═══ KEY TAKEAWAY ═══${NC}"
echo ""
echo "📊 Database stores: URL/Path (text, ~100 bytes)"
echo "📄 S3/Disk stores: Actual PDF file (binary, ~5 MB)"
echo ""
echo "Ratio: 100 bytes vs 5,000,000 bytes = 50,000x smaller in database!"
echo ""

echo -e "${BLUE}═══ WHEN S3 IS CONFIGURED ═══${NC}"
echo ""
echo "After you configure AWS credentials in config/aws.php:"
echo ""
echo "Upload → EC2 sends file to S3 → S3 returns URL → URL saved to database"
echo "        (file deleted    (permanent    (this URL stays"
echo "         from EC2)         storage)      in database)"
echo ""
echo "Example S3 URL that would be stored:"
echo "https://research-portal-papers.s3.us-east-1.amazonaws.com/papers/2026/01/paper_677566b2d4f5a1.23456789.pdf"
echo ""

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║                      Documentation Files                         ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""
echo "Read these for complete understanding:"
echo "  📖 S3_ARCHITECTURE_EXPLAINED.md - Complete flow & diagrams"
echo "  📖 AWS_SETUP_GUIDE.md - How to configure S3"
echo "  📖 DATABASE_GUIDE.md - Database structure"
echo ""
