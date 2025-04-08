<?php

namespace RegularLabs\Scoped\DeepCopy\Matcher\Doctrine;

use RegularLabs\Scoped\DeepCopy\Matcher\Matcher;
use RegularLabs\Scoped\Doctrine\Persistence\Proxy;
/**
 * @final
 */
class DoctrineProxyMatcher implements Matcher
{
    /**
     * Matches a Doctrine Proxy class.
     *
     * {@inheritdoc}
     */
    public function matches($object, $property)
    {
        return $object instanceof Proxy;
    }
}
