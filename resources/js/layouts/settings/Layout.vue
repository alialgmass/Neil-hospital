<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
interface SettingsNavItem {
    title: string;
    href: ReturnType<typeof editProfile>;
    icon: string;
}

const navItems: SettingsNavItem[] = [
    { title: 'بيانات الحساب',  href: editProfile(),    icon: '👤' },
    { title: 'الأمان',         href: editSecurity(),   icon: '🔒' },
    { title: 'المظهر',         href: editAppearance(), icon: '🎨' },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div>
        <!-- Page header -->
        <div class="settings-page-header">
            <h2 class="settings-page-title">⚙️ إعدادات الحساب</h2>
            <p class="settings-page-sub">إدارة بياناتك الشخصية وإعدادات الأمان والمظهر</p>
        </div>

        <!-- Tab navigation -->
        <nav class="settings-nav">
            <Link
                v-for="item in navItems"
                :key="toUrl(item.href)"
                :href="toUrl(item.href)"
                class="settings-nav-item"
                :class="{ 'settings-nav-item-active': isCurrentOrParentUrl(item.href) }"
            >
                <span>{{ (item as any).icon }}</span>
                {{ item.title }}
            </Link>
        </nav>

        <!-- Content -->
        <div class="settings-content">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.settings-page-header {
    padding: 20px 0 16px;
    border-bottom: 2px solid var(--color-hospital-border, #DDE4EF);
    margin-bottom: 0;
}
.settings-page-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--color-hospital-text, #0D1F3C);
    margin-bottom: 3px;
}
.settings-page-sub {
    font-size: 12px;
    color: var(--color-hospital-text-3, #8A96AE);
}

/* ── Tab nav ── */
.settings-nav {
    display: flex;
    gap: 2px;
    background: var(--color-hospital-bg, #F3F6FA);
    border-bottom: 1.5px solid var(--color-hospital-border, #DDE4EF);
    padding: 0 2px;
    overflow-x: auto;
}
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 11px 18px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-hospital-text-2, #4A5878);
    border-bottom: 3px solid transparent;
    white-space: nowrap;
    text-decoration: none;
    transition: color 0.15s, border-color 0.15s;
    cursor: pointer;
}
.settings-nav-item:hover {
    color: var(--color-hospital-primary, #0A4FA6);
    border-bottom-color: var(--color-hospital-primary, #0A4FA6)40;
}
.settings-nav-item-active {
    color: var(--color-hospital-primary, #0A4FA6);
    border-bottom-color: var(--color-hospital-primary, #0A4FA6);
    background: transparent;
}

/* ── Content area ── */
.settings-content {
    padding: 20px 0;
    max-width: 860px;
}
</style>
