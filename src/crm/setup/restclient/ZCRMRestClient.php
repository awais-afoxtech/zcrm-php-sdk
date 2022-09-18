<?php

namespace zcrmsdk\crm\setup\restclient;

use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\api\handler\MetaDataAPIHandler;
use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\crm\api\handler\OrganizationAPIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\bulkcrud\ZCRMBulkRead;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWrite;
use zcrmsdk\crm\utility\ZCRMConfigUtil;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZCRMCustomView;

class ZCRMRestClient
{

    private function __construct()
    {
    }

    private static string $CurrentUserEmailID;

    /**
     * method to get the instance of the rest client
     *
     * @return ZCRMRestClient instance of the ZCRMRestClient class
     */
    public static function getInstance(): ZCRMRestClient
    {
        return new ZCRMRestClient();
    }

    public static function setCurrentUserEmailId($UserEmailId): void
    {
        self::$CurrentUserEmailID = $UserEmailId;
    }

    /**
     * method to initialize the configuration the rest client
     *
     * @param  array  $configuration  array of configurations.
     *
     * @throws ZohoOAuthException
     */
    public static function initialize(array $configuration): void
    {
        ZCRMConfigUtil::initialize($configuration);
    }

    /**
     * method to get all the modules of the rest-client
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class containing the bulk api response
     * @throws ZCRMException
     */
    public function getAllModules(): BulkAPIResponse
    {
        return MetaDataAPIHandler::getInstance()->getAllModules();
    }

    /**
     * method to get the module of the rest client
     *
     * @param  string  $moduleName  api name of the module
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     * @throws ZCRMException
     */
    public function getModule(string $moduleName): APIResponse
    {
        return MetaDataAPIHandler::getInstance()->getModule($moduleName);
    }

    /**
     * method to get the organization of the rest client
     *
     * @return ZCRMOrganization instance of the ZCRMOrganization class
     */
    public function getOrganizationInstance(): ZCRMOrganization
    {
        return ZCRMOrganization::getInstance();
    }

    /**
     * method to get the Custom view of the organisation
     *
     * @return ZCRMCustomView instance of the ZCRMCustomView class
     */
    public function getCustomViewInstance($moduleAPIName, $id): ZCRMCustomView
    {
        return ZCRMCustomView::getInstance($moduleAPIName, $id);
    }

    /**
     * method to get the module of the rest client
     *
     * @param  string  $moduleAPIName  module api name
     *
     * @return ZCRMModule instance of the ZCRMModule class
     */
    public function getModuleInstance(string $moduleAPIName): ZCRMModule
    {
        return ZCRMModule::getInstance($moduleAPIName);
    }

    /**
     * method to get the record of the client
     *
     * @param  string  $moduleAPIName  module api name
     * @param  string  $entityId  record id
     *
     * @return ZCRMRecord instance of the ZCRMRecord class
     */
    public function getRecordInstance(string $moduleAPIName, string $entityId): ZCRMRecord
    {
        return ZCRMRecord::getInstance($moduleAPIName, $entityId);
    }

    /**
     * method to get the current user of the rest client
     *
     * @return BulkAPIResponse instance of the APIResponse class containing the api response
     */
    public function getCurrentUser(): BulkAPIResponse
    {
        return OrganizationAPIHandler::getInstance()->getCurrentUser();
    }

    /**
     * method to get the current user email id
     *
     * @return string current user email id
     */
    public static function getCurrentUserEmailID(): string
    {
        return self::$CurrentUserEmailID;
    }

    /**
     * method to get the organization details of the rest client
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     * @throws ZCRMException
     */
    public static function getOrganizationDetails(): APIResponse
    {
        return OrganizationAPIHandler::getInstance()->getOrganizationDetails();
    }

    /**
     * Method to get the bulk read instance
     *
     * @param  string|null  $moduleName
     * @param  string|null  $jobId
     *
     * @return ZCRMBulkRead - class instance
     */
    public function getBulkReadInstance(string $moduleName = null, string $jobId = null): ZCRMBulkRead
    {
        return ZCRMBulkRead::getInstance($moduleName, $jobId);
    }

    /**
     * Method to get the bulk write instance
     *
     * @param  string|null  $operation  - bulk write operation (insert or update)
     * @param  string|null  $jobId  - bulk write job id
     * @param  string|null  $moduleAPIName  - bulk write module api name
     *
     * @return ZCRMBulkWrite - class instance
     */
    public function getBulkWriteInstance(string $operation = null, string $jobId = null, string $moduleAPIName = null): ZCRMBulkWrite
    {
        return ZCRMBulkWrite::getInstance($operation, $jobId, $moduleAPIName);
    }
}