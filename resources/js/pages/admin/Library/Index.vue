<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Category = { id: number; name: string; books_count: number };
type Book = {
    id: number; title: string; author: string; isbn: string | null;
    publisher: string | null; year: number | null; shelf: string | null;
    total_copies: number; available_copies: number;
    category: { id: number; name: string } | null;
};
type PaginatedBooks = { data: Book[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    books: PaginatedBooks;
    categories: Category[];
    filters: { search?: string; category_id?: string };
}>();

const search = ref(props.filters.search ?? '');

function filter() {
    router.get('/admin/library', { search: search.value || undefined }, { preserveState: true });
}

const showDialog = ref(false);
const editing = ref<Book | null>(null);
const form = useForm({
    title: '', author: '', isbn: '', publisher: '', year: null as number | null,
    category_id: null as number | null, shelf: '', total_copies: 1,
});

function openCreate() { editing.value = null; form.reset(); form.total_copies = 1; showDialog.value = true; }

function openEdit(b: Book) {
    editing.value = b;
    form.title = b.title; form.author = b.author; form.isbn = b.isbn ?? '';
    form.publisher = b.publisher ?? ''; form.year = b.year;
    form.category_id = b.category?.id ?? null; form.shelf = b.shelf ?? '';
    form.total_copies = b.total_copies;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/books/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    } else {
        form.post('/admin/books', { onSuccess: () => { showDialog.value = false; form.reset(); } });
    }
}

function destroy(b: Book) { if (confirm(`Delete "${b.title}"?`)) router.delete(`/admin/books/${b.id}`); }

// Category
const showCatDialog = ref(false);
const catForm = useForm({ name: '' });
function submitCat() { catForm.post('/admin/book-categories', { onSuccess: () => { showCatDialog.value = false; catForm.reset(); } }); }
function destroyCat(id: number) { if (confirm('Delete?')) router.delete(`/admin/book-categories/${id}`); }
</script>

<template>
    <Head title="Library" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Library" description="Manage books and categories." />
            <div class="flex gap-2">
                <Button variant="outline" @click="showCatDialog = true"><Plus class="mr-1 size-4" /> Category</Button>
                <Button @click="openCreate"><Plus class="mr-1 size-4" /> Add Book</Button>
            </div>
        </div>

        <!-- Categories tags -->
        <div class="flex flex-wrap gap-2">
            <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-1 rounded-lg border px-3 py-1 text-sm">
                {{ cat.name }} <Badge variant="secondary" class="ml-1">{{ cat.books_count }}</Badge>
                <button @click="destroyCat(cat.id)" class="ml-1 text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
            </div>
        </div>

        <div class="flex items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Search title, author, ISBN..." class="pl-9 w-64" />
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Title</th>
                        <th class="px-4 py-3 text-left font-medium">Author</th>
                        <th class="px-4 py-3 text-left font-medium">Category</th>
                        <th class="px-4 py-3 text-center font-medium">Copies</th>
                        <th class="px-4 py-3 text-center font-medium">Available</th>
                        <th class="px-4 py-3 text-left font-medium">Shelf</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="book in books.data" :key="book.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ book.title }}</div>
                            <div v-if="book.isbn" class="text-xs text-muted-foreground">ISBN: {{ book.isbn }}</div>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ book.author }}</td>
                        <td class="px-4 py-2">{{ book.category?.name ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">{{ book.total_copies }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="book.available_copies > 0 ? 'default' : 'destructive'">{{ book.available_copies }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ book.shelf ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(book)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(book)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="books.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in books.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent><DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Add' }} Book</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Title</Label><Input v-model="form.title" /><InputError :message="form.errors.title" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Author</Label><Input v-model="form.author" /><InputError :message="form.errors.author" /></div>
                    <div><Label>ISBN</Label><Input v-model="form.isbn" /></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Category</Label>
                        <select v-model="form.category_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div><Label>Shelf</Label><Input v-model="form.shelf" placeholder="A1" /></div>
                    <div><Label>Copies</Label><Input type="number" v-model.number="form.total_copies" min="1" /></div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Add' }}</Button>
                </DialogFooter>
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
