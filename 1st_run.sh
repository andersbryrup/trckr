#!/bin/bash
echo "This will create a clean database"
echo ""
read -p "Press [Enter] key to continue"
cp empty.db time.db
echo ""
echo "Ensure that the parent directory is writable, sqlite needs to create tmp files"
echo ""
echo "You can use trckr.sh to serve the app locally"
