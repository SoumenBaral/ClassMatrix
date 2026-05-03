<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Issue = {
    id: number; book_title: string; book_author: string; user_name: string;
    issued_at: string; due_date: string; returned_at: string | null;
    fine: string; status: string; is_overdue: boolean;
};
type PaginatedData = { data: Issue[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{ issues: PaginatedData; selectedStatus: string }>();

const status = ref(props.selectedStatus);
watch(status, () => router.get('/admin/library/issues', { status: status.value }, { preserveState: true }));

// Issue book dialog
const showIssueDialog = ref(false);
const issueForm = useForm({ book_id: '' as any, user_id: '' as any, due_date: '' });
function submitIssue() {
    issueForm.post('/admin/library/issue', { onSuccess: () => { showIssueDialog.value = false; issueForm.reset(); } });
}

// Return dialog
const showReturnDialog = ref(false);
const returningId = ref(0);
const returnForm = useForm({ fine: 0 });
function openReturn(id: number) { returningId.value = id; returnForm.fine = 0; showReturnDialog.value = true; }
function submitReturn() {
    returnForm.post(`/admin/library/return/${returningId.value}`, { onSuccess: () => { showReturnDialog.value = false; } });
}

const statusColors: Record<string, string> = { issued: 'secondary', returned: 'default', overdue: 'destructive', lost: 'destructive' };
</script>

<template>
    <Head title="Book Issues" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Book Issues" description="Issue and return books." />
            <Button @click="showIssueDialog = true">Issue Book</Button>
        </div>

        <div class="flex items-end gap-4">
            <div>
                <Label>Status</Label>
                <select v-model="status" class="flex h-9 w-40 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="issued">Issued</option><option value="returned">Returned</option><option value="overdue">Overdue</option><option value="all">All</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Book</th>
                        <th class="px-4 py-3 text-left font-medium">Issued To</th>
                        <th class="px-4 py-3 text-center font-medium">Issued</th>
                        <th class="px-4 py-3 text-center font-medium">Due</th>
                        <th class="px-4 py-3 text-center font-medium">Returned</th>
                        <th class="px-4 py-3 text-center font-medium">Fine</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in issues.data" :key="i.id" class="border-b last:border-0 hover:bg-muted/30" :class="{ 'bg-red-50/50 dark:bg-red-950/20': i.is_overdue }">
                        <td class="px-4 py-2"><div class="font-medium">{{ i.book_title }}</div><div class="text-xs text-muted-foreground">{{ i.book_author }}</div></td>
                        <td class="px-4 py-2">{{ i.user_name }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ i.issued_at }}</td>
                        <td class="px-4 py-2 text-center" :class="i.is_overdue ? 'text-red-600 font-bold' : 'text-muted-foreground'">{{ i.due_date }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ i.returned_at ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">{{ Number(i.fine) > 0 ? i.fine : '-' }}</td>
                        <td class="px-4 py-2 text-center"><Badge :variant="(statusColors[i.status] as any)" class="capitalize">{{ i.status }}</Badge></td>
                        <td class="px-4 py-2 text-right">
                            <Button v-if="i.status === 'issued'" variant="outline" size="sm" @click="openReturn(i.id)">Return</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!issues.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No records found.</div>
    </div>

    <Dialog v-model:open="showIssueDialog">
        <DialogContent><DialogHeader><DialogTitle>Issue Book</DialogTitle></DialogHeader>
            <form @submit.prevent="submitIssue" class="space-y-4">
                <div><Label>Book ID</Label><Input type="number" v-model="issueForm.book_id" placeholder="Book ID" /><InputError :message="issueForm.errors.book_id" /></div>
                <div><Label>User ID</Label><Input type="number" v-model="issueForm.user_id" placeholder="User ID" /><InputError :message="issueForm.errors.user_id" /></div>
                <div><Label>Due Date</Label><Input type="date" v-model="issueForm.due_date" /><InputError :message="issueForm.errors.due_date" /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showIssueDialog = false">Cancel</Button><Button type="submit" :disabled="issueForm.processing">Issue</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showReturnDialog">
        <DialogContent><DialogHeader><DialogTitle>Return Book</DialogTitle></DialogHeader>
            <form @submit.prevent="submitReturn" class="space-y-4">
                <div><Label>Fine Amount</Label><Input type="number" v-model.number="returnForm.fine" min="0" step="0.01" /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showReturnDialog = false">Cancel</Button><Button type="submit" :disabled="returnForm.processing">Confirm Return</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
