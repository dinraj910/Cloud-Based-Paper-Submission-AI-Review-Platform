#!/bin/bash

# ====================================================================
# Research Portal Database Backup Script
# ====================================================================

# Configuration
DB_NAME="research_portal"
DB_USER="studentuser"
DB_PASS="student123"
BACKUP_DIR="/var/www/html/research-portal/database/backups"
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_${DATE}.sql"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Perform backup
echo "Starting database backup..."
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Backup successful: $BACKUP_FILE"
    
    # Compress the backup
    gzip "$BACKUP_FILE"
    echo "✓ Backup compressed: ${BACKUP_FILE}.gz"
    
    # Show backup size
    ls -lh "${BACKUP_FILE}.gz"
    
    # Keep only last 5 backups
    cd "$BACKUP_DIR"
    ls -t ${DB_NAME}_backup_*.sql.gz | tail -n +6 | xargs -r rm
    echo "✓ Old backups cleaned up (keeping last 5)"
else
    echo "✗ Backup failed!"
    exit 1
fi

echo "Backup complete!"
