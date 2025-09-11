<?php

class curl_ringotel {
    private $headers;
    private $url;

    public function __construct(string $url = '', array $headers = []) {
        $this->headers = !empty($headers) ? $headers : [];
        if (!empty($url)) {
            $this->set_base_url($url);
        } else {
            $this->url = '';
        }
    }

    private function handleResponse($httpCode, $response) {
        switch ($httpCode) {
            case 200:
                return ['success' => true, 'data' => json_decode($response, true)];
            case 304:
                return ['success' => false, 'error' => 'Not Modified (304)'];
            case 404:
                return ['success' => false, 'error' => 'Not Found (404)'];
            case 501:
                return ['success' => false, 'error' => 'Not Implemented (501)'];
            default:
                return ['success' => false, 'error' => 'Unexpected HTTP code: ' . $httpCode];
        }
    }

    public function request($api, $method, $parameters, $headers = null) {
        $ch = curl_init($api);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($parameters)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if (curl_errno($ch)) {
            $errorMessage = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException($errorMessage);
        }
    
        curl_close($ch);
        return $this->handleResponse($httpCode, $response);
    }


    public function post($api, $parameters, $headers = null) {
        return $this->request($api, 'POST', json_encode($parameters), $headers);
    }

    public function get($api, $parameters, $headers = null) {
        $queryString = http_build_query($parameters);
        $apiWithParams = !empty($queryString) ? $api . '?' . $queryString : $api;
        return $this->request($apiWithParams, 'GET', null, $headers);
    }

    public function set_base_url(string $url): self {
        $parts = parse_url($url);
        if (!is_string($url) || !filter_var($url, FILTER_VALIDATE_URL) || !isset($parts['scheme']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            throw new \InvalidArgumentException("Url is not valid");
        }
        $this->url = $url;
        return $this;
    }

    public function set_headers(array $headers): void {
        foreach ($headers as $key => $value) {
            $this->add_header($key, $value);
        }
    }

    public function add_header(string $header_key, string $header_value): self {
        if (empty($header_key) || empty($header_value)) {
            throw new \InvalidArgumentException("Header key and value must not be empty");
        }
        $this->headers[] = "$header_key: $header_value";
        return $this;
    }

    public function set_content_type(string $content_type): self {
        if (!in_array($content_type, ['application/json', 'application/xml', 'text/plain'], true)) {
            throw new \InvalidArgumentException("Content type is not currently supported");
        }
        return $this->add_header('Content-Type', $content_type);
    }

    public function set_content_type_json(): self {
        return $this->set_content_type('application/json');
    }
}
