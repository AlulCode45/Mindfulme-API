<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use Illuminate\Http\Request;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $notif = new Notification();

        $payment = Payments::where('order_id', $notif->order_id)->first();

        if ($payment) {
            $payment->update([
                'midtrans_transaction_id' => $notif->transaction_id,
                'payment_type' => $notif->payment_type,
                'transaction_status' => $notif->transaction_status,
                'fraud_status' => $notif->fraud_status ?? null,
                'payment_details' => json_encode($notif),
                'transaction_time' => $notif->transaction_time,
                'expiry_time' => $notif->expiry_time ?? null,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}
