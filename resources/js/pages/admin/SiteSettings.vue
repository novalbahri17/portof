<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    IconBrandGithub,
    IconBrandLinkedin,
    IconBrandX,
    IconBrandInstagram,
    IconBrandYoutube,
    IconBrandTiktok,
    IconBrandDiscord,
    IconWorld,
    IconTrash,
} from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import FileInput from '@/components/FileInput.vue';
import RichEditor from '@/components/RichEditor.vue';
import StorageImage from '@/components/StorageImage.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const socialFields = [
    {
        key: 'social_github',
        label: 'GitHub',
        icon: IconBrandGithub,
        placeholder: 'https://github.com/usuario',
    },
    {
        key: 'social_linkedin',
        label: 'LinkedIn',
        icon: IconBrandLinkedin,
        placeholder: 'https://linkedin.com/in/usuario',
    },
    {
        key: 'social_twitter',
        label: 'X / Twitter',
        icon: IconBrandX,
        placeholder: 'https://x.com/usuario',
    },
    {
        key: 'social_instagram',
        label: 'Instagram',
        icon: IconBrandInstagram,
        placeholder: 'https://instagram.com/usuario',
    },
    {
        key: 'social_youtube',
        label: 'YouTube',
        icon: IconBrandYoutube,
        placeholder: 'https://youtube.com/@canal',
    },
    {
        key: 'social_tiktok',
        label: 'TikTok',
        icon: IconBrandTiktok,
        placeholder: 'https://tiktok.com/@usuario',
    },
    {
        key: 'social_discord',
        label: 'Discord',
        icon: IconBrandDiscord,
        placeholder: 'https://discord.gg/invite',
    },
    {
        key: 'social_website',
        label: 'Situs web',
        icon: IconWorld,
        placeholder: 'https://situsaya.com',
    },
] as const;

const props = defineProps<{
    settings: Record<string, string>;
}>();

const sectionFields = [
    { key: 'section_about_visible', label: 'Tentang Saya' },
    { key: 'section_projects_visible', label: 'Side Projects' },
    { key: 'section_portfolio_visible', label: 'Portofolio' },
    { key: 'section_certifications_visible', label: 'Sertifikasi' },
    { key: 'section_experience_visible', label: 'Pengalaman & Pendidikan' },
    { key: 'section_blog_visible', label: 'Blog' },
    { key: 'section_contact_visible', label: 'Kontak' },
] as const;

const form = useForm({
    hero_title: props.settings.hero_title || '',
    hero_subtitle: props.settings.hero_subtitle || '',
    hero_badge: props.settings.hero_badge || '',
    hero_image_shape: props.settings.hero_image_shape || 'circle',
    hero_image_size: props.settings.hero_image_size || '112',
    about: props.settings.about || '',
    hobbies: props.settings.hobbies || '',
    social_github: props.settings.social_github || '',
    social_linkedin: props.settings.social_linkedin || '',
    social_twitter: props.settings.social_twitter || '',
    social_instagram: props.settings.social_instagram || '',
    social_youtube: props.settings.social_youtube || '',
    social_tiktok: props.settings.social_tiktok || '',
    social_discord: props.settings.social_discord || '',
    social_website: props.settings.social_website || '',
    section_about_visible: props.settings.section_about_visible ?? '1',
    section_projects_visible: props.settings.section_projects_visible ?? '1',
    section_portfolio_visible: props.settings.section_portfolio_visible ?? '1',
    section_certifications_visible:
        props.settings.section_certifications_visible ?? '1',
    section_experience_visible:
        props.settings.section_experience_visible ?? '1',
    section_blog_visible: props.settings.section_blog_visible ?? '1',
    section_contact_visible: props.settings.section_contact_visible ?? '1',
    seo_title: props.settings.seo_title || '',
    seo_description: props.settings.seo_description || '',
    seo_keywords: props.settings.seo_keywords || '',
    seo_canonical: props.settings.seo_canonical || '',
    og_title: props.settings.og_title || '',
    og_description: props.settings.og_description || '',
    og_type: props.settings.og_type || 'website',
    twitter_card: props.settings.twitter_card || 'summary_large_image',
    twitter_title: props.settings.twitter_title || '',
    twitter_description: props.settings.twitter_description || '',
});

const ogImagePreview = ref(props.settings.og_image || '');
const twitterImagePreview = ref(props.settings.twitter_image || '');
const faviconPreview = ref(props.settings.favicon || '');
const heroImagePreview = ref(props.settings.hero_image || '');
const logoLightPreview = ref(props.settings.logo_light || '');
const uploadingImage = ref(false);
const heroImageSizePx = computed(() => {
    const size = Number(form.hero_image_size);
    if (!Number.isFinite(size)) return 112;
    return Math.min(240, Math.max(64, Math.round(size)));
});
const heroImageShapeClass = computed(() => {
    if (form.hero_image_shape === 'square') return 'rounded-none';
    if (form.hero_image_shape === 'rounded') return 'rounded-2xl';
    return 'rounded-full';
});

function save() {
    form.put('/admin/settings');
}

function uploadSeoImage(
    event: Event,
    type:
        | 'og_image'
        | 'twitter_image'
        | 'favicon'
        | 'hero_image'
        | 'logo_light',
) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    uploadingImage.value = true;
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', type);
    router.post('/admin/settings/seo-image', formData, {
        preserveScroll: true,
        onSuccess: () => {
            const url = URL.createObjectURL(file);
            if (type === 'og_image') ogImagePreview.value = url;
            else if (type === 'twitter_image') twitterImagePreview.value = url;
            else if (type === 'favicon') faviconPreview.value = url;
            else if (type === 'logo_light') logoLightPreview.value = url;
            else heroImagePreview.value = url;
        },
        onFinish: () => {
            uploadingImage.value = false;
        },
    });
}

function deleteSeoImage(
    type:
        | 'og_image'
        | 'twitter_image'
        | 'favicon'
        | 'hero_image'
        | 'logo_light',
) {
    router.delete('/admin/settings/seo-image', {
        data: { type },
        preserveScroll: true,
        onSuccess: () => {
            if (type === 'og_image') ogImagePreview.value = '';
            else if (type === 'twitter_image') twitterImagePreview.value = '';
            else if (type === 'favicon') faviconPreview.value = '';
            else if (type === 'logo_light') logoLightPreview.value = '';
            else heroImagePreview.value = '';
        },
    });
}
</script>

<template>
    <Head title="Pengaturan Situs" />
    <AdminLayout>
        <template #title>Pengaturan Situs</template>
        <form class="max-w-4xl space-y-6" @submit.prevent="save">
            <!-- Section Visibility -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Visibilitas bagian
                </h3>
                <p class="text-xs text-muted-foreground">
                    Pilih bagian mana yang ditampilkan di portofolio publik
                    Anda. Bagian yang disembunyikan tidak akan muncul di navbar
                    maupun halaman.
                </p>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <label
                        v-for="s in sectionFields"
                        :key="s.key"
                        class="flex cursor-pointer items-center justify-between rounded-lg border border-input px-4 py-3 transition-colors"
                        :class="
                            (form as any)[s.key] === '1'
                                ? 'border-primary/30 bg-primary/5'
                                : 'bg-background'
                        "
                    >
                        <span class="text-sm font-medium text-foreground">{{
                            s.label
                        }}</span>
                        <button
                            type="button"
                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors"
                            :class="
                                (form as any)[s.key] === '1'
                                    ? 'bg-primary'
                                    : 'bg-muted'
                            "
                            @click="
                                (form as any)[s.key] =
                                    (form as any)[s.key] === '1' ? '0' : '1'
                            "
                        >
                            <span
                                class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform"
                                :class="
                                    (form as any)[s.key] === '1'
                                        ? 'translate-x-4'
                                        : 'translate-x-0.5'
                                "
                            />
                        </button>
                    </label>
                </div>
            </div>

            <!-- Hero -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Hero / Judul Utama
                </h3>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Badge</label
                    >
                    <input
                        v-model="form.hero_badge"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Full Stack Developer"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Foto profil hero</label
                    >
                    <p class="mb-2 text-xs text-muted-foreground">
                        Format disarankan: gambar persegi (mis. 500×500).
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            v-if="heroImagePreview"
                            class="flex items-center gap-3"
                        >
                            <StorageImage
                                :path="heroImagePreview"
                                alt="Hero profile"
                                image-class="border-2 border-primary/40 object-cover"
                                :class="heroImageShapeClass"
                                :style="{
                                    width: `${heroImageSizePx}px`,
                                    height: `${heroImageSizePx}px`,
                                }"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-md border border-destructive/30 px-2.5 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/10"
                                @click="deleteSeoImage('hero_image')"
                            >
                                <IconTrash
                                    class="h-3.5 w-3.5"
                                    :stroke-width="1.5"
                                />
                                Hapus
                            </button>
                        </div>
                        <FileInput
                            label="Unggah foto"
                            accept="image/*"
                            :show-selection="false"
                            @change="uploadSeoImage($event, 'hero_image')"
                        />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-foreground"
                            >Bentuk foto</label
                        >
                        <select
                            v-model="form.hero_image_shape"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        >
                            <option value="circle">Bulat</option>
                            <option value="square">Persegi</option>
                            <option value="rounded">Sudut membulat</option>
                        </select>
                    </div>
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label
                                class="block text-sm font-medium text-foreground"
                                >Ukuran</label
                            >
                            <span class="text-xs text-muted-foreground"
                                >{{ heroImageSizePx }} px</span
                            >
                        </div>
                        <input
                            v-model="form.hero_image_size"
                            type="range"
                            min="64"
                            max="240"
                            step="4"
                            class="w-full accent-primary"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Judul utama</label
                    >
                    <input
                        v-model="form.hero_title"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Halo, saya Seorang Developer Laravel"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Subjudul</label
                    >
                    <textarea
                        v-model="form.hero_subtitle"
                        rows="2"
                        class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Membangun pengalaman web..."
                    />
                </div>
            </div>

            <!-- About & Hobbies -->
            <div>
                <label class="mb-2 block text-sm font-medium text-foreground"
                    >Tentang Saya</label
                >
                <RichEditor
                    v-model="form.about"
                    :height="220"
                    placeholder="Ceritakan tentang diri Anda..."
                    hint="Pisahkan antar paragraf dengan satu baris kosong."
                />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-foreground"
                    >Hobbies</label
                >
                <RichEditor
                    v-model="form.hobbies"
                    :height="180"
                    placeholder="Satu hobi per baris, contoh: Membaca — Buku tentang arsitektur software"
                    hint="Satu hobi per baris. Gunakan format: Judul — Keterangan (tanda pisah opsional)."
                />
            </div>

            <!-- Social Links -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Media sosial
                </h3>
                <p class="text-xs text-muted-foreground">
                    Kosongkan untuk menyembunyikan. Hanya yang memiliki URL yang
                    ditampilkan.
                </p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="s in socialFields"
                        :key="s.key"
                        class="flex items-center gap-2"
                    >
                        <component
                            :is="s.icon"
                            class="h-5 w-5 shrink-0 text-muted-foreground"
                            :stroke-width="1.5"
                        />
                        <input
                            v-model="(form as any)[s.key]"
                            type="url"
                            class="w-full rounded-lg border border-input bg-background px-3 py-1.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            :placeholder="s.placeholder"
                        />
                    </div>
                </div>
            </div>

            <!-- SEO General -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">SEO</h3>
                <p class="text-xs text-muted-foreground">
                    Atur judul, deskripsi, dan keyword untuk mesin pencari.
                </p>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Judul SEO</label
                    >
                    <input
                        v-model="form.seo_title"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Portofolio Saya — Developer Full Stack"
                    />
                    <span class="mt-1 block text-xs text-muted-foreground"
                        >{{ form.seo_title.length }}/200 karakter</span
                    >
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Meta deskripsi</label
                    >
                    <textarea
                        v-model="form.seo_description"
                        rows="2"
                        class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Developer web yang berspesialisasi di Laravel dan Vue.js..."
                    />
                    <span class="mt-1 block text-xs text-muted-foreground"
                        >{{ form.seo_description.length }}/500 karakter</span
                    >
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Keywords</label
                    >
                    <input
                        v-model="form.seo_keywords"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="laravel, vue, developer, portofolio"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >URL kanonik</label
                    >
                    <input
                        v-model="form.seo_canonical"
                        type="url"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="https://situsaya.com"
                    />
                </div>
            </div>

            <!-- Logo -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Logo situs
                </h3>
                <p class="text-xs text-muted-foreground">
                    Logo ini tampil di navbar publik, sidebar admin, dan halaman
                    login. Jika tidak diunggah, logo bawaan akan digunakan.
                </p>
                <div class="space-y-3 rounded-lg border border-input p-4">
                    <div>
                        <p class="text-sm font-medium text-foreground">Logo</p>
                        <p class="text-xs text-muted-foreground">
                            Tampil di latar gelap. Gunakan logo versi
                            terang/putih.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            v-if="logoLightPreview"
                            class="flex items-center gap-2"
                        >
                            <StorageImage
                                :path="logoLightPreview"
                                alt="Logo"
                                image-class="h-10 rounded border border-border bg-white object-contain p-1"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-md border border-destructive/30 px-2.5 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/10"
                                @click="deleteSeoImage('logo_light')"
                            >
                                <IconTrash
                                    class="h-3.5 w-3.5"
                                    :stroke-width="1.5"
                                />
                                Hapus
                            </button>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <img
                                src="/logo-code.svg"
                                alt="Logo bawaan"
                                class="h-10 w-10 rounded"
                            />
                            <span class="text-xs text-muted-foreground"
                                >Logo bawaan (ikon kode)</span
                            >
                        </div>
                        <FileInput
                            label="Unggah logo"
                            accept="image/*"
                            :show-selection="false"
                            @change="uploadSeoImage($event, 'logo_light')"
                        />
                    </div>
                </div>
            </div>

            <!-- Favicon -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">Favicon</h3>
                <p class="text-xs text-muted-foreground">
                    Unggah favicon kustom (disarankan: 32×32 atau 512×512 PNG).
                </p>
                <div class="flex items-center gap-4">
                    <div v-if="faviconPreview" class="flex items-center gap-3">
                        <StorageImage
                            :path="faviconPreview"
                            :show-label="false"
                            alt="Favicon"
                            image-class="h-10 w-10 rounded border border-border object-contain"
                        />
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-destructive/30 px-2.5 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/10"
                            @click="deleteSeoImage('favicon')"
                        >
                            <IconTrash
                                class="h-3.5 w-3.5"
                                :stroke-width="1.5"
                            />
                            Hapus
                        </button>
                    </div>
                    <FileInput
                        label="Unggah favicon"
                        accept="image/*"
                        :show-selection="false"
                        @change="uploadSeoImage($event, 'favicon')"
                    />
                </div>
            </div>

            <!-- Open Graph -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Open Graph (Facebook, LinkedIn, etc.)
                </h3>
                <p class="text-xs text-muted-foreground">
                    Atur tampilan situs Anda saat dibagikan ke media sosial.
                    Jika dibiarkan kosong, nilai SEO akan digunakan.
                </p>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Judul OG</label
                    >
                    <input
                        v-model="form.og_title"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Gunakan judul SEO jika kosong"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Deskripsi OG</label
                    >
                    <textarea
                        v-model="form.og_description"
                        rows="2"
                        class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Gunakan meta deskripsi jika kosong"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >OG Tipo</label
                    >
                    <select
                        v-model="form.og_type"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                    >
                        <option value="website">website</option>
                        <option value="article">article</option>
                        <option value="profile">profile</option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Gambar OG</label
                    >
                    <p class="mb-2 text-xs text-muted-foreground">
                        Disarankan: 1200×630px
                    </p>
                    <div class="flex items-start gap-4">
                        <div
                            v-if="ogImagePreview"
                            class="flex flex-col items-center gap-2"
                        >
                            <StorageImage
                                :path="ogImagePreview"
                                :show-label="false"
                                alt="OG Image"
                                image-class="h-24 w-44 rounded border border-border object-cover"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-md border border-destructive/30 px-2.5 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/10"
                                @click="deleteSeoImage('og_image')"
                            >
                                <IconTrash
                                    class="h-3.5 w-3.5"
                                    :stroke-width="1.5"
                                />
                                Hapus
                            </button>
                        </div>
                        <FileInput
                            label="Unggah gambar"
                            accept="image/*"
                            :show-selection="false"
                            @change="uploadSeoImage($event, 'og_image')"
                        />
                    </div>
                </div>
            </div>

            <!-- Twitter Card -->
            <div class="space-y-4 rounded-lg border border-border p-5">
                <h3 class="text-sm font-semibold text-foreground">
                    Twitter Card
                </h3>
                <p class="text-xs text-muted-foreground">
                    Atur tampilan situs Anda saat dibagikan ke X/Twitter. Jika
                    dibiarkan kosong, nilai Open Graph akan digunakan.
                </p>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Tipo de card</label
                    >
                    <select
                        v-model="form.twitter_card"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                    >
                        <option value="summary_large_image">
                            summary_large_image
                        </option>
                        <option value="summary">summary</option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Judul Twitter</label
                    >
                    <input
                        v-model="form.twitter_title"
                        type="text"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Gunakan judul OG jika kosong"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Deskripsi Twitter</label
                    >
                    <textarea
                        v-model="form.twitter_description"
                        rows="2"
                        class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Gunakan deskripsi OG jika kosong"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Gambar Twitter</label
                    >
                    <p class="mb-2 text-xs text-muted-foreground">
                        Jika tidak diunggah, gambar Open Graph akan digunakan.
                    </p>
                    <div class="flex items-start gap-4">
                        <div
                            v-if="twitterImagePreview"
                            class="flex flex-col items-center gap-2"
                        >
                            <StorageImage
                                :path="twitterImagePreview"
                                :show-label="false"
                                alt="Twitter Image"
                                image-class="h-24 w-44 rounded border border-border object-cover"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-md border border-destructive/30 px-2.5 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/10"
                                @click="deleteSeoImage('twitter_image')"
                            >
                                <IconTrash
                                    class="h-3.5 w-3.5"
                                    :stroke-width="1.5"
                                />
                                Hapus
                            </button>
                        </div>
                        <FileInput
                            label="Unggah gambar"
                            accept="image/*"
                            :show-selection="false"
                            @change="uploadSeoImage($event, 'twitter_image')"
                        />
                    </div>
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan perubahan' }}
            </button>
        </form>
    </AdminLayout>
</template>
