<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';
import {
  IconCalendar, IconTag, IconArrowLeft, IconClock, IconShare,
  IconBrandX, IconBrandLinkedin, IconBrandFacebook,
} from '@tabler/icons-vue';
import { computed } from 'vue';

type Blog = {
  id: number; title: string; slug: string; excerpt: string | null;
  content: string; image: string | null; tags: string[] | null;
  published_at: string | null;
};

type Seo = {
  title: string; description: string; keywords: string; canonical: string;
  ogTitle: string; ogDescription: string; ogImage: string; ogType: string;
  twitterCard: string; twitterTitle: string; twitterDescription: string;
  twitterImage: string; favicon: string; publishedAt: string | null;
};

const props = defineProps<{
  blog: Blog;
  relatedBlogs: Blog[];
  seo: Seo;
}>();


const readingTime = computed(() => {
  const words = props.blog.content.replace(/<[^>]*>/g, '').split(/\s+/).length;
  return Math.ceil(words / 200);
});

function shareOn(platform: string) {
  const url = encodeURIComponent(window.location.href);
  const text = encodeURIComponent(props.blog.title);
  const urls: Record<string, string> = {
    twitter: `https://twitter.com/intent/tweet?text=${text}&url=${url}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
  };
  window.open(urls[platform], '_blank', 'width=600,height=400');
}
</script>

<template>
<Head :title="seo.title">
  <meta v-if="seo.description" name="description" :content="seo.description" />
  <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
  <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />
  <link v-if="seo.favicon" rel="icon" :href="`/storage/${seo.favicon}`" />
  <!-- Open Graph -->
  <meta property="og:title" :content="seo.ogTitle" />
  <meta v-if="seo.ogDescription" property="og:description" :content="seo.ogDescription" />
  <meta property="og:type" :content="seo.ogType" />
  <meta v-if="seo.ogImage" property="og:image" :content="seo.ogImage" />
  <meta v-if="seo.canonical" property="og:url" :content="seo.canonical" />
  <meta v-if="seo.publishedAt" property="article:published_time" :content="seo.publishedAt" />
  <!-- Twitter Card -->
  <meta name="twitter:card" :content="seo.twitterCard" />
  <meta name="twitter:title" :content="seo.twitterTitle" />
  <meta v-if="seo.twitterDescription" name="twitter:description" :content="seo.twitterDescription" />
  <meta v-if="seo.twitterImage" name="twitter:image" :content="seo.twitterImage" />
</Head>
<Navbar :showSections="false" />

<main class="min-h-screen bg-background pt-14">
  <!-- Back button -->
  <div class="relative z-20 mx-auto max-w-4xl px-6 pt-6">
    <Link href="/#blog" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
      <IconArrowLeft class="h-4 w-4" :stroke-width="1.5" /> Kembali ke blog
    </Link>
  </div>

  <!-- Article header -->
  <article class="relative z-20 mx-auto max-w-4xl px-6 py-8">
    <header class="mb-8">
      <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
        <span class="inline-flex items-center gap-1.5">
          <IconCalendar class="h-4 w-4" :stroke-width="1.5" />
          {{ blog.published_at ? new Date(blog.published_at).toLocaleDateString('id', { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}
        </span>
        <span class="inline-flex items-center gap-1.5">
          <IconClock class="h-4 w-4" :stroke-width="1.5" />
          {{ readingTime }} menit membaca
        </span>
      </div>
      <h1 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl leading-tight">{{ blog.title }}</h1>
      <p v-if="blog.excerpt" class="mt-4 text-lg text-muted-foreground leading-relaxed">{{ blog.excerpt }}</p>
      <div v-if="blog.tags?.length" class="mt-4 flex flex-wrap gap-2">
        <span v-for="tag in blog.tags" :key="tag" class="inline-flex items-center gap-1 rounded-full border border-border/50 bg-white/50 dark:bg-white/5 backdrop-blur-lg px-3 py-1 text-xs font-medium text-secondary-foreground">
          <IconTag class="h-3 w-3" :stroke-width="1.5" /> {{ tag }}
        </span>
      </div>
    </header>

    <!-- Featured image -->
    <div v-if="blog.image" class="mb-8 overflow-hidden rounded-xl border border-border/50">
      <img :src="`/storage/${blog.image}`" :alt="blog.title" class="w-full h-auto object-cover" />
    </div>

    <!-- Content -->
    <div class="rounded-xl border border-border/50 bg-white/50 dark:bg-white/5 backdrop-blur-lg p-8 lg:p-12">
      <div class="prose prose-lg dark:prose-invert max-w-none
        prose-headings:font-bold prose-headings:tracking-tight
        prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4
        prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3
        prose-p:text-muted-foreground prose-p:leading-relaxed
        prose-a:text-primary prose-a:no-underline hover:prose-a:underline
        prose-strong:text-foreground prose-strong:font-semibold
        prose-code:text-primary prose-code:bg-primary/10 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm prose-code:before:content-none prose-code:after:content-none
        prose-pre:bg-zinc-900 prose-pre:border prose-pre:border-border/50
        prose-ul:text-muted-foreground prose-ol:text-muted-foreground
        prose-li:marker:text-primary
        prose-blockquote:border-l-primary prose-blockquote:text-muted-foreground prose-blockquote:italic
        prose-img:rounded-lg prose-img:border prose-img:border-border/50"
        v-html="blog.content"
      />
    </div>

    <!-- Share -->
    <div class="mt-8 rounded-xl border border-border/50 bg-white/50 dark:bg-white/5 backdrop-blur-lg p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm font-medium text-foreground">
          <IconShare class="h-4 w-4" :stroke-width="1.5" /> Bagikan artikel
        </div>
        <div class="flex items-center gap-2">
          <button @click="shareOn('twitter')" class="rounded-lg border border-border/50 bg-white/40 dark:bg-white/5 p-2 text-muted-foreground transition-colors hover:text-foreground hover:border-primary/30" aria-label="Bagikan di Twitter">
            <IconBrandX class="h-4 w-4" :stroke-width="1.5" />
          </button>
          <button @click="shareOn('linkedin')" class="rounded-lg border border-border/50 bg-white/40 dark:bg-white/5 p-2 text-muted-foreground transition-colors hover:text-foreground hover:border-primary/30" aria-label="Bagikan di LinkedIn">
            <IconBrandLinkedin class="h-4 w-4" :stroke-width="1.5" />
          </button>
          <button @click="shareOn('facebook')" class="rounded-lg border border-border/50 bg-white/40 dark:bg-white/5 p-2 text-muted-foreground transition-colors hover:text-foreground hover:border-primary/30" aria-label="Bagikan di Facebook">
            <IconBrandFacebook class="h-4 w-4" :stroke-width="1.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Related blogs -->
    <div v-if="relatedBlogs.length" class="mt-12">
      <h2 class="mb-5 text-xl font-bold text-foreground">Artikel terkait</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Link v-for="rb in relatedBlogs" :key="rb.id" :href="`/blog/${rb.slug}`" class="group overflow-hidden rounded-xl border border-border/50 bg-white/50 dark:bg-white/5 backdrop-blur-lg transition-colors hover:border-primary/30">
          <img v-if="rb.image" :src="`/storage/${rb.image}`" :alt="rb.title" class="h-36 w-full object-cover" />
          <div class="p-4">
            <div class="mb-1.5 flex items-center gap-1.5 text-[11px] text-muted-foreground">
              <IconCalendar class="h-3 w-3" :stroke-width="1.5" />
              <span v-if="rb.published_at">{{ new Date(rb.published_at).toLocaleDateString('id') }}</span>
            </div>
            <h3 class="text-sm font-semibold text-foreground line-clamp-2">{{ rb.title }}</h3>
            <p v-if="rb.excerpt" class="mt-1.5 line-clamp-2 text-[13px] text-muted-foreground">{{ rb.excerpt }}</p>
          </div>
        </Link>
      </div>
    </div>
  </article>

  <!-- Footer -->
  <footer class="relative z-20 border-t border-border/30 py-6 mt-12">
    <div class="mx-auto max-w-4xl px-6 text-center text-[13px] text-muted-foreground">
      <p>&copy; {{ new Date().getFullYear() }} — Hak cipta dilindungi</p>
    </div>
  </footer>
</main>
</template>
