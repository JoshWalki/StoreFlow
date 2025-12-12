<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderReceiptController extends Controller
{
    /**
     * Display a printable receipt for the specified order.
     *
     * This generates an 80mm thermal receipt optimized for browser printing.
     * The receipt automatically triggers the browser's print dialog on load.
     *
     * @param Order $order The order to generate receipt for
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Order $order)
    {
        // Authorize using the existing OrderPolicy::view method
        // This ensures only authorized users (same merchant, proper role) can view
        $this->authorize('view', $order);

        // Eager load relationships to prevent N+1 queries
        $order->load([
            'store',                    // Store details (name, address, phone)
            'customer',                 // Customer name
            'items' => function($query) {
                $query->with('addons'); // Order items with their addons
            }
        ]);

        // Return the standalone Blade view optimized for printing
        return view('orders.receipt', [
            'order' => $order,
            'store' => $order->store,
            'merchant' => $order->store->merchant ?? null,
        ]);
    }
}
