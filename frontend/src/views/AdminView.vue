<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Loader2, LogIn, LogOut, Eye, EyeOff, ArrowLeft, Search, ShieldAlert } from 'lucide-vue-next';
import api from '../lib/api';

const router = useRouter();

const token = ref(localStorage.getItem('admin_token') || '');
const email = ref('admin@cinevault.local');
const password = ref('');
const loginError = ref('');
const loggingIn = ref(false);

const movies = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref('all'); // all | published | unpublished
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const loggedIn = computed(() => !!token.value);

const authHeaders = () => ({ headers: { Authorization: `Bearer ${token.value}` } });

const login = async () => {
  loggingIn.value = true;
  loginError.value = '';
  try {
    const { data } = await api.post('/admin/login', { email: email.value, password: password.value });
    token.value = data.token;
    localStorage.setItem('admin_token', data.token);
    await fetchMovies();
  } catch (e) {
    loginError.value = e.response?.data?.error || '登录失败';
  } finally {
    loggingIn.value = false;
  }
};

const logout = () => {
  token.value = '';
  localStorage.removeItem('admin_token');
};

const fetchMovies = async (page = 1) => {
  if (!token.value) return;
  loading.value = true;
  try {
    const { data } = await api.get('/admin/movies', {
      ...authHeaders(),
      params: {
        page,
        search: search.value || undefined,
        status: statusFilter.value === 'all' ? undefined : statusFilter.value,
      },
    });
    movies.value = data.data;
    currentPage.value = data.current_page;
    lastPage.value = data.last_page;
    total.value = data.total;
  } catch (e) {
    if (e.response?.status === 401) {
      logout();
    }
  } finally {
    loading.value = false;
  }
};

const togglePublish = async (movie) => {
  const next = !movie.is_published;
  // 乐观更新
  const prev = movie.is_published;
  movie.is_published = next;
  try {
    await api.patch(`/admin/movies/${movie.id}/publish`, { is_published: next }, authHeaders());
  } catch (e) {
    movie.is_published = prev;
    alert(e.response?.data?.error || '操作失败');
  }
};

let searchTimer;
const onSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchMovies(1), 300);
};

onMounted(() => {
  if (token.value) fetchMovies();
});
</script>

<template>
  <main class="container mx-auto max-w-5xl px-4 py-8 sm:px-6">
    <!-- 未登录 -->
    <div v-if="!loggedIn" class="mx-auto mt-10 max-w-sm">
      <div class="rounded-2xl border border-white/10 bg-dark-800 p-8">
        <div class="mb-6 flex items-center gap-2">
          <ShieldAlert class="h-6 w-6 text-purple-400" />
          <h1 class="text-xl font-bold text-white">片库后台登录</h1>
        </div>
        <form @submit.prevent="login" class="space-y-4">
          <div>
            <label class="mb-1.5 block text-xs text-gray-400">邮箱</label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full rounded-lg border-none bg-white/5 px-3 py-2.5 text-white ring-1 ring-white/10 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-xs text-gray-400">密码</label>
            <input
              v-model="password"
              type="password"
              required
              class="w-full rounded-lg border-none bg-white/5 px-3 py-2.5 text-white ring-1 ring-white/10 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
          <p v-if="loginError" class="text-sm text-red-400">{{ loginError }}</p>
          <button
            type="submit"
            :disabled="loggingIn"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-purple-600 py-2.5 text-sm font-medium text-white transition hover:bg-purple-500 disabled:opacity-50"
          >
            <Loader2 v-if="loggingIn" class="h-4 w-4 animate-spin" />
            <LogIn v-else class="h-4 w-4" />
            登录
          </button>
        </form>
        <p class="mt-4 text-center text-xs text-gray-600">
          默认账号 admin@cinevault.local / admin123456
        </p>
      </div>
      <button
        @click="router.push('/')"
        class="mt-4 flex w-full items-center justify-center gap-1.5 text-sm text-gray-500 transition hover:text-white"
      >
        <ArrowLeft class="h-4 w-4" /> 返回片库
      </button>
    </div>

    <!-- 已登录：影片管理 -->
    <div v-else>
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">影片上下架管理 <span class="text-sm font-normal text-gray-500">共 {{ total }} 部</span></h1>
        <button
          @click="logout"
          class="flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-2 text-xs text-gray-300 transition hover:bg-white/10"
        >
          <LogOut class="h-3.5 w-3.5" /> 退出
        </button>
      </div>

      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative min-w-[220px] flex-1">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
          <input
            v-model="search"
            @input="onSearch"
            type="text"
            placeholder="搜索片名或导演..."
            class="w-full rounded-lg border-none bg-white/5 py-2 pl-9 pr-3 text-sm text-white ring-1 ring-white/10 focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div class="flex rounded-lg bg-white/5 p-1 text-xs">
          <button
            v-for="opt in [{ v: 'all', t: '全部' }, { v: 'published', t: '已上架' }, { v: 'unpublished', t: '已下架' }]"
            :key="opt.v"
            @click="statusFilter = opt.v; fetchMovies(1)"
            :class="['rounded-md px-3 py-1.5 transition', statusFilter === opt.v ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white']"
          >
            {{ opt.t }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="flex h-64 items-center justify-center">
        <Loader2 class="h-8 w-8 animate-spin text-purple-500" />
      </div>

      <div v-else class="overflow-hidden rounded-xl border border-white/10">
        <table class="w-full text-sm">
          <thead class="bg-white/5 text-left text-xs text-gray-400">
            <tr>
              <th class="px-4 py-3">影片</th>
              <th class="hidden px-4 py-3 sm:table-cell">年份</th>
              <th class="px-4 py-3">状态</th>
              <th class="px-4 py-3 text-right">操作</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="m in movies" :key="m.id" class="hover:bg-white/[0.02]">
              <td class="max-w-[260px] truncate px-4 py-3 text-gray-200">{{ m.translated_title || m.title }}</td>
              <td class="hidden px-4 py-3 text-gray-400 sm:table-cell">{{ m.year }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs',
                    m.is_published ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400',
                  ]"
                >
                  <span :class="['h-1.5 w-1.5 rounded-full', m.is_published ? 'bg-green-400' : 'bg-red-400']"></span>
                  {{ m.is_published ? '已上架' : '已下架' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="togglePublish(m)"
                  :class="[
                    'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition',
                    m.is_published
                      ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20'
                      : 'bg-green-500/10 text-green-400 hover:bg-green-500/20',
                  ]"
                >
                  <component :is="m.is_published ? EyeOff : Eye" class="h-3.5 w-3.5" />
                  {{ m.is_published ? '下架' : '上架' }}
                </button>
              </td>
            </tr>
            <tr v-if="movies.length === 0">
              <td colspan="4" class="px-4 py-12 text-center text-gray-500">暂无影片</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 分页 -->
      <div v-if="lastPage > 1" class="mt-4 flex items-center justify-center gap-2">
        <button
          @click="fetchMovies(currentPage - 1)"
          :disabled="currentPage === 1"
          class="rounded-lg px-3 py-1.5 text-sm text-gray-400 hover:bg-white/5 disabled:opacity-30"
        >
          上一页
        </button>
        <span class="text-sm text-gray-400">{{ currentPage }} / {{ lastPage }}</span>
        <button
          @click="fetchMovies(currentPage + 1)"
          :disabled="currentPage === lastPage"
          class="rounded-lg px-3 py-1.5 text-sm text-gray-400 hover:bg-white/5 disabled:opacity-30"
        >
          下一页
        </button>
      </div>
    </div>
  </main>
</template>
