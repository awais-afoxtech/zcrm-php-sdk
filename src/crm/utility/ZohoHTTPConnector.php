<?php

namespace zcrmsdk\crm\utility;

/**
 * Purpose of this class is to trigger API call and fetch the response
 *
 * @author sumanth-3058
 *
 */
class ZohoHTTPConnector
{

    private $url = null;

    private array $requestParams = array();

    private array $requestHeaders = array();

    private int $requestParamCount = 0;

    private $requestBody;

    private string $requestType = APIConstants::REQUEST_METHOD_GET;

    private string $userAgent = "ZohoCRM PHP SDK";

    private ?string $apiKey = null;

    private bool $isBulkRequest = false;

    private function __construct()
    {
    }

    public static function getInstance(): ZohoHTTPConnector
    {
        return new ZohoHTTPConnector();
    }

    public function fireRequest(): array
    {
        $curl_pointer = curl_init();
        if (is_array(self::getRequestParamsMap()) && count(self::getRequestParamsMap()) > 0) {
            $url = self::getUrl()."?".self::getUrlParamsAsString(self::getRequestParamsMap());
            curl_setopt($curl_pointer, CURLOPT_URL, $url);
        } else {
            curl_setopt($curl_pointer, CURLOPT_URL, self::getUrl());
        }
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, self::getRequestHeadersAsArray());
        curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_GET);

        if ($this->requestType === APIConstants::REQUEST_METHOD_POST) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_POST);
            curl_setopt($curl_pointer, CURLOPT_POST, true);
            curl_setopt($curl_pointer, CURLOPT_POSTFIELDS,
                $this->isBulkRequest ? json_encode(self::getRequestBody()) : self::getRequestBody());
        } elseif ($this->requestType === APIConstants::REQUEST_METHOD_PUT) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_PUT);
            curl_setopt($curl_pointer, CURLOPT_POSTFIELDS,
                $this->isBulkRequest ? json_encode(self::getRequestBody()) : self::getRequestBody());
        } elseif ($this->requestType === APIConstants::REQUEST_METHOD_DELETE) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_DELETE);
        }
        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);

        return array(
            $result,
            $responseInfo,
        );
    }

    public function downloadFile(): array
    {
        $curl_pointer = curl_init();
        curl_setopt($curl_pointer, CURLOPT_URL, self::getUrl());
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, self::getRequestHeadersAsArray());
        // curl_setopt($curl_pointer,CURLOPT_SSLVERSION,3);
        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);

        return array(
            $result,
            $responseInfo,
        );
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function addParam($key, $value): void
    {
        if ($this->requestParams[$key] == null) {
            $this->requestParams[$key] = array(
                $value,
            );
        } else {
            $valArray = $this->requestParams[$key];
            $valArray[] = $value;
            $this->requestParams[$key] = $valArray;
        }
    }

    public function addHeader($key, $value): void
    {
        if ($this->requestHeaders[$key] == null) {
            $this->requestHeaders[$key] = array(
                $value,
            );
        } else {
            $valArray = $this->requestHeaders[$key];
            $valArray[] = $value;
            $this->requestHeaders[$key] = $valArray;
        }
    }

    public function getUrlParamsAsString($urlParams): array|string
    {
        $params_as_string = "";
        foreach ($urlParams as $key => $valueArray) {
            foreach ($valueArray as $value) {
                $params_as_string = $params_as_string.$key."=".urlencode($value)."&";
                $this->requestParamCount++;
            }
        }
        $params_as_string = rtrim($params_as_string, "&");
        $params_as_string = str_replace(PHP_EOL, '', $params_as_string);

        return $params_as_string;
    }

    public function setRequestHeadersMap($headers): void
    {
        $this->requestHeaders = $headers;
    }

    public function getRequestHeadersMap(): array
    {
        return $this->requestHeaders;
    }

    public function setRequestParamsMap($params): void
    {
        $this->requestParams = $params ?? [];
    }

    public function getRequestParamsMap(): array
    {
        return $this->requestParams;
    }

    public function setRequestBody($reqBody): void
    {
        $this->requestBody = $reqBody;
    }

    public function getRequestBody()
    {
        return $this->requestBody;
    }

    public function setRequestType($reqType)
    {
        $this->requestType = $reqType;
    }

    public function getRequestType(): string
    {
        return $this->requestType;
    }

    public function getRequestHeadersAsArray(): array
    {
        $headersArray = array();
        $headersMap = self::getRequestHeadersMap();
        foreach ($headersMap as $key => $value) {
            $headersArray[] = $key.":".$value;
        }

        return $headersArray;
    }

    /**
     * Get the API Key used in the input json data(like 'modules', 'data','layouts',..etc)
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Set the API Key used in the input json data(like 'modules', 'data','layouts',..etc)
     *
     * @param  String  $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * isBulkRequest
     *
     * @return bool
     */
    public function isBulkRequest(): bool
    {
        return $this->isBulkRequest;
    }

    /**
     * isBulkRequest
     *
     * @param
     *            $isBulkRequest
     */
    public function setBulkRequest($isBulkRequest): void
    {
        $this->isBulkRequest = $isBulkRequest;
    }
}