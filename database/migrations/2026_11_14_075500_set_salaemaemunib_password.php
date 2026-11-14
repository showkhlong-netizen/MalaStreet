<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $email = 'salaemaemunib@gmail.com';
        $newPassword = '1122';

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
        } elseif (!app()->environment('testing')) {
            User::create([
                'name' => 'มุนีบ สะและแม (Admin)',
                'email' => $email,
                'password' => Hash::make($newPassword),
            ]);
        }

        // Also sync employee_data_{id} profile password if this email appears in access_users_list
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        foreach ($savedUsers as $u) {
            if (strtolower(trim((string) ($u['email'] ?? ''))) === $email && !empty($u['id'])) {
                $empKey = 'employee_data_' . $u['id'];
                $empData = json_decode(Setting::get($empKey, '{}'), true) ?: [];
                $empData['profile'] = array_merge($empData['profile'] ?? [], [
                    'email' => $email,
                    'password' => $newPassword,
                    'has_custom_password' => true,
                    'password_updated_at' => now()->toDateTimeString(),
                ]);
                Setting::set($empKey, json_encode($empData), 'employees');
            }
        }
    }

    public function down(): void
    {
        // Intentional no-op
    }
};
