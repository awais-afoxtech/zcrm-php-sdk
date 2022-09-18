<?php

namespace zcrmsdk\crm\utility;

class CommonUtil
{
    public static function getEmptyJSONObject(): \ArrayObject
    {
        return new \ArrayObject();
    }

    public static function removeNullValuesAlone($value): bool
    {
        return $value !== null;
    }
}