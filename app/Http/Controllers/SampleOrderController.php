<?php

namespace App\Http\Controllers;

use App\Models\SampleOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SampleOrderController extends Controller
{
    public function create()
    {
        return view('sample-orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'collection_method' => ['required', Rule::in(['office', 'lalamove'])],
            'pickup_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:pickup_date',
            'deposit_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        do {
            $orderNumber = 'SO-' . now()->format('YmdHis') . Str::upper(Str::random(4));
        } while (SampleOrder::where('order_number', $orderNumber)->exists());

        $validated['order_number'] = $orderNumber;
        $validated['deposit_status'] = 'pending';
        $validated['status'] = 'pending_payment';
        $validated['customer_token'] = Str::uuid();

        $sampleOrder = SampleOrder::create($validated);

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample order created successfully.');
    }

    public function index()
    {
        $query = SampleOrder::query();

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($orders) use ($search) {
                $orders->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('collection_method')) {
            $query->where('collection_method', request('collection_method'));
        }
        if (request()->filled('customer')) {
            $query->where('customer_name', 'like', '%' . request('customer') . '%');
        }
        if (request()->filled('pickup_date')) {
            $query->whereDate('pickup_date', request('pickup_date'));
        }
        if (request()->filled('return_date')) {
            $query->whereDate('return_date', request('return_date'));
        }
        $activeStatuses = ['ready_for_collection', 'collected', 'in_transit', 'received', 'return_pending', 'returned'];
        if (request('return_due') === 'upcoming') {
            $query->whereIn('status', $activeStatuses)->whereDate('return_date', '>=', today())->whereDate('return_date', '<=', today()->addDays(7));
        } elseif (request('return_due') === 'overdue') {
            $query->whereIn('status', $activeStatuses)->whereDate('return_date', '<', today());
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'total' => SampleOrder::count(),
            'pending_payment' => SampleOrder::where('deposit_status', 'pending')->count(),
            'currently_out' => SampleOrder::whereIn('status', ['collected', 'in_transit', 'received'])->count(),
            'upcoming_returns' => SampleOrder::whereIn('status', $activeStatuses)->whereDate('return_date', '>=', today())->whereDate('return_date', '<=', today()->addDays(7))->count(),
            'overdue_returns' => SampleOrder::whereIn('status', $activeStatuses)->whereDate('return_date', '<', today())->count(),
            'office' => SampleOrder::where('collection_method', 'office')->count(),
            'lalamove' => SampleOrder::where('collection_method', 'lalamove')->count(),
            'active' => SampleOrder::whereIn('status', $activeStatuses)->count(),
            'completed' => SampleOrder::where('status', 'completed')->count(),
        ];

        return view('sample-orders.index', compact('orders', 'summary'));
    }

    public function show(SampleOrder $sampleOrder)
    {
        $sampleOrder->load([
            'sampleItems.sample',
            'payments.confirmedBy',
            'photos',
            'sampleReturn',
        ]);
        $photoCheckpoints = $sampleOrder->collection_method === 'office'
            ? [
                ['key' => 'before_handover', 'legacy' => [], 'label' => 'Before Handover', 'description' => 'Photograph the sample just before the customer collects it.', 'customer' => false],
                ['key' => 'after_return', 'legacy' => [], 'label' => 'After Return', 'description' => 'Photograph the sample after it is returned to Victo.', 'customer' => false],
            ]
            : [
                ['key' => 'before_delivery', 'legacy' => ['before_handover'], 'label' => 'Before Delivery', 'description' => 'Photograph the sample before handing it to Lalamove.', 'customer' => false],
                ['key' => 'customer_received', 'legacy' => [], 'label' => 'Customer Received', 'description' => 'The customer uploads a photo after receiving the sample.', 'customer' => true],
                ['key' => 'before_customer_return', 'legacy' => ['before_return'], 'label' => 'Before Customer Return', 'description' => 'The customer uploads a photo before returning the sample.', 'customer' => true],
                ['key' => 'after_return', 'legacy' => [], 'label' => 'After Return', 'description' => 'Photograph the sample after it arrives back at the office.', 'customer' => false],
            ];

        $photosByCheckpoint = collect($photoCheckpoints)->mapWithKeys(function (array $checkpoint) use ($sampleOrder): array {
            $types = array_merge([$checkpoint['key']], $checkpoint['legacy']);
            return [$checkpoint['key'] => $sampleOrder->photos->whereIn('photo_type', $types)->values()];
        });
        $customerPhotoUrl = $sampleOrder->collection_method === 'lalamove'
            ? route('customer-sample-photos.show', $sampleOrder->customer_token)
            : null;

        return view('sample-orders.show', compact('sampleOrder', 'photosByCheckpoint', 'photoCheckpoints', 'customerPhotoUrl'));
    }

    public function updateStatus(Request $request, SampleOrder $sampleOrder)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['ready_for_collection', 'collected', 'in_transit', 'return_pending', 'completed'])],
        ]);
        $next = $data['status'];
        $office = $sampleOrder->collection_method === 'office';
        $depositSatisfied = (float) $sampleOrder->deposit_amount <= 0 || $sampleOrder->deposit_status === 'paid';
        $photoTypes = $sampleOrder->photos()->pluck('photo_type')->all();

        $allowed = match ($next) {
            'ready_for_collection' => $office && $sampleOrder->status === 'pending_payment' && $depositSatisfied,
            'collected' => $office && $sampleOrder->status === 'ready_for_collection'
                && in_array('before_handover', $photoTypes, true),
            'in_transit' => ! $office && $sampleOrder->status === 'pending_payment' && $depositSatisfied
                && (in_array('before_delivery', $photoTypes, true) || in_array('before_handover', $photoTypes, true)),
            'return_pending' => $office && $sampleOrder->status === 'collected',
            'completed' => $sampleOrder->status === 'returned'
                && $sampleOrder->sampleReturn()->exists()
                && in_array('after_return', $photoTypes, true),
            default => false,
        };

        if (! $allowed) {
            throw ValidationException::withMessages([
                'status' => 'This step is not available yet. Check the deposit, collection method, and required photos.',
            ]);
        }

        $sampleOrder->update(['status' => $next]);

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample order progress updated.');
    }
}
