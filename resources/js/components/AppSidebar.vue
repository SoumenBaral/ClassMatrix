<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award,
    BadgeDollarSign,
    Bell,
    BookOpen,
    BookText,
    Box,
    Briefcase,
    Building,
    Building2,
    Bus,
    Calendar,
    CalendarDays,
    CircleHelp,
    ClipboardCheck,
    ClipboardList,
    Clock,
    CreditCard,
    FileText,
    GraduationCap,
    LayoutGrid,
    Layers,
    Library,
    NotebookPen,
    PenLine,
    Receipt,
    Settings,
    TreePalm,
    Users,
    Wallet,
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

const page = usePage();
const user = page.props.auth?.user;

const academicNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Academic Years', href: '/admin/academic-years', icon: Calendar },
    { title: 'Classes & Sections', href: '/admin/classes', icon: Layers },
    { title: 'Subjects', href: '/admin/subjects', icon: BookOpen },
    { title: 'Departments', href: '/admin/departments', icon: Building2 },
];

const dailyOpsNavItems: NavItem[] = [
    { title: 'Attendance', href: '/admin/attendance', icon: ClipboardCheck },
    { title: 'Attendance Report', href: '/admin/attendance/report', icon: ClipboardCheck },
    { title: 'Periods', href: '/admin/periods', icon: Clock },
    { title: 'Timetable', href: '/admin/timetable', icon: CalendarDays },
];

const examNavItems: NavItem[] = [
    { title: 'Exams', href: '/admin/exams', icon: ClipboardList },
    { title: 'Marks Entry', href: '/admin/marks', icon: PenLine },
    { title: 'Results', href: '/admin/results', icon: FileText },
    { title: 'Grade Schemes', href: '/admin/grades', icon: Award },
];

const financeNavItems: NavItem[] = [
    { title: 'Fee Structure', href: '/admin/fees', icon: BadgeDollarSign },
    { title: 'Invoices', href: '/admin/invoices', icon: Receipt },
    { title: 'Payroll', href: '/admin/payroll', icon: Wallet },
    { title: 'Salary Structures', href: '/admin/salary-structures', icon: CreditCard },
    { title: 'Leaves', href: '/admin/leaves', icon: TreePalm },
];

const learningNavItems: NavItem[] = [
    { title: 'Lessons', href: '/admin/lessons', icon: BookText },
    { title: 'Assignments', href: '/admin/assignments', icon: NotebookPen },
    { title: 'Quizzes', href: '/admin/quizzes', icon: CircleHelp },
];

const communicationNavItems: NavItem[] = [
    { title: 'Notices', href: '/admin/notices', icon: Bell },
    { title: 'Calendar', href: '/admin/calendar', icon: Calendar },
];

const auxiliaryNavItems: NavItem[] = [
    { title: 'Library', href: '/admin/library', icon: Library },
    { title: 'Book Issues', href: '/admin/library/issues', icon: BookOpen },
    { title: 'Transport', href: '/admin/transport', icon: Bus },
    { title: 'Hostel', href: '/admin/hostel', icon: Building },
    { title: 'Inventory', href: '/admin/inventory', icon: Box },
];

const managementNavItems: NavItem[] = [
    { title: 'Students', href: '/admin/students', icon: GraduationCap },
    { title: 'Teachers & Staff', href: '/admin/staff', icon: Briefcase },
    { title: 'Parents', href: '/admin/parents', icon: Users },
    { title: 'Parent Links', href: '/admin/parent-links', icon: Users },
    { title: 'All Users', href: '/admin/users', icon: Users },
];

const footerNavItems: NavItem[] = [
    { title: 'Settings', href: '/settings/profile', icon: Settings },
];

const dashboardHref = user?.user_type === 'admin' ? '/admin/dashboard' : '/dashboard';
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="academicNavItems" label="Academic" />
            <NavMain :items="dailyOpsNavItems" label="Daily Operations" />
            <NavMain :items="examNavItems" label="Examinations" />
            <NavMain :items="financeNavItems" label="Finance & HR" />
            <NavMain :items="learningNavItems" label="Online Learning" />
            <NavMain :items="auxiliaryNavItems" label="Auxiliary" />
            <NavMain :items="communicationNavItems" label="Communication" />
            <NavMain :items="managementNavItems" label="Management" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
