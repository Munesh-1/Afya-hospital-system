<?php
declare(strict_types=1);

// Lightweight M-Pesa (Daraja) helper. Configure credentials below.
// This file will use Guzzle if available, otherwise falls back to a cURL implementation.

require_once __DIR__ . '/config.php';

// If Composer autoload exists, include it (allows Guzzle to be available)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Configuration - replace with your Daraja credentials
const MPESA_ENV = 'sandbox'; // 'sandbox' or 'production'
const MPESA_CONSUMER_KEY = 'YOUR_CONSUMER_KEY';
const MPESA_CONSUMER_SECRET = 'YOUR_CONSUMER_SECRET';
const MPESA_SHORTCODE = '174379'; // Lipa na M-Pesa Shortcode (sandbox default)
const MPESA_PASSKEY = 'YOUR_PASSKEY';
const MPESA_CALLBACK_URL = 'https://yourdomain.example/mpesa_callback.php';

function mpesa_get_base_url(): string
{
    return MPESA_ENV === 'production' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
}

function mpesa_get_access_token(): string
{
    static $token = null;
    if ($token !== null) {
        return $token;
    }
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    try {
        $path = '/oauth/v1/generate?grant_type=client_credentials';
        $headers = [
            'Authorization' => 'Basic ' . $credentials,
            'Accept' => 'application/json'
        ];

        $resp = mpesa_http_request('GET', $path, ['headers' => $headers]);
        if ($resp['ok']) {
            $body = json_decode($resp['body'], true);
            if (!empty($body['access_token'])) {
                $token = $body['access_token'];
                return $token;
            }
        } else {
            error_log('MPESA token request failed: ' . ($resp['error'] ?? 'unknown'));
        }
    } catch (\Exception $e) {
        error_log('MPESA token error: ' . $e->getMessage());
    }

    return '';
}

function mpesa_stk_push(string $phone, float $amount, string $accountRef = 'Bill', string $description = ''): array
{
    $accessToken = mpesa_get_access_token();
    if ($accessToken === '') {
        return ['success' => false, 'message' => 'Could not obtain access token'];
    }

    // Format phone to international format 2547XXXXXXXX
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    }
    if (strpos($phone, '7') === 0) {
        $phone = '254' . $phone;
    }

    $timestamp = (new DateTime('now', new DateTimeZone('Africa/Nairobi')))->format('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => (int)ceil($amount),
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $accountRef,
        'TransactionDesc' => $description ?: 'Payment'
    ];

    try {
        $path = '/mpesa/stkpush/v1/processrequest';
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ];

        $resp = mpesa_http_request('POST', $path, ['headers' => $headers, 'json' => $payload]);
        if ($resp['ok']) {
            $body = json_decode($resp['body'], true);
            return ['success' => true, 'response' => $body];
        }

        return ['success' => false, 'message' => $resp['error'] ?? 'Request failed'];
    } catch (\Exception $e) {
        error_log('MPESA STK push error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Internal HTTP helper: uses Guzzle if available, otherwise cURL.
 * Returns array with keys: ok (bool), body (string), error (string|null)
 */
function mpesa_http_request(string $method, string $path, array $opts = []): array
{
    $base = mpesa_get_base_url();

    // Use Guzzle if installed
    if (class_exists('\GuzzleHttp\Client')) {
        try {
            $client = new \GuzzleHttp\Client(['base_uri' => $base, 'timeout' => 15]);
            $guzzleOpts = [];
            if (!empty($opts['headers'])) {
                $guzzleOpts['headers'] = $opts['headers'];
            }
            if (!empty($opts['json'])) {
                $guzzleOpts['json'] = $opts['json'];
            }
            $resp = $client->request($method, $path, $guzzleOpts);
            $body = $resp->getBody()->getContents();
            return ['ok' => true, 'body' => $body, 'error' => null];
        } catch (\Exception $e) {
            return ['ok' => false, 'body' => '', 'error' => $e->getMessage()];
        }
    }

    // Fallback to cURL
    $url = rtrim($base, '/') . $path;
    $ch = curl_init($url);
    $headers = [];
    if (!empty($opts['headers']) && is_array($opts['headers'])) {
        foreach ($opts['headers'] as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        $body = '';
        if (!empty($opts['json'])) {
            $body = json_encode($opts['json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            if (!in_array('Content-Type: application/json', $headers, true)) {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }
    }

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'body' => '', 'error' => $err];
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status >= 200 && $status < 300) {
        return ['ok' => true, 'body' => $resp, 'error' => null];
    }

    return ['ok' => false, 'body' => $resp, 'error' => 'HTTP ' . $status];
}

// Example callback handler stub can be created at mpesa_callback.php to record confirmations.

?>
