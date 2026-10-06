<?php

namespace App\Http\Controllers;

use App\Models\SampleOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SampleOrderController extends Controller
{
    public function create()
    {
        return view('sample-orders.create');
    }

    public function store(Request $request)
    {
        $legacyPayload = ! $request->exists('full_name') && $request->exists('customer_name');
        if ($legacyPayload) {
            $request->merge(['full_name' => $request->input('customer_name')]);
        }
        $method = $request->input('collection_method');
        $collectionDate = $method === 'lalamove' && ! $legacyPayload ? 'delivery_date' : 'pickup_date';
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => [$legacyPayload ? 'nullable' : 'required', 'string', 'max:30'],
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'collection_method' => ['required', Rule::in(['office', 'lalamove'])],
            'pickup_date' => [$collectionDate === 'pickup_date' ? 'required' : 'nullable', 'date'],
            'delivery_date' => [$collectionDate === 'delivery_date' ? 'required' : 'nullable', 'date'],
            'delivery_address' => [$method === 'lalamove' && ! $legacyPayload ? 'required' : 'nullable', 'string', 'max:2000'],
            'return_date' => ['required', 'date', 'after_or_equal:' . $collectionDate],
            'deposit_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
            'items' => 'nullable|array|max:20',
            'items.*.item_type' => ['required', Rule::in(['shirt', 'short', 'others'])],
            'items.*.sample_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
            'items.*.fabric' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string|max:5000',
            'sample_photos' => 'nullable|array|max:10',
            'sample_photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        foreach ($request->input('items', []) as $index => $item) {
            if (($item['item_type'] ?? null) === 'others' && blank($item['sample_name'] ?? null)) {
                throw ValidationException::withMessages(["items.$index.sample_name" => 'Please specify the sample you are borrowing.']);
            }
        }
        if (filled($validated['phone'] ?? null)) {
            $digits = preg_replace('/\D+/', '', $validated['phone']);
            if (str_starts_with($digits, '60')) $digits = '0' . substr($digits, 2);
            $validated['phone'] = $digits;
        }

        do {
            $orderNumber = 'SO-' . now()->format('YmdHis') . Str::upper(Str::random(4));
        } while (SampleOrder::where('order_number', $orderNumber)->exists());

        $customerName = $validated['full_name'];
        $customerDetails = [
            'customer_name' => $customerName,
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'email' => $validated['email'] ?? null,
        ];
        unset($validated['full_name'], $validated['phone'], $validated['company'], $validated['email']);
        $validated['customer_name'] = $customerName;
        $validated['pickup_date'] = $method === 'office' || $legacyPayload ? ($validated['pickup_date'] ?? null) : null;
        $validated['delivery_date'] = $method === 'lalamove' ? ($validated['delivery_date'] ?? null) : null;
        $validated['delivery_address'] = $method === 'lalamove' ? ($validated['delivery_address'] ?? null) : null;
        $validated['order_number'] = $orderNumber;
        $validated['deposit_status'] = 'pending';
        $validated['status'] = 'pending_payment';
        $validated['customer_token'] = Str::uuid();

        $items = $validated['items'] ?? [];
        $photos = $validated['sample_photos'] ?? [];
        unset($validated['items'], $validated['sample_photos']);
        $storedPaths = [];

        try {
            $sampleOrder = DB::transaction(function () use ($validated, $items, $photos, $request, $customerDetails, &$storedPaths): SampleOrder {
                if ($customerDetails['phone']) {
                    $customer = Customer::query()->where('phone', $customerDetails['phone'])->lockForUpdate()->first() ?? new Customer();
                    $customer->fill(array_filter($customerDetails, fn ($value) => $value !== null && $value !== ''));
                    $customer->save();
                    $validated['customer_id'] = $customer->id;
                }
                $order = SampleOrder::create($validated);

                foreach ($items as $item) {
                    if (($item['item_type'] ?? null) !== 'others') $item['sample_name'] = null;
                    $order->sampleItems()->create($item);
                }

                foreach ($photos as $photo) {
                    $path = $photo->store("sample-orders/{$order->id}/photos", 'local');
                    if (! $path) {
                        throw new \RuntimeException('The sample photo could not be stored.');
                    }
                    $storedPaths[] = $path;
                    $order->photos()->create([
                        'photo_type' => 'original',
                        'uploaded_by_user_id' => $request->user()->id,
                        'uploaded_by_customer' => false,
                        'file_path' => $path,
                    ]);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            report($exception);

            return redirect()->route('sample-orders.create')
                ->withInput($request->except('sample_photos'))
                ->withErrors(['order' => 'The sample order could not be saved. Please try again.']);
        }

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample order created successfully.');
    }

    public function index()
    {
        $query = SampleOrder::query()
            ->with('customer')
            ->withSum([
                'payments as paid_deposit_amount' => fn ($payments) => $payments->where('status', 'paid'),
            ], 'amount');

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
            'customer',
            'sampleItems.sample',
            'payments.confirmedBy',
            'photos',
            'sampleReturn',
            'events',
        ]);
        $depositSatisfied = $sampleOrder->depositIsSatisfied();
        $samplePhotos = $sampleOrder->photos->where('photo_type', 'original')->values();
        $photoCheckpoints = $sampleOrder->collection_method === 'office'
            ? [
                ['key' => 'before_handover', 'legacy' => [], 'label' => 'Before Handover', 'description' => 'Photograph the sample just before the customer collects it.', 'customer' => false, 'available' => $sampleOrder->status === 'ready_for_collection' && $depositSatisfied],
                ['key' => 'after_return', 'legacy' => [], 'label' => 'After Return', 'description' => 'Photograph the sample after it is returned to Victo.', 'customer' => false, 'available' => $sampleOrder->status === 'returned'],
            ]
            : [
                ['key' => 'before_delivery', 'legacy' => ['before_handover'], 'label' => 'Before Delivery', 'description' => 'Photograph the sample before handing it to Lalamove.', 'customer' => false, 'available' => $sampleOrder->status === 'pending_payment' && $depositSatisfied],
                ['key' => 'customer_received', 'legacy' => [], 'label' => 'Customer Received', 'description' => 'The customer uploads a photo after receiving the sample.', 'customer' => true, 'available' => false],
                ['key' => 'before_return', 'legacy' => ['before_customer_return'], 'label' => 'Before Return', 'description' => 'The customer uploads a photo before returning the sample.', 'customer' => true, 'available' => false],
                ['key' => 'after_return', 'legacy' => [], 'label' => 'After Return', 'description' => 'Photograph the sample after it arrives back at the office.', 'customer' => false, 'available' => $sampleOrder->status === 'returned'],
            ];

        $photosByCheckpoint = collect($photoCheckpoints)->mapWithKeys(function (array $checkpoint) use ($sampleOrder): array {
            $types = array_merge([$checkpoint['key']], $checkpoint['legacy']);
            return [$checkpoint['key'] => $sampleOrder->photos->whereIn('photo_type', $types)->values()];
        });
        $customerPhotoUrl = $sampleOrder->collection_method === 'lalamove'
            ? route('customer-sample-photos.show', $sampleOrder->customer_token)
            : null;

        return view('sample-orders.show', compact('sampleOrder', 'samplePhotos', 'photosByCheckpoint', 'photoCheckpoints', 'customerPhotoUrl'));
    }

    public function updateStatus(Request $request, SampleOrder $sampleOrder)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['ready_for_collection', 'collected', 'in_transit', 'return_pending', 'completed'])],
        ]);
        $next = $data['status'];
        DB::transaction(function () use ($request, $sampleOrder, $next): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
            $office = $lockedOrder->collection_method === 'office';
            $depositSatisfied = $lockedOrder->depositIsSatisfied();
            $photoTypes = $lockedOrder->photos()->pluck('photo_type')->all();

            $allowed = match ($next) {
                'ready_for_collection' => $office && $lockedOrder->status === 'pending_payment' && $depositSatisfied,
                'collected' => $office && $lockedOrder->status === 'ready_for_collection'
                    && in_array('before_handover', $photoTypes, true),
                'in_transit' => ! $office && $lockedOrder->status === 'pending_payment' && $depositSatisfied
                    && (in_array('before_delivery', $photoTypes, true) || in_array('before_handover', $photoTypes, true)),
                'return_pending' => $office && $lockedOrder->status === 'collected',
                'completed' => $lockedOrder->status === 'returned'
                    && $lockedOrder->sampleReturn()->exists()
                    && in_array('after_return', $photoTypes, true),
                default => false,
            };

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'status' => 'This step is not available yet. Check the deposit, collection method, and required photos.',
                ]);
            }

            $lockedOrder->update(['status' => $next]);
            $eventKey = match ($next) {
                'ready_for_collection' => 'prepared_for_pickup',
                'collected' => 'customer_collected',
                'in_transit' => 'sent_with_lalamove',
                'return_pending' => 'return_expected',
                'completed' => 'order_completed',
            };
            $lockedOrder->events()->create([
                'event_key' => $eventKey,
                'user_id' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample order progress updated.');
    }
}
