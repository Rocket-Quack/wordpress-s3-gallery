<?php
class S3Client {
    private $accessKey;
    private $secretKey;
    private $region;
    private $bucket;
    private $endpoint;

    public function __construct($config = []) {
        $this->accessKey = $config['access_key'] ?? get_option('s3_gallery_key');
        $this->secretKey = $config['secret_key'] ?? get_option('s3_gallery_secret');
        $this->region    = $config['region'] ?? get_option('s3_gallery_region');
        $this->bucket    = $config['bucket'] ?? get_option('s3_gallery_bucket');
        $this->endpoint  = rtrim($config['endpoint'] ?? get_option('s3_gallery_endpoint'), '/');

        if (!$this->endpoint || !$this->accessKey || !$this->secretKey || !$this->bucket) {
            throw new Exception("S3-Konfiguration unvollständig.");
        }
    }

    public function list_directories(): array {
        $params = [
            'delimiter'  => '/',
            'list-type'  => '2',
        ];
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $response = $this->sendRequest($query);
        $prefixes = [];

        if ($response) {
            $xml = simplexml_load_string($response);
            if (isset($xml->CommonPrefixes)) {
                foreach ($xml->CommonPrefixes as $prefix) {
                    $prefixes[] = rtrim((string)$prefix->Prefix, '/');
                }
            }
        }

        $response = $this->sendRequest($query);

        return $prefixes;
    }

    public function list_objects_in_folder(string $bucket, string $folder): array {
        $prefix = rtrim($folder, '/') . '/';
        $query = "list-type=2&prefix=" . rawurlencode($prefix);
        $response = $this->sendRequest($query);

        $images = [];
        $parsed = simplexml_load_string($response);
        if ($parsed && isset($parsed->Contents)) {
            foreach ($parsed->Contents as $item) {
                $key = (string)$item->Key;
                if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $key)) {
                    $images[] = $this->getObjectUrl($bucket, $key);
                }
            }
        }

        return $images;
    }

    private function sendRequest(string $query): string {
        $method = 'GET';
        $service = 's3';
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        $uri = "/{$this->bucket}/";
        $url = "https://{$host}{$uri}?{$query}";

        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', '');

        $canonicalHeaders = "host:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n";
        $signedHeaders = "host;x-amz-content-sha256;x-amz-date";

        $canonicalRequest = implode("\n", [
            $method,
            $uri,
            $query,
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash
        ]);

        $credentialScope = "$dateStamp/{$this->region}/$service/aws4_request";
        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest)
        ]);

        $signingKey = $this->getSignatureKey($this->secretKey, $dateStamp, $this->region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        $headers = [
            "x-amz-date: $amzDate",
            "x-amz-content-sha256: $payloadHash",
            "Authorization: $authorizationHeader"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("S3 returned HTTP $httpCode: $response");
        }

        return $response;
    }

    private function getSignatureKey($key, $dateStamp, $regionName, $serviceName) {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $key, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        return $kSigning;
    }


    public function getObjectUrl($bucket, $key): string {
        return rtrim($this->endpoint, '/') . '/' . $bucket . '/' . ltrim($key, '/');
    }

}
