<?php

declare(strict_types=1);

namespace App\Common;

use ReflectionClass;
use ReflectionException;


class ObjectToArray
{
    /**
     * @throws ReflectionException
     */
    public function convert($object): array
    {
        $reflectionClass = new ReflectionClass($object);

        $properties = $reflectionClass->getProperties();

        $array = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if (is_object($value)) {
                $array[$property->getName()] = self::convert($value);
            } else {
                $array[$property->getName()] = $value;
            }
        }
        return $array;
    }
}