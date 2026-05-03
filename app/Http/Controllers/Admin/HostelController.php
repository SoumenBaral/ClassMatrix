<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HostelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Hostel/Index', [
            'hostels' => Hostel::with(['warden:id,name', 'rooms' => fn ($q) => $q->withCount('activeAllocations')])
                ->withCount('rooms')
                ->get(),
            'teachers' => fn () => User::where('user_type', 'teacher')->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:boys,girls',
            'warden_id' => 'nullable|exists:users,id',
        ]);

        Hostel::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Hostel created.']);
    }

    public function update(Request $request, Hostel $hostel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:boys,girls',
            'warden_id' => 'nullable|exists:users,id',
        ]);

        $hostel->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Hostel updated.']);
    }

    public function destroy(Hostel $hostel): RedirectResponse
    {
        $hostel->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Hostel deleted.']);
    }

    // --- Rooms ---
    public function storeRoom(Request $request, Hostel $hostel): RedirectResponse
    {
        $validated = $request->validate([
            'room_no' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:ac,non-ac',
            'rent' => 'required|numeric|min:0',
        ]);

        $hostel->rooms()->create($validated);
        $hostel->increment('total_rooms');

        return back()->with('flash', ['type' => 'success', 'message' => 'Room added.']);
    }

    public function destroyRoom(Room $room): RedirectResponse
    {
        $room->hostel->decrement('total_rooms');
        $room->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Room removed.']);
    }
}
