# Remove WSL2 Network Port Forwarding
# Run this script as Administrator in PowerShell

Write-Host "[*] Removing WSL2 Network Port Forwarding for StoreFlow" -ForegroundColor Red
Write-Host "=========================================================="

$ports = @(5173, 8000)

Write-Host "`n[*] Removing Firewall Rules..." -ForegroundColor Yellow
foreach ($port in $ports) {
    $ruleName = "WSL2 StoreFlow Port $port"
    Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
    Write-Host "[+] Removed firewall rule for port $port" -ForegroundColor Green
}

Write-Host "`n[*] Removing Port Forwarding..." -ForegroundColor Yellow
foreach ($port in $ports) {
    netsh interface portproxy delete v4tov4 listenport=$port listenaddress=0.0.0.0
    Write-Host "[+] Removed port forwarding for port $port" -ForegroundColor Green
}

Write-Host "`n[*] Remaining Port Forwarding Configuration:" -ForegroundColor Cyan
netsh interface portproxy show v4tov4

Write-Host "`n[SUCCESS] Cleanup Complete!" -ForegroundColor Green
