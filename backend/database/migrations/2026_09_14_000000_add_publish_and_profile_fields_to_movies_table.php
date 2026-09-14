<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            // 适合人群（如：青少年 / 成人 / 家庭观影）
            $table->string('target_audience', 255)->nullable()->after('genre');
            // 来源单位（提供该片源/馆藏的机构）
            $table->string('source_organization', 255)->nullable()->after('target_audience');
            // 上架状态：true=公开可访问，false=后台下架
            $table->boolean('is_published')->default(true)->after('source_organization');

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['is_published']);
            $table->dropColumn(['target_audience', 'source_organization', 'is_published']);
        });
    }
};
