<?php
/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2022  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

namespace Magento\TestFramework;

use ReflectionClass;
use ReflectionParameter;

/**
 * Returns a reflection parameter's class if possible.
 */
trait GetParameterClassTrait
{
    /**
     * Get class by reflection parameter
     *
     * @param ReflectionParameter $reflectionParameter
     *
     * @return ReflectionClass|null
     * @throws ReflectionException
     */
    private function getParameterClass(ReflectionParameter $reflectionParameter): ?ReflectionClass
    {
        $parameterType = $reflectionParameter->getType();
        // In PHP8, $parameterType could be an instance of ReflectionUnionType, which doesn't have isBuiltin method.
        if ($parameterType !== null && method_exists($parameterType, 'isBuiltin') === false) {
            return null;
        }

        return $parameterType && !$parameterType->isBuiltin()
            ? new ReflectionClass($parameterType->getName())
            : null;
    }
}