#!/bin/bash
# ==============================================================================
# SCRIPT AUTO-BACKUP VOUCHERNET KE GOOGLE DRIVE
# Menghasilkan snapshot konsisten SQLite + Storage Assets (.tar.gz)
# ==============================================================================

set -e

APP_DIR="/home/seno/vouchernet"
BACKUP_DIR="$APP_DIR/backups"
LOG_FILE="$APP_DIR/backup.log"
DRIVE_REMOTE="gdrive:Backup_VoucherNet"
TIMESTAMP=$(date +'%Y-%m-%d_%H-%M-%S')
FILENAME="vouchernet_backup_${TIMESTAMP}.tar.gz"
STAGING_DIR="/tmp/vouchernet_backup_${TIMESTAMP}"

mkdir -p "$BACKUP_DIR"
mkdir -p "$STAGING_DIR"

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "🚀 Memulai proses pencadangan VoucherNet..."

# 1. Buat Safe Snapshot SQLite dari dalam container
log "📦 1/4 Membuat snapshot SQLite konsisten..."
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app sqlite3 /var/www/html/database/database.sqlite ".backup '/var/www/html/database/backup_snapshot.sqlite'"

mv "$APP_DIR/database/backup_snapshot.sqlite" "$STAGING_DIR/database.sqlite"

# 2. Salin aset foto & bukti struk (storage/app/public)
log "🖼️  2/4 Menyalin aset storage & media..."
if [ -d "$APP_DIR/storage/app/public" ]; then
    mkdir -p "$STAGING_DIR/storage"
    cp -r "$APP_DIR/storage/app/public/"* "$STAGING_DIR/storage/" 2>/dev/null || true
fi

# 3. Catat metadata info
cat << META > "$STAGING_DIR/metadata.json"
{
    "app": "VoucherNet RT RW",
    "backup_at": "$TIMESTAMP",
    "version": "1.0"
}
META

# 4. Kompresi menjadi paket .tar.gz
log "🗜️  3/4 Mengompresi paket cadangan ($FILENAME)..."
tar -czf "$BACKUP_DIR/$FILENAME" -C "$STAGING_DIR" .
rm -rf "$STAGING_DIR"

FILESIZE=$(du -h "$BACKUP_DIR/$FILENAME" | cut -f1)
log "✅ Paket cadangan berhasil dibuat (Ukuran: $FILESIZE)."

# 5. Unggah ke Google Drive menggunakan rclone
log "☁️  4/4 Mengunggah ke Google Drive ($DRIVE_REMOTE)..."
if rclone copy "$BACKUP_DIR/$FILENAME" "$DRIVE_REMOTE"; then
    log "🎉 Berhasil mengunggah $FILENAME ke Google Drive!"
else
    log "❌ Gagal mengunggah ke Google Drive. File tetap tersimpan di lokal."
    exit 1
fi

# 6. Pembersihan file lama (Retensi 14 hari)
log "🧹 Membersihkan cadangan lokal yang lebih dari 14 hari..."
find "$BACKUP_DIR" -name "vouchernet_backup_*.tar.gz" -mtime +14 -delete 2>/dev/null || true

log "🧹 Membersihkan cadangan Google Drive yang lebih dari 14 hari..."
rclone delete --min-age 14d "$DRIVE_REMOTE" 2>/dev/null || true

log "✨ Selesai! Semua proses pencadangan berhasil 100%."
echo "----------------------------------------------------------------------" >> "$LOG_FILE"
