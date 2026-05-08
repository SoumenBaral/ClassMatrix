<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Bot,
    Brain,
    Calendar,
    CalendarCheck,
    ClipboardList,
    Clock,
    LayoutGrid,
    Settings,
    Sparkles,
    Trophy,
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

const mySpaceItems: NavItem[] = [
    { title: 'Dashboard', href: '/student/dashboard', icon: LayoutGrid },
    { title: 'Timetable', href: '/student/timetable', icon: Clock },
];

const academicItems: NavItem[] = [
    { title: 'Assignments', href: '/student/assignments', icon: ClipboardList },
    { title: 'Quizzes', href: '/student/quizzes', icon: Brain },
    { title: 'Lessons', href: '/student/lessons', icon: BookOpen },
    { title: 'Results', href: '/student/results', icon: Trophy },
    { title: 'Attendance', href: '/student/attendance', icon: CalendarCheck },
];

const aiItems: NavItem[] = [
    { title: 'AI Teacher', href: '/student/chat', icon: Bot },
    { title: 'My Routine', href: '/student/routines', icon: Sparkles },
    { title: 'Preferences', href: '/student/routines/preferences', icon: Settings },
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
            <NavMain :items="mySpaceItems" label="My Space" />
            <NavMain :items="academicItems" label="Academics" />
            <NavMain :items="aiItems" label="AI Assistant" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
