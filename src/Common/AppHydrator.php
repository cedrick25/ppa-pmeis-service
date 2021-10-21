<?php

declare(strict_types=1);

namespace App\Common;

use ReflectionClass;
use ReflectionException;

class AppHydrator
{
    /**
     * @throws ReflectionException
     */
    public function convertObjectToArray($object): array
    {
        $reflectionClass = new ReflectionClass($object);

        $properties = $reflectionClass->getProperties();

        $array = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if (is_object($value)) {
                $array[$property->getName()] = self::convertObjectToArray($value);
            } else {
                $array[$property->getName()] = $value;
            }
        }

        return $array;
    }

    /**
     * @throws ReflectionException
     */
    public function convertArrayToObject(array $args, string $class): object
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->newInstanceArgs($args);
    }

    public function transferModelValuesToEntityObject(object $model, string $entityClass): object
    {
        return (object) [];
    }
}