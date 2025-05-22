<?php

namespace App\Util;

class ClassHelper
{
    /**
     * @param $obj
     * @param $prop
     * @return mixed
     * @throws \ReflectionException
     */
    public static function accessProtectedProperty($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}