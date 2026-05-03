<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    invoice: {
        id: number; invoice_no: string; total_amount: string;
        discount_amount: string; paid_amount: string; balance: number;
        due_date: string; status: string; notes: string | null;
        generated_at: string;
        student: { admission_no: string; user: { name: string } };
        items: { id: number; description: string; amount: string }[];
        payments: { id: number; amount: string; method: string; paid_at: string; receipt_no: string; receiver: { name: string } | null }[];
    };
}>();

const statusColors: Record<string, string> = {
    unpaid: 'secondary', partial: 'outline', paid: 'default', overdue: 'destructive',
};

// Payment dialog
const showPayDialog = ref(false);
const payForm = useForm({
    amount: props.invoice.balance,
    method: 'cash',
    transaction_id: '',
    notes: '',
});

function submitPayment() {
    payForm.post(`/admin/invoices/${props.invoice.id}/pay`, {
        onSuccess: () => { showPayDialog.value = false; },
    });
}

const methods = ['cash', 'card', 'bank', 'online', 'cheque'];
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_no}`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child><a href="/admin/invoices"><ArrowLeft class="size-4" /></a></Button>
            <Heading :title="`Invoice ${invoice.invoice_no}`" :description="invoice.student.user.name" />
            <div class="flex-1" />
            <Badge :variant="(statusColors[invoice.status] as any)" class="capitalize text-sm">{{ invoice.status }}</Badge>
            <Button v-if="invoice.balance > 0" @click="showPayDialog = true">
                <CreditCard class="mr-2 size-4" /> Record Payment
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Invoice details -->
            <Card class="lg:col-span-2">
                <CardHeader><CardTitle>Invoice Items</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="py-2 text-left">Description</th><th class="py-2 text-right">Amount</th></tr></thead>
                        <tbody>
                            <tr v-for="item in invoice.items" :key="item.id" class="border-b last:border-0">
                                <td class="py-2">{{ item.description }}</td>
                                <td class="py-2 text-right font-mono">{{ Number(item.amount).toLocaleString() }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t font-bold">
                                <td class="py-2">Total</td>
                                <td class="py-2 text-right font-mono">{{ Number(invoice.total_amount).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="Number(invoice.discount_amount) > 0" class="text-green-600">
                                <td class="py-1">Discount</td>
                                <td class="py-1 text-right font-mono">-{{ Number(invoice.discount_amount).toLocaleString() }}</td>
                            </tr>
                            <tr class="text-green-600">
                                <td class="py-1">Paid</td>
                                <td class="py-1 text-right font-mono">-{{ Number(invoice.paid_amount).toLocaleString() }}</td>
                            </tr>
                            <tr class="border-t text-lg font-bold" :class="invoice.balance > 0 ? 'text-red-600' : 'text-green-600'">
                                <td class="py-2">Balance Due</td>
                                <td class="py-2 text-right font-mono">{{ invoice.balance.toLocaleString() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </CardContent>
            </Card>

            <!-- Sidebar info -->
            <div class="space-y-4">
                <Card>
                    <CardHeader><CardTitle class="text-base">Student</CardTitle></CardHeader>
                    <CardContent class="text-sm space-y-1">
                        <div><span class="text-muted-foreground">Name:</span> {{ invoice.student.user.name }}</div>
                        <div><span class="text-muted-foreground">Admission:</span> {{ invoice.student.admission_no }}</div>
                        <div><span class="text-muted-foreground">Due Date:</span> {{ invoice.due_date }}</div>
                        <div v-if="invoice.notes"><span class="text-muted-foreground">Notes:</span> {{ invoice.notes }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle class="text-base">Payment History</CardTitle></CardHeader>
                    <CardContent>
                        <div v-for="p in invoice.payments" :key="p.id" class="flex items-center justify-between border-b py-2 last:border-0 text-sm">
                            <div>
                                <div class="font-mono text-xs">{{ p.receipt_no }}</div>
                                <div class="text-xs text-muted-foreground capitalize">{{ p.method }} | {{ p.paid_at }}</div>
                            </div>
                            <div class="font-mono font-medium text-green-600">+{{ Number(p.amount).toLocaleString() }}</div>
                        </div>
                        <p v-if="!invoice.payments.length" class="text-sm text-muted-foreground">No payments yet.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <!-- Pay Dialog -->
    <Dialog v-model:open="showPayDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Record Payment</DialogTitle></DialogHeader>
            <form @submit.prevent="submitPayment" class="space-y-4">
                <div class="rounded-lg bg-muted/50 p-3 text-sm">Balance due: <strong class="font-mono">{{ invoice.balance.toLocaleString() }}</strong></div>
                <div><Label>Amount</Label><Input type="number" v-model.number="payForm.amount" :max="invoice.balance" min="0.01" step="0.01" /><InputError :message="payForm.errors.amount" /></div>
                <div>
                    <Label>Method</Label>
                    <select v-model="payForm.method" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option v-for="m in methods" :key="m" :value="m" class="capitalize">{{ m }}</option>
                    </select>
                </div>
                <div><Label>Transaction ID</Label><Input v-model="payForm.transaction_id" placeholder="Optional" /></div>
                <div><Label>Notes</Label><Input v-model="payForm.notes" placeholder="Optional" /></div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPayDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="payForm.processing">Record Payment</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
