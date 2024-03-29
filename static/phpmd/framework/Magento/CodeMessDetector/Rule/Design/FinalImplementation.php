<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CodeMessDetector\Rule\Design;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\ClassAware;
use PHPMD\Rule\MethodAware;

/**
 * Magento is a highly extensible and customizable platform.
 * Usage of final classes and methods is prohibited.
 * @todo not enforced in 2.4.4
 */
class FinalImplementation extends AbstractRule implements ClassAware, MethodAware
{
    /**
     * @inheritdoc
     */
    public function apply(AbstractNode $node)
    {
        if ($node->isFinal()) {
            $this->addViolation($node, [$node->getType(), $node->getFullQualifiedName()]);
        }
    }
}