<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test\Integrity\Magento\Webapi\Model;

use Magento\TestFramework\Integrity\AbstractConfig;

/**
 * Find webapi xml files and validate them
 */
class ConfigTest extends AbstractConfig
{
    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'webapi.xml';
    }

    /**
     * Returns the name of the XSD file to be used to validate the XSD
     *
     * @return string
     */
    protected function _getXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:module:Magento_Webapi:etc/webapi_merged.xsd');
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:module:Magento_Webapi:etc/webapi.xsd');
    }
}
