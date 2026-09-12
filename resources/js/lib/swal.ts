import Swal from 'sweetalert2';
import type {SweetAlertOptions} from 'sweetalert2';

/**
 * Helper SweetAlert2 terpusat.
 *
 * Sebelumnya setiap halaman menulis warna tombol sendiri-sendiri secara
 * hard-code (`'#fafafa'`, `'#dc2626'`, dst). Tema aplikasi ini dipaksa gelap
 * dengan `--primary: hsl(217 91% 60%)` (biru terang), sehingga tombol yang
 * dipaksa putih di atas popup gelap jadi kontras dan tabrakan.
 *
 * Solusinya: baca nilai token warna asli dari CSS variables, jadi warna
 * dialog selalu ikut tema dan cuma diatur di satu tempat.
 */

const FALLBACK = {
    background: '#0a0a0a',
    color: '#fafafa',
    primary: 'hsl(217 91% 60%)',
    destructive: 'hsl(0 72% 51%)',
    mutedForeground: 'hsl(220 13% 62%)',
    accent: 'hsl(222 35% 16%)',
    border: 'hsl(223 25% 15%)',
} as const;

function isDark(): boolean {
    if (typeof document === 'undefined') return true;
    return document.documentElement.classList.contains('dark');
}

/** Ambil nilai token warna dari CSS variable, dengan fallback aman. */
function token(name: string, fallback: string): string {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return fallback;
    }

    const value = getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();

    return value || fallback;
}

export interface ThemeColors {
    background: string;
    color: string;
    primary: string;
    primaryForeground: string;
    destructive: string;
    mutedForeground: string;
    accent: string;
    border: string;
}

/** Palet warna dialog yang mengikuti tema aktif. */
export function swalTheme(): ThemeColors {
    const dark = isDark();

    return {
        background: dark
            ? token('--card', FALLBACK.background)
            : token('--background', '#ffffff'),
        color: dark ? token('--foreground', FALLBACK.color) : '#0a0a0a',
        primary: token('--primary', FALLBACK.primary),
        primaryForeground: token('--primary-foreground', '#ffffff'),
        destructive: token('--destructive', FALLBACK.destructive),
        mutedForeground: token('--muted-foreground', FALLBACK.mutedForeground),
        accent: token('--accent', FALLBACK.accent),
        border: token('--border', FALLBACK.border),
    };
}

export type SwalVariant = 'primary' | 'destructive';

interface ConfirmOptions {
    title: string;
    text?: string;
    /** `destructive` untuk aksi hapus, `primary` untuk aksi biasa. */
    variant?: SwalVariant;
    confirmButtonText?: string;
    cancelButtonText?: string;
    icon?: SweetAlertOptions['icon'];
}

/**
 * Bangun opsi dialog terpusat. Semua dialog memakai ini supaya:
 * - tombol utama = warna primary tema (biru), bukan putih terang
 * - tombol hapus = warna destructive tema
 * - tombol batal = gaya netral (outline), bukan abu-abu solid
 */
export function swalOptions({
    title,
    text,
    variant = 'primary',
    confirmButtonText = 'Lanjutkan',
    cancelButtonText = 'Batal',
    icon = 'question',
}: ConfirmOptions): SweetAlertOptions {
    const theme = swalTheme();
    const danger = variant === 'destructive';

    return {
        icon,
        title,
        text,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        // Warna tombol utama ikut tema: biru untuk aksi biasa,
        // merah untuk aksi merusak.
        confirmButtonColor: danger ? theme.destructive : theme.primary,
        // Tombol batal memakai gaya netral supaya tidak menyaingi tombol utama.
        cancelButtonColor: theme.accent,
        background: theme.background,
        color: theme.color,
        customClass: {
            popup: 'rounded-xl border border-border',
            confirmButton: danger ? 'swal-btn-destructive' : 'swal-btn-primary',
            cancelButton: 'swal-btn-cancel',
        },
        buttonsStyling: false,
    };
}

/** Dialog konfirmasi (Ya/Batal) dengan gaya tema. */
export function confirmDialog(options: ConfirmOptions) {
    return Swal.fire(swalOptions(options));
}

/** Dialog informasi satu tombol (mis. notifikasi sukses). */
export function infoDialog({
    title,
    text,
    icon = 'success',
    confirmButtonText = 'Tutup',
}: Omit<ConfirmOptions, 'variant' | 'cancelButtonText'>) {
    const theme = swalTheme();

    return Swal.fire({
        icon,
        title,
        text,
        confirmButtonText,
        confirmButtonColor: theme.primary,
        background: theme.background,
        color: theme.color,
        customClass: {
            popup: 'rounded-xl border border-border',
            confirmButton: 'swal-btn-primary',
        },
        buttonsStyling: false,
    });
}

export { Swal };
export default Swal;
