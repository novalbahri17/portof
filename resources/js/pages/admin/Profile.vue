<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { User, Lock } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    user: { name: string; email: string };
}>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const profileSuccess = ref(false);
const passwordSuccess = ref(false);

function saveProfile() {
    profileForm.put('/admin/profile', {
        preserveScroll: true,
        onSuccess: () => {
            profileSuccess.value = true;
            setTimeout(() => (profileSuccess.value = false), 3000);
        },
    });
}

function savePassword() {
    passwordForm.put('/admin/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordSuccess.value = true;
            setTimeout(() => (passwordSuccess.value = false), 3000);
        },
        onError: () => {
            passwordForm.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <Head title="Akun Saya" />
    <AdminLayout>
        <template #title>Akun Saya</template>

        <div class="mx-auto max-w-2xl space-y-8">
            <!-- Profile Section -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <User class="h-5 w-5 text-primary" />
                    <h2 class="text-lg font-semibold text-foreground">Informasi profil</h2>
                </div>
                <p class="mb-6 text-sm text-muted-foreground">Perbarui nama dan email Anda.</p>

                <form @submit.prevent="saveProfile" class="space-y-4">
                    <div class="grid gap-2">
                        <label for="name" class="text-sm font-medium text-foreground">Nama</label>
                        <input
                            id="name"
                            v-model="profileForm.name"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Nama Anda"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="profileForm.errors.name" class="text-sm text-destructive">{{ profileForm.errors.name }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label for="email" class="text-sm font-medium text-foreground">Email</label>
                        <input
                            id="email"
                            v-model="profileForm.email"
                            type="email"
                            required
                            autocomplete="username"
                            placeholder="tu@email.com"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="profileForm.errors.email" class="text-sm text-destructive">{{ profileForm.errors.email }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                        >
                            Simpan profil
                        </button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="profileSuccess" class="text-sm text-green-600">Tersimpan.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <!-- Password Section -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <Lock class="h-5 w-5 text-primary" />
                    <h2 class="text-lg font-semibold text-foreground">Ganti kata sandi</h2>
                </div>
                <p class="mb-6 text-sm text-muted-foreground">Gunakan kata sandi yang panjang dan aman untuk melindungi akun Anda.</p>

                <form @submit.prevent="savePassword" class="space-y-4">
                    <div class="grid gap-2">
                        <label for="current_password" class="text-sm font-medium text-foreground">Kata sandi saat ini</label>
                        <input
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Kata sandi saat ini"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="passwordForm.errors.current_password" class="text-sm text-destructive">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label for="password" class="text-sm font-medium text-foreground">Kata sandi baru</label>
                        <input
                            id="password"
                            v-model="passwordForm.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Kata sandi baru"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="passwordForm.errors.password" class="text-sm text-destructive">{{ passwordForm.errors.password }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label for="password_confirmation" class="text-sm font-medium text-foreground">Konfirmasi kata sandi</label>
                        <input
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Konfirmasi kata sandi"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="passwordForm.errors.password_confirmation" class="text-sm text-destructive">{{ passwordForm.errors.password_confirmation }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                        >
                            Ganti kata sandi
                        </button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="passwordSuccess" class="text-sm text-green-600">Kata sandi diperbarui.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
