#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
BASE_DIR="${1:-/home/jaynorton17/covermenowone/codex-workspaces}"
START_REF="${2:-HEAD}"

workspaces=(
  "workspace/am-crm:am-crm"
  "workspace/schools:schools"
  "workspace/candidates:candidates"
  "workspace/admin-ops:admin-ops"
  "workspace/shared-platform:shared-platform"
)

mkdir -p "${BASE_DIR}"
git -C "${REPO_ROOT}" worktree prune >/dev/null 2>&1 || true

for entry in "${workspaces[@]}"; do
  branch="${entry%%:*}"
  dir_name="${entry##*:}"
  target_path="${BASE_DIR}/${dir_name}"

  if git -C "${REPO_ROOT}" worktree list --porcelain | grep -Fq "worktree ${target_path}"; then
    echo "Skipping existing worktree: ${target_path}"
    continue
  fi

  if [[ -e "${target_path}" && -n "$(ls -A "${target_path}" 2>/dev/null || true)" ]]; then
    echo "Cannot create worktree at ${target_path}: path already exists and is not empty." >&2
    exit 1
  fi

  if git -C "${REPO_ROOT}" show-ref --verify --quiet "refs/heads/${branch}"; then
    git -C "${REPO_ROOT}" worktree add "${target_path}" "${branch}"
  else
    git -C "${REPO_ROOT}" worktree add -b "${branch}" "${target_path}" "${START_REF}"
  fi
done

echo
echo "Codex workspaces ready under ${BASE_DIR}"
git -C "${REPO_ROOT}" worktree list
