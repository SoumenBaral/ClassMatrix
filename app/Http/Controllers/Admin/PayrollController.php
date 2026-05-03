<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        return Inertia::render('admin/Payroll/Index', [
            'payslips' => Payslip::with(['staff.user:id,name', 'staff:id,user_id,employee_no,designation'])
                ->where('month', $month)
                ->where('year', $year)
                ->orderBy('staff_id')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'staff_name' => $p->staff->user->name,
                    'employee_no' => $p->staff->employee_no,
                    'designation' => $p->staff->designation,
                    'basic' => $p->basic,
                    'gross' => $p->gross,
                    'net' => $p->net,
                    'status' => $p->status,
                    'paid_at' => $p->paid_at?->format('Y-m-d'),
                ]),
            'month' => (int) $month,
            'year' => (int) $year,
            'salaryStructures' => SalaryStructure::all(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2050',
        ]);

        $staffMembers = Staff::where('status', 'active')->get();
        $structures = SalaryStructure::all()->keyBy('designation');
        $count = 0;

        DB::transaction(function () use ($staffMembers, $structures, $validated, &$count) {
            foreach ($staffMembers as $staff) {
                $existing = Payslip::where('staff_id', $staff->id)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->exists();

                if ($existing) {
                    continue;
                }

                $structure = $structures->get($staff->designation);
                if (! $structure) {
                    continue;
                }

                Payslip::create([
                    'staff_id' => $staff->id,
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'basic' => $structure->basic,
                    'allowances' => [
                        'HRA' => $structure->hra,
                        'DA' => $structure->da,
                        'TA' => $structure->ta,
                        ...collect($structure->other_allowances ?? [])->mapWithKeys(fn ($a) => [$a['name'] => $a['amount']])->all(),
                    ],
                    'deductions' => [
                        'PF' => $structure->pf,
                        'Tax' => $structure->tax,
                        ...collect($structure->other_deductions ?? [])->mapWithKeys(fn ($d) => [$d['name'] => $d['amount']])->all(),
                    ],
                    'gross' => $structure->grossSalary(),
                    'net' => $structure->netSalary(),
                    'status' => 'generated',
                    'generated_at' => now(),
                ]);

                $count++;
            }
        });

        return back()->with('flash', ['type' => 'success', 'message' => "Generated {$count} payslips."]);
    }

    public function markPaid(Payslip $payslip): RedirectResponse
    {
        $payslip->update(['status' => 'paid', 'paid_at' => now()]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Payslip marked as paid.']);
    }

    // --- Salary Structures ---
    public function structures(): Response
    {
        return Inertia::render('admin/Payroll/Structures', [
            'structures' => SalaryStructure::orderBy('designation')->get(),
        ]);
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'designation' => 'required|string|max:100',
            'basic' => 'required|numeric|min:0',
            'hra' => 'numeric|min:0',
            'da' => 'numeric|min:0',
            'ta' => 'numeric|min:0',
            'pf' => 'numeric|min:0',
            'tax' => 'numeric|min:0',
        ]);

        SalaryStructure::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Salary structure created.']);
    }

    public function updateStructure(Request $request, SalaryStructure $salaryStructure): RedirectResponse
    {
        $validated = $request->validate([
            'designation' => 'required|string|max:100',
            'basic' => 'required|numeric|min:0',
            'hra' => 'numeric|min:0',
            'da' => 'numeric|min:0',
            'ta' => 'numeric|min:0',
            'pf' => 'numeric|min:0',
            'tax' => 'numeric|min:0',
        ]);

        $salaryStructure->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Salary structure updated.']);
    }

    public function destroyStructure(SalaryStructure $salaryStructure): RedirectResponse
    {
        $salaryStructure->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Salary structure deleted.']);
    }
}
