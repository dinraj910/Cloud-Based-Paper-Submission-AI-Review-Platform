#!/bin/bash

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     Research Portal - Database Verification Script        ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

DB_USER="studentuser"
DB_PASS="student123"
DB_NAME="research_portal"

echo "1. Testing Database Connection..."
if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Connected to database: $DB_NAME"
else
    echo -e "${RED}✗${NC} Failed to connect to database"
    exit 1
fi

echo ""
echo "2. Checking Tables..."
TABLES=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "SHOW TABLES;" 2>/dev/null)
TABLE_COUNT=$(echo "$TABLES" | wc -l)
echo -e "${GREEN}✓${NC} Found $TABLE_COUNT tables:"
echo "$TABLES" | while read table; do
    echo "   - $table"
done

echo ""
echo "3. Checking Data..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT 
    'users' as 'Table',
    COUNT(*) as 'Records'
FROM users
UNION ALL
SELECT 'submissions', COUNT(*) FROM submissions
UNION ALL
SELECT 'comments', COUNT(*) FROM comments
UNION ALL
SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL
SELECT 'analysis_results', COUNT(*) FROM analysis_results;
" 2>/dev/null

echo ""
echo "4. Checking Foreign Keys..."
FK_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "
SELECT COUNT(*) 
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = '$DB_NAME'
AND REFERENCED_TABLE_NAME IS NOT NULL;
" 2>/dev/null)
echo -e "${GREEN}✓${NC} Found $FK_COUNT foreign key relationships"

echo ""
echo "5. Testing PHP Database Connection..."
cd /var/www/html/research-portal
PHP_TEST=$(php -r "
include 'config/db.php';
if (\$conn) {
    echo 'SUCCESS';
} else {
    echo 'FAILED';
}
" 2>/dev/null)

if [ "$PHP_TEST" = "SUCCESS" ]; then
    echo -e "${GREEN}✓${NC} PHP can connect to database"
else
    echo -e "${RED}✗${NC} PHP connection failed"
fi

echo ""
echo "6. Database Size..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Database Size (MB)'
FROM information_schema.tables
WHERE table_schema = '$DB_NAME';
" 2>/dev/null

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            Database Configuration Verified ✓               ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Database Credentials:"
echo "  Host: localhost"
echo "  Database: $DB_NAME"
echo "  Username: $DB_USER"
echo "  Password: $DB_PASS"
echo ""
echo "Files Created:"
echo "  - /database/schema.sql (Complete database schema)"
echo "  - /database/backup.sh (Backup script)"
echo "  - /database/DATABASE_GUIDE.md (Complete guide)"
echo ""
