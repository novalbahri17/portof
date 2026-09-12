<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Navbar from '@/components/Navbar.vue';
import { resolveTablerIcon } from '@/lib/tabler-icons';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import {
  IconArrowLeft,
  IconArrowLeftCircle,
  IconArrowRightCircle,
  IconX,
  IconTag,
  IconExternalLink,
  IconBrandGithub,
  IconChecklist,
  IconPhoto,
  IconStack2,
  IconRocket,
  IconBriefcase,
} from '@tabler/icons-vue';

type Technology = {
  key: string;
  name: string;
  icon: string;
};

type Project = {
  id: number;
  slug: string;
  title: string;
  description: string;
  challenge: string | null;
  solution: string | null;
  results: string | null;
  image: string | null;
  gallery: string[] | null;
  url: string | null;
  repo_url: string | null;
  tags: string[] | null;
  features: string[] | null;
  technologies: Technology[] | null;
  type: 'side_project' | 'portfolio';
};

type Seo = {
  title: string;
  description: string;
  keywords: string;
  canonical: string;
  ogTitle: string;
  ogDescription: string;
  ogImage: string;
  ogType: string;
  twitterCard: string;
  twitterTitle: string;
  twitterDescription: string;
  twitterImage: string;
  favicon: string;
  publishedAt: string | null;
  modifiedAt: string | null;
  siteName: string;
  structuredData: string;
};

const props = defineProps<{
  project: Project;
  relatedProjects: Project[];
  seo: Seo;
}>();


const backHref = computed(() => (props.project.type === 'side_project' ? '/#side-projects' : '/#portfolio'));
const backLabel = computed(() => (props.project.type === 'side_project' ? 'Kembali ke side projects' : 'Kembali ke portofolio'));
const projectTypeLabel = computed(() => (props.project.type === 'side_project' ? 'Side Project' : 'Portofolio'));
const galleryImages = computed(() => props.project.gallery || []);
const isLightboxOpen = ref(false);
const activeImageIndex = ref(0);
const lightboxCloseButton = ref<HTMLButtonElement | null>(null);

const currentImage = computed(() => {
  if (!galleryImages.value.length) return null;
  return galleryImages.value[activeImageIndex.value] ?? null;
});

const currentImageAlt = computed(() => `${props.project.title} - gambar ${activeImageIndex.value + 1}`);

function openLightbox(index: number) {
  activeImageIndex.value = index;
  isLightboxOpen.value = true;
}

function goToPreviousImage() {
  if (!galleryImages.value.length) return;
  activeImageIndex.value = (activeImageIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length;
}

function goToNextImage() {
  if (!galleryImages.value.length) return;
  activeImageIndex.value = (activeImageIndex.value + 1) % galleryImages.value.length;
}

function onLightboxKeydown(event: KeyboardEvent) {
  if (!isLightboxOpen.value) return;

  if (event.key === 'ArrowLeft') {
    event.preventDefault();
    goToPreviousImage();
  }

  if (event.key === 'ArrowRight') {
    event.preventDefault();
    goToNextImage();
  }

  if (event.key === 'Escape') {
    isLightboxOpen.value = false;
  }
}

watch(isLightboxOpen, (open) => {
  if (!open) return;
  nextTick(() => {
    lightboxCloseButton.value?.focus();
  });
});

onMounted(() => {
  window.addEventListener('keydown', onLightboxKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onLightboxKeydown);
});
</script>

<template>
<Head :title="seo.title">
  <meta v-if="seo.description" name="description" :content="seo.description" />
  <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
  <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />
  <link v-if="seo.favicon" rel="icon" :href="`/storage/${seo.favicon}`" />
  <meta property="og:title" :content="seo.ogTitle" />
  <meta v-if="seo.ogDescription" property="og:description" :content="seo.ogDescription" />
  <meta property="og:type" :content="seo.ogType" />
  <meta v-if="seo.ogImage" property="og:image" :content="seo.ogImage" />
  <meta v-if="seo.canonical" property="og:url" :content="seo.canonical" />
  <meta v-if="seo.siteName" property="og:site_name" :content="seo.siteName" />
  <meta v-if="seo.publishedAt" property="article:published_time" :content="seo.publishedAt" />
  <meta v-if="seo.modifiedAt" property="article:modified_time" :content="seo.modifiedAt" />
  <meta name="twitter:card" :content="seo.twitterCard" />
  <meta name="twitter:title" :content="seo.twitterTitle" />
  <meta v-if="seo.twitterDescription" name="twitter:description" :content="seo.twitterDescription" />
  <meta v-if="seo.twitterImage" name="twitter:image" :content="seo.twitterImage" />
  <component :is="'script'" v-if="seo.structuredData" type="application/ld+json" v-html="seo.structuredData" />
</Head>

<Navbar :showSections="false" />

<main class="min-h-screen bg-background pt-14">
  <div class="relative z-20 mx-auto max-w-5xl px-6 pt-6">
    <Link :href="backHref" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
      <IconArrowLeft class="h-4 w-4" :stroke-width="1.5" /> {{ backLabel }}
    </Link>
  </div>

  <article class="relative z-20 mx-auto max-w-5xl px-6 py-8">
    <header class="mb-8 rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
      <div class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
        <component :is="project.type === 'side_project' ? IconRocket : IconBriefcase" class="h-3.5 w-3.5" :stroke-width="1.5" />
        {{ projectTypeLabel }}
      </div>
      <h1 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl leading-tight">{{ project.title }}</h1>
      <div v-if="project.tags?.length" class="mt-4 flex flex-wrap gap-2">
        <span v-for="tag in project.tags" :key="tag" class="inline-flex items-center gap-1 rounded-full border border-border/50 bg-white/50 px-3 py-1 text-xs font-medium text-secondary-foreground dark:bg-white/5">
          <IconTag class="h-3 w-3" :stroke-width="1.5" /> {{ tag }}
        </span>
      </div>
    </header>

    <div v-if="project.image" class="mb-8 overflow-hidden rounded-xl border border-border/50">
      <img :src="`/storage/${project.image}`" :alt="project.title" class="h-auto w-full object-cover" />
    </div>

    <div class="space-y-6">
      <section class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
        <h2 class="mb-3 text-xl font-semibold text-foreground">Ringkasan</h2>
        <div class="prose prose-sm max-w-none text-muted-foreground dark:prose-invert" v-html="project.description" />
      </section>

      <section v-if="project.challenge || project.solution || project.results" class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div v-if="project.challenge" class="rounded-xl border border-border/50 bg-white/50 p-5 backdrop-blur-lg dark:bg-white/5">
          <h3 class="mb-2 text-base font-semibold text-foreground">Masalah</h3>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ project.challenge }}</p>
        </div>
        <div v-if="project.solution" class="rounded-xl border border-border/50 bg-white/50 p-5 backdrop-blur-lg dark:bg-white/5">
          <h3 class="mb-2 text-base font-semibold text-foreground">Solusi</h3>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ project.solution }}</p>
        </div>
        <div v-if="project.results" class="rounded-xl border border-border/50 bg-white/50 p-5 backdrop-blur-lg dark:bg-white/5">
          <h3 class="mb-2 text-base font-semibold text-foreground">Hasil</h3>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ project.results }}</p>
        </div>
      </section>

      <section v-if="project.features?.length" class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
        <div class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-foreground">
          <IconChecklist class="h-4 w-4 text-primary" :stroke-width="1.5" /> Fitur
        </div>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li v-for="feature in project.features" :key="feature" class="flex items-start gap-2">
            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary" />
            <span>{{ feature }}</span>
          </li>
        </ul>
      </section>

      <section v-if="project.technologies?.length" class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
        <div class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-foreground">
          <IconStack2 class="h-4 w-4 text-primary" :stroke-width="1.5" /> Teknologi
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-for="tech in project.technologies" :key="tech.key" class="inline-flex items-center gap-1.5 rounded-full border border-border/50 bg-background px-3 py-1 text-xs font-medium text-secondary-foreground">
            <component :is="resolveTablerIcon(tech.icon)" class="h-3.5 w-3.5 text-primary" />
            {{ tech.name }}
          </span>
        </div>
      </section>

      <section v-if="galleryImages.length" class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
        <div class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-foreground">
          <IconPhoto class="h-4 w-4 text-primary" :stroke-width="1.5" /> Galeri
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <button
            v-for="(image, index) in galleryImages"
            :key="image"
            type="button"
            class="group relative overflow-hidden rounded-lg border border-border/50"
            @click="openLightbox(index)"
            :aria-label="`Buka gambar ${index + 1} dari ${galleryImages.length}`"
          >
            <img
              :src="`/storage/${image}`"
              :alt="`${project.title} - gambar ${index + 1}`"
              class="h-44 w-full object-cover transition-transform duration-200 group-hover:scale-[1.02]"
            />
          </button>
        </div>
      </section>

      <section v-if="project.url || project.repo_url" class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5">
        <h3 class="mb-3 text-base font-semibold text-foreground">Tautan</h3>
        <div class="flex flex-wrap gap-3">
          <a v-if="project.url" :href="project.url" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <IconExternalLink class="h-4 w-4" :stroke-width="1.5" /> Lihat demo
          </a>
          <a v-if="project.repo_url" :href="project.repo_url" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground hover:border-primary/30">
            <IconBrandGithub class="h-4 w-4" :stroke-width="1.5" /> Kode sumber
          </a>
        </div>
      </section>

      <section v-if="relatedProjects.length" class="mt-4">
        <h2 class="mb-4 text-xl font-bold text-foreground">Proyek terkait</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <Link v-for="item in relatedProjects" :key="item.id" :href="`/projects/${item.slug}`" class="group overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 dark:bg-white/5">
            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.title" class="h-36 w-full object-cover" />
            <div class="p-4">
              <h3 class="line-clamp-2 text-sm font-semibold text-foreground">{{ item.title }}</h3>
              <div class="prose prose-sm mt-1.5 line-clamp-2 text-[13px] text-muted-foreground" v-html="item.description" />
            </div>
          </Link>
        </div>
      </section>
    </div>
  </article>

  <footer class="relative z-20 mt-12 border-t border-border/30 py-6">
    <div class="mx-auto max-w-5xl px-6 text-center text-[13px] text-muted-foreground">
      <p>&copy; {{ new Date().getFullYear() }} — Hak cipta dilindungi</p>
    </div>
  </footer>
</main>

<Dialog :open="isLightboxOpen" @update:open="isLightboxOpen = $event">
  <DialogContent
    :show-close-button="false"
    class="h-[92vh] w-[96vw] max-w-[96vw] border-0 bg-black/95 p-0 shadow-none sm:max-w-[96vw]"
  >
    <DialogTitle class="sr-only">Galeri {{ project.title }}</DialogTitle>

    <div class="relative flex h-full w-full items-center justify-center px-12 py-6 sm:px-16">
      <img
        v-if="currentImage"
        :src="`/storage/${currentImage}`"
        :alt="currentImageAlt"
        class="max-h-full max-w-full rounded-md object-contain"
      />

      <button
        ref="lightboxCloseButton"
        type="button"
        class="absolute top-2 right-2 rounded-full border border-white/30 bg-black/60 p-1.5 text-white transition-colors hover:bg-black/80"
        aria-label="Tutup galeri"
        @click="isLightboxOpen = false"
      >
        <IconX class="h-5 w-5" :stroke-width="1.8" />
      </button>

      <button
        type="button"
        class="absolute left-2 rounded-full border border-white/30 bg-black/60 p-1.5 text-white transition-colors hover:bg-black/80 sm:left-4"
        aria-label="Gambar sebelumnya"
        @click="goToPreviousImage"
      >
        <IconArrowLeftCircle class="h-7 w-7" :stroke-width="1.8" />
      </button>

      <button
        type="button"
        class="absolute right-2 rounded-full border border-white/30 bg-black/60 p-1.5 text-white transition-colors hover:bg-black/80 sm:right-4"
        aria-label="Gambar berikutnya"
        @click="goToNextImage"
      >
        <IconArrowRightCircle class="h-7 w-7" :stroke-width="1.8" />
      </button>

      <div class="absolute bottom-2 rounded-full bg-black/65 px-3 py-1 text-xs font-medium text-white">
        {{ activeImageIndex + 1 }} / {{ galleryImages.length }}
      </div>
    </div>
  </DialogContent>
</Dialog>
</template>
