<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $user = User::make($data);
        $user->role = $data['role']; // validated to admin|user above; role is deliberately not mass-assignable
        $user->save();

        AuditLogger::log($request->user(), 'admin_user_created', $request, [
            'target_user_id' => $user->id, 'role' => $user->role,
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $newRole = $data['role']; // validated to admin|user above; role is deliberately not mass-assignable
        $oldRole = $user->role;
        unset($data['role']);

        $passwordChanged = ! empty($data['password']);

        if (! $passwordChanged) {
            unset($data['password']);
        }

        $user->fill($data);
        $user->role = $newRole;
        $user->save();

        // Changing someone's password (or their role) from the admin panel is
        // exactly as sensitive as a password reset: whoever had a session
        // before this — possibly the reason an admin is intervening — must
        // not keep it. Learning progress is untouched (only tokens are removed).
        if ($passwordChanged || $newRole !== $oldRole) {
            $user->tokens()->delete();
        }

        if ($passwordChanged) {
            AuditLogger::log($request->user(), 'admin_password_changed', $request, ['target_user_id' => $user->id]);
        }

        if ($newRole !== $oldRole) {
            AuditLogger::log($request->user(), 'admin_role_changed', $request, [
                'target_user_id' => $user->id, 'from' => $oldRole, 'to' => $newRole,
            ]);
        }

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(422, "You can't delete your own account.");
        }

        AuditLogger::log($request->user(), 'admin_user_deleted', $request, [
            'target_user_id' => $user->id, 'target_email' => $user->email,
        ]);

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
