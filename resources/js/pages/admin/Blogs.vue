<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import RichEditor from '@/components/RichEditor.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

type Blog = {
    id: number; title: string; slug: string; excerpt: string | null;
    content: string; image: string | null; tags: string[] | null;
    published: boolean; published_at: string | null;
};

const props = defineProps<{ blogs: Blog[] }>();
const showForm = ref(false);
const editing = ref<Blog | null>(null);

const form = useForm({
    title: '', excerpt: '', content: '',
    image: null as File | null, tags: [] as string[], published: false,
});
const tagInput = ref('');

function addTag() {
    if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) {
        form.tags.push(tagInput.value.trim());
    }
    tagInput.value = '';
}
function removeTag(tag: string) { form.tags = form.tags.filter((t) => t !== tag); }

function openCreate() {
    editing.value = null; form.reset(); showForm.value = true;
}
function openEdit(blog: Blog) {
    editing.value = blog;
    form.title = blog.title; form.excerpt = blog.excerpt || '';
    form.content = blog.content; form.tags = blog.tags || [];
    form.published = blog.published; form.image = null;
    showForm.value = true;
}
function submit() {
    const url = editing.value ? `/admin/blogs/${editing.value.id}` : '/admin/blogs';
    const opts = { forceFormData: true, onSuccess: () => { showForm.value = false; form.reset(); } };
    if (editing.value) {
        form.post(url, { ...opts, headers: { 'X-HTTP-Method-Override': 'PUT' } });
    } else {
        form.post(url, opts);
    }
}
function destroy(id: number) {
    if (confirm('Hapus blog ini?')) router.delete(`/admin/blogs/${id}`);
}
</script>

<template>
    <Head title="Blog" />
    <AdminLayout>
        <template #title>Blog</template>

        <div class="mb-6">
            <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90" @click="openCreate">
                <Plus class="h-4 w-4" /> Blog baru
            </button>
        </div>

        <div v-if="showForm" class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-foreground">{{ editing ? 'Edit' : 'Tambah' }} blog</h3>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Judul</label>
                    <input v-model="form.title" type="text" required class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Ringkasan</label>
                    <RichEditor v-model="form.excerpt" :height="150" minimal />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Konten</label>
                    <RichEditor v-model="form.content" :height="400" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Gambar</label>
                    <input type="file" accept="image/*" class="text-sm" @change="(e: any) => form.image = e.target.files[0]" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Tags</label>
                    <div class="flex gap-2">
                        <input v-model="tagInput" type="text" class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Tambah tag" @keydown.enter.prevent="addTag" />
                        <button type="button" class="rounded-lg bg-secondary px-3 py-2 text-sm" @click="addTag">+</button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="tag in form.tags" :key="tag" class="inline-flex items-center gap-1 rounded-full bg-secondary px-2 py-0.5 text-xs">
                            {{ tag }} <button type="button" class="hover:text-destructive" @click="removeTag(tag)">&times;</button>
                        </span>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.published" type="checkbox" class="rounded" /> Diterbitkan
                </label>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                    <button type="button" class="rounded-lg border border-border px-4 py-2 text-sm" @click="showForm = false">Batal</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Judul</th>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tanggal</th>
                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="blog in blogs" :key="blog.id" class="border-b border-border last:border-0">
                        <td class="px-4 py-3 font-medium text-foreground">{{ blog.title }}</td>
                        <td class="px-4 py-3">
                            <span :class="blog.published ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'" class="rounded-full px-2 py-0.5 text-xs">{{ blog.published ? 'Terbit' : 'Draf' }}</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ blog.published_at ? new Date(blog.published_at).toLocaleDateString('id') : '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button class="mr-2 text-muted-foreground hover:text-foreground" @click="openEdit(blog)"><Pencil class="h-4 w-4" /></button>
                            <button class="text-muted-foreground hover:text-destructive" @click="destroy(blog.id)"><Trash2 class="h-4 w-4" /></button>
                        </td>
                    </tr>
                    <tr v-if="!blogs.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Belum ada blog.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
