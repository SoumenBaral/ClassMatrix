<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Leave = {
    id: number; staff_name: string; employee_no: string;
    type: string; from_date: string; to_date: string;
    days: number; reason: string | null; status: string; approver: string | null;
};
type PaginatedData = { data: Leave[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{ leaves: PaginatedData; selectedStatus: string }>();

const status = ref(props.selectedStatus);

watch(status, () => {
    router.get('/admin/leaves', { status: status.value }, { preserveState: true });
});

function approve(id: number) {
    router.post(`/admin/leaves/${id}/approve`, {}, { preserveScroll: true });
}

function reject(id: number) {
    if (confirm('Reject this leave request?')) {
        router.post(`/admin/leaves/${id}/reject`, {}, { preserveScroll: true });
    }
}

const statusColors: Record<string, string> = {
    pending: 'secondary', approved: 'default', rejected: 'destructive',
};
const typeColors: Record<string, string> = {
    casual: 'outline', sick: 'secondary', earned: 'default', unpaid: 'destructive',
};
</script>

<template>
    <Head title="Leave Management" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Leave Management" description="Review and approve staff leave requests." />

        <div class="flex items-end gap-4">
            <div>
                <Label>Status</Label>
                <select v-model="status" class="flex h-9 w-40 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Staff</th>
                        <th class="px-4 py-3 text-center font-medium">Type</th>
                        <th class="px-4 py-3 text-center font-medium">From</th>
                        <th class="px-4 py-3 text-center font-medium">To</th>
                        <th class="px-4 py-3 text-center font-medium">Days</th>
                        <th class="px-4 py-3 text-left font-medium">Reason</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="l in leaves.data" :key="l.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ l.staff_name }}</div>
                            <div class="text-xs text-muted-foreground">{{ l.employee_no }}</div>
                        </td>
                        <td class="px-4 py-2 text-center"><Badge :variant="(typeColors[l.type] as any)" class="capitalize">{{ l.type }}</Badge></td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ l.from_date }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ l.to_date }}</td>
                        <td class="px-4 py-2 text-center font-medium">{{ l.days }}</td>
                        <td class="px-4 py-2 text-muted-foreground max-w-[200px] truncate">{{ l.reason ?? '-' }}</td>
                        <td class="px-4 py-2 text-center"><Badge :variant="(statusColors[l.status] as any)" class="capitalize">{{ l.status }}</Badge></td>
                        <td class="px-4 py-2 text-right">
                            <div v-if="l.status === 'pending'" class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" @click="approve(l.id)">Approve</Button>
                                <Button variant="outline" size="sm" class="text-destructive" @click="reject(l.id)">Reject</Button>
                            </div>
                            <span v-else class="text-xs text-muted-foreground">{{ l.approver }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!leaves.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No leave requests found.
        </div>
    </div>
</template>
