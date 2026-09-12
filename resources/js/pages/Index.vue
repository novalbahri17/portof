<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    IconUser,
    IconRocket,
    IconBriefcase,
    IconArticle,
    IconMail,
    IconBrandGithub,
    IconBrandLinkedin,
    IconBrandX,
    IconBrandInstagram,
    IconBrandYoutube,
    IconBrandTiktok,
    IconBrandDiscord,
    IconWorld,
    IconExternalLink,
    IconArrowUp,
    IconArrowDown,
    IconSend,
    IconCalendar,
    IconTag,
    IconCode,
    IconDeviceGamepad2,
    IconCertificate,
    IconChevronLeft,
    IconChevronRight,
    IconX,
    IconSchool,
    IconMapPin,
    IconSparkles,
    IconClock,
    IconPhone,
    IconBrandLaravel,
    IconBrandVue,
    IconBrandTailwind,
    IconBrandPhp,
    IconBrandMysql,
    IconBrandTypescript,
    IconBrandJavascript,
    IconBrandDocker,
    IconBrandGit,
    IconBrandNpm,
    IconBrandInertia,
    IconDatabase,
} from '@tabler/icons-vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Component } from 'vue';
import Navbar from '@/components/Navbar.vue';
import StorageImage from '@/components/StorageImage.vue';
import { infoDialog } from '@/lib/swal';

type Project = {
    id: number;
    slug: string;
    title: string;
    description: string;
    image: string | null;
    url: string | null;
    repo_url: string | null;
    tags: string[] | null;
    type: string;
    featured: boolean;
};
type Blog = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    image: string | null;
    tags: string[] | null;
    published_at: string | null;
};
type Certification = {
    id: number;
    title: string;
    description: string;
    images: string[];
    certificate_file: string | null;
    published: boolean;
    sort_order: number;
};
type Experience = {
    id: number;
    type: 'work' | 'education';
    employment_type: string | null;
    title: string;
    institution: string;
    location: string | null;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    description: string | null;
};
type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
};
type Social = { network: string; url: string };

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
};

const props = defineProps<{
    heroTitle: string;
    heroSubtitle: string;
    heroBadge: string;
    heroImage: string;
    heroImageShape: 'circle' | 'square' | 'rounded';
    heroImageSize: number;
    about: string;
    hobbies: string;
    socials: Social[];
    contactInfo: { email: string; phone: string; address: string };
    sideProjects: Paginated<Project>;
    portfolios: Paginated<Project>;
    certifications: Certification[];
    experiences: Experience[];
    blogs: Paginated<Blog>;
    sectionVisibility: Record<string, boolean>;
    seo: Seo;
}>();

const socialIcons: Record<string, Component> = {
    github: IconBrandGithub,
    linkedin: IconBrandLinkedin,
    twitter: IconBrandX,
    instagram: IconBrandInstagram,
    youtube: IconBrandYoutube,
    tiktok: IconBrandTiktok,
    discord: IconBrandDiscord,
    website: IconWorld,
};

/** Label jenis pekerjaan (harus sama dengan Experience::EMPLOYMENT_TYPES). */
const employmentTypeLabels: Record<string, string> = {
    school_internship: 'Magang Sekolah / Internship',
    internship: 'Magang Kerja',
    full_time: 'Penuh Waktu / Full-time',
    part_time: 'Paruh Waktu / Part-time',
    contract: 'Kontrak / Contract',
    freelance: 'Pekerja Lepas / Freelance',
    remote: 'Remote / Jarak Jauh',
};

const heroImageSizePx = computed(() => {
    const size = Number(props.heroImageSize);
    if (!Number.isFinite(size)) return 112;
    return Math.min(240, Math.max(64, Math.round(size)));
});
const heroImageShapeClass = computed(() => {
    if (props.heroImageShape === 'square') return 'rounded-none';
    if (props.heroImageShape === 'rounded') return 'rounded-2xl';
    return 'rounded-full';
});

const contactForm = useForm({ name: '', email: '', subject: '', message: '' });
function submitContact() {
    contactForm.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            contactForm.reset();
            infoDialog({
                icon: 'success',
                title: 'Pesan Terkirim',
                text: 'Terima kasih sudah menulis, saya akan segera membalas.',
                confirmButtonText: 'Tutup',
            });
        },
    });
}

const allSections = [
    { id: 'about', label: 'Tentang', icon: IconUser, visKey: 'about' },
    {
        id: 'side-projects',
        label: 'Projects',
        icon: IconRocket,
        visKey: 'projects',
    },
    {
        id: 'portfolio',
        label: 'Portofolio',
        icon: IconBriefcase,
        visKey: 'portfolio',
    },
    {
        id: 'experience',
        label: 'Pengalaman',
        icon: IconBriefcase,
        visKey: 'experience',
    },
    {
        id: 'certifications',
        label: 'Sertifikasi',
        icon: IconCertificate,
        visKey: 'certifications',
    },
    { id: 'blog', label: 'Blog', icon: IconArticle, visKey: 'blog' },
    { id: 'contact', label: 'Kontak', icon: IconMail, visKey: 'contact' },
];

const timelineTab = ref<'work' | 'education'>('work');
const timelineTabs = [
    { key: 'work' as const, label: 'Kerja', icon: IconBriefcase },
    { key: 'education' as const, label: 'Pendidikan', icon: IconSchool },
];
const timelineItems = computed(() =>
    props.experiences.filter((e) => e.type === timelineTab.value),
);

function formatPeriod(e: Experience): string {
    const opts: Intl.DateTimeFormatOptions = {
        month: 'short',
        year: 'numeric',
    };
    const start = new Date(e.start_date).toLocaleDateString('id-ID', opts);
    const end = e.is_current
        ? 'Sekarang'
        : e.end_date
          ? new Date(e.end_date).toLocaleDateString('id-ID', opts)
          : '';
    return end ? `${start} — ${end}` : start;
}

function splitDescription(desc: string | null): string[] {
    if (!desc) return [];
    return desc
        .split(/[•\n]/)
        .map((s) => s.trim())
        .filter(Boolean);
}

const hobbyItems = computed(() => {
    if (!props.hobbies) return [];
    return props.hobbies
        .split(/\n/)
        .map((s) => s.trim())
        .filter(Boolean)
        .map((line) => {
            const idx = line.indexOf(' — ');
            return idx > 0
                ? { title: line.slice(0, idx), body: line.slice(idx + 3) }
                : { title: line, body: '' };
        });
});

/** Kata terakhir pada judul hero — dipakai sebagai aksen warna. */
const heroAccent = computed(() => {
    const words = props.heroTitle.trim().split(/\s+/);
    return words.length ? words[words.length - 1] : '';
});

/** Judul hero tanpa kata aksen (untuk dirender sebelum span berwarna). */
const heroTitleLead = computed(() => {
    const accent = heroAccent.value;
    if (!accent) return props.heroTitle;
    return props.heroTitle.slice(0, props.heroTitle.lastIndexOf(accent));
});

/** Teknologi yang ditampilkan pada marquee di bawah hero. */
const marqueeTech = [
    { name: 'Laravel', icon: IconBrandLaravel },
    { name: 'Vue.js', icon: IconBrandVue },
    { name: 'TailwindCSS', icon: IconBrandTailwind },
    { name: 'PHP', icon: IconBrandPhp },
    { name: 'Inertia.js', icon: IconBrandInertia },
    { name: 'TypeScript', icon: IconBrandTypescript },
    { name: 'JavaScript', icon: IconBrandJavascript },
    { name: 'MySQL', icon: IconBrandMysql },
    { name: 'Docker', icon: IconBrandDocker },
    { name: 'Git', icon: IconBrandGit },
    { name: 'npm', icon: IconBrandNpm },
    { name: 'Eloquent', icon: IconDatabase },
];

/** Label script + teks outline untuk tiap judul section (gaya referensi). */
const sectionMeta: Record<string, { script: string; outline: string }> = {
    'side-projects': { script: 'Projects', outline: 'SIDE PROJECTS' },
    portfolio: { script: 'Karya', outline: 'PORTOFOLIO' },
    certifications: { script: 'Sertifikasi', outline: 'CERTIFICATIONS' },
    blog: { script: 'Artikel', outline: 'BLOG' },
    contact: { script: 'Kontak', outline: 'CONTACT' },
};

/** Section yang tampil di footer. */
const visibleFooterSections = computed(() =>
    allSections.filter((s) => props.sectionVisibility[s.visKey] !== false),
);
function scrollTo(id: string) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}
function goToPage(url: string | null, sectionId: string) {
    if (!url) return;
    router.get(
        url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['sideProjects', 'portfolios', 'blogs'],
            onSuccess: () => {
                setTimeout(() => scrollTo(sectionId), 100);
            },
        },
    );
}
function paginationLabel(label: string): string {
    return label
        .replace('&laquo; Previous', '« Sebelumnya')
        .replace('Next &raquo;', 'Berikutnya »');
}
function goToProject(slug: string) {
    router.visit(`/projects/${slug}`);
}

const certificationStartIndex = ref(0);
const certificationItemsPerView = ref(3);
const isCertificationPaused = ref(false);
const prefersReducedMotion = ref(false);
let certificationAutoplay: ReturnType<typeof setInterval> | null = null;
let reducedMotionQuery: MediaQueryList | null = null;

/** Lightbox galeri sertifikasi. */
const lightbox = ref<{ images: string[]; index: number } | null>(null);

/** Indeks gambar aktif per kartu sertifikasi. */
const certImageIndex = ref<Record<number, number>>({});

function activeCertImage(certification: Certification): number {
    const index = certImageIndex.value[certification.id] ?? 0;
    return Math.min(index, Math.max(0, certification.images.length - 1));
}

function setCertImage(id: number, index: number) {
    certImageIndex.value = { ...certImageIndex.value, [id]: index };
}

function openCertificate(images: string[], index = 0) {
    if (!images.length) return;
    lightbox.value = { images, index };
}

function closeLightbox() {
    lightbox.value = null;
}

function stepLightbox(delta: number) {
    if (!lightbox.value) return;
    const total = lightbox.value.images.length;
    lightbox.value.index = (lightbox.value.index + delta + total) % total;
}

function onLightboxKey(event: KeyboardEvent) {
    if (!lightbox.value) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowRight') stepLightbox(1);
    if (event.key === 'ArrowLeft') stepLightbox(-1);
}

watch(lightbox, (value) => {
    if (typeof document === 'undefined') return;

    if (value) {
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', onLightboxKey);
    } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', onLightboxKey);
    }
});

const certificationMaxStartIndex = computed(() => {
    return Math.max(
        0,
        props.certifications.length - certificationItemsPerView.value,
    );
});

const certificationTrackStyle = computed(() => ({
    transform: `translateX(-${(certificationStartIndex.value * 100) / certificationItemsPerView.value}%)`,
}));

const certificationCanSlide = computed(() => {
    return props.certifications.length > certificationItemsPerView.value;
});

function updateCertificationItemsPerView() {
    if (typeof window === 'undefined') return;

    if (window.innerWidth >= 1024) {
        certificationItemsPerView.value = 3;
    } else if (window.innerWidth >= 640) {
        certificationItemsPerView.value = 2;
    } else {
        certificationItemsPerView.value = 1;
    }

    if (certificationStartIndex.value > certificationMaxStartIndex.value) {
        certificationStartIndex.value = certificationMaxStartIndex.value;
    }

    startCertificationAutoplay();
}

function nextCertification() {
    if (!certificationCanSlide.value) return;

    certificationStartIndex.value =
        certificationStartIndex.value >= certificationMaxStartIndex.value
            ? 0
            : certificationStartIndex.value + 1;
}

function prevCertification() {
    if (!certificationCanSlide.value) return;

    certificationStartIndex.value =
        certificationStartIndex.value <= 0
            ? certificationMaxStartIndex.value
            : certificationStartIndex.value - 1;
}

function stopCertificationAutoplay() {
    if (!certificationAutoplay) return;
    clearInterval(certificationAutoplay);
    certificationAutoplay = null;
}

function startCertificationAutoplay() {
    stopCertificationAutoplay();

    if (prefersReducedMotion.value || !certificationCanSlide.value) return;

    certificationAutoplay = setInterval(() => {
        if (isCertificationPaused.value) return;
        nextCertification();
    }, 4500);
}

function handleReducedMotionChange(event: MediaQueryListEvent) {
    prefersReducedMotion.value = event.matches;
    startCertificationAutoplay();
}

onMounted(() => {
    if (typeof window === 'undefined') return;

    updateCertificationItemsPerView();
    window.addEventListener('resize', updateCertificationItemsPerView);

    reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    prefersReducedMotion.value = reducedMotionQuery.matches;
    reducedMotionQuery.addEventListener('change', handleReducedMotionChange);

    startCertificationAutoplay();
});

onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', updateCertificationItemsPerView);
    }

    if (reducedMotionQuery) {
        reducedMotionQuery.removeEventListener(
            'change',
            handleReducedMotionChange,
        );
    }

    stopCertificationAutoplay();
});
</script>

<template>
    <Head :title="seo.title || 'Portofolio'">
        <meta
            v-if="seo.description"
            name="description"
            :content="seo.description"
        />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />
        <link v-if="seo.favicon" rel="icon" :href="`/storage/${seo.favicon}`" />
        <!-- Open Graph -->
        <meta
            v-if="seo.ogTitle || seo.title"
            property="og:title"
            :content="seo.ogTitle || seo.title"
        />
        <meta
            v-if="seo.ogDescription || seo.description"
            property="og:description"
            :content="seo.ogDescription || seo.description"
        />
        <meta property="og:type" :content="seo.ogType || 'website'" />
        <meta
            v-if="seo.ogImage"
            property="og:image"
            :content="`/storage/${seo.ogImage}`"
        />
        <meta v-if="seo.canonical" property="og:url" :content="seo.canonical" />
        <!-- Twitter Card -->
        <meta
            name="twitter:card"
            :content="seo.twitterCard || 'summary_large_image'"
        />
        <meta
            v-if="seo.twitterTitle || seo.ogTitle || seo.title"
            name="twitter:title"
            :content="seo.twitterTitle || seo.ogTitle || seo.title"
        />
        <meta
            v-if="
                seo.twitterDescription || seo.ogDescription || seo.description
            "
            name="twitter:description"
            :content="
                seo.twitterDescription || seo.ogDescription || seo.description
            "
        />
        <meta
            v-if="seo.twitterImage || seo.ogImage"
            name="twitter:image"
            :content="`/storage/${seo.twitterImage || seo.ogImage}`"
        />
    </Head>
    <Navbar
        :sections="allSections"
        :sectionVisibility="sectionVisibility"
        :showSections="true"
    />

    <main class="min-h-screen overflow-x-hidden bg-background pt-20">
        <!-- ===== HERO ===== -->
        <section class="relative z-20 mx-auto max-w-5xl px-6 pt-14 pb-10">
            <div
                class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-14"
            >
                <!-- Teks -->
                <div class="text-center lg:text-left">
                    <span
                        v-if="heroBadge"
                        class="animate-fade-up inline-flex items-center gap-2 rounded-full border border-primary/25 bg-primary/10 px-3.5 py-1.5 text-xs font-medium text-primary"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                        {{ heroBadge }}
                    </span>

                    <h1
                        class="animate-fade-up mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-foreground sm:text-5xl lg:text-[3.4rem]"
                        style="animation-delay: 0.08s"
                    >
                        <template v-if="heroAccent"
                            >{{ heroTitleLead
                            }}<span class="text-primary">{{
                                heroAccent
                            }}</span></template
                        >
                        <template v-else>{{ heroTitle }}</template>
                    </h1>

                    <p
                        class="animate-fade-up mx-auto mt-5 max-w-lg text-base leading-relaxed text-muted-foreground sm:text-lg lg:mx-0"
                        style="animation-delay: 0.16s"
                    >
                        {{ heroSubtitle }}
                    </p>

                    <div
                        class="animate-fade-up mt-8 flex items-center justify-center gap-3 lg:justify-start"
                        style="animation-delay: 0.24s"
                    >
                        <button
                            @click="scrollTo('contact')"
                            class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-colors hover:bg-primary/90"
                        >
                            <IconMail class="h-4 w-4" :stroke-width="1.5" />
                            Hubungi Saya
                        </button>
                        <button
                            @click="scrollTo('portfolio')"
                            class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-card/50 px-6 py-3 text-sm font-semibold text-foreground backdrop-blur-lg transition-colors hover:border-primary/40 hover:text-primary"
                        >
                            <IconBriefcase
                                class="h-4 w-4"
                                :stroke-width="1.5"
                            />
                            Lihat Karya
                        </button>
                    </div>

                    <div
                        v-if="socials.length"
                        class="animate-fade-up mt-8 flex items-center justify-center gap-3 lg:justify-start"
                        style="animation-delay: 0.32s"
                    >
                        <a
                            v-for="s in socials"
                            :key="s.network"
                            :href="s.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-full border border-border/60 p-2 text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
                            :title="s.network"
                        >
                            <component
                                :is="socialIcons[s.network] ?? IconWorld"
                                class="h-4 w-4"
                                :stroke-width="1.5"
                            />
                        </a>
                    </div>
                </div>

                <!-- Foto -->
                <div
                    class="animate-fade-up relative flex justify-center lg:justify-end"
                    style="animation-delay: 0.2s"
                >
                    <div
                        class="pointer-events-none absolute inset-0 flex items-center justify-center"
                    >
                        <div
                            class="h-72 w-72 rounded-full bg-primary/20 blur-3xl"
                        />
                    </div>

                    <div class="relative">
                        <div
                            v-if="heroImage"
                            class="relative overflow-hidden shadow-2xl ring-1 shadow-primary/20 ring-primary/30"
                            :class="heroImageShapeClass"
                            :style="{
                                width: `${heroImageSizePx}px`,
                                height: `${heroImageSizePx}px`,
                            }"
                        >
                            <StorageImage
                                :path="heroImage"
                                alt="Foto profil"
                                :show-label="false"
                                image-class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="relative flex items-center justify-center overflow-hidden shadow-2xl ring-1 shadow-primary/20 ring-primary/30"
                            :class="heroImageShapeClass"
                            :style="{
                                width: `${heroImageSizePx}px`,
                                height: `${heroImageSizePx}px`,
                            }"
                        >
                            <div
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/25 via-primary/10 to-transparent"
                            >
                                <IconCode
                                    class="text-primary"
                                    :style="{
                                        width: `${heroImageSizePx / 3}px`,
                                        height: `${heroImageSizePx / 3}px`,
                                    }"
                                    :stroke-width="1.5"
                                />
                            </div>
                        </div>

                        <div
                            class="absolute -bottom-2 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full border border-border/70 bg-card/90 px-3 py-1.5 text-[11px] font-medium text-foreground backdrop-blur-lg"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-green-500"
                            />
                            Open to work
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marquee teknologi -->
            <div
                class="animate-fade-up mt-14 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_12%,black_88%,transparent)]"
                style="animation-delay: 0.4s"
            >
                <div class="animate-marquee flex w-max items-center gap-10">
                    <div
                        v-for="loop in 2"
                        :key="loop"
                        class="flex items-center gap-10"
                    >
                        <div
                            v-for="t in marqueeTech"
                            :key="`${loop}-${t.name}`"
                            class="flex shrink-0 items-center gap-2 text-muted-foreground/70"
                        >
                            <component
                                :is="t.icon"
                                class="h-5 w-5"
                                :stroke-width="1.5"
                            />
                            <span class="text-sm font-medium">{{
                                t.name
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-center">
                <button
                    class="animate-bounce-slow text-muted-foreground/60 transition-colors hover:text-primary"
                    aria-label="Gulir ke bawah"
                    @click="scrollTo('about')"
                >
                    <IconArrowDown class="h-5 w-5" :stroke-width="1.5" />
                </button>
            </div>
        </section>

        <!-- ===== ABOUT + HOBBIES ===== -->
        <section
            v-if="sectionVisibility.about"
            id="about"
            class="relative z-20 mx-auto max-w-5xl px-6 py-10"
        >
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div
                    class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg lg:col-span-2 dark:bg-white/5"
                >
                    <div
                        class="mb-3 inline-flex items-center gap-1.5 text-xs font-medium text-primary"
                    >
                        <IconUser class="h-3.5 w-3.5" :stroke-width="1.5" />
                        Tentang Saya
                    </div>
                    <div
                        v-if="about"
                        class="prose prose-sm dark:prose-invert text-muted-foreground"
                        v-html="about"
                    ></div>
                    <p v-else class="text-sm text-muted-foreground">
                        Developer yang berspesialisasi di Laravel dan Vue.js.
                    </p>
                </div>
                <div
                    class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg dark:bg-white/5"
                >
                    <div
                        class="mb-3 inline-flex items-center gap-1.5 text-xs font-medium text-primary"
                    >
                        <IconDeviceGamepad2
                            class="h-3.5 w-3.5"
                            :stroke-width="1.5"
                        />
                        Hobbies
                    </div>
                    <ul v-if="hobbyItems.length" class="space-y-2">
                        <li
                            v-for="(h, hi) in hobbyItems"
                            :key="hi"
                            class="flex items-start gap-2.5 rounded-lg bg-white/40 px-3 py-2 text-[13px] leading-relaxed dark:bg-white/5"
                        >
                            <span class="min-w-0"
                                ><span class="font-medium text-foreground">{{
                                    h.title
                                }}</span
                                ><span
                                    v-if="h.body"
                                    class="text-muted-foreground"
                                >
                                    — {{ h.body }}</span
                                ></span
                            >
                        </li>
                    </ul>
                    <div
                        v-else-if="hobbies"
                        class="prose prose-sm dark:prose-invert text-[13px] text-muted-foreground"
                        v-html="hobbies"
                    ></div>
                    <p v-else class="text-sm text-muted-foreground">
                        Atur dari admin.
                    </p>
                </div>
            </div>
        </section>

        <!-- ===== SIDE PROJECTS ===== -->
        <section
            v-if="sectionVisibility.projects"
            id="side-projects"
            class="relative z-20 mx-auto max-w-5xl px-6 py-12"
        >
            <div class="mb-7">
                <span class="font-script text-2xl text-primary">{{
                    sectionMeta['side-projects'].script
                }}</span>
                <h2
                    class="text-outline mt-1 text-4xl font-bold tracking-tight sm:text-5xl"
                >
                    {{ sectionMeta['side-projects'].outline }}
                </h2>
            </div>
            <div
                v-if="sideProjects.data.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <template v-for="(p, i) in sideProjects.data" :key="p.id">
                    <!-- Pattern: wide, compact, compact (repeats every 3) -->
                    <article
                        v-if="i % 3 === 0"
                        class="group cursor-pointer overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 sm:col-span-2 dark:bg-white/5"
                        role="link"
                        tabindex="0"
                        @click="goToProject(p.slug)"
                        @keydown.enter.prevent="goToProject(p.slug)"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2">
                            <img
                                v-if="p.image"
                                :src="`/storage/${p.image}`"
                                :alt="p.title"
                                class="h-52 w-full object-cover sm:h-full"
                            />
                            <div class="flex flex-col p-5">
                                <div
                                    v-if="p.featured"
                                    class="mb-2 inline-flex w-fit items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
                                >
                                    <IconRocket
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    Unggulan
                                </div>
                                <h3
                                    class="text-base font-semibold text-foreground"
                                >
                                    {{ p.title }}
                                </h3>
                                <div
                                    class="prose prose-sm mt-2 text-[13px] leading-relaxed text-muted-foreground"
                                    v-html="p.description"
                                ></div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in p.tags"
                                        :key="tag"
                                        class="rounded-full bg-white/60 px-2 py-0.5 text-[11px] text-secondary-foreground dark:bg-white/10"
                                        >{{ tag }}</span
                                    >
                                </div>
                                <div class="mt-auto flex gap-3 pt-4">
                                    <a
                                        v-if="p.url"
                                        :href="p.url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[13px] text-primary hover:underline"
                                        @click.stop
                                        ><IconExternalLink
                                            class="h-3 w-3"
                                            :stroke-width="1.5"
                                        />
                                        Demo</a
                                    >
                                    <a
                                        v-if="p.repo_url"
                                        :href="p.repo_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[13px] text-muted-foreground hover:text-foreground"
                                        @click.stop
                                        ><IconBrandGithub
                                            class="h-3 w-3"
                                            :stroke-width="1.5"
                                        />
                                        Kode</a
                                    >
                                </div>
                            </div>
                        </div>
                    </article>
                    <article
                        v-else
                        class="group cursor-pointer overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 dark:bg-white/5"
                        role="link"
                        tabindex="0"
                        @click="goToProject(p.slug)"
                        @keydown.enter.prevent="goToProject(p.slug)"
                    >
                        <img
                            v-if="p.image"
                            :src="`/storage/${p.image}`"
                            :alt="p.title"
                            class="h-40 w-full object-cover"
                        />
                        <div class="flex flex-col p-5">
                            <h3 class="text-sm font-semibold text-foreground">
                                {{ p.title }}
                            </h3>
                            <div
                                class="prose prose-sm mt-1.5 line-clamp-3 text-[13px] text-muted-foreground"
                                v-html="p.description"
                            ></div>
                            <div class="mt-3 flex flex-wrap gap-1">
                                <span
                                    v-for="tag in p.tags"
                                    :key="tag"
                                    class="rounded-full bg-white/60 px-2 py-0.5 text-[11px] text-secondary-foreground dark:bg-white/10"
                                    >{{ tag }}</span
                                >
                            </div>
                            <div class="mt-auto flex gap-3 pt-3">
                                <a
                                    v-if="p.url"
                                    :href="p.url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[13px] text-primary hover:underline"
                                    @click.stop
                                    ><IconExternalLink
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    Demo</a
                                >
                                <a
                                    v-if="p.repo_url"
                                    :href="p.repo_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[13px] text-muted-foreground hover:text-foreground"
                                    @click.stop
                                    ><IconBrandGithub
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    Kode</a
                                >
                            </div>
                        </div>
                    </article>
                </template>
            </div>
            <div
                v-else
                class="rounded-xl border border-dashed border-border/50 bg-white/50 p-10 text-center text-sm text-muted-foreground backdrop-blur-lg dark:bg-white/5"
            >
                Segera...
            </div>
            <div
                v-if="sideProjects.last_page > 1"
                class="mt-4 flex items-center justify-center gap-1"
            >
                <button
                    v-for="link in sideProjects.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="goToPage(link.url, 'side-projects')"
                    class="rounded-md border px-2.5 py-1 text-xs transition-colors disabled:opacity-30"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border/50 bg-white/50 text-muted-foreground backdrop-blur-lg hover:text-foreground dark:bg-white/5'
                    "
                    v-html="paginationLabel(link.label)"
                />
            </div>
        </section>

        <!-- ===== PORTFOLIO ===== -->
        <section
            v-if="sectionVisibility.portfolio"
            id="portfolio"
            class="relative z-20 mx-auto max-w-5xl px-6 py-12"
        >
            <div class="mb-7">
                <span class="font-script text-2xl text-primary">{{
                    sectionMeta.portfolio.script
                }}</span>
                <h2
                    class="text-outline mt-1 text-4xl font-bold tracking-tight sm:text-5xl"
                >
                    {{ sectionMeta.portfolio.outline }}
                </h2>
            </div>
            <div
                v-if="portfolios.data.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <template v-for="(p, i) in portfolios.data" :key="p.id">
                    <!-- Pattern: compact, compact, wide (repeats every 3) -->
                    <article
                        v-if="i % 3 === 2"
                        class="group cursor-pointer overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 sm:col-span-2 dark:bg-white/5"
                        role="link"
                        tabindex="0"
                        @click="goToProject(p.slug)"
                        @keydown.enter.prevent="goToProject(p.slug)"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2">
                            <img
                                v-if="p.image"
                                :src="`/storage/${p.image}`"
                                :alt="p.title"
                                class="h-52 w-full object-cover sm:h-full"
                            />
                            <div class="flex flex-col p-5">
                                <h3
                                    class="text-base font-semibold text-foreground"
                                >
                                    {{ p.title }}
                                </h3>
                                <div
                                    class="prose prose-sm mt-2 text-[13px] leading-relaxed text-muted-foreground"
                                    v-html="p.description"
                                ></div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in p.tags"
                                        :key="tag"
                                        class="rounded-full bg-white/60 px-2 py-0.5 text-[11px] text-secondary-foreground dark:bg-white/10"
                                        >{{ tag }}</span
                                    >
                                </div>
                                <div class="mt-auto flex gap-3 pt-4">
                                    <a
                                        v-if="p.url"
                                        :href="p.url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[13px] text-primary hover:underline"
                                        @click.stop
                                        ><IconExternalLink
                                            class="h-3 w-3"
                                            :stroke-width="1.5"
                                        />
                                        Lihat Proyek</a
                                    >
                                    <a
                                        v-if="p.repo_url"
                                        :href="p.repo_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[13px] text-muted-foreground hover:text-foreground"
                                        @click.stop
                                        ><IconBrandGithub
                                            class="h-3 w-3"
                                            :stroke-width="1.5"
                                        />
                                        Kode</a
                                    >
                                </div>
                            </div>
                        </div>
                    </article>
                    <article
                        v-else
                        class="group cursor-pointer overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 dark:bg-white/5"
                        role="link"
                        tabindex="0"
                        @click="goToProject(p.slug)"
                        @keydown.enter.prevent="goToProject(p.slug)"
                    >
                        <img
                            v-if="p.image"
                            :src="`/storage/${p.image}`"
                            :alt="p.title"
                            class="h-44 w-full object-cover transition-transform group-hover:scale-[1.02]"
                        />
                        <div class="p-5">
                            <h3 class="text-sm font-semibold text-foreground">
                                {{ p.title }}
                            </h3>
                            <div
                                class="prose prose-sm mt-1.5 line-clamp-2 text-[13px] text-muted-foreground"
                                v-html="p.description"
                            ></div>
                            <div class="mt-3 flex flex-wrap gap-1">
                                <span
                                    v-for="tag in p.tags"
                                    :key="tag"
                                    class="rounded-full bg-white/60 px-2 py-0.5 text-[11px] text-secondary-foreground dark:bg-white/10"
                                    >{{ tag }}</span
                                >
                            </div>
                            <div class="mt-3 flex gap-3">
                                <a
                                    v-if="p.url"
                                    :href="p.url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[13px] text-primary hover:underline"
                                    @click.stop
                                    ><IconExternalLink
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    Lihat Proyek</a
                                >
                                <a
                                    v-if="p.repo_url"
                                    :href="p.repo_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[13px] text-muted-foreground hover:text-foreground"
                                    @click.stop
                                    ><IconBrandGithub
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    Kode</a
                                >
                            </div>
                        </div>
                    </article>
                </template>
            </div>
            <div
                v-else
                class="rounded-xl border border-dashed border-border/50 bg-white/50 p-10 text-center text-sm text-muted-foreground backdrop-blur-lg dark:bg-white/5"
            >
                Segera...
            </div>
            <div
                v-if="portfolios.last_page > 1"
                class="mt-4 flex items-center justify-center gap-1"
            >
                <button
                    v-for="link in portfolios.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="goToPage(link.url, 'portfolio')"
                    class="rounded-md border px-2.5 py-1 text-xs transition-colors disabled:opacity-30"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border/50 bg-white/50 text-muted-foreground backdrop-blur-lg hover:text-foreground dark:bg-white/5'
                    "
                    v-html="paginationLabel(link.label)"
                />
            </div>
        </section>

        <!-- ===== EXPERIENCE & EDUCATION ===== -->
        <section
            v-if="sectionVisibility.experience"
            id="experience"
            class="relative z-20 mx-auto max-w-5xl px-6 py-10"
        >
            <div class="mb-6 text-center">
                <div
                    class="mb-2 inline-flex items-center gap-1.5 text-xs font-semibold tracking-widest text-primary uppercase"
                >
                    <IconSparkles class="h-3.5 w-3.5" :stroke-width="1.5" />
                    Perjalanan
                </div>
                <h2
                    class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    Pengalaman &amp; Pendidikan
                </h2>
                <div
                    class="mx-auto mt-3 h-1 w-16 rounded-full bg-gradient-to-r from-primary to-primary/30"
                />
            </div>

            <!-- Tab toggle -->
            <div class="mb-8 flex justify-center">
                <div
                    class="inline-flex rounded-xl border border-border/50 bg-white/50 p-1 backdrop-blur-lg dark:bg-white/5"
                >
                    <button
                        v-for="tab in timelineTabs"
                        :key="tab.key"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-5 py-2 text-sm font-medium transition-all"
                        :class="
                            timelineTab === tab.key
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="timelineTab = tab.key"
                    >
                        <component
                            :is="tab.icon"
                            class="h-4 w-4"
                            :stroke-width="1.5"
                        />
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Timeline -->
            <div v-if="timelineItems.length" class="relative">
                <!-- Center line (desktop) / left line (mobile) -->
                <div
                    class="absolute top-0 bottom-0 left-4 w-px bg-gradient-to-b from-primary/60 via-border to-transparent md:left-1/2 md:-translate-x-1/2"
                />

                <div class="space-y-8">
                    <div
                        v-for="(e, i) in timelineItems"
                        :key="e.id"
                        class="relative pl-12 md:grid md:grid-cols-2 md:gap-12 md:pl-0"
                    >
                        <!-- Node dot -->
                        <span
                            class="absolute top-2 left-4 z-10 flex h-4 w-4 -translate-x-1/2 items-center justify-center md:left-1/2"
                        >
                            <span
                                class="absolute inline-flex h-4 w-4 animate-ping rounded-full bg-primary/40"
                            />
                            <span
                                class="relative inline-flex h-3 w-3 rounded-full border-2 border-background bg-primary"
                            />
                        </span>

                        <!-- Card -->
                        <article
                            class="rounded-xl border border-border/50 bg-white/50 p-5 backdrop-blur-lg transition-colors hover:border-primary/30 dark:bg-white/5"
                            :class="
                                i % 2 === 0
                                    ? 'md:col-start-1'
                                    : 'md:col-start-2'
                            "
                        >
                            <div
                                class="mb-1 flex flex-wrap items-center gap-2 text-xs font-medium text-primary"
                            >
                                <span>{{ formatPeriod(e) }}</span>
                                <span
                                    v-if="e.is_current"
                                    class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-[11px] text-green-600 dark:text-green-400"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-green-500"
                                    />
                                    Aktif
                                </span>
                                <span
                                    v-if="e.employment_type"
                                    class="inline-flex items-center gap-1 rounded-full border border-primary/25 bg-primary/10 px-2 py-0.5 text-[11px] font-normal text-primary"
                                >
                                    <IconWorld
                                        v-if="e.employment_type === 'remote'"
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    {{
                                        employmentTypeLabels[
                                            e.employment_type
                                        ] ?? e.employment_type
                                    }}
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-foreground">
                                {{ e.title }}
                            </h3>
                            <div
                                class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-muted-foreground"
                            >
                                <span class="font-medium text-primary/90">{{
                                    e.institution
                                }}</span>
                                <span
                                    v-if="e.location"
                                    class="inline-flex items-center gap-1 text-[13px]"
                                >
                                    <IconMapPin
                                        class="h-3 w-3"
                                        :stroke-width="1.5"
                                    />
                                    {{ e.location }}
                                </span>
                            </div>
                            <ul
                                v-if="splitDescription(e.description).length"
                                class="mt-3 space-y-1.5"
                            >
                                <li
                                    v-for="(line, li) in splitDescription(
                                        e.description,
                                    )"
                                    :key="li"
                                    class="flex gap-2 text-[13px] leading-relaxed text-muted-foreground"
                                >
                                    <span
                                        class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-primary/50"
                                    />
                                    <span>{{ line }}</span>
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="rounded-xl border border-dashed border-border/50 bg-white/50 p-10 text-center text-sm text-muted-foreground backdrop-blur-lg dark:bg-white/5"
            >
                {{
                    timelineTab === 'work'
                        ? 'Belum ada pengalaman kerja.'
                        : 'Belum ada data pendidikan.'
                }}
            </div>
        </section>

        <!-- ===== CERTIFICATIONS ===== -->
        <section
            v-if="sectionVisibility.certifications"
            id="certifications"
            class="relative z-20 mx-auto max-w-5xl px-6 py-12"
        >
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="font-script text-2xl text-primary">{{
                        sectionMeta.certifications.script
                    }}</span>
                    <h2
                        class="text-outline mt-1 text-4xl font-bold tracking-tight sm:text-5xl"
                    >
                        {{ sectionMeta.certifications.outline }}
                    </h2>
                </div>
                <div
                    v-if="certificationCanSlide"
                    class="flex items-center gap-2"
                >
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 bg-card/50 text-muted-foreground backdrop-blur-lg transition-colors hover:border-primary/40 hover:text-primary"
                        aria-label="Sebelumnya"
                        @click="prevCertification"
                    >
                        <IconChevronLeft class="h-4 w-4" :stroke-width="1.5" />
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 bg-card/50 text-muted-foreground backdrop-blur-lg transition-colors hover:border-primary/40 hover:text-primary"
                        aria-label="Berikutnya"
                        @click="nextCertification"
                    >
                        <IconChevronRight class="h-4 w-4" :stroke-width="1.5" />
                    </button>
                </div>
            </div>

            <div
                v-if="certifications.length"
                class="overflow-hidden"
                @mouseenter="isCertificationPaused = true"
                @mouseleave="isCertificationPaused = false"
                @focusin="isCertificationPaused = true"
                @focusout="isCertificationPaused = false"
            >
                <div
                    class="-mx-1 flex transition-transform duration-500 ease-out"
                    :style="certificationTrackStyle"
                >
                    <article
                        v-for="certification in certifications"
                        :key="certification.id"
                        class="w-full shrink-0 px-1 sm:w-1/2 lg:w-1/3"
                    >
                        <div
                            class="group flex h-full flex-col overflow-hidden rounded-xl border border-border/50 bg-white/50 backdrop-blur-lg transition-colors hover:border-primary/30 dark:bg-white/5"
                        >
                            <div class="relative">
                                <StorageImage
                                    :path="
                                        certification.images[
                                            activeCertImage(certification)
                                        ]
                                    "
                                    :alt="certification.title"
                                    image-class="h-44 w-full cursor-zoom-in object-cover"
                                    @click="
                                        openCertificate(
                                            certification.images,
                                            activeCertImage(certification),
                                        )
                                    "
                                />
                                <span
                                    v-if="certification.images.length > 1"
                                    class="pointer-events-none absolute top-2 right-2 rounded-full bg-black/60 px-2 py-0.5 text-[11px] text-white backdrop-blur-sm"
                                >
                                    {{ activeCertImage(certification) + 1 }} /
                                    {{ certification.images.length }}
                                </span>

                                <template
                                    v-if="certification.images.length > 1"
                                >
                                    <button
                                        type="button"
                                        aria-label="Gambar sebelumnya"
                                        class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                                        @click.stop="
                                            setCertImage(
                                                certification.id,
                                                (activeCertImage(
                                                    certification,
                                                ) -
                                                    1 +
                                                    certification.images
                                                        .length) %
                                                    certification.images.length,
                                            )
                                        "
                                    >
                                        <IconChevronLeft
                                            class="h-4 w-4"
                                            :stroke-width="1.75"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        aria-label="Gambar berikutnya"
                                        class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                                        @click.stop="
                                            setCertImage(
                                                certification.id,
                                                (activeCertImage(
                                                    certification,
                                                ) +
                                                    1) %
                                                    certification.images.length,
                                            )
                                        "
                                    >
                                        <IconChevronRight
                                            class="h-4 w-4"
                                            :stroke-width="1.75"
                                        />
                                    </button>

                                    <div
                                        class="absolute inset-x-0 bottom-2 flex justify-center gap-1.5"
                                    >
                                        <button
                                            v-for="(
                                                img, imgIndex
                                            ) in certification.images"
                                            :key="imgIndex"
                                            type="button"
                                            :aria-label="`Gambar ${imgIndex + 1}`"
                                            class="h-1.5 rounded-full transition-all"
                                            :class="
                                                imgIndex ===
                                                activeCertImage(certification)
                                                    ? 'w-4 bg-white'
                                                    : 'w-1.5 bg-white/50 hover:bg-white/80'
                                            "
                                            @click.stop="
                                                setCertImage(
                                                    certification.id,
                                                    imgIndex,
                                                )
                                            "
                                        />
                                    </div>
                                </template>
                            </div>
                            <div class="flex h-full flex-col p-5">
                                <h3
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{ certification.title }}
                                </h3>
                                <p
                                    class="mt-1.5 line-clamp-4 text-[13px] text-muted-foreground"
                                >
                                    {{ certification.description }}
                                </p>
                                <a
                                    v-if="certification.certificate_file"
                                    :href="`/storage/${certification.certificate_file}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-auto inline-flex items-center gap-1 pt-4 text-[13px] text-primary hover:underline"
                                >
                                    <IconExternalLink
                                        class="h-3.5 w-3.5"
                                        :stroke-width="1.5"
                                    />
                                    Lihat Sertifikat
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
            <div
                v-else
                class="rounded-xl border border-dashed border-border/50 bg-white/50 p-10 text-center text-sm text-muted-foreground backdrop-blur-lg dark:bg-white/5"
            >
                Segera...
            </div>
        </section>

        <!-- ===== BLOG ===== -->
        <section
            v-if="sectionVisibility.blog"
            id="blog"
            class="relative z-20 mx-auto max-w-5xl px-6 py-12"
        >
            <div class="mb-7">
                <span class="font-script text-2xl text-primary">{{
                    sectionMeta.blog.script
                }}</span>
                <h2
                    class="text-outline mt-1 text-4xl font-bold tracking-tight sm:text-5xl"
                >
                    {{ sectionMeta.blog.outline }}
                </h2>
            </div>
            <div
                v-if="blogs.data.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-3"
            >
                <Link
                    v-for="b in blogs.data"
                    :key="b.id"
                    :href="`/blog/${b.slug}`"
                    class="group overflow-hidden rounded-xl border border-border/50 bg-card/50 backdrop-blur-lg transition-colors hover:border-primary/40"
                >
                    <img
                        v-if="b.image"
                        :src="`/storage/${b.image}`"
                        :alt="b.title"
                        class="h-40 w-full object-cover"
                    />
                    <div class="flex flex-col p-5">
                        <div
                            class="mb-1.5 flex items-center gap-1.5 text-[11px] text-muted-foreground"
                        >
                            <IconCalendar class="h-3 w-3" :stroke-width="1.5" />
                            <span v-if="b.published_at">{{
                                new Date(b.published_at).toLocaleDateString(
                                    'id',
                                )
                            }}</span>
                        </div>
                        <h3 class="text-sm font-semibold text-foreground">
                            {{ b.title }}
                        </h3>
                        <p
                            v-if="b.excerpt"
                            class="mt-1.5 line-clamp-3 text-[13px] text-muted-foreground"
                        >
                            {{ b.excerpt }}
                        </p>
                        <div class="mt-auto flex flex-wrap gap-1 pt-3">
                            <span
                                v-for="tag in b.tags"
                                :key="tag"
                                class="inline-flex items-center gap-0.5 rounded-full bg-white/60 px-2 py-0.5 text-[11px] text-secondary-foreground dark:bg-white/10"
                                ><IconTag
                                    class="h-2.5 w-2.5"
                                    :stroke-width="1.5"
                                />
                                {{ tag }}</span
                            >
                        </div>
                    </div>
                </Link>
            </div>
            <div
                v-else
                class="rounded-xl border border-dashed border-border/50 bg-white/50 p-10 text-center text-sm text-muted-foreground backdrop-blur-lg dark:bg-white/5"
            >
                Segera...
            </div>
            <div
                v-if="blogs.last_page > 1"
                class="mt-4 flex items-center justify-center gap-1"
            >
                <button
                    v-for="link in blogs.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="goToPage(link.url, 'blog')"
                    class="rounded-md border px-2.5 py-1 text-xs transition-colors disabled:opacity-30"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border/50 bg-white/50 text-muted-foreground backdrop-blur-lg hover:text-foreground dark:bg-white/5'
                    "
                    v-html="paginationLabel(link.label)"
                />
            </div>
        </section>

        <!-- ===== CONTACT ===== -->
        <section
            v-if="sectionVisibility.contact"
            id="contact"
            class="relative z-20 mx-auto max-w-5xl px-6 py-14"
        >
            <div class="mb-10 text-center">
                <span class="font-script text-3xl text-primary">{{
                    sectionMeta.contact.script
                }}</span>
                <h2
                    class="mt-1 text-4xl font-bold tracking-tight text-foreground sm:text-5xl"
                >
                    Mari Bekerja Sama
                </h2>
                <p
                    class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-muted-foreground"
                >
                    Tulis pesan untuk saya dan mari diskusikan bagaimana saya
                    bisa membantu membangun sesuatu yang luar biasa.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <!-- Info + socials -->
                <div
                    class="flex flex-col rounded-2xl border border-border/60 bg-card/50 p-6 backdrop-blur-lg lg:col-span-2"
                >
                    <h3 class="text-base font-semibold text-foreground">
                        Punya proyek dalam pikiran?
                    </h3>
                    <p
                        class="mt-2 text-[13px] leading-relaxed text-muted-foreground"
                    >
                        Ceritakan ide, kebutuhan, atau timeline Anda. Saya akan
                        bantu memetakan langkah teknisnya.
                    </p>
                    <ul
                        class="mt-5 space-y-2.5 text-[13px] text-muted-foreground"
                    >
                        <li
                            v-if="contactInfo.email"
                            class="flex items-center gap-2"
                        >
                            <IconMail
                                class="h-3.5 w-3.5 shrink-0 text-primary"
                                :stroke-width="1.5"
                            />
                            <a
                                :href="`mailto:${contactInfo.email}`"
                                class="break-all transition-colors hover:text-primary"
                                >{{ contactInfo.email }}</a
                            >
                        </li>
                        <li
                            v-if="contactInfo.phone"
                            class="flex items-center gap-2"
                        >
                            <IconPhone
                                class="h-3.5 w-3.5 shrink-0 text-primary"
                                :stroke-width="1.5"
                            />
                            <a
                                :href="`tel:${contactInfo.phone}`"
                                class="transition-colors hover:text-primary"
                                >{{ contactInfo.phone }}</a
                            >
                        </li>
                        <li
                            v-if="contactInfo.address"
                            class="flex items-start gap-2"
                        >
                            <IconMapPin
                                class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary"
                                :stroke-width="1.5"
                            />
                            <span>{{ contactInfo.address }}</span>
                        </li>
                    </ul>
                    <div
                        class="mt-4 flex items-center gap-2 text-[13px] text-muted-foreground"
                    >
                        <IconClock class="h-3.5 w-3.5" :stroke-width="1.5" />
                        Saya balas kurang dari 24 jam
                    </div>
                    <!-- Social links -->
                    <div
                        v-if="socials.length"
                        class="mt-auto flex flex-wrap gap-2 pt-6"
                    >
                        <a
                            v-for="s in socials"
                            :key="s.network"
                            :href="s.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-full border border-border/60 bg-card/50 p-2.5 text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
                            :aria-label="s.network"
                        >
                            <component
                                :is="socialIcons[s.network]"
                                class="h-4 w-4"
                                :stroke-width="1.5"
                            />
                        </a>
                    </div>
                </div>
                <!-- Form -->
                <form
                    class="rounded-xl border border-border/50 bg-white/50 p-6 backdrop-blur-lg lg:col-span-3 dark:bg-white/5"
                    @submit.prevent="submitContact"
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                for="c-name"
                                class="mb-1 block text-[13px] font-medium text-foreground"
                                >Nama</label
                            >
                            <input
                                id="c-name"
                                v-model="contactForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-white/60 px-3 py-2 text-sm text-foreground backdrop-blur placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white/5"
                                placeholder="Nama Anda"
                            />
                            <span
                                v-if="contactForm.errors.name"
                                class="mt-1 text-xs text-destructive"
                                >{{ contactForm.errors.name }}</span
                            >
                        </div>
                        <div>
                            <label
                                for="c-email"
                                class="mb-1 block text-[13px] font-medium text-foreground"
                                >Email</label
                            >
                            <input
                                id="c-email"
                                v-model="contactForm.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-input bg-white/60 px-3 py-2 text-sm text-foreground backdrop-blur placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white/5"
                                placeholder="tu@email.com"
                            />
                            <span
                                v-if="contactForm.errors.email"
                                class="mt-1 text-xs text-destructive"
                                >{{ contactForm.errors.email }}</span
                            >
                        </div>
                    </div>
                    <div class="mt-3">
                        <label
                            for="c-subject"
                            class="mb-1 block text-[13px] font-medium text-foreground"
                            >Subjek</label
                        >
                        <input
                            id="c-subject"
                            v-model="contactForm.subject"
                            type="text"
                            class="w-full rounded-lg border border-input bg-white/60 px-3 py-2 text-sm text-foreground backdrop-blur placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white/5"
                            placeholder="Ingin membahas apa?"
                        />
                    </div>
                    <div class="mt-3">
                        <label
                            for="c-message"
                            class="mb-1 block text-[13px] font-medium text-foreground"
                            >Pesan</label
                        >
                        <textarea
                            id="c-message"
                            v-model="contactForm.message"
                            required
                            rows="3"
                            class="w-full resize-none rounded-lg border border-input bg-white/60 px-3 py-2 text-sm text-foreground backdrop-blur placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white/5"
                            placeholder="Ceritakan tentang proyek Anda..."
                        />
                        <span
                            v-if="contactForm.errors.message"
                            class="mt-1 text-xs text-destructive"
                            >{{ contactForm.errors.message }}</span
                        >
                    </div>
                    <button
                        type="submit"
                        :disabled="contactForm.processing"
                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50"
                    >
                        <IconSend class="h-3.5 w-3.5" :stroke-width="1.5" />{{
                            contactForm.processing
                                ? 'Mengirim...'
                                : 'Kirim pesan'
                        }}
                    </button>
                </form>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-20 mt-12 border-t border-border/40 py-12">
            <div
                class="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-6 sm:grid-cols-[1.6fr_1fr_1fr]"
            >
                <div>
                    <SiteLogo img-class="h-9 w-9" />
                    <p
                        class="mt-4 max-w-xs text-[13px] leading-relaxed text-muted-foreground"
                    >
                        {{ heroSubtitle }}
                    </p>
                </div>
                <div>
                    <p
                        class="mb-3 text-[11px] font-semibold tracking-widest text-muted-foreground/70 uppercase"
                    >
                        Halaman
                    </p>
                    <ul class="space-y-2 text-[13px] text-muted-foreground">
                        <li v-for="s in visibleFooterSections" :key="s.id">
                            <button
                                class="transition-colors hover:text-primary"
                                @click="scrollTo(s.id)"
                            >
                                {{ s.label }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div>
                    <p
                        class="mb-3 text-[11px] font-semibold tracking-widest text-muted-foreground/70 uppercase"
                    >
                        Sosial
                    </p>
                    <ul
                        class="space-y-2 text-[13px] text-muted-foreground capitalize"
                    >
                        <li v-for="s in socials" :key="s.network">
                            <a
                                :href="s.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="transition-colors hover:text-primary"
                                >{{ s.network }}</a
                            >
                        </li>
                        <li
                            v-if="!socials.length"
                            class="text-muted-foreground/60"
                        >
                            Belum ada tautan
                        </li>
                    </ul>
                </div>
            </div>
            <div
                class="mx-auto mt-10 max-w-5xl border-t border-border/30 px-6 pt-6 text-[12px] text-muted-foreground/70"
            >
                &copy; {{ new Date().getFullYear() }} — Dibuat dengan Laravel +
                Vue
            </div>
        </footer>

        <!-- Scroll to top -->
        <button
            class="fixed right-5 bottom-5 z-30 rounded-full border border-border/50 bg-white/50 p-2.5 shadow-lg backdrop-blur-lg transition-all hover:bg-white/70 dark:bg-white/5 dark:hover:bg-white/10"
            @click="scrollTo('about')"
            aria-label="Kembali ke atas"
        >
            <IconArrowUp class="h-4 w-4 text-foreground" :stroke-width="1.5" />
        </button>

        <!-- Lightbox galeri sertifikasi -->
        <Teleport to="body">
            <div
                v-if="lightbox"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
                @click="closeLightbox"
            >
                <img
                    :src="`/storage/${lightbox.images[lightbox.index]}`"
                    alt="Pratinjau sertifikat"
                    class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl"
                    @click.stop
                />

                <button
                    type="button"
                    class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                    aria-label="Tutup"
                    @click="closeLightbox"
                >
                    <IconX class="h-5 w-5" :stroke-width="1.5" />
                </button>

                <template v-if="lightbox.images.length > 1">
                    <button
                        type="button"
                        class="absolute left-3 rounded-full bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20"
                        aria-label="Gambar sebelumnya"
                        @click.stop="stepLightbox(-1)"
                    >
                        <IconChevronLeft class="h-5 w-5" :stroke-width="1.75" />
                    </button>
                    <button
                        type="button"
                        class="absolute right-3 rounded-full bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20"
                        aria-label="Gambar berikutnya"
                        @click.stop="stepLightbox(1)"
                    >
                        <IconChevronRight
                            class="h-5 w-5"
                            :stroke-width="1.75"
                        />
                    </button>
                    <span
                        class="absolute bottom-5 rounded-full bg-white/10 px-3 py-1 text-xs text-white"
                    >
                        {{ lightbox.index + 1 }} / {{ lightbox.images.length }}
                    </span>
                </template>
            </div>
        </Teleport>
    </main>
</template>
