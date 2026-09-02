<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\OrderReference;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $search = $request->search;

    $status = $request->get('status');

    $sort = $request->get('sort', 'created_at');

    $direction = $request->get('direction', 'desc');


    $query = Order::with(['customer', 'items'])

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        ->when($search, function ($query) use ($search) {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'order_no',
                    'like',
                    "%{$search}%"
                )

                ->orWhereHas('customer', function ($customerQuery) use ($search) {

                    $customerQuery->where(
                        'customer_name',
                        'like',
                        "%{$search}%"
                    );

                });

            });

        })


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        ->when($status, function ($query) use ($status) {

            $query->where(
                'status',
                $status
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    switch ($sort) {

        case 'customer':

            $query->join(
                'customers',
                'orders.customer_id',
                '=',
                'customers.id'
            )
            ->select('orders.*')
            ->orderBy(
                'customers.customer_name',
                $direction
            );

            break;


        case 'status':

            if ($direction == 'asc') {

                $query->orderByRaw("
                    FIELD(
                        status,
                        'Pending',
                        'Assigned',
                        'In Progress',
                        'Pending Approval',
                        'Printing',
                        'Ready at HQ',
                        'Photo Session',
                        'Photo Completed',
                        'Out for Delivery',
                        'Waiting for Pickup',
                        'Completed'
                    )
                ");

            } else {

                $query->orderByRaw("
                    FIELD(
                        status,
                        'Completed',
                        'Waiting for Pickup',
                        'Out for Delivery',
                        'Photo Completed',
                        'Photo Session',
                        'Ready at HQ',
                        'Printing',
                        'Pending Approval',
                        'In Progress',
                        'Assigned',
                        'Pending'
                    )
                ");

            }

            break;


        default:

            $query->orderBy(
                $sort,
                $direction
            );

            break;

    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $orders = $query
        ->paginate(10)
        ->withQueryString();


    if (auth()->user()->hasRole('admin')) {

    return view('admin.orders.index', compact(
        'orders',
        'search',
        'sort',
        'direction',
        'status'
    ));

}

return view('orders.index', compact(
    'orders',
    'search',
    'sort',
    'direction',
    'status'
));
}

    public function create()
{
    $customers = Customer::orderBy('customer_name')->get();

    $designers = User::role('designer')
        ->orderBy('name')
        ->get();


    if (auth()->user()->hasRole('admin')) {

        return view('admin.orders.create', compact(
            'customers',
            'designers'
        ));

    }


    return view('orders.create', compact(
        'customers',
        'designers'
    ));
}

    public function store(Request $request)
{
    $validated = $request->validate([

        'customer_id' => 'required|exists:customers,id',

        'designer_id' => 'nullable|exists:users,id',

        'due_date' => 'required|date',

        'delivery_method' => 'required|in:Delivery,Self Pickup',

        'remarks' => 'nullable|string',

        'customer_brief' => 'nullable|string',

        'reference_files.*' =>
            'nullable|file|mimes:jpg,jpeg,png,pdf,ai,eps,svg,psd,cdr|max:51200',

        'reference_links' => 'nullable|array',

        'reference_links.*' => 'nullable|url',

        'product_name' => 'required|array|min:1',

        'product_name.*' =>
            'required|string|max:255',

        'quantity' => 'required|array|min:1',

        'quantity.*' =>
            'required|integer|min:1',

        'unit_price' => 'required|array|min:1',

        'unit_price.*' =>
            'required|numeric|min:0',

    ]);


    $order = null;


    DB::transaction(function () use (
        $validated,
        $request,
        &$order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Generate Order Number
        |--------------------------------------------------------------------------
        */

        $lastOrder = Order::latest('id')->first();

        $nextNumber = $lastOrder
            ? $lastOrder->id + 1
            : 1;

        $orderNo =
            'ORD-' .
            str_pad(
                $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Create Order
        |--------------------------------------------------------------------------
        */

        $order = Order::create([

            'customer_id' =>
                $validated['customer_id'],

            'designer_id' =>
                $validated['designer_id'] ?? null,

            'order_no' =>
                $orderNo,

            'due_date' =>
                $validated['due_date'],

            'remarks' =>
                $validated['remarks'] ?? null,

            'customer_brief' =>
                $validated['customer_brief'] ?? null,

            'delivery_method' =>
                $validated['delivery_method'],

            'status' =>
                empty($validated['designer_id'])
                    ? 'Pending'
                    : 'Assigned',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Save Order Items
        |--------------------------------------------------------------------------
        */

        foreach (
            $validated['product_name']
            as $index => $product
        ) {

            OrderItem::create([

                'order_id' =>
                    $order->id,

                'product_name' =>
                    $product,

                'quantity' =>
                    $validated['quantity'][$index],

                'unit_price' =>
                    $validated['unit_price'][$index],

                'subtotal' =>
                    $validated['quantity'][$index]
                    *
                    $validated['unit_price'][$index],

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Save Customer Reference Files
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('reference_files')) {

            foreach (
                $request->file('reference_files')
                as $file
            ) {

                $path =
                    $file->store(
                        'order-references',
                        'public'
                    );


                OrderReference::create([

                    'order_id' =>
                        $order->id,

                    'uploaded_by' =>
                        auth()->id(),

                    'reference_link' =>
                        null,

                    'file_name' =>
                        $file->getClientOriginalName(),

                    'file_path' =>
                        $path,

                    'file_extension' =>
                        strtolower(
                            $file->getClientOriginalExtension()
                        ),

                ]);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Save Customer Reference Links
        |--------------------------------------------------------------------------
        */

        if ($request->filled('reference_links')) {

            foreach (
                $request->reference_links
                as $link
            ) {

                if (!empty(trim($link))) {

                    OrderReference::create([

                        'order_id' =>
                            $order->id,

                        'uploaded_by' =>
                            auth()->id(),

                        'reference_link' =>
                            $link,

                        'file_name' =>
                            null,

                        'file_path' =>
                            null,

                        'file_extension' =>
                            null,

                    ]);

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Notify Designer
        |--------------------------------------------------------------------------
        */

        if ($order->designer_id) {

            Notification::create([

                'user_id' =>
                    $order->designer_id,

                'order_id' =>
                    $order->id,

                'title' =>
                    'New Order Assigned',

                'message' =>
                    'You have been assigned to Order '
                    . $order->order_no,

            ]);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Redirect According to Role
    |--------------------------------------------------------------------------
    */

    if (auth()->user()->hasRole('admin')) {

        return redirect()
            ->route('admin.orders.index')
            ->with(
                'success',
                'Order created successfully.'
            );

    }


    return redirect()
        ->route('orders.index')
        ->with(
            'success',
            'Order created successfully.'
        );
}

    public function show(Order $order)
{
    $order->load([
        'references',
        'designFiles.uploader',
        'items',
        'customer',
        'designer',
        'cameraman',
        'activityLogs.user',
        'jobOrders.items',
        'jobOrders.creator',

        // Repeat Order
        'originalOrder.designFiles.uploader',
        'originalOrder.designer',
    ]);

    $cameramen = User::role('cameraman')
        ->orderBy('name')
        ->get();

    return view('orders.show', compact(
        'order',
        'cameramen'
    ));
}
    public function edit(Order $order)
{
    $order->load('items');

    $customers = Customer::orderBy('customer_name')->get();

    $designers = User::role('designer')
        ->orderBy('name')
        ->get();


    if (auth()->user()->hasRole('admin')) {

        return view('admin.orders.edit', compact(
            'order',
            'customers',
            'designers'
        ));

    }


    return view('orders.edit', compact(
        'order',
        'customers',
        'designers'
    ));
}

    public function update(Request $request, Order $order)
{
    $validated = $request->validate([

        'customer_id' =>
            'required|exists:customers,id',

        'designer_id' =>
            'nullable|exists:users,id',

        'due_date' =>
            'required|date',

        'delivery_method' =>
            'required|in:Delivery,Self Pickup',

        'remarks' =>
            'nullable|string',

        'customer_brief' =>
            'nullable|string',

        'reference_files.*' =>
            'nullable|file|mimes:jpg,jpeg,png,pdf,ai,eps,svg,psd,cdr|max:51200',

        'reference_links' =>
            'nullable|array',

        'reference_links.*' =>
            'nullable|url',

        'product_name' =>
            'required|array|min:1',

        'product_name.*' =>
            'required|string|max:255',

        'quantity' =>
            'required|array|min:1',

        'quantity.*' =>
            'required|integer|min:1',

        'unit_price' =>
            'required|array|min:1',

        'unit_price.*' =>
            'required|numeric|min:0',

    ]);


    DB::transaction(function () use (
        $validated,
        $order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Update Order Status
        |--------------------------------------------------------------------------
        */

        $status = $order->status;


        if (empty($validated['designer_id'])) {

            $status = 'Pending';

        }

        elseif ($order->status === 'Pending') {

            $status = 'Assigned';

        }


        /*
        |--------------------------------------------------------------------------
        | Update Order
        |--------------------------------------------------------------------------
        */

        $order->update([

            'customer_id' =>
                $validated['customer_id'],

            'designer_id' =>
                $validated['designer_id'] ?? null,

            'due_date' =>
                $validated['due_date'],

            'remarks' =>
                $validated['remarks'] ?? null,

            'customer_brief' =>
                $validated['customer_brief'] ?? null,

            'delivery_method' =>
                $validated['delivery_method'],

            'status' =>
                $status,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Replace Order Items
        |--------------------------------------------------------------------------
        */

        $order->items()->delete();


        foreach (
            $validated['product_name']
            as $index => $product
        ) {

            OrderItem::create([

                'order_id' =>
                    $order->id,

                'product_name' =>
                    $product,

                'quantity' =>
                    $validated['quantity'][$index],

                'unit_price' =>
                    $validated['unit_price'][$index],

                'subtotal' =>
                    $validated['quantity'][$index]
                    *
                    $validated['unit_price'][$index],

            ]);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Redirect According to Role
    |--------------------------------------------------------------------------
    */

    if (auth()->user()->hasRole('admin')) {

        return redirect()
            ->route('admin.orders.index')
            ->with(
                'success',
                'Order updated successfully.'
            );

    }


    return redirect()
        ->route('orders.index')
        ->with(
            'success',
            'Order updated successfully.'
        );
}
    public function destroy(Order $order)
{
    DB::transaction(function () use ($order) {

        // Delete all order items
        $order->items()->delete();

        // Delete order
        $order->delete();

    });


    if (auth()->user()->hasRole('admin')) {

        return redirect()
            ->route('admin.orders.index')
            ->with(
                'success',
                'Order deleted successfully.'
            );

    }


    return redirect()
        ->route('orders.index')
        ->with(
            'success',
            'Order deleted successfully.'
        );
}

public function nextStatus(Order $order)
{
    switch ($order->status) {

        case 'Pending':
            $order->status = 'Designing';
            break;

        case 'Designing':
            $order->status = 'Printing';
            break;

        case 'Printing':
            $order->status = 'Completed';
            break;

        default:
            return back()->with('success', 'Order already completed.');

    }

    $order->save();

    return back()->with('success', 'Order status updated.');
}

public function approve(Order $order)
{
    if ($order->status != 'Pending Approval') {

        return back()->with(
            'error',
            'This order cannot be approved.'
        );

    }

    $order->update([

        'status' => 'Printing',

        'owner_review_comment' => null,

        'reviewed_by' => auth()->id(),

        'reviewed_at' => now(),

    ]);

    ActivityLog::create([

        'order_id' => $order->id,

        'user_id' => auth()->id(),

        'action' => 'Design Approved',

        'description' => 'Owner approved the design and order is now ready for printing.',

    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify Designer
    |--------------------------------------------------------------------------
    */

    if ($order->designer_id) {

        Notification::create([

            'user_id' => $order->designer_id,

            'order_id' => $order->id,

            'title' => 'Design Approved',

            'message' => "Your design for Order {$order->order_no} has been approved.",

        ]);

    }

    return back()->with(

        'success',

        'Design approved successfully.'

    );
}

public function requestRevision(Request $request, Order $order)
{
    if ($order->status != 'Pending Approval') {

        return back()->with(
            'error',
            'Revision cannot be requested.'
        );

    }

    $validated = $request->validate([

        'owner_review_comment' => 'required|string|max:2000',

    ]);

    $order->update([

        'status' => 'In Progress',

        'owner_review_comment' => $validated['owner_review_comment'],

        'reviewed_by' => auth()->id(),

        'reviewed_at' => now(),

    ]);

    ActivityLog::create([

        'order_id' => $order->id,

        'user_id' => auth()->id(),

        'action' => 'Revision Requested',

        'description' => $validated['owner_review_comment'],

    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify Designer
    |--------------------------------------------------------------------------
    */

    if ($order->designer_id) {

        Notification::create([

            'user_id' => $order->designer_id,

            'order_id' => $order->id,

            'title' => 'Design Revision Required',

            'message' => "Revision requested for Order {$order->order_no}: "
                       . $validated['owner_review_comment'],

        ]);

    }

    return back()->with(

        'success',

        'Revision requested successfully.'

    );
}

public function readyAtHQ(Order $order)
{
    if ($order->status != 'Printing') {

        return back()->with(
            'error',
            'This order is not in printing stage.'
        );

    }

    $order->update([

        'status' => 'Ready at HQ',

    ]);

    ActivityLog::create([

        'order_id' => $order->id,

        'user_id' => auth()->id(),

        'action' => 'Ready at HQ',

        'description' => 'Production completed and order returned to HQ.',

    ]);

    return back()->with(
        'success',
        'Order is now ready at HQ.'
    );
}

public function assignCameraman(Request $request, Order $order)
{
    if ($order->status != 'Ready at HQ') {

        return back()->with(
            'error',
            'This order is not ready at HQ.'
        );

    }

    $request->validate([

        'cameraman_id' => 'required|exists:users,id',

    ]);

    $order->update([

        'cameraman_id' => $request->cameraman_id,

    ]);

    ActivityLog::create([

        'order_id' => $order->id,

        'user_id' => auth()->id(),

        'action' => 'Cameraman Assigned',

        'description' => 'Assigned cameraman to this order.',

    ]);

    return back()->with(
        'success',
        'Cameraman assigned successfully.'
    );
}

public function dispatchDelivery(Order $order)
{
    if ($order->status != 'Photo Completed') {

    return back()->with(
        'error',
        'Photo session has not been completed.'
    );

}

    $order->update([
        'status' => 'Out for Delivery',
    ]);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Out for Delivery',
        'description' => 'Order has been dispatched for delivery.',
    ]);

    return back()->with('success', 'Order dispatched successfully.');
}

public function readyForPickup(Order $order)
{
    if ($order->status != 'Photo Completed') {

        return back()->with(
            'error',
            'Photo session has not been completed.'
        );

    }

    $order->update([
        'status' => 'Waiting for Pickup',
    ]);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Waiting for Pickup',
        'description' => 'Order is ready for customer pickup.',
    ]);

    return back()->with('success', 'Order is ready for pickup.');
}

public function markDelivered(Order $order)
{
    if ($order->status != 'Out for Delivery') {
        return back()->with('error', 'Order is not out for delivery.');
    }

    $order->update([
        'status' => 'Completed',
    ]);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Delivered',
        'description' => 'Order successfully delivered to customer.',
    ]);

    return back()->with('success', 'Order marked as delivered.');
}

public function confirmPickup(Order $order)
{
    if ($order->status != 'Waiting for Pickup') {
        return back()->with('error', 'Order is not waiting for pickup.');
    }

    $order->update([
        'status' => 'Completed',
    ]);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Picked Up',
        'description' => 'Customer collected the order.',
    ]);

    return back()->with('success', 'Customer pickup confirmed.');
}

public function repeat(Order $order)
{
    $order->load([
        'customer',
        'items',
    ]);

    return view(
        'orders.repeat',
        compact('order')
    );
}

public function storeRepeat(Request $request, Order $order)
{
    /*
    |--------------------------------------------------------------------------
    | Validate Repeat Order
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([

        'repeat_type' => [
            'required',
            'in:same_design,minor_changes',
        ],

        'due_date' => [
            'required',
            'date',
        ],

        'delivery_method' => [
            'required',
            'in:Delivery,Self Pickup',
        ],

        'remarks' => [
            'nullable',
            'string',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | New Repeat Order Status
    |--------------------------------------------------------------------------
    |
    | The original designer is automatically assigned.
    |
    */

    $status = 'Assigned';


    /*
    |--------------------------------------------------------------------------
    | Generate New Order Number
    |--------------------------------------------------------------------------
    */

    $nextOrderId =
        (Order::max('id') ?? 0) + 1;


    $orderNo =
        'ORD-' .
        str_pad(
            $nextOrderId,
            6,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Create Repeat Order
    |--------------------------------------------------------------------------
    */

    $newOrder = Order::create([

        /*
        |--------------------------------------------------------------------------
        | Basic Order Information
        |--------------------------------------------------------------------------
        */

        'order_no' =>
            $orderNo,

        'customer_id' =>
            $order->customer_id,


        /*
        |--------------------------------------------------------------------------
        | Automatically Assign Original Designer
        |--------------------------------------------------------------------------
        */

        'designer_id' =>
            $order->designer_id,


        /*
        |--------------------------------------------------------------------------
        | Repeat Order Information
        |--------------------------------------------------------------------------
        */

        'is_repeat_order' =>
            true,

        'repeat_from_order_id' =>
            $order->id,

        'repeat_type' =>
            $validated['repeat_type'],


        /*
        |--------------------------------------------------------------------------
        | Order Information
        |--------------------------------------------------------------------------
        */

        'status' =>
            $status,

        'due_date' =>
            $validated['due_date'],

        'delivery_method' =>
            $validated['delivery_method'],

        'remarks' =>
            $validated['remarks'] ?? null,

    ]);


    /*
    |--------------------------------------------------------------------------
    | Do NOT copy Order Items
    |--------------------------------------------------------------------------
    |
    | Quantity is no longer determined during Repeat Order creation.
    |
    | The Designer will create the Job Order later and specify:
    |
    | - Item / Design
    | - Name
    | - Number
    | - Size
    | - Quantity
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'orders.show',
            $newOrder
        )
        ->with(
            'success',
            'Repeat order created successfully. The original designer has been automatically assigned.'
        );
}
}