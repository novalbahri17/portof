<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    Briefcase,
    GraduationCap,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Experience = {
    id: number;
    type: 'work' | 'education';
    employment_type: string | null;
    work_mode: string | null;
    title: string;
    institution: string;
    location: string | null;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    description: string | null;
    published: boolean;
    sort_order: number;
};

const props = defineProps<{
    experiences: Experience[];
    employmentTypes: Record<string, string>;
    workModes: Record<string, string>;
}>();
const showForm = ref(false);
const editing = ref<Experience | null>(null);

const form = useForm({
    type: 'work' as 'work' | 'education',
    employment_type: '',
    work_mode: '',
    title: '',
    institution: '',
    location: '',
    start_date: '',
    end_date: '',
    is_current: false,
    description: '',
    published: false,
    sort_order: 0,
});

const workCount = computed(
    () => props.experiences.filter((e) => e.type === 'work').length,
);
const educationCount = computed(
    () => props.experiences.filter((e) => e.type === 'education').length,
);

const institutionLabel = computed(() =>
    form.type === 'work' ? 'Perusahaan' : 'Institusi / Sekolah',
);
const titleLabel = computed(() =>
    form.type === 'work' ? 'Posisi' : 'Jurusan / Program',
);

function openCreate() {
    editing.value = null;
    form.reset();
    form.type = 'work';
    form.is_current = false;
    showForm.value = true;
}

function openEdit(experience: Experience) {
    editing.value = experience;
    form.type = experience.type;
    form.employment_type = experience.employment_type ?? '';
    form.work_mode = experience.work_mode ?? '';
    form.title = experience.title;
    form.institution = experience.institution;
    form.location = experience.location ?? '';
    form.start_date = experience.start_date?.slice(0, 7) ?? '';
    form.end_date = experience.end_date?.slice(0, 7) ?? '';
    form.is_current = experience.is_current;
    form.description = experience.description ?? '';
    form.published = experience.published;
    form.sort_order = experience.sort_order;
    showForm.value = true;
}

function toggleCurrent() {
    form.is_current = !form.is_current;
    if (form.is_current) form.end_date = '';
}

function submit() {
    const url = editing.value
        ? `/admin/experiences/${editing.value.id}`
        : '/admin/experiences';

    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            editing.value = null;
            form.reset();
        },
    };

    // Input bertipe "month" hanya menyimpan yyyy-MM, backend butuh tanggal lengkap.
    form.transform((data) => ({
        ...data,
        start_date: data.start_date ? `${data.start_date}-01` : '',
        end_date:
            data.is_current || !data.end_date ? null : `${data.end_date}-01`,
    }));

    if (editing.value) {
        form.put(url, opts);
        return;
    }

    form.post(url, opts);
}

function formatDate(date: string | null): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString('id-ID', {
        month: 'short',
        year: 'numeric',
    });
}

function period(e: Experience): string {
    const start = formatDate(e.start_date);
    const end = e.is_current ? 'Sekarang' : formatDate(e.end_date);
    return `${start} — ${end}`;
}

async function destroy(experience: Experience) {
    const isDarkMode =
        typeof document !== 'undefined' &&
        document.documentElement.classList.contains('dark');

    const result = await Swal.fire({
        icon: 'warning',
        title: 'Hapus pengalaman?',
        text: `"${experience.title}" akan dihapus permanen.`,
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        background: isDarkMode ? '#0a0a0a' : '#ffffff',
        color: isDarkMode ? '#fafafa' : '#0a0a0a',
        customClass: { popup: 'rounded-xl border border-white/10' },
    });

    if (!result.isConfirmed) return;

    router.delete(`/admin/experiences/${experience.id}`);
}
</script>

<template>
    <Head title="Pengalaman & Pendidikan" />

    <AdminLayout>
        <template #title>Pengalaman & Pendidikan</template>

        <div class="mb-4 flex flex-wrap gap-2 text-xs text-muted-foreground">
            <span
                class="inline-flex items-center gap-1 rounded-full border border-border px-2.5 py-1"
            >
                <Briefcase class="h-3 w-3" /> {{ workCount }} pengalaman kerja
            </span>
            <span
                class="inline-flex items-center gap-1 rounded-full border border-border px-2.5 py-1"
            >
                <GraduationCap class="h-3 w-3" />
                {{ educationCount }} pendidikan
            </span>
        </div>

        <div class="mb-6">
            <button
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" /> Tambah baru
            </button>
        </div>

        <div
            v-if="showForm"
            class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <h3 class="mb-4 text-lg font-semibold text-foreground">
                {{ editing ? 'Edit' : 'Tambah' }} pengalaman
            </h3>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-foreground"
                        >Kategori</label
                    >
                    <div class="inline-flex rounded-lg border border-input p-1">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm transition-colors"
                            :class="
                                form.type === 'work'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="form.type = 'work'"
                        >
                            <Briefcase class="h-3.5 w-3.5" /> Kerja
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm transition-colors"
                            :class="
                                form.type === 'education'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="form.type = 'education'"
                        >
                            <GraduationCap class="h-3.5 w-3.5" /> Pendidikan
                        </button>
                    </div>
                </div>

                <div
                    v-if="form.type === 'work'"
                    class="grid gap-4 sm:grid-cols-2"
                >
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Tipe Pekerjaan</label
                        >
                        <select
                            v-model="form.employment_type"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        >
                            <option value="">— Tidak disebutkan —</option>
                            <option
                                v-for="(label, value) in employmentTypes"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.employment_type"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.employment_type }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Sistem Kerja</label
                        >
                        <select
                            v-model="form.work_mode"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        >
                            <option value="">— Tidak disebutkan —</option>
                            <option
                                v-for="(label, value) in workModes"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.work_mode"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.work_mode }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >{{ titleLabel }}</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            :placeholder="
                                form.type === 'work'
                                    ? 'Full Stack Developer'
                                    : 'S1 Teknik Informatika'
                            "
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
                            >{{ institutionLabel }}</label
                        >
                        <input
                            v-model="form.institution"
                            type="text"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            :placeholder="
                                form.type === 'work'
                                    ? 'PT Maju Jaya'
                                    : 'Univers Indonesia'
                            "
                        />
                        <p
                            v-if="form.errors.institution"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.institution }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Mulai</label
                        >
                        <input
                            v-model="form.start_date"
                            type="month"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                        <p
                            v-if="form.errors.start_date"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Selesai</label
                        >
                        <input
                            v-model="form.end_date"
                            type="month"
                            :disabled="form.is_current"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none disabled:opacity-50"
                        />
                        <label
                            class="mt-1.5 flex items-center gap-2 text-xs text-muted-foreground"
                        >
                            <input
                                type="checkbox"
                                :checked="form.is_current"
                                class="rounded"
                                @change="toggleCurrent"
                            />
                            Masih berlangsung
                        </label>
                        <p
                            v-if="form.errors.end_date"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Lokasi
                            <span class="text-muted-foreground"
                                >(opsional)</span
                            ></label
                        >
                        <input
                            v-model="form.location"
                            type="text"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Jakarta, Indonesia"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-foreground"
                        >Deskripsi
                        <span class="text-muted-foreground"
                            >(opsional)</span
                        ></label
                    >
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        placeholder="Pisahkan pencapaian dengan tanda • — mis. Memimpin tim 5 developer • Meningkatkan performa 40%"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-xs text-destructive"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="flex items-center gap-6">
                    <label
                        class="flex items-center gap-2 text-sm text-foreground"
                    >
                        <input
                            v-model="form.published"
                            type="checkbox"
                            class="rounded"
                        />
                        Tampil di situs publik
                    </label>
                    <label
                        class="flex items-center gap-2 text-sm text-foreground"
                    >
                        <span>Urutan</span>
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            class="w-20 rounded-lg border border-input bg-background px-2 py-1 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </label>
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

        <div class="space-y-6">
            <div
                v-for="group in ['work', 'education'] as const"
                :key="group"
                class="overflow-x-auto rounded-xl border border-border"
            >
                <div
                    class="flex items-center gap-2 border-b border-border bg-muted/50 px-4 py-3"
                >
                    <Briefcase
                        v-if="group === 'work'"
                        class="h-4 w-4 text-primary"
                    />
                    <GraduationCap v-else class="h-4 w-4 text-primary" />
                    <h3 class="text-sm font-semibold text-foreground">
                        {{
                            group === 'work' ? 'Pengalaman Kerja' : 'Pendidikan'
                        }}
                    </h3>
                </div>

                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Judul
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Periode
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
                            v-for="experience in experiences.filter(
                                (e) => e.type === group,
                            )"
                            :key="experience.id"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-4 py-3">
                                <p class="font-medium text-foreground">
                                    {{ experience.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ experience.institution
                                    }}<span v-if="experience.location">
                                        · {{ experience.location }}</span
                                    >
                                </p>
                            </td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{ period(experience) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="
                                        experience.published
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                                    "
                                    class="rounded-full px-2 py-0.5 text-xs"
                                >
                                    {{
                                        experience.published
                                            ? 'Tampil'
                                            : 'Tersembunyi'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    class="mr-2 text-muted-foreground hover:text-foreground"
                                    @click="openEdit(experience)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    class="text-muted-foreground hover:text-destructive"
                                    @click="destroy(experience)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr
                            v-if="
                                !experiences.filter((e) => e.type === group)
                                    .length
                            "
                        >
                            <td
                                :colspan="4"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                Belum ada data
                                {{
                                    group === 'work'
                                        ? 'pengalaman kerja'
                                        : 'pendidikan'
                                }}.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
