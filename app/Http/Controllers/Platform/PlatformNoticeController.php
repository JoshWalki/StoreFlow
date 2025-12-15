<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SystemNotice;
use Illuminate\Http\Request;

class PlatformNoticeController extends Controller
{
    /**
     * Store or update the system notice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'bg_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['boolean'],
        ]);

        // Deactivate all existing notices
        SystemNotice::query()->update(['is_active' => false]);

        // Create new notice
        $notice = SystemNotice::create([
            'message' => $validated['message'],
            'bg_color' => $validated['bg_color'],
            'text_color' => $validated['text_color'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('platform.dashboard')
            ->with('success', 'System notice updated successfully.');
    }

    /**
     * Delete the active notice.
     */
    public function destroy()
    {
        SystemNotice::where('is_active', true)->update(['is_active' => false]);

        return redirect()->route('platform.dashboard')
            ->with('success', 'System notice removed successfully.');
    }
}
