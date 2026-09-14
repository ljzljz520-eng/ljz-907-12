<script setup>
import Navbar from './components/Navbar.vue';
import { RouterView } from 'vue-router';
</script>

<template>
  <div class="min-h-screen bg-dark-900">
    <Navbar />
    <RouterView />
  </div>
</template>
