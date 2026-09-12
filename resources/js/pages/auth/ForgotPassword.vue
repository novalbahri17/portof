<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { IconMail, IconSend } from '@tabler/icons-vue';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout title="Pulihkan kata sandi" description="Masukkan email Anda untuk menerima tautan pemulihan">
        <Head title="Pulihkan kata sandi" />

        <div v-if="status" class="mb-4 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-2.5 text-center text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <div class="space-y-5">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <div class="grid gap-1.5">
                    <Label for="email" class="text-sm font-medium text-foreground">Email</Label>
                    <div class="relative">
                        <IconMail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" :stroke-width="1.5" />
                        <input id="email" type="email" name="email" autocomplete="off" autofocus placeholder="tu@email.com" class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <button type="submit" :disabled="processing" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50">
                    <svg v-if="processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                    <IconSend v-else class="h-4 w-4" :stroke-width="1.5" />
                    {{ processing ? 'Mengirim...' : 'Kirim tautan' }}
                </button>
            </Form>

            <div class="text-center text-sm text-muted-foreground">
                <span>Kembali ke </span>
                <TextLink :href="login()">halaman masuk</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
