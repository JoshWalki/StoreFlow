# StoreFlow Network Configuration Scripts

These PowerShell scripts help you access your StoreFlow app from other devices on your local network.

## The Problem

WSL2 uses a virtualized network that's isolated from your Windows host. Other devices on your network can't directly access services running in WSL2.

## The Solution

These scripts set up port forwarding from your Windows host to WSL2, making your development servers accessible on your local network.

## Scripts Overview

### 1. `setup-network-forwarding.ps1` ⭐ Main Script
**Run this first!**

Sets up port forwarding and firewall rules.

**How to run:**
```powershell
# Right-click PowerShell and select "Run as Administrator"
cd C:\xampp\htdocs\storeflow\scripts
.\setup-network-forwarding.ps1
```

**What it does:**
- Detects your WSL2 IP address
- Creates firewall rules for ports 5173 (Vite) and 8000 (Laravel)
- Sets up port forwarding from Windows to WSL2
- Shows you the URLs to access from other devices

### 2. `check-network-status.ps1`
Check if everything is configured correctly.

**How to run:**
```powershell
# Can run without Administrator privileges
cd C:\xampp\htdocs\storeflow\scripts
.\check-network-status.ps1
```

**What it shows:**
- WSL2 and Windows IP addresses
- Port forwarding status
- Firewall rules status
- Port accessibility test
- Access URLs for other devices

### 3. `start-dev-servers.ps1`
One-click setup and start (with instructions).

**How to run:**
```powershell
# Right-click PowerShell and select "Run as Administrator"
cd C:\xampp\htdocs\storeflow\scripts
.\start-dev-servers.ps1
```

### 4. `remove-network-forwarding.ps1`
Clean up when you're done.

**How to run:**
```powershell
# Right-click PowerShell and select "Run as Administrator"
cd C:\xampp\htdocs\storeflow\scripts
.\remove-network-forwarding.ps1
```

## Quick Start Guide

### First Time Setup

1. **Open PowerShell as Administrator**
   - Press `Win + X`
   - Select "Windows PowerShell (Admin)" or "Terminal (Admin)"

2. **Navigate to scripts folder**
   ```powershell
   cd C:\xampp\htdocs\storeflow\scripts
   ```

3. **Run setup script**
   ```powershell
   .\setup-network-forwarding.ps1
   ```

4. **Start your servers in WSL2** (if not already running)
   ```bash
   # In WSL2 terminal
   cd /mnt/c/xampp/htdocs/storeflow
   npm run dev
   # In another WSL2 terminal
   php artisan serve --host=0.0.0.0 --port=8000
   ```

5. **Access from other devices**
   - Use the URLs shown by the setup script
   - Example: `http://192.168.1.100:5173`

### Daily Use

**Every time you restart your computer:**

WSL2 gets a new IP address on restart, so you need to run the setup script again:

```powershell
# As Administrator
cd C:\xampp\htdocs\storeflow\scripts
.\setup-network-forwarding.ps1
```

Then start your dev servers in WSL2 as usual.

### Checking Status

```powershell
# Can run without admin
cd C:\xampp\htdocs\storeflow\scripts
.\check-network-status.ps1
```

## Troubleshooting

### "Access Denied" Error
- You must run PowerShell as Administrator
- Right-click PowerShell → "Run as Administrator"

### "Execution Policy" Error
```powershell
# Run this once as Administrator
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Still Can't Access from Other Devices

1. **Check Windows Firewall**
   - Make sure it's not blocking the connections
   - Run `check-network-status.ps1` to verify rules

2. **Check Network Type**
   - Open Settings → Network & Internet
   - Make sure your network is set to "Private" not "Public"

3. **Verify Servers are Running**
   ```bash
   # In WSL2
   curl http://localhost:5173  # Should return HTML
   curl http://localhost:8000  # Should return Laravel response
   ```

4. **Check if ports are open**
   ```powershell
   # Run check-network-status.ps1
   .\check-network-status.ps1
   ```

### WSL2 IP Changed
This happens every time you restart Windows.

**Solution:** Just run `setup-network-forwarding.ps1` again.

## How It Works

1. **Port Forwarding**: Uses Windows `netsh interface portproxy` to forward traffic from `0.0.0.0:5173` and `0.0.0.0:8000` to your WSL2 IP

2. **Firewall Rules**: Creates Windows Firewall rules to allow inbound traffic on ports 5173 and 8000

3. **Dynamic Detection**: Automatically detects your WSL2 IP address each time you run the script

## Security Note

These scripts allow devices on your local network to access your development servers. Only use on trusted networks (your home/office network).

To remove access when done:
```powershell
.\remove-network-forwarding.ps1
```

## Additional Notes

- Port forwarding persists until Windows restarts
- WSL2 IP address changes on every Windows restart
- Re-run setup after restarting Windows
- Your Windows host IP may also change (DHCP)
- Consider setting a static IP for your development machine

## Files Modified

The scripts don't modify any of your project files. They only configure Windows networking:
- Windows Firewall rules
- Windows port forwarding (netsh portproxy)

## Support

If you encounter issues:
1. Run `check-network-status.ps1` and share the output
2. Check that both servers are running in WSL2
3. Verify your network is set to "Private"
4. Try temporarily disabling antivirus software
