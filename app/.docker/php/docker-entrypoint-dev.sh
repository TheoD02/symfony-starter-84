#!/usr/bin/env sh

set -e

echo "Executing docker-entrypoint-dev.sh"

echo "Autorun"
if [ -d "/autorun" ]; then
    echo "Autorun directory exists"
    # run each script in the autorun directory that contain #!/bin/sh
    for f in /autorun/*; do
        if [ -f "$f" ] && [ -x "$f" ]; then
            echo "Executing $f"
            "$f"
        fi
    done
    echo "Autorun directory processed"
else
    echo "Autorun directory does not exist"
fi

exec "$@"