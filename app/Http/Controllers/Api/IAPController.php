<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IapTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IAPController extends Controller
{
    /**
     * Main endpoint called from frontend
     *
     * Frontend payload example:
     * {
     *   "productId": "points_2000",
     *   "platform": "ios" | "android",
     *   "receipt": { ...platform specific payload... }
     * }
     */
    public function validatePurchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productId' => 'required|string',
            'platform' => 'required|string|in:ios,android',
            'receipt' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = $request->user();
        $platform = strtolower($validated['platform']);
        $clientProductId = $validated['productId'];
        $receipt = $validated['receipt'];

        Log::info('📥 IAP Incoming Request', [
            'user_id' => $user?->id,
            'platform' => $platform,
            'client_product_id' => $clientProductId,
            'receipt' => $receipt,
        ]);

        $transactionId = null;
        $purchaseToken = null;
        $environment = null;
        $productIdFromReceipt = null;
        $rawPayload = null;

        try {
            if ($platform === 'ios') {
                // iOS / Apple validation
                $appleReceipt = is_string($receipt) ? $receipt : ($receipt['transactionReceipt'] ?? null);

                Log::info('🍎 Apple: Validating Receipt', [
                    'apple_raw_receipt' => $appleReceipt,
                ]);
                $result = $this->validateAppleReceipt($appleReceipt);
                Log::info('🍎 Apple: Validation Result', [
                    'result' => $result,
                ]);

                if (!$result['valid']) {
                    $this->logTransaction(
                        $user,
                        $platform,
                        $result['product_id'] ?? $clientProductId,
                        $result['transaction_id'] ?? null,
                        null,
                        $result['environment'] ?? null,
                        0,
                        'failed',
                        $result['raw'] ?? null,
                        $this->mapAppleStatusMessage($result['status'] ?? null)
                    );

                    return response()->json([
                        'error' => 'Invalid Apple purchase',
                        'apple_status' => $result['status'] ?? null,
                        'message' => $this->mapAppleStatusMessage($result['status'] ?? null),
                    ], 400);
                }

                $transactionId = $result['transaction_id'] ?? null;
                $productIdFromReceipt = $result['product_id'] ?? $clientProductId;
                $environment = $result['environment'] ?? null;
                $rawPayload = $result['raw'] ?? null;
            } else {
                // Android / Google Play validation
                $purchaseToken = $receipt ?? null;
                $packageName = config('services.google.package_name');

                Log::info('🤖 Google: Validating Receipt', [
                    'purchaseToken' => $purchaseToken,
                    'productId' => $clientProductId,
                    'packageName' => $packageName,
                ]);
                $result = $this->validateGoogleReceipt(
                    $purchaseToken,
                    $clientProductId,
                    $packageName
                );
                Log::info('🤖 Google: Validation Result', [
                    'validation_result' => $result,
                ]);

                if (!$result['valid']) {
                    $this->logTransaction(
                        $user,
                        $platform,
                        $result['data']['productId'] ?? $clientProductId,
                        $result['data']['orderId'] ?? null,
                        $purchaseToken,
                        'production',
                        0,
                        'failed',
                        $result['data'] ?? null,
                        $result['error'] ?? 'Invalid Google purchase'
                    );

                    return response()->json([
                        'error' => 'Invalid Google purchase',
                        'message' => $result['error'] ?? 'Validation failed',
                    ], 400);
                }

                $googleData = $result['data'] ?? [];
                $productIdFromReceipt = $googleData['productId'] ?? $clientProductId;
                $transactionId = $googleData['orderId'] ?? null;
                $environment = 'production';
                $rawPayload = $googleData;

                // Auto acknowledge if needed
                if (
                    isset($googleData['purchaseState']) &&
                    (int) $googleData['purchaseState'] === 0 &&
                    isset($googleData['acknowledgementState']) &&
                    (int) $googleData['acknowledgementState'] === 0
                ) {
                    $this->acknowledgeGooglePurchase($packageName, $productIdFromReceipt, $purchaseToken);
                }
            }

            // Double-spend protection
            if ($transactionId || $purchaseToken) {
                Log::info('🔍 Double Spend Check', [
                    'transactionId' => $transactionId,
                    'purchaseToken' => $purchaseToken,
                ]);
                $alreadyProcessed = IapTransaction::query()
                    ->where('platform', $platform)
                    ->where('user_id', $user->id)
                    ->when($transactionId, function ($q) use ($transactionId) {
                        $q->orWhere('transaction_id', $transactionId);
                    })
                    ->when($purchaseToken, function ($q) use ($purchaseToken) {
                        $q->orWhere('purchase_token', $purchaseToken);
                    })
                    ->exists();

                if ($alreadyProcessed) {
                    return response()->json([
                        'error' => 'This purchase has already been processed.',
                    ], 409);
                }
            }

            Log::info('🎯 Resolving Points', [
                'productId_from_receipt' => $productIdFromReceipt,
            ]);
            // Resolve points using the official productId from receipt (NOT from frontend)
            $points = $this->getPointsForProduct($productIdFromReceipt);

            if ($points === null) {
                $this->logTransaction(
                    $user,
                    $platform,
                    $productIdFromReceipt,
                    $transactionId,
                    $purchaseToken,
                    $environment,
                    0,
                    'failed',
                    $rawPayload,
                    'Unknown product id'
                );

                return response()->json(['error' => 'Unknown product'], 400);
            }

            Log::info('➕ Adding Points to User', [
                'user_id' => $user->id,
                'current_points' => $user->points,
                'points_to_add' => $points,
            ]);
            // Add points to user
            $user->points = (int) ($user->points ?? 0) + (int) $points;
            $user->save();

            // Log success
            $this->logTransaction(
                $user,
                $platform,
                $productIdFromReceipt,
                $transactionId,
                $purchaseToken,
                $environment,
                $points,
                'completed',
                $rawPayload,
                null
            );

            Log::info('✅ IAP Success Response', [
                'user_id' => $user->id,
                'total_points' => $user->points,
            ]);
            return response()->json([
                'success' => true,
                'points' => $user->points,
            ]);
        } catch (\Throwable $e) {
            Log::error('IAP validation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Best-effort log
            try {
                $this->logTransaction(
                    $user,
                    $platform ?? null,
                    $productIdFromReceipt ?? $clientProductId ?? null,
                    $transactionId,
                    $purchaseToken,
                    $environment,
                    0,
                    'error',
                    $rawPayload ?? $receipt ?? null,
                    $e->getMessage()
                );
            } catch (\Throwable $inner) {
                // Swallow logging errors to avoid masking original error
            }

            return response()->json([
                'error' => 'Something went wrong while validating your purchase. Please try again.',
            ], 500);
        }
    }

    /**
     * Simple history endpoint for user's IAP transactions
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $transactions = IapTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Apple StoreKit 2 — Verify Signed Transaction (JWS)
     *
     * @param string|null $jwsTransaction
     * @return array
     */
    private function validateAppleReceipt(?string $jwsTransaction): array
    {
        if (empty($jwsTransaction)) {
            return [
                'valid' => false,
                'status' => null,
                'environment' => null,
                'product_id' => null,
                'transaction_id' => null,
                'raw' => null,
            ];
        }

        // Apple Server API JWT
        $developerToken = $this->generateAppleServerJWT();

        if (!$developerToken) {
            return [
                'valid' => false,
                'status' => null,
                'environment' => null,
                'product_id' => null,
                'transaction_id' => null,
                'raw' => ['error' => 'Unable to generate Apple Developer Token'],
            ];
        }

        $url = "https://api.storekit.itunes.apple.com/inApps/v1/verifyTransaction";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$developerToken}",
            'Content-Type' => 'application/json',
        ])->post($url, [
            'signedTransactionInfo' => $jwsTransaction,
        ]);

        $json = $response->json();

        if ($response->failed() || empty($json)) {
            return [
                'valid' => false,
                'status' => null,
                'environment' => null,
                'product_id' => null,
                'transaction_id' => null,
                'raw' => $json,
            ];
        }

        // Apple gives back a signedTransactionInfo JWS → decode middle payload
        $signed = $json['signedTransactionInfo'] ?? null;
        if (!$signed || !str_contains($signed, '.')) {
            return [
                'valid' => false,
                'status' => null,
                'environment' => null,
                'product_id' => null,
                'transaction_id' => null,
                'raw' => $json,
            ];
        }

        [$header, $payload, $sig] = explode('.', $signed);
        $decodedPayload = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        return [
            'valid' => true,
            'status' => 0,
            'environment' => $decodedPayload['environment'] ?? null,
            'product_id' => $decodedPayload['productId'] ?? null,
            'transaction_id' => $decodedPayload['transactionId'] ?? null,
            'raw' => $json,
        ];
    }

    /**
     * Generate Apple Server API Developer Token (ES256)
     *
     * @return string|null
     */
    private function generateAppleServerJWT(): ?string
    {
        $privateKey = file_get_contents(storage_path('app/apple/AuthKey_J7AVYX4989.p8'));

        $issuerId = '08e1bdf6-c5cb-43b9-aebf-80af49837b04';
        $keyId = 'J7AVYX4989';

        $header = [
            'alg' => 'ES256',
            'kid' => $keyId,
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $issuerId,
            'iat' => $now,
            'exp' => $now + 1800,
            'aud' => 'appstoreconnect-v1',
        ];

        $segments = [];
        $segments[] = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $segments[] = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signingInput = implode('.', $segments);

        $signature = '';
        $success = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$success) {
            return null;
        }

        $segments[] = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return implode('.', $segments);
    }

    /**
     * Google Play receipt validation
     *
     * Returns array:
     * [
     *   'valid' => bool,
     *   'data' => array|null,
     *   'error' => string|null,
     * ]
     */
    private function validateGoogleReceipt(?string $purchaseToken, ?string $productId, ?string $packageName): array
    {
        if (!$purchaseToken || !$productId || !$packageName) {
            return [
                'valid' => false,
                'data' => null,
                'error' => 'Missing purchaseToken, productId or packageName',
            ];
        }

        $accessToken = $this->getGoogleAccessToken();

        if (!$accessToken) {
            return [
                'valid' => false,
                'data' => null,
                'error' => 'Unable to obtain Google access token',
            ];
        }

        $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}";

        $response = Http::withToken($accessToken)->get($url);

        if ($response->failed()) {
            return [
                'valid' => false,
                'data' => $response->json(),
                'error' => 'Google purchase validation request failed',
            ];
        }

        $data = $response->json();

        // purchaseState: 0 = Purchased, 1 = Canceled, 2 = Pending
        $purchaseState = isset($data['purchaseState']) ? (int) $data['purchaseState'] : null;

        $valid = $purchaseState === 0;

        return [
            'valid' => $valid,
            'data' => $data,
            'error' => $valid ? null : 'Purchase is not in purchased state',
        ];
    }

    /**
     * Map Apple status code to a human-readable message.
     * Useful both for logs and for App Review messaging.
     */
    private function mapAppleStatusMessage($status): string
    {
        $status = (int) $status;

        return match ($status) {
            0 => 'The receipt is valid.',
            21000 => 'The App Store could not read the JSON object you provided.',
            21002 => 'The data in the receipt-data property was malformed or missing.',
            21003 => 'The receipt could not be authenticated.',
            21004 => 'The shared secret you provided does not match the shared secret on file for your account.',
            21005 => 'The receipt server is currently unavailable.',
            21006 => 'This receipt is valid but the subscription has expired.',
            21007 => 'This is a sandbox receipt, but it was sent to the production service.',
            21008 => 'This is a production receipt, but it was sent to the sandbox service.',
            default => 'Unknown Apple receipt status.',
        };
    }

    /**
     * Get Google OAuth2 access token using Service Account JSON
     */
    private function getGoogleAccessToken(): ?string
    {
        

        $serviceAccountJson = file_get_contents(storage_path('app/google/service-account.json'));

        Log::info('🧪 GOOGLE DEBUG: Decoded credentials', [
            'serviceAccountJson' => $serviceAccountJson,
           
        ]);

        $credentials = json_decode($serviceAccountJson, true);
        Log::info('🧪 GOOGLE DEBUG: Decoded credentials', [
            'decoded' => $credentials,
            'json_decode_success' => is_array($credentials),
        ]);

        if (!is_array($credentials)) {
            return null;
        }

        $now = time();

        $payload = [
            'iss' => $credentials['client_email'] ?? null,
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        if (!$payload['iss']) {
            return null;
        }

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];

        $jwt = $this->base64UrlEncode(json_encode($header)) . '.' . $this->base64UrlEncode(json_encode($payload));

        $privateKey = $credentials['private_key'] ?? null;
        Log::info('🧪 GOOGLE DEBUG: Private key exists?', [
            'has_private_key' => $privateKey ? true : false,
        ]);

        if (!$privateKey) {
            return null;
        }

        $signature = '';
        $success = openssl_sign($jwt, $signature, $privateKey, 'sha256');

        if (!$success) {
            return null;
        }

        $jwt .= '.' . $this->base64UrlEncode($signature);

        $tokenResponse = Http::asForm()->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($tokenResponse->failed()) {
            return null;
        }

        $data = $tokenResponse->json();

        return $data['access_token'] ?? null;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Acknowledge a Google Play purchase so it is not refunded.
     */
    private function acknowledgeGooglePurchase(string $packageName, string $productId, string $purchaseToken): void
    {
        $accessToken = $this->getGoogleAccessToken();

        if (!$accessToken) {
            return;
        }

        $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}:acknowledge";

        Http::withToken($accessToken)->post($url, [
            'developerPayload' => 'acknowledged_by_backend',
        ]);
    }

    /**
     * Resolve points from product ID.
     * Make sure to map ALL product IDs from App Store & Google Play here.
     */
    private function getPointsForProduct(?string $productId): ?int
    {
        if (!$productId) {
            return null;
        }

        $map = [
            // Generic internal IDs
            'points_100' => 100,
            'points_100_new' => 100,
            'points_2000' => 2000,
        ];

        return $map[$productId] ?? null;
    }

    /**
     * Central place to log all IAP transactions.
     */
    private function logTransaction(
        $user,
        ?string $platform,
        ?string $productId,
        ?string $transactionId,
        ?string $purchaseToken,
        ?string $environment,
        int $points,
        string $status,
        $rawPayload = null,
        ?string $errorMessage = null
    ): void {
        if (!$user) {
            return;
        }

        try {
            IapTransaction::create([
                'user_id' => $user->id,
                'platform' => $platform,
                'product_id' => $productId,
                'transaction_id' => $transactionId,
                'purchase_token' => $purchaseToken,
                'environment' => $environment,
                'points' => $points,
                'status' => $status,
                'raw_payload' => $rawPayload ? json_encode($rawPayload) : null,
                'error_message' => $errorMessage,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log IAP transaction', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}