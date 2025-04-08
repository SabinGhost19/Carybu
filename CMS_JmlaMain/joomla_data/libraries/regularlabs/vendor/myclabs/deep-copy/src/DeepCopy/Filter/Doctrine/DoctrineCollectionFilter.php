<?php

namespace RegularLabs\Scoped\DeepCopy\Filter\Doctrine;

use RegularLabs\Scoped\DeepCopy\Filter\Filter;
use RegularLabs\Scoped\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class DoctrineCollectionFilter implements Filter
{
    /**
     * Copies the object property doctrine collection.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $oldCollection = $reflectionProperty->getValue($object);
        $newCollection = $oldCollection->map(function ($item) use ($objectCopier) {
            return $objectCopier($item);
        });
        $reflectionProperty->setValue($object, $newCollection);
    }
}
