<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Loader2, ArrowLeft, Share2, Check, Star, Calendar, Users,
  Clapperboard, Building2, Tag, Globe, Languages, Clock, Edit3,
  ExternalLink, Film,
} from 'lucide-vue-next';
import api from '../lib/api';
import PosterImage from '../components/PosterImage.vue';
import MovieCard from '../components/MovieCard.vue';

const route = useRoute();
const router = useRouter();

const movie = ref(null);
const related = ref([]);
const loading = ref(true);
const notFound = ref(false);
const copied = ref(false);

const displayTitle = computed(() => movie.value?.translated_title || movie.value?.title || '');

// 主创名单拆分为数组展示
const splitNames = (str) =>
  (str || '')
    .split(/[\/\n,，、;；]+/)
    .map((s) => s.trim())
    .filter(Boolean);

const directors = computed(() => splitNames(movie.value?.director));
const writers = computed(() => splitNames(movie.value?.writer));
const actors = computed(() => splitNames(movie.value?.actors));

// 适合人群标签
const audienceTags = computed(() =>
  (movie.value?.target_audience || '')
    .split(/[\/,，、;；]+/)
    .map((s) => s.trim())
    .filter(Boolean)
);

const genres = computed(() =>
  (movie.value?.genre || '')
    .split(/[\/,，、;；]+/)
    .map((s) => s.trim())
    .filter(Boolean)
);

const fetchDetail = async (id) => {
  loading.value = true;
  notFound.value = false;
  movie.value = null;
  related.value = [];
  try {
    const { data } = await api.get(`/movies/${id}`);
    movie.value = data.movie;
    related.value = data.related || [];
    document.title = `${displayTitle.value} (${movie.value.year}) - 影格`;
  } catch (err) {
    if (err.response?.status === 404) {
      // 影片不存在或已被后台下架：公开端一律表现为 404，不暴露存在性
      notFound.value = true;
    }
  } finally {
    loading.value = false;
  }
};

// 返回列表：从列表进入则浏览器后退（保留搜索/分页），否则回首页
const backToList = () => {
  if (route.query.from === 'list' && window.history.state?.back) {
    router.back();
  } else {
    router.push({ name: 'home' });
  }
};

// 分享链接：去掉 from 等临时参数
const shareUrl = computed(() => {
  if (!movie.value) return '';
  const url = new URL(window.location.href);
  url.search = '';
  return url.toString();
});

const copyShareLink = async () => {
  const text = shareUrl.value;
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
    } else {
      // 兼容非安全上下文（http / 旧浏览器）
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    }
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
  } catch (e) {
    console.error('复制失败', e);
  }
};

onMounted(() => fetchDetail(route.params.id));
watch(() => route.params.id, (id) => id && fetchDetail(id));

const infoRows = computed(() => {
  if (!movie.value) return [];
  const m = movie.value;
  return [
    { icon: Calendar, label: '放映年份', value: m.year ? `${m.year} 年` : null },
    { icon: Tag, label: '类型', value: m.genre },
    { icon: Globe, label: '产地', value: m.country },
    { icon: Languages, label: '语言', value: m.language },
    { icon: Clock, label: '片长', value: m.runtime },
    { icon: Calendar, label: '上映日期', value: m.release_date },
  ].filter((r) => r.value);
});
</script>

<template>
  <main class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Loading -->
    <div v-if="loading" class="flex h-[60vh] items-center justify-center">
      <Loader2 class="h-10 w-10 animate-spin text-purple-500" />
    </div>

    <!-- 404：不存在或已下架 -->
    <div v-else-if="notFound" class="flex h-[60vh] flex-col items-center justify-center text-center">
      <div class="mb-4 rounded-full bg-white/5 p-6">
        <Film class="h-12 w-12 text-gray-500" />
      </div>
      <h1 class="text-2xl font-bold text-white">影片不存在或已下架</h1>
      <p class="mt-3 max-w-sm text-gray-400">
        您访问的影片可能已被管理员下架，或链接有误。
      </p>
      <RouterLink
        to="/"
        class="mt-6 inline-flex items-center gap-2 rounded-lg bg-purple-600 px-6 py-2.5 font-medium text-white transition hover:bg-purple-500"
      >
        <ArrowLeft class="h-4 w-4" />
        返回片库
      </RouterLink>
    </div>

    <template v-else-if="movie">
      <!-- 顶部信息 -->
      <div class="flex flex-col gap-8 lg:flex-row">
        <!-- 海报 -->
        <div class="w-full shrink-0 lg:w-[320px]">
          <div class="overflow-hidden rounded-2xl shadow-2xl ring-1 ring-white/10">
            <PosterImage :poster-url="movie.poster_url" :title="displayTitle" />
          </div>

          <!-- 评分卡片 -->
          <div class="mt-4 flex gap-3">
            <div v-if="movie.rating > 0" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/5 py-3">
              <Star class="h-5 w-5 fill-yellow-400 text-yellow-400" />
              <span class="text-lg font-bold text-yellow-400">{{ movie.rating }}</span>
              <span class="text-xs text-gray-500">豆瓣</span>
            </div>
            <div v-if="movie.imdb_rating" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/5 py-3">
              <span class="text-sm font-bold text-gray-300">IMDb</span>
              <span class="text-lg font-bold text-gray-200">{{ movie.imdb_rating }}</span>
            </div>
          </div>
        </div>

        <!-- 右侧内容 -->
        <div class="min-w-0 flex-1">
          <h1 class="text-3xl font-bold text-white md:text-4xl">{{ displayTitle }}</h1>
          <p v-if="movie.translated_title && movie.title !== movie.translated_title" class="mt-2 text-lg text-gray-400">
            {{ movie.title }}
          </p>

          <!-- 类型标签 -->
          <div v-if="genres.length" class="mt-4 flex flex-wrap gap-2">
            <span
              v-for="g in genres"
              :key="g"
              class="rounded-full border border-purple-500/30 bg-purple-500/10 px-3 py-1 text-xs text-purple-300"
            >
              {{ g }}
            </span>
          </div>

          <!-- 基础信息 -->
          <dl class="mt-6 grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2">
            <div v-for="row in infoRows" :key="row.label" class="flex items-center gap-3 text-sm">
              <component :is="row.icon" class="h-4 w-4 shrink-0 text-gray-500" />
              <dt class="shrink-0 text-gray-500">{{ row.label }}</dt>
              <dd class="min-w-0 text-gray-200">{{ row.value }}</dd>
            </div>
          </dl>

          <!-- 适合人群 -->
          <div class="mt-6 flex items-start gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-4">
            <Users class="mt-0.5 h-5 w-5 shrink-0 text-purple-400" />
            <div>
              <h3 class="text-sm font-semibold text-white">适合人群</h3>
              <div v-if="audienceTags.length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="a in audienceTags"
                  :key="a"
                  class="rounded-full bg-white/10 px-3 py-1 text-xs text-gray-200"
                >
                  {{ a }}
                </span>
              </div>
              <p v-else class="mt-1 text-sm text-gray-500">暂无分级信息，请按通用观影建议选择。</p>
            </div>
          </div>

          <!-- 来源单位 -->
          <div class="mt-3 flex items-start gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-4">
            <Building2 class="mt-0.5 h-5 w-5 shrink-0 text-purple-400" />
            <div>
              <h3 class="text-sm font-semibold text-white">来源单位</h3>
              <p class="mt-1 text-sm text-gray-300">{{ movie.source_organization || '影格片库' }}</p>
            </div>
          </div>

          <!-- 外链 -->
          <div v-if="movie.imdb_link || movie.douban_link" class="mt-4 flex flex-wrap gap-3">
            <a
              v-if="movie.imdb_link"
              :href="movie.imdb_link"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs text-gray-300 transition hover:bg-white/10"
            >
              IMDb 页面 <ExternalLink class="h-3 w-3" />
            </a>
            <a
              v-if="movie.douban_link"
              :href="movie.douban_link"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs text-gray-300 transition hover:bg-white/10"
            >
              豆瓣页面 <ExternalLink class="h-3 w-3" />
            </a>
          </div>
        </div>
      </div>

      <!-- 简介 -->
      <section class="mt-12">
        <div class="mb-4 flex items-center gap-2">
          <div class="h-5 w-1 rounded-full bg-purple-500"></div>
          <h2 class="text-xl font-bold text-white">剧情简介</h2>
        </div>
        <p class="whitespace-pre-wrap text-base leading-loose text-gray-400">
          {{ movie.description || '暂无相关简介资料。' }}
        </p>
      </section>

      <!-- 主创 -->
      <section class="mt-12">
        <div class="mb-4 flex items-center gap-2">
          <div class="h-5 w-1 rounded-full bg-purple-500"></div>
          <h2 class="text-xl font-bold text-white">主创阵容</h2>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <div v-if="directors.length">
            <h3 class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-purple-300">
              <Clapperboard class="h-4 w-4" /> 导演
            </h3>
            <ul class="space-y-1 text-sm text-gray-300">
              <li v-for="(d, i) in directors.slice(0, 8)" :key="'d' + i">{{ d }}</li>
            </ul>
          </div>
          <div v-if="writers.length">
            <h3 class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-purple-300">
              <Edit3 class="h-4 w-4" /> 编剧
            </h3>
            <ul class="space-y-1 text-sm text-gray-300">
              <li v-for="(w, i) in writers.slice(0, 8)" :key="'w' + i">{{ w }}</li>
            </ul>
          </div>
          <div v-if="actors.length">
            <h3 class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-purple-300">
              <Users class="h-4 w-4" /> 主演
            </h3>
            <ul class="space-y-1 text-sm text-gray-300">
              <li v-for="(a, i) in actors.slice(0, 12)" :key="'a' + i">{{ a }}</li>
            </ul>
          </div>
          <p
            v-if="!directors.length && !writers.length && !actors.length"
            class="text-sm text-gray-500 md:col-span-3"
          >
            暂无主创信息。
          </p>
        </div>
      </section>

      <!-- 获奖情况 -->
      <section v-if="movie.awards" class="mt-12">
        <div class="mb-4 flex items-center gap-2">
          <div class="h-5 w-1 rounded-full bg-purple-500"></div>
          <h2 class="text-xl font-bold text-white">获奖情况</h2>
        </div>
        <p class="whitespace-pre-wrap text-sm italic leading-loose text-gray-400">{{ movie.awards }}</p>
      </section>

      <!-- 相关推荐 -->
      <section v-if="related.length" class="mt-14">
        <div class="mb-6 flex items-center gap-2">
          <div class="h-5 w-1 rounded-full bg-purple-500"></div>
          <h2 class="text-xl font-bold text-white">相关推荐</h2>
        </div>
        <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          <MovieCard v-for="item in related" :key="item.id" :movie="item" />
        </div>
      </section>

      <!-- 底部操作栏：返回列表 + 复制分享链接 -->
      <footer class="sticky bottom-4 z-30 mt-14">
        <div class="mx-auto flex max-w-md items-center gap-3 rounded-2xl border border-white/10 bg-dark-800/90 p-3 shadow-2xl backdrop-blur-md">
          <button
            @click="backToList"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/10 py-3 text-sm font-medium text-white transition hover:bg-white/20 active:scale-95"
          >
            <ArrowLeft class="h-4 w-4" />
            返回列表
          </button>
          <button
            @click="copyShareLink"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-purple-600 py-3 text-sm font-medium text-white transition hover:bg-purple-500 active:scale-95"
          >
            <component :is="copied ? Check : Share2" class="h-4 w-4" />
            {{ copied ? '链接已复制' : '复制分享链接' }}
          </button>
        </div>
      </footer>
    </template>
  </main>
</template>
