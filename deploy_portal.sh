#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REMOTE_PLUGIN_DIR="/home/www/public/wp-content/plugins/covermenowone-one"

required_vars=(
  CMN_HOST
  CMN_SFTP_HOST
  CMN_SFTP_USER
  CMN_VERSION
  CMN_TOKEN
)

for var in "${required_vars[@]}"; do
  if [[ -z "${!var:-}" ]]; then
    echo "Missing required environment variable: ${var}" >&2
    exit 1
  fi
done

if [[ ! "${CMN_VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "CMN_VERSION must be digits.digits.digits (e.g. 0.0.19)" >&2
  exit 1
fi

batch_file="$(mktemp)"
trap 'rm -f "${batch_file}"' EXIT

cat > "${batch_file}" <<EOF
cd ${REMOTE_PLUGIN_DIR}
put ${SCRIPT_DIR}/covermenowone-one.php
put ${SCRIPT_DIR}/frontend.css
put ${SCRIPT_DIR}/frontend.js
put ${SCRIPT_DIR}/livechat-widget.css
put ${SCRIPT_DIR}/livechat-widget.js
put ${SCRIPT_DIR}/admin.css
put ${SCRIPT_DIR}/login.css
put -r ${SCRIPT_DIR}/assets
put -r ${SCRIPT_DIR}/cv-converter
EOF

echo "Uploading plugin files to ${CMN_SFTP_USER}@${CMN_SFTP_HOST}:${REMOTE_PLUGIN_DIR} ..."
sftp -b "${batch_file}" "${CMN_SFTP_USER}@${CMN_SFTP_HOST}"

echo "Setting portal release version to ${CMN_VERSION} ..."
response="$(curl -s "https://${CMN_HOST}/?cmn_set_release_version=1&v=${CMN_VERSION}&token=${CMN_TOKEN}")"
echo "${response}"
