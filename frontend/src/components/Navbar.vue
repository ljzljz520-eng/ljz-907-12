<script setup>
import { RouterLink } from 'vue-router';
import { Film, Settings } from 'lucide-vue-next';
</script>

<template>
  <nav class="fixed top-0 z-40 w-full border-b border-white/5 bg-dark-900/80 backdrop-blur-md">
    <div class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2">
        <Film class="h-6 w-6 text-purple-500" />
        <span class="text-xl font-bold tracking-tight text-white">
          影<span class="text-purple-500">格</span>
        </span>
      </RouterLink>

      <!-- Admin entry -->
      <RouterLink
        to="/admin"
        class="flex items-center gap-1.5 rounded-full px-3 py-2 text-xs text-gray-500 transition hover:bg-white/10 hover:text-white"
        title="后台管理"
      >
        <Settings class="h-4 w-4" />
        <span class="hidden sm:inline">后台管理</span>
      </RouterLink>
    </div>
  </nav>
  <!-- Spacer to prevent content from hiding under fixed nav -->
  <div class="h-16"></div>
</template>
