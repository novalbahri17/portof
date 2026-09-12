#!/bin/sh
# Generate Wayfinder-compatible stubs so Vite can build without Laravel backend.
# These get replaced at runtime by `php artisan wayfinder:generate`.
set -e

ROUTES_DIR="resources/js/routes"
ACTIONS_DIR="resources/js/actions/App/Http/Controllers/Settings"

mkdir -p "$ROUTES_DIR/two-factor" "$ROUTES_DIR/password" "$ACTIONS_DIR"

# --- Wayfinder runtime stub (minimal) ---
mkdir -p "resources/js/wayfinder"
cat > "resources/js/wayfinder/index.ts" <<'WAYFINDER'
export type QueryParams = Record<string, any>;
export type RouteDefinition<T> = { url: string; method: T };
export type RouteFormDefinition<T> = { action: string; method: T };
export type RouteQueryOptions = { query?: QueryParams; mergeQuery?: QueryParams };
export const queryParams = (_o?: RouteQueryOptions) => '';
export const applyUrlDefaults = <T>(e: T): T => e;
export const validateParameters = (_a: any, _o: string[]) => {};
WAYFINDER

# --- Helper: create a route function with .url(), .form(), .method, etc ---
# Usage: make_route <file> <export_name> <method> <url>
make_route() {
    local file="$1" name="$2" method="$3" url="$4"
    cat >> "$file" <<EOF

export const ${name} = (...a: any[]): RouteDefinition<'${method}'> => ({
    url: '${url}',
    method: '${method}',
});
${name}.url = (...a: any[]) => '${url}';
${name}.definition = { methods: ['${method}'], url: '${url}' };
${name}.${method} = (...a: any[]) => ({ url: '${url}', method: '${method}' });
const ${name}Form = (...a: any[]): RouteFormDefinition<'${method}'> => ({
    action: '${url}',
    method: '${method}',
});
${name}Form.${method} = (...a: any[]) => ({ action: '${url}', method: '${method}' });
${name}.form = ${name}Form;
EOF
}

# Helper for file header
header() {
    echo "import { type RouteDefinition, type RouteFormDefinition, type RouteQueryOptions } from '../wayfinder';" > "$1"
}

header_nested() {
    echo "import { type RouteDefinition, type RouteFormDefinition, type RouteQueryOptions } from '../../wayfinder';" > "$1"
}

# ============================================================
# @/routes/index.ts
# ============================================================
header "$ROUTES_DIR/index.ts"
make_route "$ROUTES_DIR/index.ts" "login"     "get"  "/login"
make_route "$ROUTES_DIR/index.ts" "logout"    "post" "/logout"
make_route "$ROUTES_DIR/index.ts" "home"      "get"  "/"
make_route "$ROUTES_DIR/index.ts" "dashboard" "get"  "/admin/dashboard"

# ============================================================
# @/routes/appearance.ts
# ============================================================
header "$ROUTES_DIR/appearance.ts"
make_route "$ROUTES_DIR/appearance.ts" "edit" "get" "/settings/appearance"

# ============================================================
# @/routes/profile.ts
# ============================================================
header "$ROUTES_DIR/profile.ts"
make_route "$ROUTES_DIR/profile.ts" "edit" "get" "/settings/profile"

# ============================================================
# @/routes/user-password.ts
# ============================================================
header "$ROUTES_DIR/user-password.ts"
make_route "$ROUTES_DIR/user-password.ts" "edit" "get" "/settings/password"

# ============================================================
# @/routes/login.ts
# ============================================================
header "$ROUTES_DIR/login.ts"
make_route "$ROUTES_DIR/login.ts" "store" "post" "/login"

# ============================================================
# @/routes/register.ts
# ============================================================
header "$ROUTES_DIR/register.ts"
make_route "$ROUTES_DIR/register.ts" "store" "post" "/register"

# ============================================================
# @/routes/password.ts
# ============================================================
header "$ROUTES_DIR/password.ts"
make_route "$ROUTES_DIR/password.ts" "request" "post" "/forgot-password"
make_route "$ROUTES_DIR/password.ts" "email"   "post" "/forgot-password"
make_route "$ROUTES_DIR/password.ts" "update"  "post" "/reset-password"

# ============================================================
# @/routes/password/confirm.ts
# ============================================================
header_nested "$ROUTES_DIR/password/confirm.ts"
make_route "$ROUTES_DIR/password/confirm.ts" "store" "post" "/user/confirm-password"

# ============================================================
# @/routes/verification.ts
# ============================================================
header "$ROUTES_DIR/verification.ts"
make_route "$ROUTES_DIR/verification.ts" "send" "post" "/email/verification-notification"

# ============================================================
# @/routes/two-factor/index.ts
# ============================================================
header_nested "$ROUTES_DIR/two-factor/index.ts"
make_route "$ROUTES_DIR/two-factor/index.ts" "show"                   "get"    "/user/two-factor-authentication"
make_route "$ROUTES_DIR/two-factor/index.ts" "enable"                 "post"   "/user/two-factor-authentication"
make_route "$ROUTES_DIR/two-factor/index.ts" "disable"                "delete" "/user/two-factor-authentication"
make_route "$ROUTES_DIR/two-factor/index.ts" "confirm"                "post"   "/user/confirmed-two-factor-authentication"
make_route "$ROUTES_DIR/two-factor/index.ts" "qrCode"                 "get"    "/user/two-factor-qr-code"
make_route "$ROUTES_DIR/two-factor/index.ts" "recoveryCodes"          "get"    "/user/two-factor-recovery-codes"
make_route "$ROUTES_DIR/two-factor/index.ts" "secretKey"              "get"    "/user/two-factor-secret-key"
make_route "$ROUTES_DIR/two-factor/index.ts" "regenerateRecoveryCodes" "post"  "/user/two-factor-recovery-codes"

# ============================================================
# @/routes/two-factor/login.ts
# ============================================================
header_nested "$ROUTES_DIR/two-factor/login.ts"
make_route "$ROUTES_DIR/two-factor/login.ts" "store" "post" "/two-factor-challenge"

# ============================================================
# @/actions/App/Http/Controllers/Settings/ProfileController.ts
# ============================================================
cat > "$ACTIONS_DIR/ProfileController.ts" <<'EOF'
import { type RouteDefinition, type RouteFormDefinition, type RouteQueryOptions } from '../../../../../wayfinder';

const update = (...a: any[]): RouteDefinition<'put'> => ({
    url: '/settings/profile',
    method: 'put',
});
update.url = (...a: any[]) => '/settings/profile';
update.definition = { methods: ['put'], url: '/settings/profile' };
update.put = (...a: any[]) => ({ url: '/settings/profile', method: 'put' });
const updateForm = (...a: any[]): RouteFormDefinition<'post'> => ({
    action: '/settings/profile?_method=PUT',
    method: 'post',
});
updateForm.put = (...a: any[]) => ({ action: '/settings/profile?_method=PUT', method: 'post' });
update.form = updateForm;

const destroy = (...a: any[]): RouteDefinition<'delete'> => ({
    url: '/settings/profile',
    method: 'delete',
});
destroy.url = (...a: any[]) => '/settings/profile';
destroy.definition = { methods: ['delete'], url: '/settings/profile' };
destroy.delete = (...a: any[]) => ({ url: '/settings/profile', method: 'delete' });
const destroyForm = (...a: any[]): RouteFormDefinition<'post'> => ({
    action: '/settings/profile?_method=DELETE',
    method: 'post',
});
destroyForm.delete = (...a: any[]) => ({ action: '/settings/profile?_method=DELETE', method: 'post' });
destroy.form = destroyForm;

const ProfileController = { update, destroy };
export default ProfileController;
EOF

# ============================================================
# @/actions/App/Http/Controllers/Settings/PasswordController.ts
# ============================================================
cat > "$ACTIONS_DIR/PasswordController.ts" <<'EOF'
import { type RouteDefinition, type RouteFormDefinition, type RouteQueryOptions } from '../../../../../wayfinder';

const update = (...a: any[]): RouteDefinition<'put'> => ({
    url: '/settings/password',
    method: 'put',
});
update.url = (...a: any[]) => '/settings/password';
update.definition = { methods: ['put'], url: '/settings/password' };
update.put = (...a: any[]) => ({ url: '/settings/password', method: 'put' });
const updateForm = (...a: any[]): RouteFormDefinition<'post'> => ({
    action: '/settings/password?_method=PUT',
    method: 'post',
});
updateForm.put = (...a: any[]) => ({ action: '/settings/password?_method=PUT', method: 'post' });
update.form = updateForm;

const PasswordController = { update };
export default PasswordController;
EOF

echo "✅ Wayfinder stubs generated (with .form() support)"
