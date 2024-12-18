#!/bin/sh

set -e

echo ""
echo "Installing dependencies..."
yarn install

echo ""
echo "Dependencies installed."

echo ""
echo "Running web server..."
yarn run dev

echo ""
