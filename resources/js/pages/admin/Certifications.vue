<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, FileText, X, ImagePlus } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import FileInput from '@/components/FileInput.vue';
import StorageImage from '@/components/StorageImage.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Certification = {
    id: number;
    title: string;
    description: string;
    images: string[];
    certificate_file: string | null;
    published: boolean;
    sort_order: number;
};

defineProps<{ certifications: Certification[] }>();
const showForm = ref(false);
const editing = ref<Certification | null>(null);
const picker = ref<InstanceType<typeof FileInput> | null>(null);

const form = useForm({
    title: '',
    description: '',
    images: [] as File[],
    keep_images: [] as string[],
    certificate_file: null as File | null,
    published: false,
    sort_order: 0,
});

/** Object URL untuk pratinjau gambar yang baru dipilih. */
const previews = computed(() =>
    form.images.map((file) => ({
        name: file.name,
        url: URL.createObjectURL(file),
    })),
);

function resetFileInput() {
    picker.value?.reset();
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.images = [];
    form.keep_images = [];
    form.certificate_file = null;
    resetFileInput();
    showForm.value = true;
}

function openEdit(certification: Certification) {
    editing.value = certification;
    form.title = certification.title;
    form.description = certification.description;
    form.images = [];
    form.keep_images = [...certification.images];
    form.certificate_file = null;
    form.published = certification.published;
    form.sort_order = certification.sort_order;
    resetFileInput();
    showForm.value = true;
}

function onPickImages(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    form.images = [...form.images, ...files];
}

function removeNewImage(index: number) {
    form.images = form.images.filter((_, i) => i !== index);
    resetFileInput();
}

function removeExistingImage(path: string) {
    form.keep_images = form.keep_images.filter((p) => p !== path);
}

function submit() {
    const url = editing.value
        ? `/admin/certifications/${editing.value.id}`
        : '/admin/certifications';

    const opts = {
        forceFormData: true,
        onSuccess: () => {
            showForm.value = false;
            editing.value = null;
            form.reset();
            form.images = [];
            form.keep_images = [];
            form.certificate_file = null;
            resetFileInput();
        },
    };

    if (editing.value) {
        form.post(url, {
            ...opts,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
        });
        return;
    }

    form.post(url, opts);
}

async function destroy(certification: Certification) {
    const isDarkMode =
        typeof document !== 'undefined' &&
        document.documentElement.classList.contains('dark');

    const result = await Swal.fire({
        icon: 'warning',
        title: 'Hapus sertifikasi?',
        text: 'Tindakan ini juga akan menghapus semua gambar dan diploma yang terkait.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        background: isDarkMode ? '#0a0a0a' : '#ffffff',
        color: isDarkMode ? '#fafafa' : '#0a0a0a',
        customClass: {
            popup: 'rounded-xl border border-white/10',
        },
    });

    if (!result.isConfirmed) return;

    router.delete(`/admin/certifications/${certification.id}`);
}
</script>

<template>
    <Head title="Sertifikasi" />

    <AdminLayout>
        <template #title>Sertifikasi</template>

        <div class="mb-6">
            <button
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" /> Sertifikasi baru
            </button>
        </div>

        <div
            v-if="showForm"
            class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <h3 class="mb-4 text-lg font-semibold text-foreground">
                {{ editing ? 'Edit' : 'Tambah' }} sertifikasi
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
                        <p
                            v-if="form.errors.title"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Urutan</label
                        >
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                        <p
                            v-if="form.errors.sort_order"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.sort_order }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-foreground"
                        >Deskripsi</label
                    >
                    <textarea
                        v-model="form.description"
                        rows="4"
                        required
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-xs text-destructive"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                        >
                            Gambar
                            <span class="font-normal text-muted-foreground">
                                (bisa pilih banyak{{
                                    editing ? '' : ', minimal 1'
                                }})
                            </span>
                        </label>
                        <FileInput
                            ref="picker"
                            label="Pilih gambar"
                            accept="image/*"
                            multiple
                            :show-preview="false"
                            hint="Bisa pilih beberapa gambar sekaligus. Maks 2 MB per gambar."
                            :error="form.errors.images"
                            @change="onPickImages"
                        />
                        <p
                            v-for="(err, i) in Object.entries(form.errors).find(
                                ([k]) => k.startsWith('images.'),
                            )?.[1]
                                ? [
                                      Object.entries(form.errors).find(([k]) =>
                                          k.startsWith('images.'),
                                      )![1],
                                  ]
                                : []"
                            :key="i"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ err }}
                        </p>

                        <!-- Gambar tersimpan -->
                        <div
                            v-if="editing && form.keep_images.length"
                            class="mt-3"
                        >
                            <p
                                class="mb-1.5 text-xs font-medium text-muted-foreground"
                            >
                                Gambar tersimpan ({{ form.keep_images.length }})
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="path in form.keep_images"
                                    :key="path"
                                    class="group relative"
                                >
                                    <StorageImage
                                        :path="path"
                                        :show-label="false"
                                        image-class="h-16 w-24 rounded-lg border border-border object-cover"
                                    />
                                    <button
                                        type="button"
                                        title="Hapus gambar ini"
                                        class="absolute -top-1.5 -right-1.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                                        @click="removeExistingImage(path)"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Pratinjau gambar baru -->
                        <div v-if="previews.length" class="mt-3">
                            <p
                                class="mb-1.5 text-xs font-medium text-muted-foreground"
                            >
                                Akan diunggah ({{ previews.length }})
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="(preview, index) in previews"
                                    :key="preview.url"
                                    class="group relative"
                                >
                                    <img
                                        :src="preview.url"
                                        :alt="preview.name"
                                        class="h-16 w-24 rounded-lg border border-primary/40 object-cover"
                                    />
                                    <button
                                        type="button"
                                        title="Batalkan gambar ini"
                                        class="absolute -top-1.5 -right-1.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                                        @click="removeNewImage(index)"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p
                            v-else-if="editing && !form.keep_images.length"
                            class="mt-2 inline-flex items-center gap-1.5 text-xs text-amber-500"
                        >
                            <ImagePlus class="h-3.5 w-3.5" />
                            Belum ada gambar — pilih minimal satu sebelum
                            menyimpan.
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                        >
                            Diploma (PDF opsional)
                        </label>
                        <FileInput
                            label="Pilih PDF"
                            accept="application/pdf"
                            :show-preview="false"
                            :error="form.errors.certificate_file"
                            empty-text="Belum ada berkas dipilih"
                            @change="
                                (e: Event) =>
                                    (form.certificate_file =
                                        ((e.target as HTMLInputElement).files ??
                                            [])[0] || null)
                            "
                        />

                        <a
                            v-if="editing?.certificate_file"
                            :href="`/storage/${editing.certificate_file}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-2 inline-flex items-center gap-1 text-xs text-primary hover:underline"
                        >
                            <FileText class="h-3.5 w-3.5" /> Lihat PDF saat ini
                        </a>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-foreground">
                    <input
                        v-model="form.published"
                        type="checkbox"
                        class="rounded"
                    />
                    Tampil di situs publik
                </label>

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
                            Status
                        </th>
                        <th
                            class="px-4 py-3 text-left font-medium text-muted-foreground"
                        >
                            Urutan
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
                        v-for="certification in certifications"
                        :key="certification.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <StorageImage
                                    :path="certification.images[0]"
                                    :show-label="false"
                                    :alt="certification.title"
                                    image-class="h-10 w-16 rounded border border-border object-cover"
                                />
                                <div>
                                    <p class="font-medium text-foreground">
                                        {{ certification.title }}
                                        <span
                                            v-if="
                                                certification.images.length > 1
                                            "
                                            class="ml-1.5 rounded-full bg-primary/10 px-1.5 py-0.5 text-[10px] font-normal text-primary"
                                        >
                                            {{ certification.images.length }}
                                            gambar
                                        </span>
                                    </p>
                                    <p
                                        class="line-clamp-1 text-xs text-muted-foreground"
                                    >
                                        {{ certification.description }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="
                                    certification.published
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                                "
                                class="rounded-full px-2 py-0.5 text-xs"
                            >
                                {{
                                    certification.published
                                        ? 'Tampil'
                                        : 'Tersembunyi'
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ certification.sort_order }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button
                                class="mr-2 text-muted-foreground hover:text-foreground"
                                @click="openEdit(certification)"
                            >
                                <Pencil class="h-4 w-4" />
                            </button>
                            <button
                                class="text-muted-foreground hover:text-destructive"
                                @click="destroy(certification)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!certifications.length">
                        <td
                            colspan="4"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Belum ada sertifikasi.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
