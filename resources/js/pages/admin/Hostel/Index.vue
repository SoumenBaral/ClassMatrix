<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Room = { id: number; room_no: string; capacity: number; type: string; rent: string; active_allocations_count: number };
type Teacher = { id: number; name: string };
type Hostel = { id: number; name: string; type: string; warden: { name: string } | null; rooms_count: number; rooms: Room[] };

const props = defineProps<{ hostels: Hostel[]; teachers: Teacher[] }>();

const showHostelDialog = ref(false);
const editing = ref<Hostel | null>(null);
const form = useForm({ name: '', type: 'boys', warden_id: null as number | null });

function openCreate() { editing.value = null; form.reset(); showHostelDialog.value = true; }
function openEdit(h: Hostel) { editing.value = h; form.name = h.name; form.type = h.type; form.warden_id = h.warden ? null : null; showHostelDialog.value = true; }
function submit() {
    if (editing.value) form.put(`/admin/hostels/${editing.value.id}`, { onSuccess: () => { showHostelDialog.value = false; } });
    else form.post('/admin/hostels', { onSuccess: () => { showHostelDialog.value = false; form.reset(); } });
}
function destroyHostel(id: number) { if (confirm('Delete hostel?')) router.delete(`/admin/hostels/${id}`); }

const showRoomDialog = ref(false);
const roomHostelId = ref(0);
const roomForm = useForm({ room_no: '', capacity: 2, type: 'non-ac', rent: 0 });
function openAddRoom(hostelId: number) { roomHostelId.value = hostelId; roomForm.reset(); roomForm.capacity = 2; showRoomDialog.value = true; }
function submitRoom() { roomForm.post(`/admin/hostels/${roomHostelId.value}/rooms`, { onSuccess: () => { showRoomDialog.value = false; roomForm.reset(); } }); }
function destroyRoom(id: number) { if (confirm('Delete room?')) router.delete(`/admin/rooms/${id}`); }
</script>

<template>
    <Head title="Hostel" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Hostel Management" description="Manage hostels, rooms, and allocations." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Add Hostel</Button>
        </div>

        <div class="space-y-4">
            <Card v-for="hostel in hostels" :key="hostel.id">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">{{ hostel.name }}</CardTitle>
                        <div class="flex gap-2 mt-1">
                            <Badge :variant="hostel.type === 'boys' ? 'default' : 'secondary'" class="capitalize">{{ hostel.type }}</Badge>
                            <span class="text-xs text-muted-foreground">{{ hostel.rooms_count }} rooms | Warden: {{ hostel.warden?.name ?? 'None' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="openAddRoom(hostel.id)"><Plus class="mr-1 size-3" /> Room</Button>
                        <Button variant="ghost" size="sm" @click="openEdit(hostel)"><Pencil class="size-3" /></Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroyHostel(hostel.id)"><Trash2 class="size-3" /></Button>
                    </div>
                </CardHeader>
                <CardContent v-if="hostel.rooms.length">
                    <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        <div v-for="room in hostel.rooms" :key="room.id" class="flex items-center justify-between rounded-lg border p-2 text-sm">
                            <div>
                                <div class="font-medium">{{ room.room_no }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ room.active_allocations_count }}/{{ room.capacity }} |
                                    <Badge variant="outline" class="text-xs">{{ room.type }}</Badge>
                                </div>
                            </div>
                            <button @click="destroyRoom(room.id)" class="text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
                        </div>
                    </div>
                </CardContent>
                <CardContent v-else><p class="text-sm text-muted-foreground">No rooms added.</p></CardContent>
            </Card>
        </div>
        <div v-if="!hostels.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No hostels created.</div>
    </div>

    <Dialog v-model:open="showHostelDialog">
        <DialogContent><DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Add' }} Hostel</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Name</Label><Input v-model="form.name" /><InputError :message="form.errors.name" /></div>
                <div>
                    <Label>Type</Label>
                    <select v-model="form.type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="boys">Boys</option><option value="girls">Girls</option>
                    </select>
                </div>
                <div>
                    <Label>Warden</Label>
                    <select v-model="form.warden_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showHostelDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showRoomDialog">
        <DialogContent><DialogHeader><DialogTitle>Add Room</DialogTitle></DialogHeader>
            <form @submit.prevent="submitRoom" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Room No</Label><Input v-model="roomForm.room_no" placeholder="101" /><InputError :message="roomForm.errors.room_no" /></div>
                    <div><Label>Capacity</Label><Input type="number" v-model.number="roomForm.capacity" min="1" /></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Type</Label>
                        <select v-model="roomForm.type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="non-ac">Non-AC</option><option value="ac">AC</option>
                        </select>
                    </div>
                    <div><Label>Rent</Label><Input type="number" v-model.number="roomForm.rent" min="0" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showRoomDialog = false">Cancel</Button><Button type="submit" :disabled="roomForm.processing">Add</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
