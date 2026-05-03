<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, MapPin } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Stop = { id: number; name: string; pickup_time: string | null; drop_time: string | null; order: number };
type Vehicle = { id: number; registration_no: string; type: string; capacity: number; driver: { name: string } | null; status: string };
type RouteData = { id: number; name: string; fare: string; distance_km: string | null; vehicle: Vehicle | null; stops: Stop[]; student_transports_count: number };

const props = defineProps<{ routes: RouteData[]; vehicles: Vehicle[] }>();

// Vehicle dialog
const showVehicleDialog = ref(false);
const vehicleForm = useForm({ registration_no: '', type: 'bus', capacity: 40, driver_id: null as number | null });
function submitVehicle() { vehicleForm.post('/admin/vehicles', { onSuccess: () => { showVehicleDialog.value = false; vehicleForm.reset(); } }); }
function destroyVehicle(id: number) { if (confirm('Delete vehicle?')) router.delete(`/admin/vehicles/${id}`); }

// Route dialog
const showRouteDialog = ref(false);
const routeForm = useForm({ name: '', vehicle_id: null as number | null, fare: 0, distance_km: '' });
function submitRoute() { routeForm.post('/admin/routes', { onSuccess: () => { showRouteDialog.value = false; routeForm.reset(); } }); }
function destroyRoute(id: number) { if (confirm('Delete route?')) router.delete(`/admin/routes/${id}`); }

// Stop dialog
const showStopDialog = ref(false);
const stopRouteId = ref(0);
const stopForm = useForm({ name: '', pickup_time: '', drop_time: '', order: 1 });
function openAddStop(routeId: number) { stopRouteId.value = routeId; stopForm.reset(); showStopDialog.value = true; }
function submitStop() { stopForm.post(`/admin/routes/${stopRouteId.value}/stops`, { onSuccess: () => { showStopDialog.value = false; stopForm.reset(); } }); }
function destroyStop(id: number) { router.delete(`/admin/route-stops/${id}`); }
</script>

<template>
    <Head title="Transport" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Transport" description="Manage vehicles, routes, and stops." />
            <div class="flex gap-2">
                <Button variant="outline" @click="showVehicleDialog = true"><Plus class="mr-1 size-4" /> Vehicle</Button>
                <Button @click="showRouteDialog = true"><Plus class="mr-1 size-4" /> Route</Button>
            </div>
        </div>

        <!-- Vehicles -->
        <div>
            <h3 class="text-sm font-semibold mb-2">Vehicles</h3>
            <div class="flex flex-wrap gap-3">
                <div v-for="v in vehicles" :key="v.id" class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm">
                    <div><div class="font-medium">{{ v.registration_no }}</div><div class="text-xs text-muted-foreground">{{ v.type }} | {{ v.capacity }} seats</div></div>
                    <button @click="destroyVehicle(v.id)" class="text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
                </div>
                <div v-if="!vehicles.length" class="text-sm text-muted-foreground">No vehicles.</div>
            </div>
        </div>

        <!-- Routes -->
        <div class="space-y-4">
            <Card v-for="route in routes" :key="route.id">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">{{ route.name }}</CardTitle>
                        <div class="text-xs text-muted-foreground mt-1">
                            Fare: {{ Number(route.fare).toLocaleString() }} | {{ route.student_transports_count }} students
                            <span v-if="route.vehicle"> | {{ route.vehicle.registration_no }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="openAddStop(route.id)"><MapPin class="mr-1 size-3" /> Add Stop</Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroyRoute(route.id)"><Trash2 class="size-3" /></Button>
                    </div>
                </CardHeader>
                <CardContent v-if="route.stops.length">
                    <div class="flex flex-wrap gap-2">
                        <div v-for="stop in route.stops" :key="stop.id" class="flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm">
                            <Badge variant="outline" class="text-xs">{{ stop.order }}</Badge>
                            <span>{{ stop.name }}</span>
                            <span v-if="stop.pickup_time" class="text-xs text-muted-foreground">{{ stop.pickup_time?.substring(0, 5) }}</span>
                            <button @click="destroyStop(stop.id)" class="text-muted-foreground hover:text-destructive"><Trash2 class="size-2.5" /></button>
                        </div>
                    </div>
                </CardContent>
                <CardContent v-else><p class="text-sm text-muted-foreground">No stops defined.</p></CardContent>
            </Card>
        </div>
        <div v-if="!routes.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No routes defined.</div>
    </div>

    <Dialog v-model:open="showVehicleDialog">
        <DialogContent><DialogHeader><DialogTitle>Add Vehicle</DialogTitle></DialogHeader>
            <form @submit.prevent="submitVehicle" class="space-y-4">
                <div><Label>Registration No</Label><Input v-model="vehicleForm.registration_no" /><InputError :message="vehicleForm.errors.registration_no" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Type</Label><Input v-model="vehicleForm.type" placeholder="bus" /></div>
                    <div><Label>Capacity</Label><Input type="number" v-model.number="vehicleForm.capacity" min="1" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showVehicleDialog = false">Cancel</Button><Button type="submit" :disabled="vehicleForm.processing">Add</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showRouteDialog">
        <DialogContent><DialogHeader><DialogTitle>Add Route</DialogTitle></DialogHeader>
            <form @submit.prevent="submitRoute" class="space-y-4">
                <div><Label>Name</Label><Input v-model="routeForm.name" placeholder="e.g. Route 1 - North" /><InputError :message="routeForm.errors.name" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Vehicle</Label>
                        <select v-model="routeForm.vehicle_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option>
                            <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.registration_no }}</option>
                        </select>
                    </div>
                    <div><Label>Fare</Label><Input type="number" v-model.number="routeForm.fare" min="0" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showRouteDialog = false">Cancel</Button><Button type="submit" :disabled="routeForm.processing">Create</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="showStopDialog">
        <DialogContent><DialogHeader><DialogTitle>Add Stop</DialogTitle></DialogHeader>
            <form @submit.prevent="submitStop" class="space-y-4">
                <div><Label>Stop Name</Label><Input v-model="stopForm.name" /><InputError :message="stopForm.errors.name" /></div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Pickup</Label><Input type="time" v-model="stopForm.pickup_time" /></div>
                    <div><Label>Drop</Label><Input type="time" v-model="stopForm.drop_time" /></div>
                    <div><Label>Order</Label><Input type="number" v-model.number="stopForm.order" min="1" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showStopDialog = false">Cancel</Button><Button type="submit" :disabled="stopForm.processing">Add</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
