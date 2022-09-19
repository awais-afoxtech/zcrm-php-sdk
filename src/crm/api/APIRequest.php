<?php

namespace zcrmsdk\crm\api;

use zcrmsdk\crm\utility\APIConstants;
use zcrmsdk\crm\utility\ZCRMConfigUtil;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\utility\ZohoHTTPConnector;
use zcrmsdk\crm\api\response\FileAPIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;

/**
 * This class is to construct the API requests and initiate the request
 *
 * @author sumanth-3058
 *
 */
class APIRequest
{

    private string $url = '';

    private string $bulkurl = '';

    private array $requestParams = array();

    private array $requestHeaders = array();

    private string $requestBody = '';

    private string $requestMethod;

    private string $apiKey = '';

    private $response;

    private array $responseInfo = [];

    private function __construct($apiHandler)
    {
        if (str_contains($apiHandler->getUrlPath(), "content") || str_contains($apiHandler->getUrlPath(), "upload")
            || str_contains($apiHandler->getUrlPath(), 'bulk-write')) {
            self::setUrl($apiHandler->getUrlPath());
        } else {
            self::constructAPIUrl($apiHandler);
            self::setUrl($this->url.$apiHandler->getUrlPath());
            if ( ! str_starts_with($apiHandler->getUrlPath(), "http")) {
                self::setUrl("https://".$this->url);
            }
        }
        self::setRequestParams($apiHandler->getRequestParams());
        self::setRequestHeaders($apiHandler->getRequestHeaders());
        self::setRequestBody($apiHandler->getRequestBody());
        self::setRequestMethod($apiHandler->getRequestMethod());
        self::setApiKey($apiHandler->getApiKey());
    }

    public static function getInstance($apiHandler): APIRequest
    {
        return new APIRequest($apiHandler);
    }

    /**
     * Method to construct the API Url
     */
    public function constructAPIUrl($apiHandler): void
    {
        $hitSandbox = ZCRMConfigUtil::getConfigValue('sandbox');
        $baseUrl = strcasecmp($hitSandbox, "true") == 0 ? str_replace('www', 'sandbox',
            ZCRMConfigUtil::getAPIBaseUrl()) : ZCRMConfigUtil::getAPIBaseUrl();
        if ($apiHandler->isBulk()) {
            $this->url = $baseUrl."/crm/bulk/".ZCRMConfigUtil::getAPIVersion()."/";
        } else {
            $this->url = $baseUrl."/crm/".ZCRMConfigUtil::getAPIVersion()."/";
        }
        $this->url = str_replace(PHP_EOL, '', $this->url);
    }

    private function authenticateRequest()
    {
        try {
            $accessToken = (new ZCRMConfigUtil())->getAccessToken();
            if (str_contains($this->url, "content") || str_contains($this->url, "upload")
                || str_contains($this->url, "bulk-write")) {
                $this->requestHeaders[APIConstants::AUTHORIZATION] = " ".APIConstants::OAUTH_HEADER_PREFIX.$accessToken;
            } else {
                $this->requestHeaders[APIConstants::AUTHORIZATION] = APIConstants::OAUTH_HEADER_PREFIX.$accessToken;
            }
            //             $this->requestHeaders[APIConstants::AUTHORIZATION] = APIConstants::OAUTH_HEADER_PREFIX . $accessToken;
        } catch (ZCRMException $ex) {
            throw $ex;
        }
    }

    /**
     * initiate the request and get the API response
     *
     * @return APIResponse
     */
    public function getAPIResponse()
    {
        try {
            $connector = ZohoHTTPConnector::getInstance();
            $connector->setUrl($this->url);
            self::authenticateRequest();
            $connector->setRequestHeadersMap($this->requestHeaders);
            $connector->setRequestParamsMap($this->requestParams);
            $connector->setRequestBody($this->requestBody);
            $connector->setRequestType($this->requestMethod);
            $connector->setApiKey($this->apiKey);
            $response = $connector->fireRequest();
            $this->response = $response[0];
            $this->responseInfo = $response[1];

            return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    /**
     * initiate the request and get the API response
     *
     * @return BulkAPIResponse
     */
    public function getBulkAPIResponse()
    {
        try {
            $connector = ZohoHTTPConnector::getInstance();
            $connector->setUrl($this->url);
            self::authenticateRequest();
            $connector->setRequestHeadersMap($this->requestHeaders);
            $connector->setRequestParamsMap($this->requestParams);
            $connector->setRequestBody($this->requestBody);
            $connector->setRequestType($this->requestMethod);
            $connector->setApiKey($this->apiKey);
            $connector->setBulkRequest(true);
            $response = $connector->fireRequest();
            $this->response = $response[0];
            $this->responseInfo = $response[1];

            return new BulkAPIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    public function uploadFile($filePath)
    {
        try {
            $mime = null;
            $filename = basename($filePath);
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);
            } elseif (function_exists('mime_content_type')) {
                $mime = mime_content_type($filePath);
            } else {
                $mime = "application/octet-stream";
            }
            if (function_exists('curl_file_create')) { // php 5.6+
                $cFile = curl_file_create($filePath, $mime, $filename);
            } else { //
                $cFile = '@'.realpath($filePath, $mime, $filename);
            }
            $post = array(
                'file' => $cFile,
            );

            $connector = ZohoHTTPConnector::getInstance();
            $connector->setUrl($this->url);
            self::authenticateRequest();
            $connector->setRequestHeadersMap($this->requestHeaders);
            $connector->setRequestParamsMap($this->requestParams);
            $connector->setRequestBody($post);
            $connector->setRequestType($this->requestMethod);
            $connector->setApiKey($this->apiKey);
            $response = $connector->fireRequest();
            $this->response = $response[0];
            $this->responseInfo = $response[1];

            return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    public function uploadLinkAsAttachment($linkURL)
    {
        try {
            $post = array(
                'attachmentUrl' => $linkURL,
            );

            $connector = ZohoHTTPConnector::getInstance();
            $connector->setUrl($this->url);
            self::authenticateRequest();
            $connector->setRequestHeadersMap($this->requestHeaders);
            $connector->setRequestBody($post);
            $connector->setRequestType($this->requestMethod);
            $connector->setApiKey($this->apiKey);
            $response = $connector->fireRequest();
            $this->response = $response[0];
            $this->responseInfo = $response[1];

            return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    public function downloadFile()
    {
        try {
            $connector = ZohoHTTPConnector::getInstance();
            $connector->setUrl($this->url);
            self::authenticateRequest();
            $connector->setRequestHeadersMap($this->requestHeaders);
            $connector->setRequestParamsMap($this->requestParams);
            $connector->setRequestType($this->requestMethod);
            $response = $connector->downloadFile();

            return (new FileAPIResponse())->setFileContent($response[0], $response[1][APIConstants::HTTP_CODE]);
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    /**
     * Get the request url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the request url
     *
     * @param  ?string  $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url ?? '';
    }

    /**
     * Get the request parameters
     *
     * @return array
     */
    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    /**
     * Set the request parameters
     *
     * @param  ?array  $requestParams
     */
    public function setRequestParams(?array $requestParams): void
    {
        $this->requestParams = $requestParams ?? [];
    }

    /**
     * Get the request headers
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * Set the request headers
     *
     * @param  ?array  $requestHeaders
     */
    public function setRequestHeaders(?array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders ?? [];
    }

    /**
     * Get the request body
     */
    public function getRequestBody(): string
    {
        return $this->requestBody;
    }

    /**
     * Set the request body
     *
     * @param $requestBody
     */
    public function setRequestBody($requestBody): void
    {
        $this->requestBody = $requestBody ?? '';
    }

    /**
     * Get the request method
     *
     * @return String
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Set the request method
     *
     * @param  ?string  $requestMethod
     */
    public function setRequestMethod(?string $requestMethod): void
    {
        $this->requestMethod = $requestMethod ?? '';
    }

    /**
     * Get the API Key used in the input json data(like 'modules', 'data','layouts',..etc)
     *
     * @return String
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Set the API Key used in the input json data(like 'modules', 'data','layouts',..etc)
     *
     * @param  ?string  $apiKey
     */
    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey ?? '';
    }
}