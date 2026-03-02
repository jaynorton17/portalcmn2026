# Deploy (Portal Plugin)

## Canonical deploy command (from plugin repo root)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --verify
```

## Dry-run command (no upload)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --dry-run
```

Dry-run prints:
- local plugin version (from `covermenowone-one.php`)
- remote plugin version
- local/remote `frontend.js` SHA256
- rsync diff list of files that would be copied

## SSH key / identity file

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --identity ~/.ssh/id_ed25519 \
  --verify
```

`--key` is still accepted as a backward-compatible alias.

## Env-based deploy (optional)

1. Copy example vars:

```bash
cp .env.example .env.local
```

2. Export the vars for your shell (example):

```bash
set -a
source .env.local
set +a
./deploy_portal.sh --verify
```

Primary env vars:
- `CMN_HOST`
- `CMN_USER`
- `CMN_PATH`
- `CMN_PORT`
- `CMN_IDENTITY` (or legacy `CMN_KEY`)
- `CMN_VERIFY`
- `CMN_VERSION`
- `CMN_TOKEN`
- `SFTP_PASS`

Legacy env vars remain supported:
- `CMN_SFTP_HOST`
- `CMN_SFTP_USER`
- `CMN_SFTP_PORT`
- `CMN_REMOTE_PLUGIN_DIR`

No deploy secrets should be committed.
