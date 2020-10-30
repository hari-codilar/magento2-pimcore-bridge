<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class AbstractStrategy
 */
abstract class  AbstractStrategy implements AttributeCreationStrategyInterface
{
    /**
     * @var eavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $attrData;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var array
     */
    protected static $defaultAttrConfig = [
        'backend'                 => '',
        'frontend'                => '',
        'input'                   => 'text',
        'class'                   => '',
        'source'                  => '',
        'global'                  => ScopedAttributeInterface::SCOPE_STORE,
        'visible'                 => true,
        'required'                => false,
        'user_defined'            => true,
        'searchable'              => true,
        'filterable'              => false,
        'comparable'              => false,
        'visible_on_front'        => false,
        'used_in_product_listing' => false,
        'unique'                  => false,
    ];

    /**
     * AbstractStrategy constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $attrData
     * @param string $code
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $attrData,
        string $code
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attrData = $attrData;
        $this->code = $code;
    }
}
