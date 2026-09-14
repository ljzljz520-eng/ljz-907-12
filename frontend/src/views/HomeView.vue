<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Loader2, Film, Search, Upload } from 'lucide-vue-next';
import api from '../lib/api';
import MovieCard from '../components/MovieCard.vue';
import Pagination from '../components/Pagination.vue';
import UploadModal from '../components/UploadModal.vue';

const route = useRoute();
const router = useRouter();

const movies = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const loading = ref(true);
const isUploadOpen = ref(false);
const searchQuery = ref(route.query.q ? String(route.query.q) : '');

const fetchMovies = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get('/movies', {
      params: { page, search: searchQuery.value || undefined },
    });
    movies.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    window.scrollTo({ top: 0 });
  } catch (error) {
    console.error('Failed to fetch movies:', error);
  } finally {
    loading.value = false;
  }
};

// Debounced search: 同步到 URL，返回列表时可还原
let searchTimeout;
watch(searchQuery, (val) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.replace({ query: val ? { q: val } : {} });
    fetchMovies(1);
  }, 300);
});

const handlePageChange = (page) => {
  router.replace({ query: { ...(searchQuery.value ? { q: searchQuery.value } : {}), page } });
  fetchMovies(page);
};

const handleUploadSuccess = () => fetchMovies(1);

onMounted(() => {
  const page = route.query.page ? parseInt(route.query.page, 10) : 1;
  fetchMovies(page);
});
</script>

<template>
  <main class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Search + Import -->
    <div class="mb-10 flex max-w-2xl items-center gap-3">
      <div class="relative w-full">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
          <Search class="h-5 w-5 text-gray-500" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="搜索电影、导演或演员..."
          class="block w-full rounded-2xl border-none bg-white/5 py-3.5 pl-12 pr-4 text-white placeholder-gray-500 ring-1 ring-white/10 transition focus:bg-white/10 focus:outline-none focus:ring-2 focus:ring-purple-500"
        />
      </div>
      <button
        @click="isUploadOpen = true"
        class="flex shrink-0 items-center gap-2 rounded-2xl bg-purple-600 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-purple-500 active:scale-95"
      >
        <Upload class="h-4 w-4" />
        <span class="hidden sm:inline">导入</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading && movies.length === 0" class="flex h-[60vh] items-center justify-center">
      <Loader2 class="h-10 w-10 animate-spin text-purple-500" />
    </div>

    <!-- Empty -->
    <div v-else-if="movies.length === 0" class="flex h-[60vh] flex-col items-center justify-center text-center">
      <div class="mb-4 rounded-full bg-white/5 p-6">
        <Film class="h-12 w-12 text-gray-500" />
      </div>
      <h2 class="text-xl font-semibold text-white">暂无电影数据</h2>
      <p class="mt-2 max-w-sm text-gray-400">没有找到匹配的影片，点击“导入”按钮添加影片。</p>
      <button
        @click="isUploadOpen = true"
        class="mt-6 rounded-lg bg-purple-600 px-6 py-2.5 font-medium text-white transition hover:bg-purple-500"
      >
        导入电影
      </button>
    </div>

    <!-- Grid -->
    <div v-else>
      <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        <MovieCard v-for="movie in movies" :key="movie.id" :movie="movie" />
      </div>
      <Pagination :current-page="currentPage" :last-page="lastPage" @page-change="handlePageChange" />
    </div>

    <UploadModal
      :is-open="isUploadOpen"
      @close="isUploadOpen = false"
      @upload-success="handleUploadSuccess"
    />
  </main>
</template>
