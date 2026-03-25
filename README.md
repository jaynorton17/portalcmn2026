# CoverMeNow ONE Plugin

## Deploy

Deployment is handled by [`deploy_portal.sh`](./deploy_portal.sh).

- Full instructions: [`DEPLOY.md`](./DEPLOY.md)
- Env template: [`.env.example`](./.env.example)

Quick start:

```bash
./deploy_portal.sh \
  --host access-5018438942.webspace-host.com \
  --user su357722 \
  --path /home/www/public/wp-content/plugins/covermenow-one \
  --dry-run
```

Export `SFTP_PASS` from `.env.local` or your shell before running deploy commands. Do not commit the password.
