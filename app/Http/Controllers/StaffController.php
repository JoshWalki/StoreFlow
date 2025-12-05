<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $store = session('selected_store');

        // Only owners can manage staff
        if (!$user->isOwner()) {
            abort(403, 'Only owners can manage staff.');
        }

        // Get all users in the same merchant
        $staff = User::where('merchant_id', $user->merchant_id)
            ->with(['stores' => function ($query) {
                $query->select('stores.id', 'stores.name')
                    ->withPivot('role');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'name' => $staffMember->name,
                    'email' => $staffMember->email,
                    'username' => $staffMember->username,
                    'role' => $staffMember->role,
                    'stores' => $staffMember->stores->map(function ($store) {
                        return [
                            'id' => $store->id,
                            'name' => $store->name,
                            'role' => $store->pivot->role,
                        ];
                    }),
                    'created_at' => $staffMember->created_at,
                ];
            });

        // Get all stores for the merchant
        $stores = Store::where('merchant_id', $user->merchant_id)
            ->select('id', 'name')
            ->get();

        return Inertia::render('Staff/Index', [
            'user' => $user,
            'store' => $store,
            'staff' => $staff,
            'stores' => $stores,
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only owners can create staff
        if (!$user->isOwner()) {
            abort(403, 'Only owners can create staff.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['owner', 'manager', 'staff'])],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
            'store_roles' => ['nullable', 'array'],
            'store_roles.*' => [Rule::in(['manager', 'staff'])],
        ]);

        // Create the user
        $newUser = User::create([
            'merchant_id' => $user->merchant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Attach stores with roles if provided
        if (!empty($validated['store_ids'])) {
            $storeRoles = $validated['store_roles'] ?? [];
            $syncData = [];

            foreach ($validated['store_ids'] as $index => $storeId) {
                // Verify store belongs to merchant
                $store = Store::where('id', $storeId)
                    ->where('merchant_id', $user->merchant_id)
                    ->first();

                if ($store) {
                    $syncData[$storeId] = [
                        'role' => $storeRoles[$index] ?? 'staff'
                    ];
                }
            }

            $newUser->stores()->sync($syncData);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully!');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        $user = Auth::user();

        // Only owners can update staff
        if (!$user->isOwner()) {
            abort(403, 'Only owners can update staff.');
        }

        // Ensure staff belongs to same merchant
        if ($staff->merchant_id !== $user->merchant_id) {
            abort(403, 'Cannot modify staff from another merchant.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['owner', 'manager', 'staff'])],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
            'store_roles' => ['nullable', 'array'],
            'store_roles.*' => [Rule::in(['manager', 'staff'])],
        ]);

        // Update user details
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role' => $validated['role'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        // Update store assignments
        if (isset($validated['store_ids'])) {
            $storeRoles = $validated['store_roles'] ?? [];
            $syncData = [];

            foreach ($validated['store_ids'] as $index => $storeId) {
                // Verify store belongs to merchant
                $store = Store::where('id', $storeId)
                    ->where('merchant_id', $user->merchant_id)
                    ->first();

                if ($store) {
                    $syncData[$storeId] = [
                        'role' => $storeRoles[$index] ?? 'staff'
                    ];
                }
            }

            $staff->stores()->sync($syncData);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully!');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        $user = Auth::user();

        // Only owners can delete staff
        if (!$user->isOwner()) {
            abort(403, 'Only owners can delete staff.');
        }

        // Ensure staff belongs to same merchant
        if ($staff->merchant_id !== $user->merchant_id) {
            abort(403, 'Cannot delete staff from another merchant.');
        }

        // Cannot delete yourself
        if ($staff->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully!');
    }
}
