<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/components/SiteLogo.vue';
import { IconMenu2, IconX } from '@tabler/icons-vue';
import { type Component, computed, onBeforeUnmount, onMounted, ref } from 'vue';

type Section = { id: string; label: string; icon: Component; visKey: string };

const props = defineProps<{
  sections?: Section[];
  sectionVisibility?: Record<string, boolean>;
  showSections?: boolean;
}>();

const visibleSections = computed(() => {
  if (!props.showSections || !props.sections || !props.sectionVisibility) return [];
  return props.sections.filter(s => props.sectionVisibility[s.visKey] !== false);
});

const activeSection = ref<string>('');
const scrolled = ref(false);
const progress = ref(0);
const menuOpen = ref(false);

function scrollTo(id: string) {
  menuOpen.value = false;
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}

function handleScroll() {
  const y = window.scrollY;
  scrolled.value = y > 12;

  const max = document.documentElement.scrollHeight - window.innerHeight;
  progress.value = max > 0 ? Math.min(100, (y / max) * 100) : 0;

  let current = '';
  for (const s of visibleSections.value) {
    const el = document.getElementById(s.id);
    if (el && el.getBoundingClientRect().top <= 140) current = s.id;
  }
  activeSection.value = current;
}

onMounted(() => {
  if (typeof window === 'undefined') return;
  handleScroll();
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
<div class="fixed top-0 right-0 left-0 z-50">
  <!-- Scroll progress -->
  <div class="h-0.5 w-full bg-transparent">
    <div class="h-full bg-primary transition-[width] duration-150" :style="{ width: `${progress}%` }" />
  </div>

  <nav class="px-4 pt-3">
    <div
      class="mx-auto flex h-14 max-w-5xl items-center justify-between rounded-full border border-border/70 bg-card/80 px-3 pl-4 backdrop-blur-xl transition-shadow duration-300"
      :class="scrolled ? 'shadow-lg shadow-black/40' : 'shadow-none'"
    >
      <Link href="/" class="flex items-center gap-2.5">
        <SiteLogo img-class="h-9 w-9" />
      </Link>

      <div v-if="showSections && visibleSections.length" class="hidden items-center gap-0.5 md:flex">
        <button
          v-for="s in visibleSections"
          :key="s.id"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[13px] transition-colors"
          :class="activeSection === s.id ? 'bg-primary/15 text-primary' : 'text-muted-foreground hover:text-foreground'"
          @click="scrollTo(s.id)"
        >
          <component :is="s.icon" class="h-3.5 w-3.5" :stroke-width="1.5" />{{ s.label }}
        </button>
      </div>

      <div class="flex items-center gap-2">
        <Link v-if="$page.props.auth?.user" href="/admin" class="rounded-full bg-primary px-3.5 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90 transition-colors">Admin</Link>
        <button v-if="showSections && visibleSections.length" class="rounded-full border border-border/70 p-2 text-muted-foreground md:hidden" aria-label="Menu" @click="menuOpen = !menuOpen">
          <component :is="menuOpen ? IconX : IconMenu2" class="h-4 w-4" :stroke-width="1.5" />
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="menuOpen" class="mx-auto mt-2 max-w-5xl rounded-2xl border border-border/70 bg-card/95 p-2 backdrop-blur-xl md:hidden">
      <button
        v-for="s in visibleSections"
        :key="s.id"
        class="flex w-full items-center gap-2 rounded-xl px-4 py-2.5 text-sm transition-colors"
        :class="activeSection === s.id ? 'bg-primary/15 text-primary' : 'text-muted-foreground'"
        @click="scrollTo(s.id)"
      >
        <component :is="s.icon" class="h-4 w-4" :stroke-width="1.5" />{{ s.label }}
      </button>
    </div>
  </nav>
</div>
</template>
