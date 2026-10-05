#!/usr/bin/env bash
# Pasang deploy tarik (sekali saja), jalankan di server sebagai user kangkung:
#   curl -fsSL https://raw.githubusercontent.com/kangkungtest-code/e-commerce/claude-dev/deploy/pasang-tarik.sh | bash
set -euo pipefail

SALINAN="/var/www/.toko-tarik.sh"
curl -fsSL "https://raw.githubusercontent.com/kangkungtest-code/e-commerce/claude-dev/deploy/tarik.sh" -o "$SALINAN.baru"
mv "$SALINAN.baru" "$SALINAN"
chmod 755 "$SALINAN"

BARIS="* * * * * bash ${SALINAN} >> /tmp/toko-tarik-cron.log 2>&1"
ADA="$(crontab -l 2>/dev/null || true)"
if ! printf '%s\n' "$ADA" | grep -qF "$SALINAN"; then
  printf '%s\n%s\n' "$ADA" "$BARIS" | sed '/^$/d' | crontab -
fi

echo "Deploy tarik terpasang. Cron:"
crontab -l | grep -F "$SALINAN"
echo "Menjalankan sekali sekarang (bisa 1-3 menit)..."
bash "$SALINAN" && echo "Selesai. Status: lihat https://kangkungdev.duckdns.org/deploy-status.txt"
