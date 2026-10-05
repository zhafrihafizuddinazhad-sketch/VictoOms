<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SampleOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerSampleRequestController extends Controller
{
    public function create()
    {
        return view('customer-sample-requests.create');
    }

    public function store(Request $request)
    {
        $method = $request->input('collection_method');
        $collectionDate = $method === 'lalamove' ? 'delivery_date' : 'pickup_date';
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.item_type' => ['required', Rule::in(['shirt', 'short', 'others'])],
            'items.*.sample_name' => ['nullable', 'required_if:items.*.item_type,others', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.fabric' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:5000'],
            'collection_method' => ['required', Rule::in(['office', 'lalamove'])],
            'pickup_date' => [$method === 'office' ? 'required' : 'nullable', 'date'],
            'delivery_date' => [$method === 'lalamove' ? 'required' : 'nullable', 'date'],
            'delivery_address' => [$method === 'lalamove' ? 'required' : 'nullable', 'string', 'max:2000'],
            'return_date' => ['required', 'date', 'after_or_equal:' . $collectionDate],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'return_date.after_or_equal' => 'The return date must be on or after the pickup or delivery date.',
            'items.*.sample_name.required_if' => 'Please specify the sample you are borrowing.',
        ]);
        $validator->after(function ($validator) use ($request): void {
            foreach ($request->input('items', []) as $index => $item) {
                if (($item['item_type'] ?? null) === 'others' && blank($item['sample_name'] ?? null)) {
                    $validator->errors()->add("items.$index.sample_name", 'Please specify the sample you are borrowing.');
                }
            }
        });
        $validator->after(function ($validator) use ($request): void {
            $phoneInput = $request->input('phone');
            $phone = preg_replace('/[\s().-]+/', '', is_string($phoneInput) ? $phoneInput : '');
            $digits = preg_replace('/\D+/', '', $phone);
            if (str_starts_with($digits, '60')) {
                $digits = '0' . substr($digits, 2);
            }
            if (! preg_match('/^(?:01\d{8,9}|0[2-9]\d{7,8})$/', $digits)) {
                $validator->errors()->add('phone', 'Enter a valid Malaysian phone number, including its area or mobile prefix.');
            }
        });
        $data = $validator->validate();

        $phoneDigits = preg_replace('/\D+/', '', $data['phone']);
        if (str_starts_with($phoneDigits, '60')) {
            $phoneDigits = '0' . substr($phoneDigits, 2);
        }
        $data['phone'] = $phoneDigits;

        try {
            $order = DB::transaction(function () use ($data): SampleOrder {
                $customer = Customer::query()->where('phone', $data['phone'])->lockForUpdate()->first();
                if (! $customer) {
                    $customer = new Customer(['phone' => $data['phone']]);
                }
                $customer->fill([
                    'customer_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'company' => $data['company'] ?? $customer->company,
                    'email' => $data['email'] ?? $customer->email,
                ]);
                $customer->save();

                do {
                    $orderNumber = 'SO-' . now()->format('YmdHis') . Str::upper(Str::random(4));
                } while (SampleOrder::where('order_number', $orderNumber)->exists());

                $order = SampleOrder::create([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->customer_name,
                    'order_number' => $orderNumber,
                    'collection_method' => $data['collection_method'],
                    'pickup_date' => $data['collection_method'] === 'office' ? $data['pickup_date'] : null,
                    'delivery_date' => $data['collection_method'] === 'lalamove' ? $data['delivery_date'] : null,
                    'delivery_address' => $data['collection_method'] === 'lalamove' ? $data['delivery_address'] : null,
                    'return_date' => $data['return_date'],
                    'deposit_amount' => 0,
                    'deposit_status' => 'pending',
                    'status' => 'pending_payment',
                    'customer_token' => (string) Str::uuid(),
                    'created_source' => 'customer',
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($data['items'] as $item) {
                    $order->sampleItems()->create([
                        'item_type' => $item['item_type'],
                        'sample_name' => $item['item_type'] === 'others' ? trim($item['sample_name']) : null,
                        'quantity' => $item['quantity'],
                        'fabric' => $item['fabric'] ?? null,
                        'description' => $item['description'] ?? null,
                    ]);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput($request->except(['_token']))
                ->withErrors(['request' => 'We could not save your sample request. Your information has not been submitted. Please try again.']);
        }

        $request->session()->put('sample_request_id', $order->id);
        return redirect()->route('customer.sample-request.success');
    }

    public function success(Request $request)
    {
        $sampleOrder = SampleOrder::query()->with('customer', 'sampleItems')
            ->whereKey($request->session()->get('sample_request_id'))
            ->firstOrFail();
        abort_unless($sampleOrder->created_source === 'customer', 404);
        $configuredNumber = preg_replace('/\D+/', '', (string) config('sample.owner_whatsapp'));
        if (str_starts_with($configuredNumber, '0')) {
            $configuredNumber = '60' . substr($configuredNumber, 1);
        }
        $whatsappUrl = null;
        if ($configuredNumber !== '') {
            $lines = [
                'Hi Victo, I would like to request samples.',
                '',
                'Reference: ' . $sampleOrder->order_number,
                'Name: ' . $sampleOrder->customer_name,
            ];
            if ($sampleOrder->customer?->company) $lines[] = 'Company: ' . $sampleOrder->customer->company;
            $lines[] = 'Collection: ' . ($sampleOrder->collection_method === 'office' ? 'Office pickup' : 'Lalamove');
            if ($sampleOrder->collection_method === 'office') {
                $lines[] = 'Pickup date: ' . $sampleOrder->pickup_date?->format('d/m/Y');
            } else {
                $lines[] = 'Delivery date: ' . $sampleOrder->delivery_date?->format('d/m/Y');
            }
            $lines[] = 'Return date: ' . $sampleOrder->return_date?->format('d/m/Y');
            $lines[] = '';
            $lines[] = 'Items:';
            foreach ($sampleOrder->sampleItems as $index => $item) {
                $itemLabel = $item->item_type === 'others' ? 'Others — ' . $item->sample_name : ucfirst($item->item_type);
                $line = ($index + 1) . '. ' . $itemLabel . ' · Qty ' . $item->quantity;
                if ($item->fabric) $line .= ' · ' . $item->fabric;
                $lines[] = $line;
            }
            $lines[] = '';
            $lines[] = 'I would like to discuss this request and the deposit. Thank you.';
            $whatsappUrl = 'https://wa.me/' . $configuredNumber . '?text=' . rawurlencode(implode("\n", $lines));
        }

        return view('customer-sample-requests.success', compact('sampleOrder', 'whatsappUrl'));
    }
}
