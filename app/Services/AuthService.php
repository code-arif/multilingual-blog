<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_VISITOR,
            'is_active' => true,
        ]);
    }

    public function updateProfile(User $user, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'bio' => $data['bio'] ?? $user->bio,
        ];

        if (isset($data['avatar'])) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete('uploads/avatars/' . $user->avatar);
            }
            $updateData['avatar'] = $data['avatar'];
        }

        $user->update($updateData);
        return $user->fresh();
    }
}
