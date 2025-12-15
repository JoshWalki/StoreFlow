# Check Network Status and Configuration
# Can be run without Administrator privileges

Write-Host "[*] StoreFlow Network Status Check" -ForegroundColor Cyan
Write-Host "===================================="

# Get WSL2 IP
Write-Host "`n[*] WSL2 Information:" -ForegroundColor Yellow
$wslIp = (wsl hostname -I).Trim().Split()[0]
Write-Host "   WSL2 IP: $wslIp" -ForegroundColor White

# Get Windows IPs
Write-Host "`n[*] Windows Host Information:" -ForegroundColor Yellow
$windowsIps = Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Ethernet*", "Wi-Fi*" | Where-Object { $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "127.*" }
foreach ($ip in $windowsIps) {
    Write-Host "   Interface: $($ip.InterfaceAlias)" -ForegroundColor Gray
    Write-Host "   IP: $($ip.IPAddress)" -ForegroundColor White
}

# Check port forwarding
Write-Host "`n[*] Port Forwarding Status:" -ForegroundColor Yellow
$portProxy = netsh interface portproxy show v4tov4
if ($portProxy -like "*5173*" -or $portProxy -like "*8000*") {
    Write-Host $portProxy
} else {
    Write-Host "   [!] No port forwarding configured" -ForegroundColor Red
    Write-Host "   Run setup-network-forwarding.ps1 as Administrator" -ForegroundColor Yellow
}

# Check firewall rules
Write-Host "`n[*] Firewall Rules:" -ForegroundColor Yellow
$firewallRules = Get-NetFirewallRule -DisplayName "WSL2 StoreFlow*" -ErrorAction SilentlyContinue
if ($firewallRules) {
    foreach ($rule in $firewallRules) {
        Write-Host "   [+] $($rule.DisplayName) - $($rule.Enabled)" -ForegroundColor Green
    }
} else {
    Write-Host "   [!] No firewall rules configured" -ForegroundColor Red
    Write-Host "   Run setup-network-forwarding.ps1 as Administrator" -ForegroundColor Yellow
}

# Test ports
Write-Host "`n[*] Testing Ports:" -ForegroundColor Yellow
$ports = @(5173, 8000)
foreach ($port in $ports) {
    $testResult = Test-NetConnection -ComputerName localhost -Port $port -WarningAction SilentlyContinue
    if ($testResult.TcpTestSucceeded) {
        Write-Host "   [+] Port $port is open and accessible" -ForegroundColor Green
    } else {
        Write-Host "   [-] Port $port is not accessible" -ForegroundColor Red
        Write-Host "      Make sure the server is running on WSL2" -ForegroundColor Yellow
    }
}

Write-Host "`n[*] Access URLs (if servers are running):" -ForegroundColor Cyan
$mainIp = $windowsIps | Select-Object -First 1
if ($mainIp) {
    Write-Host "   Frontend: http://$($mainIp.IPAddress):5173" -ForegroundColor White
    Write-Host "   Backend:  http://$($mainIp.IPAddress):8000" -ForegroundColor White
}

Write-Host ""
