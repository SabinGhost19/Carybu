<?php

namespace RegularLabs\Scoped\DeepCopy\Filter\Doctrine;

use RegularLabs\Scoped\DeepCopy\Filter\Filter;
use RegularLabs\Scoped\DeepCopy\Reflection\ReflectionHelper;
use RegularLabs\Scoped\Doctrine\Common\Collections\ArrayCollection;
/**
 * @final
 */
class DoctrineEmptyCollectionFilter implements Filter
{
    /**
     * Sets the object property to an empty doctrine collection.
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, new ArrayCollection());
    }
}
