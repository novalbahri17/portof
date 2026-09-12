<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, FileText, ImagePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import FileInput from '@/components/FileInput.vue';
import SavedImages from '@/components/SavedImages.vue';
import StorageImage from '@/components/StorageImage.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';

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

/** Pesan galat untuk gambar, mis. `images.0` dari validasi Laravel. */
const imageError = computed(() => {
    const errors = form.errors as Record<string, string | undefined>;
    const key = Object.keys(errors).find((k) => k.startsWith('images.'));

    return key ? (errors[key] ?? '') : '';
});

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
    const input = event.target as HTMLInputElement;

    // Salin apa adanya: FileInput sudah membangun ulang daftar berkasnya
    // saat salah satu dibatalkan, jadi jangan ditambahkan lagi.
    form.images = Array.from(input.files ?? []);
}

/** Buang satu gambar sertifikasi yang sudah tersimpan di server. */
function removeExistingImage(path: string) {
    if (!editing.value) return;

    router.post(
        `/admin/certifications/${editing.value.id}/images/delete`,
        { path },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.keep_images = form.keep_images.filter((p) => p !== path);

                if (editing.value) {
                    editing.value.images = editing.value.images.filter(
                        (p) => p !== path,
                    );
                }
            },
        },
    );
}

/** Buang PDF diploma yang sudah tersimpan di server. */
async function removePdf() {
    if (!editing.value) return;

    const result = await confirmDialog({
        icon: 'warning',
        title: 'Hapus PDF diploma?',
        text: 'Berkas PDF akan dihapus dari server.',
        variant: 'destructive',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
    });

    if (!result.isConfirmed) return;

    router.delete(`/admin/certifications/${editing.value.id}/pdf`, {
        preserveScroll: true,
        onSuccess: () => {
            if (editing.value) editing.value.certificate_file = null;
        },
    });
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
    const result = await confirmDialog({
        icon: 'warning',
        title: 'Hapus sertifikasi?',
        text: 'Tindakan ini juga akan menghapus semua gambar dan diploma yang terkait.',
        variant: 'destructive',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
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
                            hint="Bisa pilih beberapa gambar sekaligus. Maks 2 MB per gambar."
                            preview-class="h-16 w-24"
                            :error="imageError || form.errors.images"
                            @change="onPickImages"
                            @clear="form.images = []"
                        />

                        <!-- Gambar tersimpan -->
                        <SavedImages
                            v-if="editing"
                            :paths="form.keep_images"
                            label="Tersimpan di server"
                            alt="Gambar sertifikasi"
                            image-class="h-16 w-24"
                            confirm-text="Hapus gambar ini dari server?"
                            @remove="removeExistingImage"
                        />

                        <p
                            v-if="editing && !form.keep_images.length"
                            class="mt-2 inline-flex items-center gap-1.5 text-xs text-amber-500"
                        >
                            <ImagePlus class="h-3.5 w-3.5" />
                            Belum ada gambar tersimpan — pilih minimal satu
                            sebelum menyimpan.
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
                            :error="form.errors.certificate_file"
                            empty-text="Belum ada berkas dipilih"
                            preview-class="h-16 w-28"
                            @change="
                                (e: Event) =>
                                    (form.certificate_file =
                                        ((e.target as HTMLInputElement).files ??
                                            [])[0] || null)
                            "
                            @clear="form.certificate_file = null"
                        />

                        <div
                            v-if="editing?.certificate_file"
                            class="mt-3 flex items-center gap-3 rounded-lg border border-border bg-muted/20 p-3"
                        >
                            <FileText class="h-5 w-5 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-xs font-medium text-foreground"
                                    :title="editing.certificate_file"
                                >
                                    {{
                                        editing.certificate_file
                                            .split('/')
                                            .pop()
                                    }}
                                </p>
                                <a
                                    :href="`/storage/${editing.certificate_file}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs text-primary hover:underline"
                                >
                                    Lihat PDF
                                </a>
                            </div>
                            <button
                                type="button"
                                title="Hapus PDF ini dari server"
                                aria-label="Hapus PDF"
                                class="cursor-pointer rounded-lg p-2 text-destructive transition-colors hover:bg-destructive/10"
                                @click="removePdf"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <p
                            v-else-if="editing"
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            Belum ada PDF diploma.
                        </p>
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
