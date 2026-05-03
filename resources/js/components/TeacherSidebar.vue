<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ClipboardCheck,
    FileText,
    LayoutGrid,
    NotebookPen,
    PenLine,
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
    { title: 'Dashboard', href: '/teacher/dashboard', icon: LayoutGrid },
    { title: 'My Attendance', href: '/teacher/dashboard', icon: ClipboardCheck },
    { title: 'Marks Entry', href: '/teacher/dashboard', icon: PenLine },
    { title: 'Assignments', href: '/teacher/dashboard', icon: NotebookPen },
    { title: 'Lessons', href: '/teacher/dashboard', icon: BookOpen },
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
                        <Link href="/teacher/dashboard"><AppLogo /></Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" label="Teaching" />
        </SidebarContent>
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
