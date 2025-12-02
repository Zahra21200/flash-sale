<?php
namespace App\Http\Controllers;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, WebhookService $webhookService)
    {
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');
        $payload = $request->all();

        $webhookService->handleWebhook($idempotencyKey, $payload);

        return response()->json(['message'=>'Webhook processed']);
    }
}