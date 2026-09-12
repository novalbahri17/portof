import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

/**
 * Mode terang dinonaktifkan — situs selalu menggunakan mode gelap.
 */
const appearance = ref<Appearance>('dark');

export function updateTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.add('dark');
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    updateTheme();

    // Sinkronkan penyimpanan lama agar selalu 'dark'.
    localStorage.setItem('appearance', 'dark');
    document.cookie = 'appearance=dark;path=/;max-age=31536000;SameSite=Lax';
}

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => 'dark');

    function updateAppearance(_value: Appearance): void {
        // No-op: mode terang dinonaktifkan.
        updateTheme();
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
