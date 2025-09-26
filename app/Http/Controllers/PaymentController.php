<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request, MidtransService $midtrans)
    {
        $request->validate([
            'gross_amount' => 'required|numeric|min:1000',
        ]);

        $orderId = 'ORDER-' . uniqid();

        // Simpan dulu ke DB (status pending)
        $payment = Payments::create([
            'user_id' => $request->user()->uuid,
            'order_id' => $orderId,
            'gross_amount' => $request->gross_amount,
            'transaction_status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $payment->order_id,
                'gross_amount' => $payment->gross_amount,
            ],
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ];

        $snap = $midtrans->createTransaction($params);

        return response()->json([
            'snap_token' => $snap->token,
            'redirect_url' => $snap->redirect_url,
        ]);
    }
}
