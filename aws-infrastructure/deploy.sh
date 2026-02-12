#!/usr/bin/env bash
set -euo pipefail

PROFILE="kwikq"
REGION="af-south-1"
STACK_NAME="capitec-myciti-assets-stack"
OUTPUT_FILE="credentials.txt"

cd "$(dirname "$0")"

echo "=== Validating template ==="
sam validate --profile "$PROFILE" --region "$REGION"

echo ""
echo "=== Building ==="
sam build --profile "$PROFILE" --region "$REGION"

echo ""
echo "=== Deploying stack: $STACK_NAME ==="
sam deploy \
  --profile "$PROFILE" \
  --region "$REGION" \
  --stack-name "$STACK_NAME" \
  --capabilities CAPABILITY_NAMED_IAM \
  --resolve-s3 \
  --no-fail-on-empty-changeset

echo ""
echo "=== Fetching outputs ==="

OUTPUTS=$(aws cloudformation describe-stacks \
  --profile "$PROFILE" \
  --region "$REGION" \
  --stack-name "$STACK_NAME" \
  --query "Stacks[0].Outputs" \
  --output json)

BUCKET=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='BucketName'))")
CF_DOMAIN=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='CloudFrontDomain'))")
CF_ID=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='CloudFrontDistributionId'))")
ACCESS_KEY=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='AccessKeyId'))")
SECRET_KEY=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='SecretAccessKey'))")
USER_NAME=$(echo "$OUTPUTS" | python3 -c "import sys,json; print(next(o['OutputValue'] for o in json.load(sys.stdin) if o['OutputKey']=='UploaderUserName'))")

cat > "$OUTPUT_FILE" <<EOF
============================================
  CapitecMyCiti Assets - AWS Credentials
  Deployed: $(date)
============================================

S3 Bucket:              $BUCKET
CloudFront Domain:      https://$CF_DOMAIN
CloudFront Dist ID:     $CF_ID

IAM User:               $USER_NAME
AWS_ACCESS_KEY_ID:      $ACCESS_KEY
AWS_SECRET_ACCESS_KEY:  $SECRET_KEY
AWS_DEFAULT_REGION:     $REGION

--- Usage ---

Images are served via: https://$CF_DOMAIN/<object-key>

Example upload:
  AWS_ACCESS_KEY_ID=$ACCESS_KEY \\
  AWS_SECRET_ACCESS_KEY=$SECRET_KEY \\
  aws s3 cp my-image.png s3://$BUCKET/images/my-image.png

Laravel .env:
  AWS_ACCESS_KEY_ID=$ACCESS_KEY
  AWS_SECRET_ACCESS_KEY=$SECRET_KEY
  AWS_DEFAULT_REGION=$REGION
  AWS_BUCKET=$BUCKET
  AWS_URL=https://$CF_DOMAIN
EOF

echo ""
echo "=== Done! Credentials written to: $OUTPUT_FILE ==="
echo ""
cat "$OUTPUT_FILE"
