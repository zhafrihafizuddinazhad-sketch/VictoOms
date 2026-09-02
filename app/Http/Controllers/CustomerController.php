<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->search;

    $sort = $request->get('sort', 'customer_name');
    $direction = $request->get('direction', 'asc');

    $customers = Customer::when($search, function ($query) use ($search) {

        $query->where('customer_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%");

    })
    ->orderBy($sort, $direction)
    ->paginate(10);

    return view('customers.index', compact(
        'customers',
        'search',
        'sort',
        'direction'
    ));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'customer_name' => 'required|max:255',
        'phone' => 'required|max:20',
        'email' => 'nullable|email',
        'company' => 'nullable|max:255',
        'address' => 'nullable',
        'state' => 'nullable|max:255',
        'postcode' => 'nullable|max:10',
        'remarks' => 'nullable',
    ]);

    Customer::create($validated);

    return redirect()
        ->route('customers.index')
        ->with('success', 'Customer added successfully.');
    }

    /**
 * Quick create customer from Create Order.
 */
public function quickStore(Request $request)
{
    $validated = $request->validate([

        'customer_name' => 'required|max:255',

        'phone' => 'required|max:20',

        'email' => 'nullable|email',

        'company' => 'nullable|max:255',

        'address' => 'nullable',

        'state' => 'nullable|max:255',

        'postcode' => 'nullable|max:10',

        'remarks' => 'nullable',

    ]);


    $customer = Customer::create($validated);


    return response()->json([

        'success' => true,

        'customer' => [

            'id' => $customer->id,

            'customer_name' => $customer->customer_name,

            'company' => $customer->company,

            'phone' => $customer->phone,

        ],

    ]);
}

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
        'customer_name' => 'required|max:255',
        'phone'         => 'required|max:20',
        'email'         => 'nullable|email',
        'company'       => 'nullable|max:255',
        'address'       => 'nullable',
        'state'         => 'nullable|max:255',
        'postcode'      => 'nullable|max:10',
        'remarks'       => 'nullable',
    ]);

    $customer->update($validated);

    return redirect()
        ->route('customers.index')
        ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

    return redirect()
        ->route('customers.index')
        ->with('success', 'Customer deleted successfully.');
    }
}
