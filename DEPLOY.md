# Deploy (Portal Plugin)

## Canonical deploy command (from plugin repo root)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --key ~/.ssh/id_ed25519 \
  --verify
```

## Dry-run command (no upload)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --key ~/.ssh/id_ed25519 \
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
  --key ~/.ssh/id_ed25519 \
  --verify
```

`--identity` is still accepted as a backward-compatible alias.

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
- `CMN_KEY` (or legacy `CMN_IDENTITY`)
- `CMN_VERIFY`
- `CMN_VERSION`
- `CMN_TOKEN`
- `SFTP_PASS`

Legacy env vars remain supported:
- `CMN_SFTP_HOST`
- `CMN_SFTP_USER`
- `CMN_SFTP_PORT`
- `CMN_REMOTE_PLUGIN_DIR`
- `CMN_IDENTITY`

No deploy secrets should be committed.
