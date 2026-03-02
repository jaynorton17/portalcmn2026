# Release Checklist

Run from plugin root:

```bash
cd /home/jaynorton17/covermenowone/wp-plugin/covermenowone-one
```

## 1) Version bump

```bash
# Show current version values
grep -nE '^ \* Version:' covermenowone-one.php
grep -nE 'const VERSION = ' covermenowone-one.php
grep -nE 'const SCHEMA_VERSION = ' covermenowone-one.php

# Set target version
NEW_VERSION="0.1.26"

# Bump plugin header + VERSION constant
sed -i -E "0,/^ \* Version: .*/s// * Version: ${NEW_VERSION}/" covermenowone-one.php
sed -i -E "s/const VERSION = '[0-9]+\.[0-9]+\.[0-9]+';/const VERSION = '${NEW_VERSION}';/" covermenowone-one.php

# Optional: schema bump only when schema changed
# NEW_SCHEMA_VERSION="76"
# sed -i -E "s/const SCHEMA_VERSION = [0-9]+;/const SCHEMA_VERSION = ${NEW_SCHEMA_VERSION};/" covermenowone-one.php

# Confirm
grep -nE '^ \* Version:' covermenowone-one.php
grep -nE 'const VERSION = ' covermenowone-one.php
grep -nE 'const SCHEMA_VERSION = ' covermenowone-one.php
```

## 2) PHP syntax check (`php -l`)

```bash
php -l covermenowone-one.php
find scripts -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

## 3) JavaScript syntax check (`node --check`)

```bash
node --check frontend.js
node --check livechat-widget.js
```

## 4) Generate endpoint manifest

```bash
php scripts/generate_endpoint_manifest.php docs/endpoint-manifest.json
```

## 5) Run endpoint policy validator

```bash
php scripts/validate_endpoint_policies.php docs/endpoint-manifest.json
php scripts/validate_handler_registrations.php docs/endpoint-manifest.json
php scripts/validate_runtime_guards.php docs/endpoint-manifest.json
```

## 5b) Unified check command

```bash
make check
```

## 6) Deploy (dry-run first, then verify deploy)

```bash
HOST="access-5018438942.webspace-host.com"
USER="su19353"
REMOTE_PATH="/home/www/public/wp-content/plugins/covermenow-one"
# Optional:
# SSH_KEY="$HOME/.ssh/id_ed25519"
# PORT="22"

# Dry-run: print local/remote version and transfer plan, no upload
./scripts/deploy_plugin.sh \
  --host "${HOST}" \
  --user "${USER}" \
  --remote-path "${REMOTE_PATH}" \
  --dry-run

# Deploy + verify + frontend hash verify
./scripts/deploy_plugin.sh \
  --host "${HOST}" \
  --user "${USER}" \
  --remote-path "${REMOTE_PATH}" \
  --verify \
  --verify-frontend-hash

# If needed, also pass:
# --ssh-key "${SSH_KEY}" --port "${PORT}"
```

## 7) Post-deploy production verification list

```bash
# 1) Remote plugin version header
ssh "${USER}@${HOST}" "grep -m1 -E '^ \* Version:' '${REMOTE_PATH}/covermenowone-one.php'"

# 2) Remote frontend hash
ssh "${USER}@${HOST}" "sha256sum '${REMOTE_PATH}/frontend.js'"

# 3) Portal entry response
curl -I "https://covermenow.co.uk/covermenow-one/"

# 4) School portal URL response
curl -I "https://covermenow.co.uk/covermenow-one/?school=cover"

# 5) Quick heartbeat endpoint sanity (authenticated browser session still required for full check)
curl -I "https://covermenow.co.uk/covermenow-one/"
```

## 8) Commit release artifacts

```bash
git status
git add covermenowone-one.php docs/endpoint-manifest.json docs/release-checklist.md
git commit -m "chore(release): v${NEW_VERSION} checklist and artifacts"
git push
```
