# Deploy (Portal Plugin)

## One-command deploy

```bash
SKIP_MARKETING_DEPLOY=1 ./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --verify
```

## Dry run (no upload)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --dry-run
```

Dry run prints:
- local plugin version
- remote plugin version
- local/remote `frontend.js` hash
- exact file list to upload

## SSH key deploy (optional)

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su19353 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --key ~/.ssh/id_ed25519 \
  --verify
```

## `.env.example`

Copy `.env.example` to your local env file format if you prefer env-based deploys.

Primary env vars used by script:
- `CMN_HOST`
- `CMN_USER`
- `CMN_PATH`
- `CMN_PORT`
- `CMN_KEY`
- `CMN_VERIFY`
- `CMN_VERSION`
- `CMN_TOKEN`
- `SFTP_PASS`

Legacy env vars are still supported for backward compatibility:
- `CMN_SFTP_HOST`
- `CMN_SFTP_USER`
- `CMN_SFTP_PORT`
- `CMN_REMOTE_PLUGIN_DIR`

No deploy secrets should be committed to git.
