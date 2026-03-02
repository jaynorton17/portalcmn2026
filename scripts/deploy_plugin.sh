#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
PLUGIN_MAIN_FILE="$PLUGIN_DIR/covermenowone-one.php"

# Optional local env support (never required)
if [[ -f "$PLUGIN_DIR/.env" ]]; then
  # shellcheck disable=SC1091
  source "$PLUGIN_DIR/.env"
fi

HOST="${CMN_HOST:-${CMN_DEPLOY_HOST:-}}"
USER="${CMN_USER:-${CMN_DEPLOY_USER:-}}"
REMOTE_PATH="${CMN_REMOTE_PATH:-${CMN_PATH:-${CMN_DEPLOY_REMOTE_PATH:-}}}"
SSH_KEY="${CMN_SSH_KEY:-${CMN_KEY:-${CMN_DEPLOY_SSH_KEY:-}}}"
PORT="${CMN_PORT:-${CMN_DEPLOY_PORT:-22}}"

DRY_RUN=0
VERIFY=0
VERIFY_FRONTEND_HASH=0

DEPLOY_ITEMS=(
  "covermenowone-one.php"
  "frontend.css"
  "frontend.js"
  "livechat-widget.css"
  "livechat-widget.js"
  "admin.css"
  "login.css"
  "assets"
  "cv-converter"
)

usage() {
  cat <<'USAGE'
Usage: scripts/deploy_plugin.sh [options]

Required:
  --host <host>               SSH host (or CMN_HOST)
  --user <user>               SSH user (or CMN_USER)
  --remote-path <path>        Remote plugin directory path (or CMN_REMOTE_PATH / CMN_PATH)

Optional:
  --ssh-key <path>            SSH private key path
  --dry-run                   Print plan only; do not copy files
  --verify                    After deploy, run post-copy rsync dry-run consistency check
  --verify-frontend-hash      Also verify remote frontend.js sha256 equals local
  --port <port>               SSH port (default: 22)
  -h, --help                  Show help

Examples:
  scripts/deploy_plugin.sh --host example.com --user deploy --remote-path /var/www/wp-content/plugins/covermenow-one --dry-run
  scripts/deploy_plugin.sh --host example.com --user deploy --remote-path /var/www/wp-content/plugins/covermenow-one --verify
  scripts/deploy_plugin.sh --host example.com --user deploy --remote-path /var/www/wp-content/plugins/covermenow-one --verify --verify-frontend-hash
USAGE
}

die() {
  echo "ERROR: $*" >&2
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --host)
      HOST="${2:-}"
      shift 2
      ;;
    --user)
      USER="${2:-}"
      shift 2
      ;;
    --remote-path)
      REMOTE_PATH="${2:-}"
      shift 2
      ;;
    --ssh-key)
      SSH_KEY="${2:-}"
      shift 2
      ;;
    --port)
      PORT="${2:-}"
      shift 2
      ;;
    --dry-run)
      DRY_RUN=1
      shift
      ;;
    --verify)
      VERIFY=1
      shift
      ;;
    --verify-frontend-hash)
      VERIFY_FRONTEND_HASH=1
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      die "Unknown argument: $1 (use --help)"
      ;;
  esac
done

[[ -n "$HOST" ]] || die "Missing --host (or CMN_HOST)."
[[ -n "$USER" ]] || die "Missing --user (or CMN_USER)."
[[ -n "$REMOTE_PATH" ]] || die "Missing --remote-path (or CMN_REMOTE_PATH/CMN_PATH)."
[[ -f "$PLUGIN_MAIN_FILE" ]] || die "Plugin file not found: $PLUGIN_MAIN_FILE"
[[ "$PORT" =~ ^[0-9]+$ ]] || die "Invalid --port value: $PORT"
[[ -z "$SSH_KEY" || -f "$SSH_KEY" ]] || die "SSH key file not found: $SSH_KEY"

command -v ssh >/dev/null 2>&1 || die "ssh is required but not found."
command -v rsync >/dev/null 2>&1 || die "rsync is required but not found."

for item in "${DEPLOY_ITEMS[@]}"; do
  [[ -e "$PLUGIN_DIR/$item" ]] || die "Deploy item missing: $PLUGIN_DIR/$item"
done

read_plugin_version() {
  local file="$1"
  local version
  version="$(grep -m1 -E '^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*' "$file" | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//;s/[[:space:]]+$//' || true)"
  [[ -n "$version" ]] || return 1
  printf '%s\n' "$version"
}

LOCAL_VERSION="$(read_plugin_version "$PLUGIN_MAIN_FILE")" || die "Unable to parse local plugin version from header in $PLUGIN_MAIN_FILE"

SSH_CMD=(ssh -p "$PORT" -o BatchMode=yes -o StrictHostKeyChecking=accept-new)
if [[ -n "$SSH_KEY" ]]; then
  SSH_CMD+=(-i "$SSH_KEY")
fi

printf -v RSYNC_RSH '%q ' "${SSH_CMD[@]}"
RSYNC_RSH="${RSYNC_RSH% }"

REMOTE_TARGET="${USER}@${HOST}"
REMOTE_DIR="${REMOTE_PATH%/}"
REMOTE_PLUGIN_FILE="${REMOTE_DIR}/covermenowone-one.php"
REMOTE_FRONTEND_FILE="${REMOTE_DIR}/frontend.js"

fetch_remote_version() {
  local remote_file="$REMOTE_PLUGIN_FILE"
  local remote_file_q
  printf -v remote_file_q '%q' "$remote_file"
  "${SSH_CMD[@]}" "$REMOTE_TARGET" \
    "if [ -f $remote_file_q ]; then grep -m1 -E '^[[:space:]]*\\*[[:space:]]*Version:[[:space:]]*' $remote_file_q | sed -E 's/^[[:space:]]*\\*[[:space:]]*Version:[[:space:]]*//;s/[[:space:]]+$//'; else echo '(missing)'; fi"
}

fetch_remote_frontend_sha() {
  local remote_file="$REMOTE_FRONTEND_FILE"
  local remote_file_q
  printf -v remote_file_q '%q' "$remote_file"
  "${SSH_CMD[@]}" "$REMOTE_TARGET" \
    "if [ -f $remote_file_q ]; then sha256sum $remote_file_q | awk '{print \$1}'; else echo '(missing)'; fi"
}

make_rsync_files_from() {
  local file="$1"
  : > "$file"
  for item in "${DEPLOY_ITEMS[@]}"; do
    printf '%s\n' "$item" >> "$file"
  done
}

run_rsync() {
  local dry_flag="$1"  # 1=dry, 0=real
  local files_from
  files_from="$(mktemp)"
  make_rsync_files_from "$files_from"

  local remote_dir_q
  printf -v remote_dir_q '%q' "$REMOTE_DIR"
  local rsync_path_cmd="mkdir -p $remote_dir_q && rsync"

  local -a cmd=(rsync -az --partial --delay-updates --itemize-changes --human-readable -e "$RSYNC_RSH" --rsync-path "$rsync_path_cmd" --files-from "$files_from")
  if [[ "$dry_flag" == "1" ]]; then
    cmd+=(--dry-run)
  fi
  cmd+=("$PLUGIN_DIR/" "${REMOTE_TARGET}:${REMOTE_DIR}/")

  local output
  output="$("${cmd[@]}" 2>&1)" || {
    rm -f "$files_from"
    echo "$output"
    return 1
  }

  rm -f "$files_from"
  echo "$output"
}

extract_itemized_paths() {
  local rsync_output="$1"
  echo "$rsync_output" | grep -E '^[<>ch\.\*]' || true
}

echo "=== Deploy Config ==="
echo "Local plugin dir:  $PLUGIN_DIR"
echo "Remote target:     $REMOTE_TARGET"
echo "Remote path:       $REMOTE_DIR"
echo "Local version:     $LOCAL_VERSION"

REMOTE_VERSION_BEFORE="$(fetch_remote_version)" || die "Unable to read remote plugin version via SSH. Check --host/--user/--remote-path/--ssh-key."
[[ -n "$REMOTE_VERSION_BEFORE" ]] || REMOTE_VERSION_BEFORE="(missing)"
echo "Remote version:    $REMOTE_VERSION_BEFORE"

echo "Deploy item scope:"
for item in "${DEPLOY_ITEMS[@]}"; do
  echo "  - $item"
done

if [[ "$DRY_RUN" == "1" ]]; then
  echo ""
  echo "=== Dry Run (rsync --dry-run) ==="
  DRY_OUT="$(run_rsync 1)" || die "Dry-run rsync failed. Check host/user/path/key."
  FILE_DELTA_LINES="$(extract_itemized_paths "$DRY_OUT")"
  if [[ -n "$FILE_DELTA_LINES" ]]; then
    echo "Files that would be transferred:"
    echo "$FILE_DELTA_LINES"
  else
    echo "(no file changes detected)"
  fi
  echo "Dry-run complete. No files copied."
  exit 0
fi

echo ""
echo "=== Deploy (rsync) ==="
DEPLOY_OUT="$(run_rsync 0)" || die "Rsync deploy failed. Check connection and remote path permissions."
if [[ -n "$DEPLOY_OUT" ]]; then
  echo "$DEPLOY_OUT"
fi

REMOTE_VERSION_AFTER="$(fetch_remote_version)" || die "Unable to read remote plugin version after deploy."
[[ -n "$REMOTE_VERSION_AFTER" ]] || REMOTE_VERSION_AFTER="(missing)"

echo ""
echo "Post-deploy version check:"
echo "  Local version : $LOCAL_VERSION"
echo "  Remote version: $REMOTE_VERSION_AFTER"

if [[ "$REMOTE_VERSION_AFTER" == "$LOCAL_VERSION" ]]; then
  echo "PASS: Remote plugin version matches local."
else
  echo "FAIL: Remote plugin version does not match local."
  exit 1
fi

if [[ "$VERIFY" == "1" ]]; then
  echo ""
  echo "=== Verify (rsync --dry-run after deploy) ==="
  VERIFY_OUT="$(run_rsync 1)" || die "Verify rsync dry-run failed."
  CHANGED_LINES="$(echo "$VERIFY_OUT" | grep -E '^[<>ch\\.\\*]' || true)"
  if [[ -z "$CHANGED_LINES" ]]; then
    echo "PASS: No outstanding rsync deltas."
  else
    echo "FAIL: Outstanding rsync deltas detected:"
    echo "$CHANGED_LINES"
    exit 1
  fi
fi

if [[ "$VERIFY_FRONTEND_HASH" == "1" ]]; then
  echo ""
  echo "=== Verify frontend.js SHA256 ==="
  LOCAL_FRONTEND_SHA="$(sha256sum "$PLUGIN_DIR/frontend.js" | awk '{print $1}')"
  REMOTE_FRONTEND_SHA="$(fetch_remote_frontend_sha)" || die "Unable to read remote frontend.js hash."
  [[ -n "$REMOTE_FRONTEND_SHA" ]] || REMOTE_FRONTEND_SHA="(missing)"
  echo "  Local frontend.js SHA256 : $LOCAL_FRONTEND_SHA"
  echo "  Remote frontend.js SHA256: $REMOTE_FRONTEND_SHA"
  if [[ "$REMOTE_FRONTEND_SHA" == "$LOCAL_FRONTEND_SHA" ]]; then
    echo "PASS: frontend.js hash matches local."
  else
    echo "FAIL: frontend.js hash does not match local."
    exit 1
  fi
fi

echo "Deploy complete."
