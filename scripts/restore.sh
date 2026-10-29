#!/bin/bash
# ==============================================================================
# SCRIPT INTERAKTIF RESTORE VOUCHERNET
# Mengembalikan database & storage dari Google Drive atau backup lokal
# ==============================================================================

set -e

APP_DIR="/home/seno/vouchernet"
BACKUP_DIR="$APP_DIR/backups"
DRIVE_REMOTE="gdrive:Backup_VoucherNet"

clear
echo "======================================================================"
echo "   📦 VOUCHERNET - RESTORE DATABASE & STORAGE WIZARD"
echo "======================================================================"
echo ""
echo "🔍 Memeriksa daftar backup di Google Drive ($DRIVE_REMOTE)..."

# Ambil daftar file backup dari Google Drive (diurutkan terbaru di atas)
mapfile -t REMOTE_FILES < <(rclone lsf "$DRIVE_REMOTE" | grep "^vouchernet_backup_.*\.tar\.gz$" | sort -r)

if [ ${#REMOTE_FILES[@]} -eq 0 ]; then
    echo "⚠️ Tidak ada file backup ditemukan di Google Drive!"
    echo "Memeriksa folder lokal: $BACKUP_DIR..."
    mapfile -t REMOTE_FILES < <(ls -1 "$BACKUP_DIR" 2>/dev/null | grep "^vouchernet_backup_.*\.tar\.gz$" | sort -r)
    IS_LOCAL=1
else
    IS_LOCAL=0
fi

if [ ${#REMOTE_FILES[@]} -eq 0 ]; then
    echo "❌ Tidak ada file backup yang tersedia untuk dipulihkan."
    exit 1
fi

echo ""
echo "Ditemukan daftar backup yang tersedia:"
for i in "${!REMOTE_FILES[@]}"; do
    FILE="${REMOTE_FILES[$i]}"
    echo "  [$((i+1))] $FILE"
done
echo ""

read -p "👉 Pilih nomor backup yang ingin Anda pulihkan [1-${#REMOTE_FILES[@]}]: " CHOICE

if ! [[ "$CHOICE" =~ ^[0-9]+$ ]] || [ "$CHOICE" -lt 1 ] || [ "$CHOICE" -gt ${#REMOTE_FILES[@]} ]; then
    echo "❌ Pilihan tidak valid! Proses restore dibatalkan."
    exit 1
fi

SELECTED_FILE="${REMOTE_FILES[$((CHOICE-1))]}"
echo ""
echo "----------------------------------------------------------------------"
echo "⚠️  PERINGATAN PENTING:"
echo "Database VoucherNet saat ini akan ditimpa dengan backup:"
echo "👉 $SELECTED_FILE"
echo "(Sistem akan otomatis membuat 'safety rollback snapshot' data sekarang)"
echo "----------------------------------------------------------------------"
read -p "Apakah Anda yakin ingin melanjutkan proses restore? (ketik 'y' lalu Enter): " CONFIRM

if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "🛑 Restore dibatalkan oleh pengguna. Database tetap utuh."
    exit 0
fi

echo ""
# 1. Buat safety snapshot dari data sekarang
SAFETY_FILE="$APP_DIR/database/safety_before_restore_$(date +'%Y%m%d_%H%M%S').sqlite"
echo "[1/4] 🛡️  Membuat safety snapshot database saat ini ke: $SAFETY_FILE..."
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$SAFETY_FILE"
fi

# 2. Ambil file dari Google Drive jika belum ada di lokal
TMP_RESTORE="/tmp/vouchernet_restore_stage_$(date +%s)"
mkdir -p "$TMP_RESTORE"

if [ -f "$BACKUP_DIR/$SELECTED_FILE" ]; then
    echo "[2/4] 📦 Menggunakan file backup lokal..."
    LOCAL_TAR="$BACKUP_DIR/$SELECTED_FILE"
else
    echo "[2/4] ☁️  Mengunduh $SELECTED_FILE dari Google Drive..."
    rclone copy "$DRIVE_REMOTE/$SELECTED_FILE" "$TMP_RESTORE"
    LOCAL_TAR="$TMP_RESTORE/$SELECTED_FILE"
fi

# 3. Ekstrak dan pulihkan
echo "[3/4] 📂 Mengekstrak dan memulihkan data..."
tar -xzf "$LOCAL_TAR" -C "$TMP_RESTORE"

if [ -f "$TMP_RESTORE/database.sqlite" ]; then
    cp "$TMP_RESTORE/database.sqlite" "$APP_DIR/database/database.sqlite"
    chmod 666 "$APP_DIR/database/database.sqlite" 2>/dev/null || true
    echo "   ✅ Database SQLite berhasil dipulihkan."
fi

if [ -d "$TMP_RESTORE/storage" ]; then
    mkdir -p "$APP_DIR/storage/app/public"
    cp -r "$TMP_RESTORE/storage/"* "$APP_DIR/storage/app/public/" 2>/dev/null || true
    echo "   ✅ Aset media / storage berhasil dipulihkan."
fi

# Bersihkan temporary
rm -rf "$TMP_RESTORE"

# 4. Refresh cache Laravel di container
echo "[4/4] ⚡ Menyegarkan cache sistem..."
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan view:clear >/dev/null 2>&1 || true
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan config:clear >/dev/null 2>&1 || true
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan cache:clear >/dev/null 2>&1 || true

echo ""
echo "======================================================================"
echo "🎉 SUKSES BESAR! Data VoucherNet berhasil dipulihkan sempurna!"
echo "Sistem sudah kembali normal dan siap digunakan di: https://netku.biz.id"
echo "======================================================================\"
