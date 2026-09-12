<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import RichEditor from '@/components/RichEditor.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

type Project = {
    id: number; title: string; slug: string; description: string;
    image: string | null; url: string | null; repo_url: string | null;
    tags: string[] | null; type: string; featured: boolean;
    sort_order: number; published: boolean;
};

const props = defineProps<{ projects: Project[] }>();
const showForm = ref(false);
const editing = ref<Project | null>(null);

const form = useForm({
    title: '', description: '', image: null as File | null,
    url: '', repo_url: '', tags: [] as string[],
    type: 'portfolio' as 'side_project' | 'portfolio',
    featured: false, sort_order: 0, published: false,
});
const tagInput = ref('');

function addTag() {
    if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) form.tags.push(tagInput.value.trim());
    tagInput.value = '';
}
function removeTag(tag: string) { form.tags = form.tags.filter((t) => t !== tag); }

function openCreate() { editing.value = null; form.reset(); showForm.value = true; }
function openEdit(p: Project) {
    editing.value = p;
    form.title = p.title; form.description = p.description;
    form.url = p.url || ''; form.repo_url = p.repo_url || '';
    form.tags = p.tags || []; form.type = p.type as any;
    form.featured = p.featured; form.sort_order = p.sort_order;
    form.published = p.published; form.image = null;
    showForm.value = true;
}
function submit() {
    const url = editing.value ? `/admin/projects/${editing.value.id}` : '/admin/projects';
    const opts = { forceFormData: true, onSuccess: () => { showForm.value = false; form.reset(); } };
    if (editing.value) {
        form.post(url, { ...opts, headers: { 'X-HTTP-Method-Override': 'PUT' } });
    } else {
        form.post(url, opts);
    }
}
function destroy(id: number) {
    if (confirm('¿Eliminar este proyecto?')) router.delete(`/admin/projects/${id}`);
}
</script>

<template>
    <Head title="Proyectos" />
    <AdminLayout>
        <template #title>Proyectos</template>

        <div class="mb-6">
            <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90" @click="openCreate">
                <Plus class="h-4 w-4" /> Nuevo proyecto
            </button>
        </div>

        <div v-if="showForm" class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-foreground">{{ editing ? 'Editar' : 'Nuevo' }} proyecto</h3>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Título</label>
                        <input v-model="form.title" type="text" required class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Tipo</label>
                        <select v-model="form.type" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                            <option value="portfolio">Portafolio</option>
                            <option value="side_project">Side Project</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Descripción</label>
                    <RichEditor v-model="form.description" :height="250" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">URL</label>
                        <input v-model="form.url" type="url" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Repo URL</label>
                        <input v-model="form.repo_url" type="url" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Imagen</label>
                    <input type="file" accept="image/*" class="text-sm" @change="(e: any) => form.image = e.target.files[0]" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground">Tags</label>
                    <div class="flex gap-2">
                        <input v-model="tagInput" type="text" class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Agregar tag" @keydown.enter.prevent="addTag" />
                        <button type="button" class="rounded-lg bg-secondary px-3 py-2 text-sm" @click="addTag">+</button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="tag in form.tags" :key="tag" class="inline-flex items-center gap-1 rounded-full bg-secondary px-2 py-0.5 text-xs">
                            {{ tag }} <button type="button" class="hover:text-destructive" @click="removeTag(tag)">&times;</button>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm"><input v-model="form.published" type="checkbox" class="rounded" /> Publicado</label>
                    <label class="flex items-center gap-2 text-sm"><input v-model="form.featured" type="checkbox" class="rounded" /> Destacado</label>
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Orden:</label>
                        <input v-model.number="form.sort_order" type="number" class="w-20 rounded-lg border border-input bg-background px-2 py-1 text-sm" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">{{ form.processing ? 'Guardando...' : 'Guardar' }}</button>
                    <button type="button" class="rounded-lg border border-border px-4 py-2 text-sm" @click="showForm = false">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Título</th>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in projects" :key="p.id" class="border-b border-border last:border-0">
                        <td class="px-4 py-3 font-medium text-foreground">{{ p.title }}</td>
                        <td class="px-4 py-3">
                            <span :class="p.type === 'side_project' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'" class="rounded-full px-2 py-0.5 text-xs">{{ p.type === 'side_project' ? 'Side Project' : 'Portafolio' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="p.published ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'" class="rounded-full px-2 py-0.5 text-xs">{{ p.published ? 'Publicado' : 'Borrador' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button class="mr-2 text-muted-foreground hover:text-foreground" @click="openEdit(p)"><Pencil class="h-4 w-4" /></button>
                            <button class="text-muted-foreground hover:text-destructive" @click="destroy(p.id)"><Trash2 class="h-4 w-4" /></button>
                        </td>
                    </tr>
                    <tr v-if="!projects.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">No hay proyectos aún.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
