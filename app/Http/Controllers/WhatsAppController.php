<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Send WhatsApp notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendNotification(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'message' => 'required|string'
            ]);

            $phone = $request->input('phone');
            $message = $request->input('message');

            // Log the WhatsApp notification request
            Log::info('WhatsApp notification requested', [
                'phone' => $phone,
                'message' => $message
            ]);

            // TODO: Integrate with actual WhatsApp API service
            // For now, just simulate the sending
            $response = $this->simulateWhatsAppSending($phone, $message);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp notification sent successfully',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', [
                'error' => $e->getMessage(),
                'phone' => $request->input('phone', 'unknown')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate WhatsApp sending (placeholder for actual integration)
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    private function simulateWhatsAppSending(string $phone, string $message): array
    {
        // Simulate API call delay
        usleep(500000); // 0.5 seconds

        return [
            'phone' => $phone,
            'message_id' => 'wa_' . uniqid(),
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
            'provider' => 'whatsapp_business_api',
            'message_preview' => substr($message, 0, 50) . '...'
        ];
    }
}
