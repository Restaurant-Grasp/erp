<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // Configure your WhatsApp API credentials here
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    public function sendMessage($phoneNumber, $message)
    {
        try {
            // Format phone number (remove spaces, add country code if needed)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Send via your WhatsApp API provider
            // This is a generic example - adjust based on your provider
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/messages', [
                'to' => $phoneNumber,
                'body' => $message,
                'type' => 'text'
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $phoneNumber,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('WhatsApp message failed', [
                    'to' => $phoneNumber,
                    'error' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'error' => $e->getMessage(),
                'to' => $phoneNumber
            ]);
            throw $e;
        }
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove spaces and special characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present (assuming Malaysia +60)
        if (!str_starts_with($phoneNumber, '60')) {
            // Remove leading 0 if present
            $phoneNumber = ltrim($phoneNumber, '0');
            $phoneNumber = '60' . $phoneNumber;
        }
        
        return $phoneNumber;
    }
}