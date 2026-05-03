<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Invoice::with(['student.user:id,name', 'student:id,user_id,admission_no'])
            ->withCount('payments');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('admission_no', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        return Inertia::render('admin/Invoices/Index', [
            'invoices' => $query->orderByDesc('created_at')->paginate(20)->through(fn ($inv) => [
                'id' => $inv->id,
                'invoice_no' => $inv->invoice_no,
                'student_name' => $inv->student->user->name,
                'admission_no' => $inv->student->admission_no,
                'total_amount' => $inv->total_amount,
                'discount_amount' => $inv->discount_amount,
                'paid_amount' => $inv->paid_amount,
                'balance' => $inv->balanceDue(),
                'due_date' => $inv->due_date->format('Y-m-d'),
                'status' => $inv->status->value,
                'payments_count' => $inv->payments_count,
                'created_at' => $inv->created_at->format('Y-m-d'),
            ]),
            'filters' => $request->only('status', 'search'),
            'stats' => [
                'total' => Invoice::sum('total_amount'),
                'collected' => Invoice::sum('paid_amount'),
                'pending' => Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum(DB::raw('total_amount - discount_amount - paid_amount')),
                'overdue' => Invoice::where('status', 'overdue')->count(),
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $section = Section::with('classLevel')->findOrFail($validated['section_id']);
        $currentYear = AcademicYear::current();

        $structures = FeeStructure::with('category')
            ->where('class_level_id', $section->class_level_id)
            ->where('academic_year_id', $currentYear->id)
            ->get();

        if ($structures->isEmpty()) {
            return back()->withErrors(['section_id' => 'No fee structure defined for this class.']);
        }

        $students = Student::where('current_section_id', $validated['section_id'])
            ->where('status', 'active')
            ->get();

        $count = 0;
        DB::transaction(function () use ($students, $structures, $validated, $currentYear, &$count) {
            foreach ($students as $student) {
                $totalAmount = $structures->sum('amount');

                $invoice = Invoice::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $currentYear->id,
                    'invoice_no' => Invoice::generateInvoiceNo(),
                    'total_amount' => $totalAmount,
                    'due_date' => $validated['due_date'],
                    'status' => 'unpaid',
                    'generated_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($structures as $structure) {
                    $invoice->items()->create([
                        'fee_structure_id' => $structure->id,
                        'description' => $structure->category->name,
                        'amount' => $structure->amount,
                    ]);
                }

                $count++;
            }
        });

        return back()->with('flash', ['type' => 'success', 'message' => "Generated {$count} invoices."]);
    }

    public function show(Invoice $invoice): Response
    {
        return Inertia::render('admin/Invoices/Show', [
            'invoice' => [
                ...$invoice->load(['student.user', 'items', 'payments.receiver:id,name'])->toArray(),
                'balance' => $invoice->balanceDue(),
            ],
        ]);
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balanceDue(),
            'method' => 'required|in:cash,card,bank,online,cheque',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($invoice, $validated, $request) {
            $invoice->lockForUpdate();

            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'paid_at' => now(),
                'received_by' => $request->user()->id,
                'receipt_no' => Payment::generateReceiptNo(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->increment('paid_amount', $validated['amount']);
            $invoice->recalculateStatus();
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Payment of ' . number_format($validated['amount'], 2) . ' recorded.']);
    }
}
