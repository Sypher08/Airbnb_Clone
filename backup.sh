#!/bin/bash
# Backup script for Airbnb clone
BACKUP_DIR="/var/backups/airbnb"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u root -p'PassWord.1' airbnb_clone > $BACKUP_DIR/airbnb_db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/airbnb_files_$DATE.tar.gz /var/www/airbnb

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
