#!/bin/bash
set -e

# Run MySQL setup script in background
/usr/local/bin/setup-mysql.sh &

# Start Apache
exec apache2-foreground
