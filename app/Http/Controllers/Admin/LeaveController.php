<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');

        return Inertia::render('admin/Leaves/Index', [
            'leaves' => Leave::with(['staff.user:id,name', 'staff:id,user_id,employee_no', 'approver:id,name'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn ($l) => [
                    'id' => $l->id,
                    'staff_name' => $l->staff->user->name,
                    'employee_no' => $l->staff->employee_no,
                    'type' => $l->type->value,
                    'from_date' => $l->from_date->format('Y-m-d'),
                    'to_date' => $l->to_date->format('Y-m-d'),
                    'days' => $l->days,
                    'reason' => $l->reason,
                    'status' => $l->status->value,
                    'approver' => $l->approver?->name,
                ]),
            'selectedStatus' => $status,
        ]);
    }

    public function approve(Leave $leave): RedirectResponse
    {
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Update leave balance
        LeaveBalance::where('staff_id', $leave->staff_id)
            ->where('year', $leave->from_date->year)
            ->where('type', $leave->type)
            ->increment('used', $leave->days);

        return back()->with('flash', ['type' => 'success', 'message' => 'Leave approved.']);
    }

    public function reject(Leave $leave): RedirectResponse
    {
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Leave rejected.']);
    }
}
