#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_REMOTE_PLUGIN_DIR="/home/www/public/wp-content/plugins/covermenow-one"

# Primary env vars
CMN_HOST_VALUE="${CMN_HOST:-${CMN_SFTP_HOST:-}}"
CMN_USER_VALUE="${CMN_USER:-${CMN_SFTP_USER:-}}"
CMN_PATH_VALUE="${CMN_PATH:-${CMN_REMOTE_PLUGIN_DIR:-$DEFAULT_REMOTE_PLUGIN_DIR}}"
CMN_PORT_VALUE="${CMN_PORT:-${CMN_SFTP_PORT:-22}}"
CMN_KEY_VALUE="${CMN_KEY:-}"
CMN_VERSION_VALUE="${CMN_VERSION:-}"
CMN_TOKEN_VALUE="${CMN_TOKEN:-}"
VERIFY_MODE="${CMN_VERIFY:-1}"

DRY_RUN=0
VERIFY_ONLY=0

usage() {
  cat <<USAGE
Usage: ./deploy_portal.sh [options]

Options:
  --host <host>            SSH/SFTP host (default: CMN_HOST)
  --user <user>            SSH/SFTP user (default: CMN_USER)
  --path <path>            Remote plugin path (default: CMN_PATH or $DEFAULT_REMOTE_PLUGIN_DIR)
  --port <port>            SSH/SFTP port (default: CMN_PORT or 22)
  --key <path>             SSH private key path (default: CMN_KEY)
  --dry-run                Print local/remote versions + transfer plan, no upload
  --verify                 Run post-deploy verification checks

Backwards-compatible legacy flags still supported:
  --sftp-host <host>, --remote-path <path>, --verify-only

Optional release endpoint flags:
  --version <x.y.z>        Release version to set via endpoint (default: CMN_VERSION or local plugin header)
  --token <token>          Release endpoint token (default: CMN_TOKEN)

Examples:
  ./deploy_portal.sh --host access-5018438942.webspace-host.com --user su19353 --path /home/www/public/wp-content/plugins/covermenow-one --dry-run
  ./deploy_portal.sh --host access-5018438942.webspace-host.com --user su19353 --path /home/www/public/wp-content/plugins/covermenow-one --verify
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --host|--sftp-host)
      CMN_HOST_VALUE="${2:-}"
      shift 2
      ;;
    --user)
      CMN_USER_VALUE="${2:-}"
      shift 2
      ;;
    --path|--remote-path)
      CMN_PATH_VALUE="${2:-}"
      shift 2
      ;;
    --port)
      CMN_PORT_VALUE="${2:-}"
      shift 2
      ;;
    --key)
      CMN_KEY_VALUE="${2:-}"
      shift 2
      ;;
    --version)
      CMN_VERSION_VALUE="${2:-}"
      shift 2
      ;;
    --token)
      CMN_TOKEN_VALUE="${2:-}"
      shift 2
      ;;
    --dry-run)
      DRY_RUN=1
      shift
      ;;
    --verify)
      VERIFY_MODE=1
      shift
      ;;
    --verify-only)
      VERIFY_MODE=1
      VERIFY_ONLY=1
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ -z "$CMN_HOST_VALUE" ]]; then
  echo "Missing required host. Provide --host (or CMN_HOST)." >&2
  exit 1
fi
if [[ -z "$CMN_USER_VALUE" ]]; then
  echo "Missing required user. Provide --user (or CMN_USER)." >&2
  exit 1
fi
if [[ -z "$CMN_PATH_VALUE" ]]; then
  echo "Missing remote plugin path. Provide --path (or CMN_PATH)." >&2
  exit 1
fi
if ! [[ "$CMN_PORT_VALUE" =~ ^[0-9]+$ ]]; then
  echo "Invalid port: $CMN_PORT_VALUE" >&2
  exit 1
fi
if [[ -n "$CMN_KEY_VALUE" && ! -f "$CMN_KEY_VALUE" ]]; then
  echo "SSH key not found: $CMN_KEY_VALUE" >&2
  exit 1
fi

local_version="$(grep -m1 -E '^\s*\*\s*Version:\s*' "$SCRIPT_DIR/covermenowone-one.php" | sed -E 's/^\s*\*\s*Version:\s*//;s/[[:space:]]+$//')"
if [[ -z "$local_version" ]]; then
  echo "Unable to read local plugin version from covermenowone-one.php" >&2
  exit 1
fi

if [[ -z "$CMN_VERSION_VALUE" ]]; then
  CMN_VERSION_VALUE="$local_version"
fi
if ! [[ "$CMN_VERSION_VALUE" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Version must match x.y.z (got: $CMN_VERSION_VALUE)" >&2
  exit 1
fi

required_local_paths=(
  "$SCRIPT_DIR/covermenowone-one.php"
  "$SCRIPT_DIR/frontend.css"
  "$SCRIPT_DIR/frontend.js"
  "$SCRIPT_DIR/livechat-widget.css"
  "$SCRIPT_DIR/livechat-widget.js"
  "$SCRIPT_DIR/admin.css"
  "$SCRIPT_DIR/login.css"
  "$SCRIPT_DIR/assets"
  "$SCRIPT_DIR/cv-converter"
)
for path in "${required_local_paths[@]}"; do
  if [[ ! -e "$path" ]]; then
    echo "Missing local deploy path: $path" >&2
    exit 1
  fi
done

SSH_BASE=(ssh -p "$CMN_PORT_VALUE" -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null)
SFTP_BASE=(sftp -P "$CMN_PORT_VALUE" -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -oBatchMode=no)
if [[ -n "$CMN_KEY_VALUE" ]]; then
  SSH_BASE+=(-i "$CMN_KEY_VALUE")
  SFTP_BASE+=(-i "$CMN_KEY_VALUE")
fi

if command -v sshpass >/dev/null 2>&1; then
  if [[ -n "${SFTP_PASS:-}" ]]; then
    export SSHPASS="$SFTP_PASS"
    SSH_BASE=(sshpass -e "${SSH_BASE[@]}")
    SFTP_BASE=(sshpass -e "${SFTP_BASE[@]}")
  elif [[ -f "$HOME/.covermenowone_sftp_pass" ]]; then
    SSH_BASE=(sshpass -f "$HOME/.covermenowone_sftp_pass" "${SSH_BASE[@]}")
    SFTP_BASE=(sshpass -f "$HOME/.covermenowone_sftp_pass" "${SFTP_BASE[@]}")
  fi
fi

SSH_TARGET="$CMN_USER_VALUE@$CMN_HOST_VALUE"

remote_probe() {
  local remote_path="$1"
  "${SSH_BASE[@]}" "$SSH_TARGET" "
    set -e
    if [ -f '$remote_path/covermenowone-one.php' ]; then
      remote_version=\$(grep -m1 -E '^\s*\*\s*Version:\s*' '$remote_path/covermenowone-one.php' | sed -E 's/^\s*\*\s*Version:\s*//;s/[[:space:]]+$//')
    else
      remote_version='(missing)'
    fi
    if [ -f '$remote_path/frontend.js' ]; then
      remote_js_sha=\$(sha256sum '$remote_path/frontend.js' | awk '{print \$1}')
    else
      remote_js_sha='(missing)'
    fi
    printf '%s\n' "\$remote_version" "\$remote_js_sha"
  "
}

print_transfer_list() {
  echo "Files to upload:"
  echo "  - covermenowone-one.php"
  echo "  - frontend.css"
  echo "  - frontend.js"
  echo "  - livechat-widget.css"
  echo "  - livechat-widget.js"
  echo "  - admin.css"
  echo "  - login.css"
  echo "  - assets/ (recursive)"
  echo "  - cv-converter/ (recursive)"
}

print_plan() {
  local remote_before_version="$1"
  local remote_before_sha="$2"
  local local_js_sha
  local_js_sha="$(sha256sum "$SCRIPT_DIR/frontend.js" | awk '{print $1}')"

  echo "=== Deploy Plan ==="
  echo "Local plugin dir:   $SCRIPT_DIR"
  echo "Remote host/user:   $SSH_TARGET:$CMN_PORT_VALUE"
  echo "Remote plugin path: $CMN_PATH_VALUE"
  echo "Local version:      $local_version"
  echo "Release version:    $CMN_VERSION_VALUE"
  echo "Remote version:     $remote_before_version"
  echo "Local frontend.js:  $local_js_sha"
  echo "Remote frontend.js: $remote_before_sha"
  print_transfer_list
}

mapfile -t before_probe < <(remote_probe "$CMN_PATH_VALUE")
remote_before_version="${before_probe[0]:-(missing)}"
remote_before_sha="${before_probe[1]:-(missing)}"

print_plan "$remote_before_version" "$remote_before_sha"

if [[ "$DRY_RUN" == "1" ]]; then
  echo "Dry run complete. No files uploaded."
  exit 0
fi

if [[ "$VERIFY_ONLY" != "1" ]]; then
  batch_file="$(mktemp)"
  trap 'rm -f "$batch_file"' EXIT

  cat > "$batch_file" <<SFTP_CMDS
-mkdir $CMN_PATH_VALUE
lcd $SCRIPT_DIR
put covermenowone-one.php $CMN_PATH_VALUE/covermenowone-one.php
put frontend.css $CMN_PATH_VALUE/frontend.css
put frontend.js $CMN_PATH_VALUE/frontend.js
put livechat-widget.css $CMN_PATH_VALUE/livechat-widget.css
put livechat-widget.js $CMN_PATH_VALUE/livechat-widget.js
put admin.css $CMN_PATH_VALUE/admin.css
put login.css $CMN_PATH_VALUE/login.css
put -r assets $CMN_PATH_VALUE
put -r cv-converter $CMN_PATH_VALUE
SFTP_CMDS

  echo "Uploading plugin files..."
  "${SFTP_BASE[@]}" -b "$batch_file" "$SSH_TARGET"

  if [[ -n "$CMN_TOKEN_VALUE" && -n "$CMN_HOST_VALUE" ]]; then
    echo "Setting release version via endpoint..."
    release_response="$(curl -s "https://${CMN_HOST_VALUE}/?cmn_set_release_version=1&v=${CMN_VERSION_VALUE}&token=${CMN_TOKEN_VALUE}")"
    echo "$release_response"
  else
    echo "Release token not supplied; skipped release endpoint update."
  fi
fi

if [[ "$VERIFY_MODE" != "1" ]]; then
  echo "Deploy completed (verification disabled)."
  exit 0
fi

mapfile -t after_probe < <(remote_probe "$CMN_PATH_VALUE")
remote_after_version="${after_probe[0]:-(missing)}"
remote_after_sha="${after_probe[1]:-(missing)}"
local_after_sha="$(sha256sum "$SCRIPT_DIR/frontend.js" | awk '{print $1}')"

frontend_changed_expected=0
if [[ "$local_after_sha" != "$remote_before_sha" ]]; then
  frontend_changed_expected=1
fi

echo "=== Post-Deploy Verify ==="
echo "Remote version after:    $remote_after_version"
echo "Expected local version:  $local_version"
echo "Remote frontend.js SHA:  $remote_after_sha"
echo "Local frontend.js SHA:   $local_after_sha"
echo "Remote SHA before deploy:$remote_before_sha"

if [[ "$remote_after_version" != "$local_version" ]]; then
  echo "ERROR: Remote plugin version does not match local version." >&2
  exit 1
fi
if [[ "$remote_after_sha" != "$local_after_sha" ]]; then
  echo "ERROR: Remote frontend.js hash does not match local frontend.js." >&2
  exit 1
fi
if [[ "$frontend_changed_expected" == "1" && "$remote_after_sha" == "$remote_before_sha" ]]; then
  echo "ERROR: frontend.js changed locally but remote hash did not change." >&2
  exit 1
fi

if [[ "$frontend_changed_expected" == "1" ]]; then
  echo "frontend.js hash updated on remote (expected)."
else
  echo "frontend.js hash unchanged (no local frontend.js delta detected pre-deploy)."
fi

echo "Deploy verification succeeded."
