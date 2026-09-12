<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { IconMail, IconLock, IconLogin } from '@tabler/icons-vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase title="Masuk" description="Masukkan kredensial Anda untuk mengakses panel">
        <Head title="Masuk" />

        <div v-if="status" class="mb-4 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-2.5 text-center text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <div class="grid gap-1.5">
                    <Label for="email" class="text-sm font-medium text-foreground">Email</Label>
                    <div class="relative">
                        <IconMail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" :stroke-width="1.5" />
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="tu@email.com"
                            class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-1.5">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-medium text-foreground">Kata sandi</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs text-muted-foreground hover:text-primary transition-colors"
                            :tabindex="5"
                        >
                            Lupa kata sandi?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <IconLock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" :stroke-width="1.5" />
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <Label for="remember" class="flex items-center gap-2 cursor-pointer">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span class="text-sm text-muted-foreground">Ingat saya</span>
                </Label>

                <button
                    type="submit"
                    :tabindex="4"
                    :disabled="processing"
                    class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50"
                >
                    <svg v-if="processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                    <IconLogin v-else class="h-4 w-4" :stroke-width="1.5" />
                    {{ processing ? 'Memproses...' : 'Masuk' }}
                </button>
            </div>
        </Form>
    </AuthBase>
</template>
