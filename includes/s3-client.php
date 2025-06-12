<?php
class S3Client {
    private $accessKey;
    private $secretKey;
    private $region;
    private $bucket;
    private $endpoint;

    public function __construct($config) {
        $this->accessKey = $config['access_key'];
        $this->secretKey = $config['secret_key'];
        $this->region    = $config['region'];
        $this->bucket    = $config['bucket'];
        $this->endpoint  = rtrim($config['endpoint'], '/');
    }

    public function listObjects($prefix = '') {
    $method = 'GET';
    $uri = '/';
    $service = 's3';
    $host = "{$this->bucket}." . parse_url($this->endpoint, PHP_URL_HOST);
    $url = "https://$host/";

    $amzDate = gmdate('Ymd\THis\Z');
    $dateStamp = gmdate('Ymd');
    $payloadHash = hash('sha256', '');

    $canonicalHeaders = "host:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n";
    $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

    $canonicalRequest = implode("\n", [
        $method,
        $uri,
        '',
        $canonicalHeaders,
        $signedHeaders,
        $payloadHash
    ]);

    $credentialScope = "$dateStamp/{$this->region}/$service/aws4_request";
    $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

    $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true);
    $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
    $kService = hash_hmac('sha256', $service, $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

    $headers = [
        "x-amz-date: $amzDate",
        "x-amz-content-sha256: $payloadHash",
        "Authorization: $authorization"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        echo '<p>Fehler beim Abrufen der S3-Daten.</p>';
    } else {
        // Debug-Ausgabe:
        // echo '<pre>' . htmlspecialchars($response) . '</pre>';
    }

    $result = [];
    if ($response) {
        $xml = simplexml_load_string($response);
        if ($xml && isset($xml->Contents)) {
            foreach ($xml->Contents as $object) {
                $result[] = (string)$object->Key;
            }
        }
    }

    return $result;
}

}
