<?php

namespace App\Services\AI\Tools;

use App\Models\Superuser\Role;
use App\Models\Superuser\User;
use App\Services\AI\Contracts\AiToolInterface;
use App\Services\AI\DTO\AiToolResult;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserTool implements AiToolInterface
{
    public function name(): string
    {
        return 'create_user';
    }

    public function description(): string
    {
        return 'Membuat user baru dengan nama, email, username, password, dan role. Role bisa diisi dengan nama role (misal: "Staff", "Admin") atau ID role.';
    }

    public function schema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'name'     => ['type' => 'string', 'description' => 'Nama lengkap user'],
                'email'    => ['type' => 'string', 'format' => 'email', 'description' => 'Email user'],
                'username' => ['type' => 'string', 'description' => 'Username untuk login (opsional)'],
                'password' => ['type' => 'string', 'description' => 'Password untuk login (minimal 8 karakter)'],
                'role'     => ['type' => 'string', 'description' => 'Nama role (misal: Staff, Admin) atau ID role'],
                'is_active' => ['type' => 'boolean', 'description' => 'Status aktif user (default: true)'],
            ],
            'required' => ['name', 'email', 'password'],
        ];
    }

    public function toDraft(array $arguments, User $user): AiToolResult
    {
        $this->authorize($user);

        $validated = Validator::make($arguments, [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'username'  => 'nullable|string|max:255|unique:users,username',
            'password'  => 'required|string|min:8',
            'role'      => 'nullable|string',
            'is_active' => 'boolean',
        ])->validate();

        // Resolve role: bisa berupa nama role atau ID
        $roleName = $validated['role'] ?? null;
        $roleId = null;

        if ($roleName) {
            // Coba cari by ID dulu
            $role = Role::find($roleName);

            // Kalau bukan ID, cari by name atau slug
            if (!$role) {
                $role = Role::where('name', $roleName)
                    ->orWhere('slug', strtolower(str_replace(' ', '_', $roleName)))
                    ->first();
            }

            if ($role) {
                $roleId = $role->id;
            }
        }

        // Default role jika tidak ada yang cocok
        if (!$roleId) {
            $defaultRole = Role::where('slug', 'staff')->orWhere('name', 'Staff')->first();
            $roleId = $defaultRole?->id;
        }

        $payload = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'username'  => $validated['username'] ?? null,
            'password'  => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'role_ids'  => $roleId ? [$roleId] : [],
        ];

        $roleDisplay = $roleId ? Role::find($roleId)?->name : 'Staff (default)';

        return AiToolResult::draft(
            modelClass: User::class,
            payload: $payload,
            summary: "Buat user baru **{$validated['name']}** ({$validated['email']}) dengan role **{$roleDisplay}**"
        );
    }

    public function confirm(array $payload, User $user): User
    {
        $this->authorize($user);

        $roleIds = $payload['role_ids'] ?? [];
        unset($payload['role_ids']);

        $newUser = User::create($payload);

        if (!empty($roleIds)) {
            $newUser->roles()->sync($roleIds);
        }

        return $newUser->fresh(['roles']);
    }

    public function isAllowedFor(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    private function authorize(User $user): void
    {
        if ($this->isAllowedFor($user)) {
            return;
        }

        throw new AuthorizationException('Kamu tidak memiliki akses untuk membuat user.');
    }
}
