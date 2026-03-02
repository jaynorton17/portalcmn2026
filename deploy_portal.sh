#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_REMOTE_PLUGIN_DIR="/home/www/public/wp-content/plugins/covermenow-one"

CMN_HOST_VALUE="${CMN_HOST:-${CMN_SFTP_HOST:-}}"
CMN_USER_VALUE="${CMN_USER:-${CMN_SFTP_USER:-}}"
CMN_PATH_VALUE="${CMN_PATH:-${CMN_REMOTE_PLUGIN_DIR:-$DEFAULT_REMOTE_PLUGIN_DIR}}"
CMN_PORT_VALUE="${CMN_PORT:-${CMN_SFTP_PORT:-22}}"
CMN_IDENTITY_VALUE="${CMN_KEY:-${CMN_IDENTITY:-}}"
CMN_VERSION_VALUE="${CMN_VERSION:-}"
CMN_TOKEN_VALUE="${CMN_TOKEN:-}"
VERIFY_MODE="${CMN_VERIFY:-1}"

DRY_RUN=0
VERIFY_ONLY=0

usage() {
  cat <<USAGE
Usage: ./deploy_portal.sh [options]

Options:
  --host <host>            SSH host (default: CMN_HOST)
  --user <user>            SSH user (default: CMN_USER)
  --path <path>            Remote plugin path (default: CMN_PATH or $DEFAULT_REMOTE_PLUGIN_DIR)
  --port <port>            SSH port (default: CMN_PORT or 22)
  --key <path>             SSH private key path (default: CMN_KEY or CMN_IDENTITY)
  --dry-run                Print local/remote versions + rsync diff, no upload
  --verify                 Run post-deploy verification checks (default enabled)

Backwards-compatible legacy flags still supported:
  --sftp-host <host>, --remote-path <path>, --identity <path>, --verify-only

Optional release endpoint flags:
  --version <x.y.z>        Release version to set via endpoint (default: CMN_VERSION or local plugin header)
  --token <token>          Release endpoint token (default: CMN_TOKEN)

Examples:
  ./deploy_portal.sh --host access-5018438942.webspace-host.com --user su19353 --path /home/www/public/wp-content/plugins/covermenow-one --key ~/.ssh/id_ed25519 --dry-run
  ./deploy_portal.sh --host access-5018438942.webspace-host.com --user su19353 --path /home/www/public/wp-content/plugins/covermenow-one --key ~/.ssh/id_ed25519 --verify
USAGE
}

require_cmd() {
  local name="$1"
  if ! command -v "$name" >/dev/null 2>&1; then
    echo "Missing required command: $name" >&2
    exit 1
  fi
}

read_local_plugin_version() {
  grep -m1 -E '^\s*\*\s*Version:\s*' "$SCRIPT_DIR/covermenowone-one.php" | sed -E 's/^\s*\*\s*Version:\s*//;s/[[:space:]]+$//'
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
    --key|--identity)
      CMN_IDENTITY_VALUE="${2:-}"
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

if [[ "$DRY_RUN" == "1" && "$VERIFY_ONLY" == "1" ]]; then
  echo "Cannot combine --dry-run and --verify-only." >&2
  exit 1
fi

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
if [[ -n "$CMN_IDENTITY_VALUE" && ! -f "$CMN_IDENTITY_VALUE" ]]; then
  echo "SSH key file not found: $CMN_IDENTITY_VALUE" >&2
  exit 1
fi

require_cmd ssh
require_cmd rsync
require_cmd sha256sum
require_cmd grep
require_cmd sed
require_cmd awk
require_cmd find

local_version="$(read_local_plugin_version)"
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

deploy_items=(
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

for item in "${deploy_items[@]}"; do
  if [[ ! -e "$SCRIPT_DIR/$item" ]]; then
    echo "Missing local deploy path: $SCRIPT_DIR/$item" >&2
    exit 1
  fi
done

stage_dir="$(mktemp -d)"
cleanup() {
  rm -rf "$stage_dir"
}
trap cleanup EXIT

for item in "${deploy_items[@]}"; do
  cp -a "$SCRIPT_DIR/$item" "$stage_dir/$item"
done

SSH_TARGET="$CMN_USER_VALUE@$CMN_HOST_VALUE"

sshpass_prefix=()
if [[ -z "$CMN_IDENTITY_VALUE" ]] && command -v sshpass >/dev/null 2>&1; then
  if [[ -n "${SFTP_PASS:-}" ]]; then
    export SSHPASS="$SFTP_PASS"
    sshpass_prefix=(sshpass -e)
  elif [[ -f "$HOME/.covermenowone_sftp_pass" ]]; then
    sshpass_prefix=(sshpass -f "$HOME/.covermenowone_sftp_pass")
  fi
fi

ssh_args=(
  -p "$CMN_PORT_VALUE"
  -o StrictHostKeyChecking=no
  -o UserKnownHostsFile=/dev/null
)
if [[ -n "$CMN_IDENTITY_VALUE" ]]; then
  ssh_args+=(-i "$CMN_IDENTITY_VALUE")
fi

rsync_ssh_cmd=(ssh "${ssh_args[@]}")
RSYNC_RSH="$(printf '%q ' "${rsync_ssh_cmd[@]}")"

run_ssh() {
  "${sshpass_prefix[@]}" ssh "${ssh_args[@]}" "$SSH_TARGET" "$@"
}

run_rsync() {
  "${sshpass_prefix[@]}" rsync -e "$RSYNC_RSH" "$@"
}

remote_probe() {
  local remote_path="$1"
  run_ssh "
    set -e
    if [ -d '$remote_path' ]; then
      path_exists='1'
    else
      path_exists='0'
    fi
    if [ -f '$remote_path/covermenowone-one.php' ]; then
      remote_version=\$(grep -m1 -E '^\\s*\\*\\s*Version:\\s*' '$remote_path/covermenowone-one.php' | sed -E 's/^\\s*\\*\\s*Version:\\s*//;s/[[:space:]]+\$//')
    else
      remote_version='(missing)'
    fi
    if [ -f '$remote_path/frontend.js' ]; then
      remote_js_sha=\$(sha256sum '$remote_path/frontend.js' | awk '{print \$1}')
    else
      remote_js_sha='(missing)'
    fi
    printf '%s\n' \"\$remote_version\" \"\$remote_js_sha\" \"\$path_exists\"
  "
}

print_plan() {
  local remote_before_version="$1"
  local remote_before_sha="$2"
  local local_js_sha="$3"

  echo "=== Deploy Plan ==="
  echo "Local plugin dir:   $SCRIPT_DIR"
  echo "Remote host/user:   $SSH_TARGET:$CMN_PORT_VALUE"
  echo "Remote plugin path: $CMN_PATH_VALUE"
  echo "Local version:      $local_version"
  echo "Release version:    $CMN_VERSION_VALUE"
  echo "Remote version:     $remote_before_version"
  echo "Local frontend.js:  $local_js_sha"
  echo "Remote frontend.js: $remote_before_sha"
}

print_diff_list() {
  local remote_exists="$1"
  local rsync_output="$2"

  echo "Files that would be transferred:"
  if [[ "$remote_exists" != "1" ]]; then
    (cd "$stage_dir" && find . -type f | sed 's|^\./|  - |' | sort)
    return
  fi

  local filtered
  filtered="$(printf '%s\n' "$rsync_output" | sed '/^sending incremental file list$/d;/^created directory /d;/^sent /d;/^total size is /d;/^$/d')"
  if [[ -z "$filtered" ]]; then
    echo "  (no changes detected)"
    return
  fi
  printf '%s\n' "$filtered" | sed 's/^/  /'
}

mapfile -t before_probe < <(remote_probe "$CMN_PATH_VALUE")
remote_before_version="${before_probe[0]:-(missing)}"
remote_before_sha="${before_probe[1]:-(missing)}"
remote_path_exists="${before_probe[2]:-0}"
local_frontend_sha="$(sha256sum "$SCRIPT_DIR/frontend.js" | awk '{print $1}')"

rsync_preview_output="$(run_rsync -az --itemize-changes --dry-run "$stage_dir/" "$SSH_TARGET:$CMN_PATH_VALUE/")"
print_plan "$remote_before_version" "$remote_before_sha" "$local_frontend_sha"
print_diff_list "$remote_path_exists" "$rsync_preview_output"

if [[ "$DRY_RUN" == "1" ]]; then
  echo "Dry run complete. No files uploaded."
  exit 0
fi

if [[ "$VERIFY_ONLY" != "1" ]]; then
  echo "Ensuring remote plugin path exists..."
  run_ssh "mkdir -p '$CMN_PATH_VALUE'"

  echo "Uploading plugin files via rsync..."
  run_rsync -az --itemize-changes "$stage_dir/" "$SSH_TARGET:$CMN_PATH_VALUE/"

  if [[ -n "$CMN_TOKEN_VALUE" ]]; then
    if command -v curl >/dev/null 2>&1; then
      echo "Setting release version via endpoint..."
      release_response="$(curl -s "https://${CMN_HOST_VALUE}/?cmn_set_release_version=1&v=${CMN_VERSION_VALUE}&token=${CMN_TOKEN_VALUE}")"
      echo "$release_response"
    else
      echo "curl not found; skipped release endpoint update."
    fi
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

frontend_changed_expected=0
if [[ "$local_frontend_sha" != "$remote_before_sha" ]]; then
  frontend_changed_expected=1
fi

echo "=== Post-Deploy Verify ==="
echo "Remote version after:     $remote_after_version"
echo "Expected local version:   $local_version"
echo "Remote frontend.js SHA:   $remote_after_sha"
echo "Local frontend.js SHA:    $local_frontend_sha"
echo "Remote SHA before deploy: $remote_before_sha"

if [[ "$remote_after_version" != "$local_version" ]]; then
  echo "ERROR: Remote plugin version does not match local version." >&2
  exit 1
fi
if [[ "$remote_after_sha" != "$local_frontend_sha" ]]; then
  echo "ERROR: Remote frontend.js hash does not match local frontend.js." >&2
  exit 1
fi
if [[ "$frontend_changed_expected" == "1" && "$remote_after_sha" == "$remote_before_sha" ]]; then
  echo "ERROR: Local frontend.js differs from remote-before, but remote hash did not change." >&2
  exit 1
fi

if [[ "$frontend_changed_expected" == "1" ]]; then
  echo "PASS: frontend.js hash changed on remote because local hash differed before deploy."
else
  echo "PASS: frontend.js hash unchanged (local and remote were already identical before deploy)."
fi

echo "PASS: Deploy verification succeeded."
