<?php

class ApiService
{
    private string $token;
    private string $apiUrl;

    public function __construct(string $token, string $apiUrl)
    {
        $this->token = $token;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * Send new lead
     */
    public function sendLead(array $leadData): array
    {
        $endpoint = $this->apiUrl . '/addlead';

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($leadData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'token: ' . $this->token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return ['success' => false, 'message' => 'cURL Error: ' . curl_error($ch)];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === true) {
            return ['success' => true, 'id' => $responseData['id'] ?? 'N/A'];
        }

        return [
            'success' => false,
            'message' => $responseData['error'] ?? $responseData['message'] ?? "API Error ($httpCode)"
        ];
    }

    /**
     * Get leads
     */
    public function getStatuses(?string $dateFrom = null, ?string $dateTo = null, int $page = 0, int $limit = 100): array
    {
        $endpoint = $this->apiUrl . '/getstatuses';

        $payload = [
            "page"  => $page,
            "limit" => $limit
        ];

        if (!empty($dateFrom)) {
            $payload['date_from'] = $dateFrom . ' 00:00:00';
        }
        if (!empty($dateTo)) {
            $payload['date_to'] = $dateTo . ' 23:59:59';
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'token: ' . $this->token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return ['success' => false, 'message' => 'cURL Error: ' . curl_error($ch), 'data' => []];
        }

        $responseData = json_decode($response, true);
        $status = isset($responseData['status']) && ($responseData['status'] === true || $responseData['status'] === 'true');

        if ($httpCode >= 200 && $httpCode < 300 && $status) {
            $leads = [];
            if (isset($responseData['data'])) {
                if (is_string($responseData['data'])) {
                    $leads = json_decode($responseData['data'], true) ?? [];
                } else {
                    $leads = $responseData['data'];
                }
            }

            return [
                'success' => true,
                'data'    => $leads
            ];
        }

        return [
            'success' => false,
            'message' => $responseData['error'] ?? "API Error ($httpCode)",
            'data'    => []
        ];
    }
}