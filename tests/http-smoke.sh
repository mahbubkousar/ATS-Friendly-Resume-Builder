#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "$0")/.." && pwd)"
server_log="$(mktemp)"
register_body="$(mktemp)"
auth_body="$(mktemp)"
APP_ENV=test php -S 127.0.0.1:8765 -t "$project_dir" >"$server_log" 2>&1 &
server_pid=$!
cleanup() {
  kill "$server_pid" 2>/dev/null || true
  wait "$server_pid" 2>/dev/null || true
  rm -f "$server_log" "$register_body" "$auth_body"
}
trap cleanup EXIT

for attempt in {1..20}; do
  if curl -fsS http://127.0.0.1:8765/index.php >/dev/null 2>&1; then
    break
  fi
  sleep 0.2
done

register_status="$(curl -sS -o "$register_body" -w '%{http_code}' http://127.0.0.1:8765/api/register.php)"
auth_status="$(curl -sS -o "$auth_body" -w '%{http_code}' -X POST http://127.0.0.1:8765/api/add-application.php)"

test "$register_status" = "405"
test "$auth_status" = "401"
grep -q 'Method not allowed' "$register_body"
grep -q 'User not authenticated' "$auth_body"
