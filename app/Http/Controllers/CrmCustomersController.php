<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CrmCustomersController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['customerSegment', 'company'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Customer type filter
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->get('customer_type'));
        }

        // Customer segment filter
        if ($request->filled('customer_segment_id')) {
            $query->where('customer_segment_id', $request->get('customer_segment_id'));
        }

        $customers = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $segments = CustomerSegment::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('CRM/Customers/Index', [
            'customers' => $customers,
            'segments' => $segments,
            'filters' => $request->only(['search', 'status', 'customer_type', 'customer_segment_id']),
        ]);
    }

    public function create()
    {
        $segments = CustomerSegment::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('CRM/Customers/Create', [
            'segments' => $segments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,company',
            'status' => 'required|in:active,inactive,prospect',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['code'] = 'CUST-' . strtoupper(uniqid());

        Customer::create($validated);

        return redirect()->route('crm.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['customerSegment', 'company', 'followUps', 'supportTickets']);

        return Inertia::render('CRM/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $segments = CustomerSegment::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('CRM/Customers/Edit', [
            'customer' => $customer,
            'segments' => $segments,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,company',
            'status' => 'required|in:active,inactive,prospect',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('crm.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('crm.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
