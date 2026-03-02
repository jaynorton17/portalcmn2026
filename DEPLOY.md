# Deploy (Portal Plugin)

## One-command deploy

```bash
SKIP_MARKETING_DEPLOY=1 ./deploy_portal.sh \
  --sftp-host access-5018438942.webspace-host.com \
  --user su19353 \
  --remote-path /home/www/public/wp-content/plugins/covermenow-one
```

## Dry run (no upload)

```bash
./deploy_portal.sh --sftp-host access-5018438942.webspace-host.com --user su19353 --dry-run
```

Dry run prints:
- local plugin version
- remote plugin version
- local/remote `frontend.js` hash
- exact file list to upload

## Verify only

```bash
./deploy_portal.sh --sftp-host access-5018438942.webspace-host.com --user su19353 --verify-only
```

Verification checks:
- remote `covermenowone-one.php` header version equals local version
- remote `frontend.js` hash equals local hash

## Optional env vars (backward compatible)
- `CMN_HOST`
- `CMN_SFTP_HOST`
- `CMN_SFTP_USER`
- `CMN_SFTP_PORT`
- `CMN_REMOTE_PLUGIN_DIR`
- `CMN_VERSION`
- `CMN_TOKEN`
- `SFTP_PASS`

No deploy secrets should be committed to git.
