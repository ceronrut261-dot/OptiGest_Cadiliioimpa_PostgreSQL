#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."

cp backend/public/css/app.css frontend/css/app.css
cp backend/public/js/app.js frontend/js/app.js
rm -rf frontend/components/vistas-blade
cp -r backend/resources/views frontend/components/vistas-blade

echo "frontend/ actualizado desde backend/."
