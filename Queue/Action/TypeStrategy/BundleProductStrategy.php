<?php
namespace Divante\PimcoreIntegration\Queue\Action\TypeStrategy;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class BundleProductStrategy implements ProductTypeCreationStrategyInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var OptionsFactory
     */
    private $optionsFactory;
    /**
     * @var ProductExtensionInterfaceFactory
     */
    private $productExtensionFactory;
    /**
     * @var OptionInterfaceFactory
     */
    private $optionInterfaceFactory;
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemInterfaceFactory;
    /**
     * @var LinkInterfaceFactory
     */
    private $linkInterfaceFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ConfigurableProductStrategy constructor.
     *
     * @param Config $eavConfig
     * @param OptionsFactory $optionsFactory
     * @param ProductExtensionInterfaceFactory $productExtensionFactory
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Config $eavConfig,
        OptionsFactory $optionsFactory,
        ProductExtensionInterfaceFactory $productExtensionFactory,
        OptionInterfaceFactory $optionInterfaceFactory,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        LinkInterfaceFactory $linkInterfaceFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->eavConfig = $eavConfig;
        $this->optionsFactory = $optionsFactory;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws LocalizedException
     */
    public function execute(ProductInterface $product): ProductInterface
    {
        /** Skip Product If Bundle Options are not available */
        if (!$product->getBundleOptions()) {
            $product->setIsSkip(true);
            return $product;
        }
        /** @var ProductExtensionInterface $productExtension */
        $productExtension = $product->getExtensionAttributes();

        //Handle Updating Existing Options
        $oldBundleOptions = $productExtension->getBundleProductOptions();
        $bundleOptions = [];
        foreach ($product->getBundleOptions() as $key => $bundleOption) {
            $links = [];
            /** @var OptionInterface $option */
            $option = $oldBundleOptions[$key] ?? $this->optionInterfaceFactory->create();
            $option->setTitle($bundleOption['option_title']);
            /** Setting 'SELECT' as fallback option type */
            $option->setType($bundleOption['option_type'] ?? 'select');
            $option->setRequired($bundleOption['is_required']);
            $missingProductIds = [];
            foreach ($bundleOption['products'] as $id => $productData) {
                /** @var LinkInterface $link */
                $link = $this->linkInterfaceFactory->create();
                try {
                    $subProduct = $this->productRepository->getByPimId($productData['id']);
                } catch (NoSuchEntityException $e) {
                    $missingProductIds[] = $productData['id'];
                    continue;
                }
                $link->setSku($subProduct->getSku())
                    ->setQty($productData['selection_qty'] ?? 1)
                    ->setCanChangeQuantity($productData['selection_can_change_qty'] ?? 0)
                    ->setIsDefault($productData['is_default']);
                $links[] = $link;
            }
            if (count($missingProductIds)) {
                throw new LocalizedException(
                    __(
                        'Unable to import product with ID "%1". Related products are not published yet: %2.',
                        $product->getPimcoreId(),
                        implode(',', $missingProductIds)
                    )
                );
            }
            $option->setProductLinks($links);
            $bundleOptions[] = $option;
        }

        $productExtension->setBundleProductOptions($bundleOptions);

        /**
         * Setting Price View as (0) 'Price Range'
         * Change it to 1 for 'As low as'
         * @see \Magento\Bundle\Model\Product\Attribute\Source\Price\View
         */
        $product->setPriceView(0);
        $product->setExtensionAttributes($productExtension);

        return $product;
    }
}
