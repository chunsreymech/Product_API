<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Get payment details for an order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to view this payment', [], 403);
        }

        $payment = $order->payment ?? Payment::create([
            'order_id' => $order->id,
            'method' => $order->payment_method ?? 'cash_on_delivery',
            'status' => 'pending',
            'amount' => $order->grand_total,
        ]);

        return $this->success(new PaymentResource($payment), 'Payment details retrieved successfully');
    }

    /**
     * Process demo payment for an order.
     */
    public function store(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to make payment for this order', [], 403);
        }

        if ($order->status === 'cancelled') {
            return $this->error('Cannot process payment for a cancelled order', [], 400);
        }

        $data = $request->validated();
        $method = $data['method'];

        $transactionId = 'TXN-' . strtoupper(Str::random(12));
        $paymentStatus = 'paid';

        // Payment details simulation
        $details = [
            'method' => $method,
            'paid_at' => now()->toISOString(),
            'transaction_id' => $transactionId,
        ];

        if ($method === 'demo_card') {
            $maskedCard = '****-****-****-' . substr($data['card_number'] ?? '4242', -4);
            $details['card_last4'] = substr($data['card_number'] ?? '4242', -4);
            $details['card_mask'] = $maskedCard;
        } elseif ($method === 'bank_transfer') {
            $details['bank_reference'] = $data['bank_reference'] ?? 'ABA-' . rand(100000, 999999);
        }

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => $method,
                'status' => $paymentStatus,
                'amount' => $order->grand_total,
                'transaction_id' => $transactionId,
                'payment_details' => $details,
            ]
        );

        // Update order status if order was pending
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
        ]);

        return $this->success(new PaymentResource($payment), 'Payment processed successfully');
    }
}
