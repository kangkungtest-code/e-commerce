#!/usr/bin/env bash
# Deploy "tarik": dijalankan cron tiap menit di server (user kangkung).
#
# Kenapa: sejak 5 Okt 2026 jaringan Biznet memblokir sambungan dari GitHub Actions ke server,
# jadi GitHub tidak bisa lagi SSH masuk. Sebagai gantinya server yang mengecek GitHub:
#   1. commit terbaru tiap branch (git ls-remote, tanpa token — repo publik);
#   2. kalau beda dengan yang terpasang, tunggu job `test` di GitHub Actions lulus;
#   3. checkout & jalankan deploy/jalankan.sh;
#   4. tulis hasilnya ke <APP_URL>/deploy-status.txt (dibaca job deploy di GitHub & Claude).
#
# Dipasang sekali dengan deploy/pasang-tarik.sh. Skrip ini memperbarui dirinya sendiri dari
# /var/www/dev/deploy/tarik.sh setiap selesai jalan.
set -uo pipefail

REPO="kangkungtest-code/e-commerce"
SALINAN="/var/www/.toko-tarik.sh"

exec 9>/tmp/toko-tarik.lock
flock -n 9 || exit 0

status_ci () {
  curl -fsS --max-time 20 -H 'Accept: application/vnd.github+json' \
    "https://api.github.com/repos/${REPO}/commits/$1/check-runs?check_name=test" 2>/dev/null \
  | php -r '$j = json_decode(stream_get_contents(STDIN), true);
            $r = $j["check_runs"][0] ?? null;
            echo $r ? ($r["status"] === "completed" ? $r["conclusion"] : "pending") : "none";'
}

tulis_status () { # dir sha hasil log
  local dir="$1" sha="$2" hasil="$3" log="${4:-}"
  {
    echo "$sha $hasil $(date -u +%Y-%m-%dT%H:%M:%SZ)"
    [ -n "$log" ] && [ -f "$log" ] && tail -n 25 "$log"
  } > "$dir/public/deploy-status.txt.baru" 2>/dev/null && mv "$dir/public/deploy-status.txt.baru" "$dir/public/deploy-status.txt"
}

# branch  folder  APP_ENV  APP_DEBUG  APP_URL  database
TARGET=(
  "claude-dev        /var/www/dev         dev  true  https://kangkungdev.duckdns.org  toko_dev"
  "toko-fastandflux  /var/www/fastandflux demo false https://fastandflux.duckdns.org  toko_fastandflux"
  "main              /var/www/demo        demo false http://103.103.23.222            toko_demo"
)

for BARIS in "${TARGET[@]}"; do
  read -r BRANCH DIR ENV DEBUG URL DB <<< "$BARIS"
  [ -d "$DIR/.git" ] || continue

  REMOTE="$(git ls-remote "https://github.com/${REPO}.git" "refs/heads/${BRANCH}" 2>/dev/null | cut -f1)"
  [ -n "$REMOTE" ] || continue
  LOKAL="$(git -C "$DIR" rev-parse HEAD 2>/dev/null || true)"
  [ "$REMOTE" = "$LOKAL" ] && continue
  # Commit yang sudah pernah gagal tidak dicoba lagi tiap menit (tunggu commit berikutnya).
  [ "$(cat "$DIR/.tarik-gagal" 2>/dev/null)" = "$REMOTE" ] && continue

  CI="$(status_ci "$REMOTE")"
  case "$CI" in
    success) ;;
    pending|none|"") continue ;;   # tes belum selesai / belum mulai
    *)
      echo "$REMOTE" > "$DIR/.tarik-gagal"
      tulis_status "$DIR" "$REMOTE" "dilewati-tes-${CI}"
      continue ;;
  esac

  LOG="/tmp/tarik-${BRANCH}.log"
  (
    set -e
    cd "$DIR"
    git fetch -q --depth 1 "https://github.com/${REPO}.git" "$BRANCH"
    # Commit lama tanpa skrip deploy ini: jangan diganti setengah jalan.
    if ! git cat-file -e FETCH_HEAD:deploy/jalankan.sh 2>/dev/null; then
      echo "Commit ini belum punya deploy/jalankan.sh — dilewati, kode di server tidak diubah."
      exit 1
    fi
    git checkout -q -B "$BRANCH" FETCH_HEAD
    git reset -q --hard FETCH_HEAD
    bash deploy/jalankan.sh "$ENV" "$URL" "$DB" "$DEBUG"
  ) > "$LOG" 2>&1 < /dev/null
  if [ $? -eq 0 ]; then
    rm -f "$DIR/.tarik-gagal"
    tulis_status "$DIR" "$REMOTE" "berhasil" "$LOG"
  else
    echo "$REMOTE" > "$DIR/.tarik-gagal"
    tulis_status "$DIR" "$REMOTE" "gagal" "$LOG"
  fi
done

# Perbarui diri sendiri dari checkout dev (berlaku mulai menit berikutnya).
BARU="/var/www/dev/deploy/tarik.sh"
if [ -f "$BARU" ] && ! cmp -s "$BARU" "$SALINAN"; then
  cp "$BARU" "$SALINAN.baru" && mv "$SALINAN.baru" "$SALINAN"
fi
