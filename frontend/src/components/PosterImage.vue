<script setup>
import { computed, ref } from 'vue';
import { ImageOff } from 'lucide-vue-next';
import { proxiedImage } from '../lib/api';

const props = defineProps({
  posterUrl: { type: String, default: null },
  title: { type: String, default: '' },
  // poster: 竖版海报（2:3）；banner: 横版
  variant: { type: String, default: 'poster' },
});

const failed = ref(false);       // 最终失败 -> 显示统一占位
const useDirect = ref(false);    // 代理失败后改用原图直链重试一次

// 代理地址 -> 失败后回退原图直链 -> 再失败显示占位
const imgSrc = computed(() => {
  if (!props.posterUrl || failed.value) return null;
  if (useDirect.value) return props.posterUrl;
  return proxiedImage(props.posterUrl);
});

const onError = () => {
  if (!useDirect.value && props.posterUrl && props.posterUrl.includes('playwoool.com')) {
    useDirect.value = true;
  } else {
    failed.value = true;
  }
};

const aspect = computed(() => (props.variant === 'banner' ? 'aspect-video' : 'aspect-[2/3]'));
</script>

<template>
  <div :class="['relative overflow-hidden bg-dark-700', aspect]">
    <img
      v-if="imgSrc"
      :key="imgSrc"
      :src="imgSrc"
      :alt="title"
      loading="lazy"
      referrerpolicy="no-referrer"
      class="h-full w-full object-cover"
      @error="onError"
    />
    <!-- 统一占位：无海报或图片加载失败，不会出现破图图标 -->
    <div
      v-else
      class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-dark-700 to-dark-800 p-3 text-center ring-1 ring-inset ring-white/5"
    >
      <ImageOff class="h-6 w-6 text-gray-600" />
      <span class="text-[10px] uppercase tracking-[0.2em] text-gray-600">暂无海报</span>
      <span v-if="title" class="line-clamp-3 text-xs font-medium text-gray-400">{{ title }}</span>
    </div>
  </div>
</template>
