<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Trash2, MailCheck } from 'lucide-vue-next';

type Contact = {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    read: boolean;
    created_at: string;
};

const props = defineProps<{ contacts: Contact[] }>();

function markAsRead(id: number) {
    router.patch(`/admin/contacts/${id}/read`);
}

function destroy(id: number) {
    if (confirm('Hapus pesan ini?')) {
        router.delete(`/admin/contacts/${id}`);
    }
}
</script>

<template>
    <Head title="Kontak" />
    <AdminLayout>
        <template #title>Kontak</template>

        <div class="space-y-4">
            <div
                v-for="contact in contacts"
                :key="contact.id"
                :class="[
                    'rounded-xl border bg-card p-6 shadow-sm',
                    contact.read ? 'border-border' : 'border-primary/30 bg-primary/5',
                ]"
            >
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-foreground">
                            {{ contact.name }}
                            <span v-if="!contact.read" class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground">Baru</span>
                        </h3>
                        <p class="text-sm text-muted-foreground">{{ contact.email }}</p>
                        <p v-if="contact.subject" class="text-sm text-muted-foreground">{{ contact.subject }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-if="!contact.read"
                            class="rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-foreground"
                            title="Tandai sudah dibaca"
                            @click="markAsRead(contact.id)"
                        >
                            <MailCheck class="h-4 w-4" />
                        </button>
                        <button
                            class="rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-destructive"
                            title="Hapus"
                            @click="destroy(contact.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <p class="text-sm text-foreground whitespace-pre-line">{{ contact.message }}</p>
                <p class="mt-3 text-xs text-muted-foreground">
                    {{ new Date(contact.created_at).toLocaleString('id') }}
                </p>
            </div>

            <div v-if="!contacts.length" class="rounded-xl border border-dashed border-border p-12 text-center text-muted-foreground">
                Belum ada pesan masuk.
            </div>
        </div>
    </AdminLayout>
</template>
