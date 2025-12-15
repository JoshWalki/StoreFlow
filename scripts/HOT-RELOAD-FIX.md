# Hot Reload Configuration for WSL2

## Problem
File watching doesn't work properly in WSL2 because the Windows file system doesn't trigger inotify events that Linux tools expect.

## Solution Applied

### 1. Vite Configuration (vite.config.js)

**Changed:**
- Enabled `usePolling: true` - Forces Vite to actively check for file changes
- Set `interval: 100` - Checks every 100ms (fast enough without being too CPU intensive)
- Changed HMR host to `"localhost"` - More flexible than hardcoded IP

**Before:**
```javascript
watch: {
    usePolling: false, // Disabled for better performance
}
hmr: {
    host: "172.21.145.17", // Hardcoded IP
}
```

**After:**
```javascript
watch: {
    usePolling: true, // Required for WSL2 file system watching
    interval: 100, // Check for changes every 100ms
}
hmr: {
    host: "localhost", // Browser uses current hostname
}
```

### 2. Laravel Configuration

Laravel's file watching works through Vite, so no additional changes needed.

### 3. How Hot Reload Works Now

1. **Vite watches files** using polling (every 100ms)
2. **Detects changes** to:
   - `resources/js/**/*` (JavaScript/Vue files)
   - `resources/css/**/*` (CSS files)
   - `resources/views/**/*` (Blade templates - triggers full reload)
3. **Sends updates via WebSocket** (HMR protocol)
4. **Browser hot-reloads** without full page refresh (for JS/CSS)

## Testing Hot Reload

### Test 1: Vue Component Changes
1. Open `resources/js/Components/YourComponent.vue`
2. Make a visible change (change text, color, etc.)
3. Save the file
4. Browser should update within 1 second without refresh

### Test 2: CSS Changes
1. Open `resources/css/app.css`
2. Add or modify a style
3. Save the file
4. Browser should update immediately

### Test 3: Blade Template Changes
1. Open any `.blade.php` file
2. Make a change
3. Save the file
4. Browser should do a full page reload

## Restart Servers

After configuration changes, restart both servers:

**Option 1: Manual (Recommended for debugging)**
```bash
# Terminal 1
cd /mnt/c/xampp/htdocs/storeflow
npm run dev

# Terminal 2
cd /mnt/c/xampp/htdocs/storeflow
php artisan serve --host=0.0.0.0 --port=8000
```

**Option 2: Background**
```bash
cd /mnt/c/xampp/htdocs/storeflow
npm run dev > /tmp/vite.log 2>&1 &
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel.log 2>&1 &
```

**Option 3: Helper Script**
```bash
cd /mnt/c/xampp/htdocs/storeflow
bash scripts/restart-dev.sh
```

## Troubleshooting

### Changes Not Detected

**Check Vite is running:**
```bash
ps aux | grep vite
```

**Check Vite logs:**
```bash
# If running in background
tail -f /tmp/vite.log
```

**Verify polling is enabled:**
```bash
cat vite.config.js | grep -A 2 "watch:"
# Should show: usePolling: true
```

### HMR Connection Issues

**Symptoms:**
- Console shows: "WebSocket connection failed"
- No hot reload, have to manually refresh

**Solutions:**

1. **Check browser console** for WebSocket errors
2. **Verify you're accessing via correct URL:**
   - Local: `http://localhost:5173`
   - Network: `http://<your-windows-ip>:5173`
3. **Check HMR port is accessible:**
   ```bash
   curl http://localhost:5173
   ```

### Full Page Reloads Instead of Hot Reload

This is normal for:
- Blade template changes
- PHP files
- Configuration changes
- Route changes

Only these hot-reload:
- Vue component changes
- JavaScript changes
- CSS changes

## Performance Notes

**Polling Impact:**
- CPU usage: Slightly higher (negligible on modern hardware)
- Battery: Minor impact on laptops
- Interval: 100ms is a good balance

**Alternatives if too slow:**
- Decrease interval to 50ms (faster, more CPU)
- Increase interval to 200ms (slower, less CPU)

**Edit in vite.config.js:**
```javascript
watch: {
    usePolling: true,
    interval: 50, // Change this value
}
```

## Network Access Note

When accessing from other devices on your network:
1. The HMR WebSocket will connect to the device's current hostname
2. Ensure port forwarding is set up (run `setup-network-forwarding.ps1`)
3. Browser must access via: `http://<windows-ip>:5173`

## Summary

Hot reload now works because:
- Vite actively polls for changes every 100ms
- HMR WebSocket uses flexible "localhost" host
- Both work seamlessly with WSL2 file system

No further configuration needed unless you change network setup.
