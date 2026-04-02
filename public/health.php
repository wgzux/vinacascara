<?php
// Simple health check endpoint for Railway
// This script DOES NOT connect to the database to ensure it always returns 200 OK
// even if the database is still initializing.

header("Content-Type: text/plain");
echo "OK - Vina Cascara is running";
exit(0);
