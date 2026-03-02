# Portal/Admin Link + Action Audit

## Scripts
- `scripts/link_audit.php`
- `scripts/action_audit.php`

## Quick Start (guest-only)
```bash
php scripts/link_audit.php \
  --base-url=https://covermenow.co.uk \
  --roles=guest \
  --output-dir=docs/audits

php scripts/action_audit.php \
  --base-url=https://covermenow.co.uk \
  --manifest=docs/endpoint-manifest.json \
  --plugin-file=covermenowone-one.php \
  --link-audit-json=docs/audits/<link-audit-json-file>.json \
  --output-dir=docs/audits
```

## Full Role Matrix
1. Copy `docs/audits/auth-config.example.json` to a local, untracked file.
2. Fill cookie jar paths or credentials per role.
3. Run:
```bash
php scripts/link_audit.php \
  --base-url=https://covermenow.co.uk \
  --roles=guest,admin,staff,school,candidate \
  --auth-config=/secure/local/auth-config.json \
  --output-dir=docs/audits
```

## Optional DNS Override
If DNS resolution is unreliable in your runtime:
```bash
php scripts/link_audit.php \
  --base-url=https://covermenow.co.uk \
  --resolve-ip=217.160.0.243
```

## Optional Safe Probe Mode
```bash
php scripts/action_audit.php \
  --base-url=https://covermenow.co.uk \
  --manifest=docs/endpoint-manifest.json \
  --plugin-file=covermenowone-one.php \
  --link-audit-json=docs/audits/<link-audit-json-file>.json \
  --probe=1 \
  --probe-role=guest \
  --output-dir=docs/audits
```

`action_audit.php` skips `writes_state=true` probes by default unless explicitly enabled with `--probe-write-endpoints=1`.
