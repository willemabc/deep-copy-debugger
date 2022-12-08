<?php

declare(strict_types=1);

namespace DeepCopyDebugger;

use Closure;
use DeepCopy\DeepCopy;
use DeepCopy\Matcher\PropertyMatcher;
use DeepCopy\Matcher\PropertyNameMatcher;
use ReflectionClass;

class DeepCopyDebugger
{
    private DeepCopy $deepCopy;

    public function __construct(DeepCopy $deepCopy)
    {
        $this->deepCopy = $deepCopy;
    }

    /*
     * Returns DeepCopy::filters. This is a private property of DeepCopy, so we have to be creative to get it exposed.
     */
    public function getFilterCollection(): array
    {
        return $this->getPrivateProperty($this->deepCopy, 'filters');
    }

    /*
     * This method returns all DeepCopy filters applicable for each Dcotrine entity property.
     * Returns an array with the property as key, and the filter/filters in an array as value.
     * $entityClass is YourDoctrineEntity::class.
     */
    public function getMatchedAndUnmatchedEntityProperties(string $entityClass): array
    {
        $matchedPropertyCollection = [];
        $propertyCollection = $this->getPropertyCollection($entityClass);
        $formattedFilterCollection = $this->getFilterCollection();

        foreach ($propertyCollection as $property) {
            $matchedPropertyCollection[$property] = [];

            foreach ($formattedFilterCollection as $filter) {
                switch ($filter['matcher']) {
                    case PropertyNameMatcher::class:
                        if ($filter['matcherData']['property'] === $property) {
                            $matchedPropertyCollection[$property][] = $filter;
                        }

                        break;
                    case PropertyMatcher::class:
                        if (
                            $filter['matcherData']['class'] === $entityClass
                            && $filter['matcherData']['property'] === $property
                        ) {
                            $matchedPropertyCollection[$property][] = $filter;
                        }

                        break;
                }
            }
        }

        return $matchedPropertyCollection;
    }

    /*
     * This method returns all DeepCopy filters applicable for each entity property, with only properties returned
     * that were matched (i.e. with filters that apply).
     * Returns an array with the property as key, and the filter/filters in an array as value.
     */
    public function getMatchedEntityProperties(string $entityClass): array
    {
        $entityProperties = $this->getMatchedAndUnmatchedEntityProperties($entityClass);

        return array_filter($entityProperties);
    }

    /*
     * This method returns entity properties that were not matched by any DeepCopy filters (i.e. with no filters
     * applied).
     * Returns an array with the property as key, and an empty array as value (to stay compatible with other methods).
     */
    public function getUnmatchedEntityProperties(string $entityClass): array
    {
        $entityProperties = $this->getMatchedAndUnmatchedEntityProperties($entityClass);

        return array_filter($entityProperties, static function ($property) {
            return empty($property) === true;
        });
    }

    /*
     * Exposes a private property on an object and returns it, by basically adding a getter to the class.
     */
    private function getPrivateProperty(object $object, string $property)
    {
        $getFilters = Closure::bind(
            static function ($object) use ($property) {
                return $object->$property;
            },
            null,
            $object
        );

        return $getFilters($object);
    }

    /*
     * Uses reflection to get all properties from an object.
     */
    private function getPropertyCollection($object): array
    {
        $propertyCollection = [];

        $reflection = new ReflectionClass($object);
        $reflectionPropertyCollection = $reflection->getProperties();
        foreach ($reflectionPropertyCollection as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyCollection[$propertyName] = $propertyName;
        }

        return $propertyCollection;
    }
}
