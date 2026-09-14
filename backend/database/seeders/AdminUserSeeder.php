<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 默认后台账号：admin@cinevault.local / admin123456
        // 生产环境请通过环境变量或修改密码覆盖
        User::updateOrCreate(
            ['email' => 'admin@cinevault.local'],
            [
                'name' => '片库管理员',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
            ]
        );
    }
}
