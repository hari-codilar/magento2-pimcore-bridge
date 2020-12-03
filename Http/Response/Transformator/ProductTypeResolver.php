<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ProductTypeResolver
 */
class ProductTypeResolver
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function resolveType(array $data): string
    {
        $type = Type::TYPE_SIMPLE;
        if ($this->isBundle($data)) {
            return Type::TYPE_BUNDLE;
        }
        if ($this->isConfigurable($data)) {
            return Configurable::TYPE_CODE;
        }

        list($data, $type) = $this->beforeReturn($data, $type);

        return $type;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isBundle(array $data): bool
    {
        return ($data['properties']['isBundle']['data']) ?? false;
        if (empty($data['type'])) {
            return false;
        }
        return $data['type'] === 'bundle' || $data['type'] === 'bundle_attributes';
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isConfigurable(array $data): bool
    {
        if (empty($data['type'])) {
            return false;
        }

        return $data['type'] === 'configurable_attributes' || $data['type'] === 'configurable';
    }

    /**
     * Plugin this method for custom types resolving
     *
     * @param array $data
     * @param string $type
     *
     * @return array
     */
    public function beforeReturn(array $data, string $type): array
    {
        return [$data, $type];
    }
}
