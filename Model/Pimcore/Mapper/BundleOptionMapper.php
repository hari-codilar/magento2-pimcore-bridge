<?php

namespace Divante\PimcoreIntegration\Model\Pimcore\Mapper;

class BundleOptionMapper implements ComplexMapperInterface
{
    /**
     * @param array $attributeData
     *
     * @return mixed
     */
    public function map(array $attributeData)
    {
        //TODO: Validate the Bundle options
        return $attributeData['value'];
    }
}
