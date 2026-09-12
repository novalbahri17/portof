<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import FileInput from '@/components/FileInput.vue';
import RichEditor from '@/components/RichEditor.vue';
import StorageImage from '@/components/StorageImage.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { resolveTablerIcon } from '@/lib/tabler-icons';
import { technologyCatalog } from '@/lib/technology-catalog';
import type { TechnologyCatalogItem } from '@/lib/technology-catalog';

type Technology = {
    key: string;
    name: string;
    icon: string;
};

type Project = {
    id: number;
    title: string;
    slug: string;
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
    type: string;
    featured: boolean;
    sort_order: number;
    published: boolean;
};

defineProps<{ projects: Project[] }>();
const showForm = ref(false);
const editing = ref<Project | null>(null);
const existingGallery = ref<string[]>([]);
const imagePicker = ref<InstanceType<typeof FileInput> | null>(null);
const galleryPicker = ref<InstanceType<typeof FileInput> | null>(null);

const form = useForm({
    title: '',
    description: '',
    challenge: '',
    solution: '',
    results: '',
    image: null as File | null,
    gallery_images: [] as File[],
    url: '',
    repo_url: '',
    tags: [] as string[],
    features: [] as string[],
    technologies: [] as Technology[],
    type: 'portfolio' as 'side_project' | 'portfolio',
    featured: false,
    sort_order: 0,
    published: false,
});

const tagInput = ref('');
const featureInput = ref('');
const technologySearch = ref('');

const filteredTechnologies = computed(() => {
    const term = technologySearch.value.trim().toLowerCase();
    if (!term) return technologyCatalog;
    return technologyCatalog.filter(
        (item) =>
            item.name.toLowerCase().includes(term) ||
            item.key.toLowerCase().includes(term),
    );
});

function addTag() {
    const value = tagInput.value.trim();
    if (value && !form.tags.includes(value)) form.tags.push(value);
    tagInput.value = '';
}

function removeTag(tag: string) {
    form.tags = form.tags.filter((t) => t !== tag);
}

function addFeature() {
    const value = featureInput.value.trim();
    if (value && !form.features.includes(value)) form.features.push(value);
    featureInput.value = '';
}

function removeFeature(feature: string) {
    form.features = form.features.filter((f) => f !== feature);
}

function hasTechnology(techKey: string) {
    return form.technologies.some((tech) => tech.key === techKey);
}

function toggleTechnology(item: TechnologyCatalogItem) {
    if (hasTechnology(item.key)) {
        form.technologies = form.technologies.filter(
            (tech) => tech.key !== item.key,
        );
        return;
    }

    form.technologies.push({
        key: item.key,
        name: item.name,
        icon: item.icon,
    });
}

function removeTechnology(techKey: string) {
    form.technologies = form.technologies.filter(
        (tech) => tech.key !== techKey,
    );
}

function handleGalleryChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.gallery_images = Array.from(input.files ?? []);
}

function openCreate() {
    editing.value = null;
    existingGallery.value = [];
    form.reset();
    form.technologies = [];
    form.tags = [];
    form.features = [];
    form.gallery_images = [];
    imagePicker.value?.reset();
    galleryPicker.value?.reset();
    showForm.value = true;
}

function openEdit(project: Project) {
    editing.value = project;
    existingGallery.value = project.gallery || [];
    form.title = project.title;
    form.description = project.description;
    form.challenge = project.challenge || '';
    form.solution = project.solution || '';
    form.results = project.results || '';
    form.url = project.url || '';
    form.repo_url = project.repo_url || '';
    form.tags = project.tags || [];
    form.features = project.features || [];
    form.technologies = project.technologies || [];
    form.type = project.type as 'side_project' | 'portfolio';
    form.featured = project.featured;
    form.sort_order = project.sort_order;
    form.published = project.published;
    form.image = null;
    form.gallery_images = [];
    imagePicker.value?.reset();
    galleryPicker.value?.reset();
    showForm.value = true;
}

function submit() {
    const url = editing.value
        ? `/admin/projects/${editing.value.id}`
        : '/admin/projects';
    const opts = {
        forceFormData: true,
        onSuccess: () => {
            showForm.value = false;
            existingGallery.value = [];
            form.reset();
            form.technologies = [];
            form.tags = [];
            form.features = [];
            form.gallery_images = [];
            imagePicker.value?.reset();
            galleryPicker.value?.reset();
        },
    };

    if (editing.value) {
        form.post(url, {
            ...opts,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
        });
    } else {
        form.post(url, opts);
    }
}

function destroy(id: number) {
    if (confirm('Hapus proyek ini?')) router.delete(`/admin/projects/${id}`);
}
</script>

<template>
    <Head title="Proyek" />
    <AdminLayout>
        <template #title>Proyek</template>

        <div class="mb-6">
            <button
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" /> Proyek baru
            </button>
        </div>

        <div
            v-if="showForm"
            class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <h3 class="mb-4 text-lg font-semibold text-foreground">
                {{ editing ? 'Edit' : 'Tambah' }} proyek
            </h3>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Judul</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Tipe</label
                        >
                        <select
                            v-model="form.type"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        >
                            <option value="portfolio">Portofolio</option>
                            <option value="side_project">Side Project</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-foreground"
                        >Ringkasan proyek</label
                    >
                    <RichEditor
                        v-model="form.description"
                        :height="180"
                        placeholder="Jelaskan proyek ini secara singkat..."
                        hint="Pisahkan antar paragraf dengan satu baris kosong."
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Masalah / konteks</label
                        >
                        <textarea
                            v-model="form.challenge"
                            rows="4"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Solusi</label
                        >
                        <textarea
                            v-model="form.solution"
                            rows="4"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Hasil</label
                        >
                        <textarea
                            v-model="form.results"
                            rows="4"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >URL</label
                        >
                        <input
                            v-model="form.url"
                            type="url"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Repo URL</label
                        >
                        <input
                            v-model="form.repo_url"
                            type="url"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Gambar utama</label
                        >
                        <FileInput
                            ref="imagePicker"
                            label="Pilih gambar"
                            accept="image/*"
                            :error="form.errors.image"
                            @change="
                                (e: Event) =>
                                    (form.image =
                                        ((e.target as HTMLInputElement).files ??
                                            [])[0] || null)
                            "
                        />
                        <StorageImage
                            v-if="editing?.image"
                            :path="editing.image"
                            alt="Gambar utama saat ini"
                            image-class="mt-2 h-20 w-32 rounded-lg border border-border object-cover"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Galeri (multi-upload)</label
                        >
                        <FileInput
                            ref="galleryPicker"
                            label="Pilih gambar"
                            accept="image/*"
                            multiple
                            hint="Bisa pilih beberapa gambar sekaligus."
                            @change="handleGalleryChange"
                        />
                    </div>
                </div>

                <div v-if="existingGallery.length" class="space-y-2">
                    <label class="block text-sm font-medium text-foreground"
                        >Galeri saat ini</label
                    >
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <StorageImage
                            v-for="img in existingGallery"
                            :key="img"
                            :path="img"
                            alt="Gambar galeri"
                            :show-label="false"
                            image-class="h-20 w-full rounded-md border border-border object-cover"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Gambar baru akan ditambahkan tanpa menghapus yang sudah
                        ada.
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-foreground"
                        >Tags</label
                    >
                    <div class="flex gap-2">
                        <input
                            v-model="tagInput"
                            type="text"
                            class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Tambah tag"
                            @keydown.enter.prevent="addTag"
                        />
                        <button
                            type="button"
                            class="rounded-lg bg-secondary px-3 py-2 text-sm"
                            @click="addTag"
                        >
                            +
                        </button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span
                            v-for="tag in form.tags"
                            :key="tag"
                            class="inline-flex items-center gap-1 rounded-full bg-secondary px-2 py-0.5 text-xs"
                        >
                            {{ tag }}
                            <button
                                type="button"
                                class="hover:text-destructive"
                                @click="removeTag(tag)"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-foreground"
                        >Fitur (bullet)</label
                    >
                    <div class="flex gap-2">
                        <input
                            v-model="featureInput"
                            type="text"
                            class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Tambah fitur"
                            @keydown.enter.prevent="addFeature"
                        />
                        <button
                            type="button"
                            class="rounded-lg bg-secondary px-3 py-2 text-sm"
                            @click="addFeature"
                        >
                            +
                        </button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span
                            v-for="feature in form.features"
                            :key="feature"
                            class="inline-flex items-center gap-1 rounded-full bg-secondary px-2 py-0.5 text-xs"
                        >
                            {{ feature }}
                            <button
                                type="button"
                                class="hover:text-destructive"
                                @click="removeFeature(feature)"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                </div>

                <div class="space-y-3 rounded-lg border border-border p-4">
                    <label class="block text-sm font-medium text-foreground"
                        >Teknologi (Tabler Icons)</label
                    >

                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <input
                            v-model="technologySearch"
                            type="text"
                            class="w-full rounded-lg border border-input bg-background py-2 pr-3 pl-8 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Cari teknologi..."
                        />
                    </div>

                    <div
                        class="max-h-40 overflow-y-auto rounded-md border border-border"
                    >
                        <button
                            v-for="item in filteredTechnologies"
                            :key="item.key"
                            type="button"
                            class="flex w-full items-center justify-between border-b border-border px-3 py-2 text-left text-sm last:border-b-0 hover:bg-muted/50"
                            @click="toggleTechnology(item)"
                        >
                            <span class="inline-flex items-center gap-2">
                                <component
                                    :is="resolveTablerIcon(item.icon)"
                                    class="h-4 w-4 text-primary"
                                />
                                {{ item.name }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{
                                hasTechnology(item.key) ? 'Terpilih' : 'Pilih'
                            }}</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tech in form.technologies"
                            :key="tech.key"
                            class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-background px-2.5 py-1 text-xs"
                        >
                            <component
                                :is="resolveTablerIcon(tech.icon)"
                                class="h-3.5 w-3.5 text-primary"
                            />
                            {{ tech.name }}
                            <button
                                type="button"
                                class="hover:text-destructive"
                                @click="removeTechnology(tech.key)"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm"
                        ><input
                            v-model="form.published"
                            type="checkbox"
                            class="rounded"
                        />
                        Diterbitkan</label
                    >
                    <label class="flex items-center gap-2 text-sm"
                        ><input
                            v-model="form.featured"
                            type="checkbox"
                            class="rounded"
                        />
                        Unggulan</label
                    >
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Urutan:</label>
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            class="w-20 rounded-lg border border-input bg-background px-2 py-1 text-sm"
                        />
                    </div>
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-border px-4 py-2 text-sm"
                        @click="showForm = false"
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left font-medium text-muted-foreground"
                        >
                            Judul
                        </th>
                        <th
                            class="px-4 py-3 text-left font-medium text-muted-foreground"
                        >
                            Tipe
                        </th>
                        <th
                            class="px-4 py-3 text-left font-medium text-muted-foreground"
                        >
                            Status
                        </th>
                        <th
                            class="px-4 py-3 text-right font-medium text-muted-foreground"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="project in projects"
                        :key="project.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ project.title }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="
                                    project.type === 'side_project'
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                        : 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'
                                "
                                class="rounded-full px-2 py-0.5 text-xs"
                            >
                                {{
                                    project.type === 'side_project'
                                        ? 'Side Project'
                                        : 'Portofolio'
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="
                                    project.published
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                                "
                                class="rounded-full px-2 py-0.5 text-xs"
                            >
                                {{ project.published ? 'Terbit' : 'Draf' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button
                                class="mr-2 text-muted-foreground hover:text-foreground"
                                @click="openEdit(project)"
                            >
                                <Pencil class="h-4 w-4" />
                            </button>
                            <button
                                class="text-muted-foreground hover:text-destructive"
                                @click="destroy(project.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!projects.length">
                        <td
                            colspan="4"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Belum ada proyek.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
