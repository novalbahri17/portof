<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { TwoFactorConfigContent } from '@/types';
import { store } from '@/routes/two-factor/login';
import { IconShieldCheck } from '@tabler/icons-vue';

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Kode pemulihan',
            description: 'Masukkan salah satu kode pemulihan darurat Anda.',
            buttonText: 'gunakan kode autentikasi',
        };
    }
    return {
        title: 'Autentikasi 2FA',
        description: 'Masukkan kode dari aplikasi autentikator Anda.',
        buttonText: 'gunakan kode pemulihan',
    };
});

const showRecoveryInput = ref(false);
const code = ref('');

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <AuthLayout :title="authConfigContent.title" :description="authConfigContent.description">
        <Head title="Verifikasi 2FA" />

        <div class="space-y-5">
            <template v-if="!showRecoveryInput">
                <Form v-bind="store.form()" class="space-y-4" reset-on-error @error="code = ''" #default="{ errors, processing, clearErrors }">
                    <input type="hidden" name="code" :value="code" />
                    <div class="flex flex-col items-center justify-center space-y-3 text-center">
                        <IconShieldCheck class="h-10 w-10 text-primary" :stroke-width="1.5" />
                        <div class="flex w-full items-center justify-center">
                            <InputOTP id="otp" v-model="code" :maxlength="6" :disabled="processing" autofocus>
                                <InputOTPGroup>
                                    <InputOTPSlot v-for="index in 6" :key="index" :index="index - 1" />
                                </InputOTPGroup>
                            </InputOTP>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <button type="submit" :disabled="processing" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50">
                        {{ processing ? 'Memverifikasi...' : 'Lanjutkan' }}
                    </button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>Atau Anda bisa </span>
                        <button type="button" class="text-foreground underline underline-offset-4 hover:text-primary transition-colors" @click="() => toggleRecoveryMode(clearErrors)">
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form v-bind="store.form()" class="space-y-4" reset-on-error #default="{ errors, processing, clearErrors }">
                    <input name="recovery_code" type="text" placeholder="Masukkan kode pemulihan" :autofocus="showRecoveryInput" required class="w-full rounded-lg border border-input bg-white/60 dark:bg-white/5 backdrop-blur px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" />
                    <InputError :message="errors.recovery_code" />
                    <button type="submit" :disabled="processing" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50">
                        {{ processing ? 'Memverifikasi...' : 'Lanjutkan' }}
                    </button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>Atau Anda bisa </span>
                        <button type="button" class="text-foreground underline underline-offset-4 hover:text-primary transition-colors" @click="() => toggleRecoveryMode(clearErrors)">
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
