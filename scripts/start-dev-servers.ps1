# Start Development Servers with Network Forwarding
# Run this script as Administrator in PowerShell

Write-Host "[*] Starting StoreFlow Development Environment" -ForegroundColor Cyan
Write-Host "==============================================="

# Setup network forwarding first
Write-Host "`n[*] Configuring network forwarding..." -ForegroundColor Yellow
& "$PSScriptRoot\setup-network-forwarding.ps1"

Write-Host "`n[*] Starting Development Servers in WSL2..." -ForegroundColor Yellow
Write-Host "   You can close this window - servers will run in WSL2 terminal" -ForegroundColor Gray

# Get Windows host IP for display
$windowsIp = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Ethernet*", "Wi-Fi*" | Where-Object { $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "127.*" } | Select-Object -First 1).IPAddress

Write-Host "`n[*] Access from any device on your network:" -ForegroundColor Green
Write-Host "   Frontend: http://${windowsIp}:5173" -ForegroundColor White
Write-Host "   Backend:  http://${windowsIp}:8000" -ForegroundColor White

Write-Host "`n[TIP] To start servers manually in WSL2:" -ForegroundColor Cyan
Write-Host "   1. Open WSL2 terminal" -ForegroundColor Gray
Write-Host "   2. cd /mnt/c/xampp/htdocs/storeflow" -ForegroundColor Gray
Write-Host "   3. Run: npm run dev" -ForegroundColor Gray
Write-Host "   4. In another terminal: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Gray

Write-Host "`nPress any key to exit..." -ForegroundColor Yellow
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
