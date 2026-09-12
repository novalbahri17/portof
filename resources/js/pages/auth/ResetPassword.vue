<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';
import { IconLock, IconCheck } from '@tabler/icons-vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout title="Kata sandi baru" description="Masukkan kata sandi baru Anda">
        <Head title="Atur ulang kata sandi" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-4">
                <div class="grid gap-1.5">
                    <Label for="email" class="text-sm font-medium text-foreground">Email</Label>
                    <input id="email" type="email" name="email" autocomplete="email" v-model="inputEmail" readonly class="w-full rounded-lg border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="password" class="text-sm font-medium text-foreground">Kata sandi baru</Label>
                    <div class="relative">
                        <IconLock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" :stroke-width="1.5" />
                        <input id="password" type="password" name="password" autocomplete="new-password" autofocus placeholder="••••••••" class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="password_confirmation" class="text-sm font-medium text-foreground">Konfirmasi kata sandi</Label>
                    <div class="relative">
                        <IconLock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" :stroke-width="1.5" />
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="••••••••" class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" />
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>

                <button type="submit" :disabled="processing" class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50">
                    <svg v-if="processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                    <IconCheck v-else class="h-4 w-4" :stroke-width="1.5" />
                    {{ processing ? 'Menyimpan...' : 'Atur ulang kata sandi' }}
                </button>
            </div>
        </Form>
    </AuthLayout>
</template>
