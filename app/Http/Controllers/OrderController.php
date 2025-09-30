<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('product')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = $orders->map(function ($order) {
            return [
                'Name'   => $order->user->name,
                'product' => $order->product->name,
                'price'  => $order->product->price,
                'qty' => $order->quantity,
                'total' => $order->total_amount,
                'status' => $order->status,
            ];
        });

        return response()->json($data);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $total = (int)$product->price * $validated['quantity'];

        $order = Order::create([
            'user_id'      => $request->user()->id,
            'product_id'   => $product->id,
            'quantity'     => $validated['quantity'],
            'total_amount' => $total,
            'status'       => 'pending',
        ]);

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'order'   => $order->load('product'),
        ]);
    }

    public function pay(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('product')->find($validatedData['order_id']);

        // Cek kepemilikan
        if ($user->id !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Midtrans Config
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        $itemDetails = [
            [
                'id'       => $order->product->id,
                'price'    => (int) $order->product->price,
                'quantity' => $order->quantity,
                'name'     => $order->product->name,
            ]
        ];

        $params = [
            'transaction_details' => [
                'order_id'     => $order->id,
                'gross_amount' => (int) $order->total_amount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => '08111222333',
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return response()->json([
            'snap_token'   => $snapToken,
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken,
        ], 200);
    }

    public function callback(Request $request)
    {
        $serverkey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverkey);

        if ($hashed === $request->signature_key) {
            $order = Order::find($request->order_id);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if (in_array($request->transaction_status, ['capture', 'settlement'])) {
                $order->update(['status' => 'paid']);
            }

            if (in_array($request->transaction_status, ['cancel', 'expire'])) {
                $order->update(['status' => 'failed']);
            }
        }

        return response()->json(['message' => 'Callback processed'], 200);
    }

}
