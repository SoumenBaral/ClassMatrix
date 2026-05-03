<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Search, Trash2, Link2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type ParentUser = {
    id: number; name: string; email: string; phone: string | null;
    status: string; children_count: number; created_at: string;
};
type PaginatedData = { data: ParentUser[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    parents: PaginatedData;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

function filter() {
    router.get('/admin/parents', { search: search.value || undefined }, { preserveState: true });
}

// Edit
const showEdit = ref(false);
const editingId = ref(0);
const editForm = useForm({ name: '', email: '', phone: '' });

function openEdit(p: ParentUser) {
    editingId.value = p.id;
    editForm.name = p.name; editForm.email = p.email; editForm.phone = p.phone ?? '';
    showEdit.value = true;
}

function submitEdit() {
    editForm.put(`/admin/parents/${editingId.value}`, { onSuccess: () => { showEdit.value = false; } });
}

function destroy(p: ParentUser) {
    if (confirm(`Delete parent "${p.name}"? This will remove their account and all child links.`)) {
        router.delete(`/admin/parents/${p.id}`);
    }
}
</script>

<template>
    <Head title="Parents" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Parents" description="Manage parent accounts. Use 'Parent Links' to connect parents to students." />
            <Button variant="outline" as-child>
                <a href="/admin/parent-links"><Link2 class="mr-2 size-4" /> Manage Links</a>
            </Button>
        </div>

        <div class="flex items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Name, email, phone..." class="pl-9 w-56" />
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Parent</th>
                        <th class="px-4 py-3 text-left font-medium">Phone</th>
                        <th class="px-4 py-3 text-center font-medium">Children</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-center font-medium">Joined</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in parents.data" :key="p.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ p.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ p.email }}</div>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ p.phone ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="p.children_count > 0 ? 'default' : 'secondary'">{{ p.children_count }} linked</Badge>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="p.status === 'active' ? 'default' : 'destructive'" class="capitalize">{{ p.status }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ p.created_at }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(p)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(p)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="parents.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in parents.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>

        <div v-if="!parents.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No parent accounts found.
        </div>
    </div>

    <!-- Edit -->
    <Dialog v-model:open="showEdit">
        <DialogContent><DialogHeader><DialogTitle>Edit Parent</DialogTitle></DialogHeader>
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div><Label>Full Name</Label><Input v-model="editForm.name" required /><InputError :message="editForm.errors.name" /></div>
                <div><Label>Email</Label><Input v-model="editForm.email" type="email" required /><InputError :message="editForm.errors.email" /></div>
                <div><Label>Phone</Label><Input v-model="editForm.phone" /><InputError :message="editForm.errors.phone" /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showEdit = false">Cancel</Button><Button type="submit" :disabled="editForm.processing">Update</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
