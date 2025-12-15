# WSL2 Network Port Forwarding Script
# Run this script as Administrator in PowerShell

Write-Host "[*] Setting up WSL2 Network Port Forwarding for StoreFlow" -ForegroundColor Cyan
Write-Host "============================================================"

# Get WSL2 IP address
Write-Host "`n[*] Detecting WSL2 IP address..." -ForegroundColor Yellow
$wslIp = (wsl hostname -I).Trim().Split()[0]
Write-Host "WSL2 IP: $wslIp" -ForegroundColor Green

# Get Windows host IP
$windowsIp = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Ethernet*", "Wi-Fi*" | Where-Object { $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "127.*" } | Select-Object -First 1).IPAddress
Write-Host "Windows Host IP: $windowsIp" -ForegroundColor Green

# Ports to forward
$ports = @(5173, 8000)

Write-Host "`n[*] Configuring Windows Firewall Rules..." -ForegroundColor Yellow

foreach ($port in $ports) {
    # Remove existing firewall rules
    $ruleName = "WSL2 StoreFlow Port $port"
    Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue

    # Add new firewall rules (Inbound)
    New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -LocalPort $port -Protocol TCP -Action Allow -Profile Any | Out-Null
    Write-Host "[+] Firewall rule created for port $port" -ForegroundColor Green
}

Write-Host "`n[*] Setting up Port Forwarding..." -ForegroundColor Yellow

foreach ($port in $ports) {
    # Remove existing port proxy
    netsh interface portproxy delete v4tov4 listenport=$port listenaddress=0.0.0.0 | Out-Null

    # Add new port proxy
    $connectAddress = $wslIp
    netsh interface portproxy add v4tov4 listenport=$port listenaddress=0.0.0.0 connectport=$port connectaddress=$connectAddress | Out-Null
    $forwardMsg = "[+] Port forwarding configured: 0.0.0.0:$port -> " + $connectAddress + ":$port"
    Write-Host $forwardMsg -ForegroundColor Green
}

Write-Host "`n[*] Current Port Forwarding Configuration:" -ForegroundColor Cyan
netsh interface portproxy show v4tov4

Write-Host "`n[SUCCESS] Setup Complete!" -ForegroundColor Green
Write-Host "`n[*] Access your app from other devices:" -ForegroundColor Cyan
Write-Host "   Frontend (Vite):  http://${windowsIp}:5173" -ForegroundColor White
Write-Host "   Backend (Laravel): http://${windowsIp}:8000" -ForegroundColor White
Write-Host "`n[!] Note: WSL2 IP changes on restart. Re-run this script if needed." -ForegroundColor Yellow
Write-Host "`n[TIP] Run 'scripts\start-dev-servers.ps1' to auto-configure and start servers." -ForegroundColor Magenta
