#!/usr/bin/env bash
# Fail if Facturation Comptoir sources import SFE / MCF signing stack.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PATTERN='App\\Services\\SFE|InvoiceSigningService|BridgeClient|VirtualMcfService|use App\\Http\\Controllers\\McfController'

if command -v rg >/dev/null 2>&1; then
  HITS=$(rg -n --glob '*.php' -e "$PATTERN" \
    "$ROOT/app/Services/Comptoir" \
    "$ROOT/app/Http/Controllers/Comptoir" \
    || true)
else
  HITS=$(grep -RInE "$PATTERN" \
    "$ROOT/app/Services/Comptoir" \
    "$ROOT/app/Http/Controllers/Comptoir" \
    --include='*.php' 2>/dev/null || true)
fi

# Filter comment-only noise: keep lines that look like imports/code (use/new)
REAL=$(echo "$HITS" | grep -E '^\S+:[0-9]+:\s*(use |new |\\\\)' || true)
# Also catch any non-comment match of use App\Services\SFE
REAL2=$(echo "$HITS" | grep -E 'use App\\\\Services\\\\SFE' || true)

if [ -n "${REAL}${REAL2}" ]; then
  echo "ERROR: SFE/MCF coupling detected in comptoir sources:"
  echo "$HITS"
  exit 1
fi

echo "OK: comptoir isolated from SFE/MCF signing stack."
