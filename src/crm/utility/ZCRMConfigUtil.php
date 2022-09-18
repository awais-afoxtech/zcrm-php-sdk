<?php

namespace zcrmsdk\crm\utility;

use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\ZohoOAuth;
use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\oauth\utility\ZohoOAuthConstants;

class ZCRMConfigUtil
{

    private static array $configProperties = array();

    public static function getInstance(): ZCRMConfigUtil
    {
        return new ZCRMConfigUtil();
    }

    /**
     * @throws ZohoOAuthException
     */
    public static function initialize($configuration): void
    {
        $mandatory_keys = array(
            ZohoOAuthConstants::CLIENT_ID,
            ZohoOAuthConstants::CLIENT_SECRET,
            ZohoOAuthConstants::REDIRECT_URL,
        );
        // check if user input contains all mandatory values
        foreach ($mandatory_keys as $key) {
            if ( ! array_key_exists($key, $configuration)) {
                throw new ZohoOAuthException($key." is mandatory");
            } elseif ($configuration[$key] == "") {
                throw new ZohoOAuthException($key." value is missing");
            }
        }
        if (array_key_exists(APIConstants::CURRENT_USER_EMAIL, $configuration)
            && $configuration[APIConstants::CURRENT_USER_EMAIL]
               != "")//if current user email id is provided in map and is not empty
        {
            ZCRMRestClient::setCurrentUserEmailId($configuration[APIConstants::CURRENT_USER_EMAIL]);
        }
        self::setConfigValues($configuration);
        ZohoOAuth::initialize($configuration);
    }

    private static function setConfigValues($configuration)
    {
        $config_keys = array(
            APIConstants::CURRENT_USER_EMAIL,
            ZohoOAuthConstants::SANDBOX,
            APIConstants::API_BASE_URL,
            APIConstants::API_VERSION,
            APIConstants::APPLICATION_LOGFILE_PATH,
            APIConstants::FILE_UPLOAD_URL,
        );

        if ( ! array_key_exists(ZohoOAuthConstants::SANDBOX, $configuration)) {
            self::$configProperties[ZohoOAuthConstants::SANDBOX] = "false";
        }
        if ( ! array_key_exists(APIConstants::API_BASE_URL, $configuration)) {
            self::$configProperties[APIConstants::API_BASE_URL] = "www.zohoapis.com";
        }
        if ( ! array_key_exists(APIConstants::API_VERSION, $configuration)) {
            self::$configProperties[APIConstants::API_VERSION] = "v2";
        }
        foreach ($config_keys as $key) {
            if (array_key_exists($key, $configuration)) {
                self::$configProperties[$key] = $configuration[$key];
            }
        }
    }

    public static function getConfigValue($key)
    {
        return self::$configProperties[$key] ?? '';
    }

    public static function setConfigValue($key, $value): void
    {
        self::$configProperties[$key] = $value;
    }

    public static function getAPIBaseUrl()
    {
        return self::getConfigValue(APIConstants::API_BASE_URL);
    }

    public static function getFileUploadURL()
    {
        return self::getConfigValue(APIConstants::FILE_UPLOAD_URL);
    }

    public static function getAPIVersion()
    {
        return self::getConfigValue(APIConstants::API_VERSION);
    }

    /**
     * @throws ZCRMException
     * @throws ZohoOAuthException
     */
    public static function getAccessToken()
    {
        $currentUserEmail = ZCRMRestClient::getCurrentUserEmailID();

        if ($currentUserEmail == null && self::getConfigValue(APIConstants::CURRENT_USER_EMAIL) == null) {
            throw new ZCRMException("current user should either be set in ZCRMRestClient or in configuration map");
        } elseif ($currentUserEmail == null) {
            $currentUserEmail = self::getConfigValue(APIConstants::CURRENT_USER_EMAIL);
        }
        $oAuthCliIns = ZohoOAuth::getClientInstance();

        return $oAuthCliIns->getAccessToken($currentUserEmail);
    }

    public static function getAllConfigs(): array
    {
        return self::$configProperties;
    }
}