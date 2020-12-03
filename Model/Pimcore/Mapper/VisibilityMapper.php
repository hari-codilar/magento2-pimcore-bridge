<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Pimcore\Mapper;

use Magento\Catalog\Model\Product\Visibility;

/**
 * Class VisibilityMapper
 */
class VisibilityMapper implements ComplexMapperInterface
{
    /**
     * @param array $attributeData
     *
     * @return int
     */
    public function map(array $attributeData): int
    {
        switch($attributeData['value']) {
            case Visibility::VISIBILITY_NOT_VISIBLE :
            case Visibility::VISIBILITY_IN_CATALOG :
            case Visibility::VISIBILITY_IN_SEARCH :
                return $attributeData['value'];
            case Visibility::VISIBILITY_BOTH :
            default:
                return Visibility::VISIBILITY_BOTH;
        }
    }
}
