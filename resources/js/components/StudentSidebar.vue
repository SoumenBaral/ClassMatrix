<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Bot,
    Calendar,
    LayoutGrid,
    MessageCircle,
    Settings,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/student/dashboard', icon: LayoutGrid },
    { title: 'My Routine', href: '/student/routines', icon: Calendar },
    { title: 'Preferences', href: '/student/routines/preferences', icon: Settings },
];

const aiNavItems: NavItem[] = [
    { title: 'AI Teacher', href: '/student/chat', icon: Bot },
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
                        <Link href="/student/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" label="My Space" />
            <NavMain :items="aiNavItems" label="AI Assistant" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
