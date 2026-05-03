<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    ClipboardCheck,
    FileText,
    GraduationCap,
    LayoutGrid,
    Settings,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar, SidebarContent, SidebarFooter, SidebarHeader,
    SidebarMenu, SidebarMenuButton, SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/parent/dashboard', icon: LayoutGrid },
    { title: 'My Children', href: '/parent/children', icon: GraduationCap },
    { title: 'Attendance', href: '/parent/attendance', icon: ClipboardCheck },
    { title: 'Results', href: '/parent/results', icon: FileText },
    { title: 'Notices', href: '/parent/notices', icon: Bell },
];

const footerNavItems: NavItem[] = [
    { title: 'Settings', href: '/settings/profile', icon: Settings },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/parent/dashboard"><AppLogo /></Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" label="My Child" />
        </SidebarContent>
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
