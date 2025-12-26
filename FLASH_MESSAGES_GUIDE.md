# Flash Messages & Toast Notifications Guide

## Current Setup

The application has a complete toast notification system using vue-toastification:

### 1. Backend (Laravel/Inertia)
- Flash messages are shared via `HandleInertiaRequests` middleware
- Supports: `success`, `error`, `warning`, `info`

### 2. Frontend (Vue/Inertia)
- `flashMessages` plugin listens for Inertia page loads
- Automatically displays toasts for flash messages
- `useToast()` composable available for manual toasts

## Proper Usage Pattern

### Controller Pattern (REQUIRED)
```php
//  CORRECT - Always include flash message
return redirect()->back()->with('success', 'Product created successfully.');
return redirect()->back()->with('error', 'Failed to delete category.');
return redirect()->back()->with('warning', 'Some items were skipped.');
return redirect()->back()->with('info', 'No changes were made.');

// ❌ WRONG - Missing flash message (user gets no feedback)
return redirect()->back();
```

### Error Handling Pattern
```php
// For validation errors - Use withErrors()
return redirect()->back()->withErrors([
    'field' => 'Error message here'
]);

// For general errors - Use flash
return redirect()->back()->with('error', 'Operation failed.');
```

### Vue Component Pattern (Optional - for client-side toasts)
```javascript
import { useToast } from '@/Composables/useToast';

const toast = useToast();

// Show success
toast.success('Settings saved!');

// Show error
toast.error('Failed to save settings');

// Show validation errors
toast.validationErrors(errors);
```

## Controllers Missing Flash Messages

Run this command to find controllers needing updates:
```bash
grep -r "return redirect\|return back" app/Http/Controllers --include="*.php" | grep -v "with('success\|with('error\|with('warning\|with('info"
```

## Common Mistakes

### 1. Missing Flash Message on Success
```php
// ❌ WRONG
public function store(Request $request) {
    Product::create($validated);
    return redirect()->route('products.index'); // No feedback!
}

//  CORRECT
public function store(Request $request) {
    Product::create($validated);
    return redirect()->route('products.index')
        ->with('success', 'Product created successfully.');
}
```

### 2. Using withErrors() for Success
```php
// ❌ WRONG
return redirect()->back()->withErrors(['message' => 'Success!']);

//  CORRECT
return redirect()->back()->with('success', 'Success!');
```

### 3. Not Using Flash Messages in AJAX/API Responses
```php
// ❌ WRONG - For Inertia requests
return response()->json(['message' => 'Success']);

//  CORRECT - For Inertia requests
return redirect()->back()->with('success', 'Success');

//  ALSO CORRECT - For true AJAX requests
return response()->json(['message' => 'Success'], 200);
// Then handle in frontend with toast.success(response.data.message)
```

## Testing Checklist

- [ ] Create operation shows success toast
- [ ] Update operation shows success toast
- [ ] Delete operation shows success toast
- [ ] Validation errors show error toasts
- [ ] Server errors show error toasts
- [ ] No operation completes silently without feedback

## Flash Message Standards

### Success Messages
- "Product created successfully."
- "Settings updated successfully."
- "Item deleted successfully."

### Error Messages
- "Failed to create product."
- "Unable to update settings."
- "Cannot delete item: [reason]."

### Warning Messages
- "Some items could not be processed."
- "Operation completed with warnings."

### Info Messages
- "No changes were made."
- "Already up to date."
