<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Transport/Index', [
            'routes' => Route::with(['vehicle:id,registration_no,type', 'stops'])
                ->withCount('studentTransports')
                ->orderBy('name')
                ->get(),
            'vehicles' => Vehicle::with('driver:id,name')->orderBy('registration_no')->get(),
        ]);
    }

    // --- Vehicles ---
    public function storeVehicle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_no' => 'required|string|max:20|unique:vehicles,registration_no',
            'type' => 'required|string|max:30',
            'capacity' => 'required|integer|min:1',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        Vehicle::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Vehicle added.']);
    }

    public function destroyVehicle(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Vehicle deleted.']);
    }

    // --- Routes ---
    public function storeRoute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fare' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        Route::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Route created.']);
    }

    public function updateRoute(Request $request, Route $route): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fare' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        $route->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Route updated.']);
    }

    public function destroyRoute(Route $route): RedirectResponse
    {
        $route->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Route deleted.']);
    }

    // --- Stops ---
    public function storeStop(Request $request, Route $route): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'pickup_time' => 'nullable|date_format:H:i',
            'drop_time' => 'nullable|date_format:H:i',
            'order' => 'required|integer|min:1',
        ]);

        $route->stops()->create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Stop added.']);
    }

    public function destroyStop(RouteStop $routeStop): RedirectResponse
    {
        $routeStop->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Stop removed.']);
    }
}
