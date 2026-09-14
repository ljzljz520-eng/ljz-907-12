<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { Star } from 'lucide-vue-next';
import PosterImage from './PosterImage.vue';

const props = defineProps({
  movie: { type: Object, required: true }
});

const firstGenre = computed(() => {
  return props.movie.genre ? props.movie.genre.split(/[,，\/]/)[0].trim() : '';
});

const displayTitle = computed(() => props.movie.translated_title || props.movie.title);
</script>

<template>
  <RouterLink
    :to="{ name: 'movie-detail', params: { id: movie.id }, query: { from: 'list' } }"
    class="group relative flex flex-col gap-2"
  >
    <!-- Poster -->
    <div class="relative rounded-lg shadow-lg ring-1 ring-white/5 transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-purple-500/20 group-hover:ring-purple-500/50">
      <PosterImage
        :poster-url="movie.poster_url"
        :title="displayTitle"
        class="overflow-hidden rounded-lg"
      />

      <!-- Rating Badge -->
      <div v-if="movie.rating > 0" class="absolute right-2 top-2 flex items-center gap-1 rounded bg-black/60 px-1.5 py-0.5 text-xs font-bold text-yellow-400 backdrop-blur-sm">
        <Star class="h-3 w-3 fill-current" />
        <span>{{ movie.rating }}</span>
      </div>

      <!-- Hover synopsis -->
      <div class="pointer-events-none absolute inset-0 rounded-lg bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
        <div class="absolute bottom-0 w-full p-3">
          <p class="line-clamp-3 text-xs font-medium text-gray-300">{{ movie.description }}</p>
        </div>
      </div>
    </div>

    <!-- Meta -->
    <div class="mt-1">
      <h3 class="truncate text-sm font-medium text-gray-100 transition-colors group-hover:text-purple-400">
        {{ displayTitle }}
      </h3>
      <div class="flex items-center justify-between text-xs text-gray-500">
        <span>{{ movie.year }}</span>
        <span v-if="firstGenre" class="max-w-[60%] truncate rounded border border-white/10 px-1.5 py-0.5 text-[10px] uppercase tracking-wider">{{ firstGenre }}</span>
      </div>
    </div>
  </RouterLink>
</template>
