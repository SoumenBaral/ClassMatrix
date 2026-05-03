<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Plus, Pencil, Trash2, Search, Package } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Category = { id: number; name: string; items_count: number };
type Item = {
    id: number; name: string; unit: string; quantity: number;
    min_stock: number; location: string | null;
    category: { id: number; name: string } | null;
};
type PaginatedItems = { data: Item[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    items: PaginatedItems;
    categories: Category[];
    lowStockCount: number;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');
function filter() { router.get('/admin/inventory', { search: search.value || undefined }, { preserveState: true }); }

// Item dialog
const showDialog = ref(false);
const editing = ref<Item | null>(null);
const form = useForm({ name: '', category_id: null as number | null, unit: 'pcs', quantity: 0, min_stock: 0, location: '' });

function openCreate() { editing.value = null; form.reset(); form.unit = 'pcs'; showDialog.value = true; }
function openEdit(item: Item) {
    editing.value = item; form.name = item.name; form.category_id = item.category?.id ?? null;
    form.unit = item.unit; form.min_stock = item.min_stock; form.location = item.location ?? '';
    showDialog.value = true;
}
function submit() {
    if (editing.value) form.put(`/admin/inventory/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    else form.post('/admin/inventory', { onSuccess: () => { showDialog.value = false; form.reset(); } });
}
function destroy(id: number) { if (confirm('Delete item?')) router.delete(`/admin/inventory/${id}`); }

// Stock movement
const showStockDialog = ref(false);
const stockItemId = ref(0);
const stockItemName = ref('');
const stockForm = useForm({ type: 'in', quantity: 1, reference: '' });
function openStock(item: Item) { stockItemId.value = item.id; stockItemName.value = item.name; stockForm.reset(); stockForm.quantity = 1; showStockDialog.value = true; }
function submitStock() { stockForm.post(`/admin/inventory/${stockItemId.value}/stock`, { onSuccess: () => { showStockDialog.value = false; } }); }

// Category
const showCatDialog = ref(false);
const catForm = useForm({ name: '' });
function submitCat() { catForm.post('/admin/item-categories', { onSuccess: () => { showCatDialog.value = false; catForm.reset(); } }); }
function destroyCat(id: number) { if (confirm('Delete?')) router.delete(`/admin/item-categories/${id}`); }
</script>

<template>
    <Head title="Inventory" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Inventory" description="Track stock, assets, and supplies." />
            <div class="flex gap-2">
                <Button variant="outline" @click="showCatDialog = true"><Plus class="mr-1 size-4" /> Category</Button>
                <Button @click="openCreate"><Plus class="mr-1 size-4" /> Add Item</Button>
            </div>
        </div>

        <div v-if="lowStockCount > 0" class="flex items-center gap-2 rounded-lg border border-orange-300 bg-orange-50 p-3 text-sm text-orange-700 dark:bg-orange-950/30 dark:text-orange-300">
            <AlertTriangle class="size-4" /> {{ lowStockCount }} item(s) below minimum stock level.
        </div>

        <div class="flex flex-wrap gap-2">
            <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-1 rounded-lg border px-3 py-1 text-sm">
                {{ cat.name }} <Badge variant="secondary" class="ml-1">{{ cat.items_count }}</Badge>
                <button @click="destroyCat(cat.id)" class="ml-1 text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
            </div>
        </div>

        <div class="flex items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Search items..." class="pl-9 w-56" />
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Item</th>
                        <th class="px-4 py-3 text-left font-medium">Category</th>
                        <th class="px-4 py-3 text-center font-medium">Quantity</th>
                        <th class="px-4 py-3 text-center font-medium">Min Stock</th>
                        <th class="px-4 py-3 text-left font-medium">Location</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id"
                        :class="['border-b last:border-0 hover:bg-muted/30', item.quantity <= item.min_stock ? 'bg-orange-50/50 dark:bg-orange-950/20' : '']">
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ item.name }}</span>
                                <span class="text-xs text-muted-foreground">({{ item.unit }})</span>
                                <AlertTriangle v-if="item.quantity <= item.min_stock" class="size-3 text-orange-500" />
                            </div>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ item.category?.name ?? '-' }}</td>
                        <td class="px-4 py-2 text-center font-mono font-bold" :class="item.quantity <= item.min_stock ? 'text-orange-600' : ''">{{ item.quantity }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ item.min_stock }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ item.location ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" @click="openStock(item)"><Package class="mr-1 size-3" /> Stock</Button>
                                <Button variant="ghost" size="sm" @click="openEdit(item)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(item.id)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent><DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Add' }} Item</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Name</Label><Input v-model="form.name" /><InputError :message="form.errors.name" /></div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Category</Label>
                        <select v-model="form.category_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div><Label>Unit</Label><Input v-model="form.unit" placeholder="pcs, kg, etc." /></div>
                    <div v-if="!editing"><Label>Quantity</Label><Input type="number" v-model.number="form.quantity" min="0" /></div>
                    <div v-else />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Min Stock</Label><Input type="number" v-model.number="form.min_stock" min="0" /></div>
                    <div><Label>Location</Label><Input v-model="form.location" placeholder="Storeroom A" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Add' }}</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showStockDialog">
        <DialogContent><DialogHeader><DialogTitle>Stock Movement: {{ stockItemName }}</DialogTitle></DialogHeader>
            <form @submit.prevent="submitStock" class="space-y-4">
                <div>
                    <Label>Type</Label>
                    <select v-model="stockForm.type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="in">Stock In</option><option value="out">Stock Out</option><option value="adjustment">Adjustment (set to)</option>
                    </select>
                </div>
                <div><Label>Quantity</Label><Input type="number" v-model.number="stockForm.quantity" min="1" /></div>
                <div><Label>Reference / Notes</Label><Input v-model="stockForm.reference" placeholder="PO #, reason, etc." /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showStockDialog = false">Cancel</Button><Button type="submit" :disabled="stockForm.processing">Submit</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showCatDialog">
        <DialogContent><DialogHeader><DialogTitle>Add Category</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCat" class="space-y-4">
                <div><Label>Name</Label><Input v-model="catForm.name" /><InputError :message="catForm.errors.name" /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showCatDialog = false">Cancel</Button><Button type="submit" :disabled="catForm.processing">Create</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
