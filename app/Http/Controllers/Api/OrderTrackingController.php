<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    /**
     * Get public order information by public_id.
     *
     * @param string $publicId
     * @return JsonResponse
     */
    public function show(string $publicId): JsonResponse
    {
        $order = Order::where('public_id', $publicId)
            ->with([
                'items.product',
                'items.options',
                'customer',
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        // Build public order response (no internal IDs exposed)
        $response = [
            'public_id' => $order->public_id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'fulfilment_type' => $order->fulfilment_type,
            'items_total_cents' => $order->items_total_cents,
            'shipping_cost_cents' => $order->shipping_cost_cents,
            'total_cents' => $order->total_cents,
            'created_at' => $order->created_at->toIso8601String(),
            'updated_at' => $order->updated_at->toIso8601String(),
            'customer' => [
                'first_name' => $order->customer->first_name,
                'last_name' => $order->customer->last_name,
                'email' => $order->customer->email,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->product->name,
                    'qty' => $item->qty,
                    'unit_price_cents' => $item->unit_price_cents,
                    'customizations' => $item->options->map(function ($option) {
                        $customOption = $option->customizationOption;
                        return [
                            'option_name' => $customOption ? $customOption->name : null,
                            'qty' => $option->qty,
                            'price_delta_cents' => $option->price_delta_cents,
                        ];
                    })->values(),
                ];
            })->values(),
            'realtime_channel' => "order.{$order->public_id}",
        ];

        // Add delivery-specific information
        if ($order->fulfilment_type === 'delivery') {
            $response['shipping'] = [
                'status' => $order->shipping_status,
                'tracking_code' => $order->tracking_code,
                'tracking_url' => $order->tracking_url,
                'address' => [
                    'name' => $order->shipping_name,
                    'line1' => $order->line1,
                    'line2' => $order->line2,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postcode' => $order->postcode,
                    'country' => $order->country,
                ],
            ];
        }

        // Add pickup-specific information
        if ($order->fulfilment_type === 'pickup') {
            $response['pickup'] = [
                'time' => $order->pickup_time?->toIso8601String(),
            ];
        }

        return response()->json($response, 200);
    }
}
