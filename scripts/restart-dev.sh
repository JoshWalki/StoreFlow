#!/bin/bash
# Restart Development Servers with Updated Configuration

echo "[*] Restarting StoreFlow Development Servers"
echo "=============================================="

# Kill existing processes
echo "[*] Stopping existing servers..."
pkill -f "vite" 2>/dev/null
pkill -f "php artisan serve" 2>/dev/null
sleep 2

# Get WSL2 IP
WSL_IP=$(hostname -I | awk '{print $1}')
echo "[*] WSL2 IP: $WSL_IP"

# Update .env with current WSL2 IP
if [ -f ".env" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=http://$WSL_IP:8000|" .env
    echo "[+] Updated APP_URL in .env"
fi

echo ""
echo "[*] Starting servers..."
echo "[!] Make sure you've run setup-network-forwarding.ps1 on Windows!"
echo ""
echo "Run these commands in separate terminals:"
echo "  1. npm run dev"
echo "  2. php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "Or run them in background:"
echo "  npm run dev > /tmp/vite.log 2>&1 &"
echo "  php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel.log 2>&1 &"
