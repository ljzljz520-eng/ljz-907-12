<script setup>
import { RouterLink } from 'vue-router';
import { Film, ArrowLeft } from 'lucide-vue-next';
</script>

<template>
  <main class="container mx-auto flex h-[70vh] flex-col items-center justify-center px-4 text-center">
    <div class="mb-4 rounded-full bg-white/5 p-6">
      <Film class="h-12 w-12 text-gray-500" />
    </div>
    <h1 class="text-2xl font-bold text-white">页面不存在</h1>
    <p class="mt-3 text-gray-400">您访问的页面已被移除或链接有误。</p>
    <RouterLink
      to="/"
      class="mt-6 inline-flex items-center gap-2 rounded-lg bg-purple-600 px-6 py-2.5 font-medium text-white transition hover:bg-purple-500"
    >
      <ArrowLeft class="h-4 w-4" />
      返回片库
    </RouterLink>
  </main>
</template>
