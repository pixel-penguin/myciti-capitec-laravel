#!/usr/bin/env bash
set -euo pipefail

PROFILE="kwikq"
REGION="af-south-1"
DISTRIBUTION_ID="EIUZ54MWGEFCF"
PATHS="${1:-/*}"

cd "$(dirname "$0")"

echo "=== Invalidating CloudFront cache ==="
echo "Distribution: $DISTRIBUTION_ID"
echo "Paths:        $PATHS"
echo ""

INVALIDATION_ID=$(aws cloudfront create-invalidation \
  --profile "$PROFILE" \
  --region "$REGION" \
  --distribution-id "$DISTRIBUTION_ID" \
  --paths "$PATHS" \
  --query "Invalidation.Id" \
  --output text)

echo "Invalidation created: $INVALIDATION_ID"
echo "Waiting for completion..."

aws cloudfront wait invalidation-completed \
  --profile "$PROFILE" \
  --region "$REGION" \
  --distribution-id "$DISTRIBUTION_ID" \
  --id "$INVALIDATION_ID"

echo ""
echo "=== Done! CloudFront cache invalidated ==="
