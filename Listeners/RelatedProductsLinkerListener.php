<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RelatedProductsLinkerListener
 */
class RelatedProductsLinkerListener implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductLinkInterfaceFactory
     */
    private $linkInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * RelatedProductsModifier constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param ProductLinkInterfaceFactory $linkInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductLinkInterfaceFactory $linkInterfaceFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $pimcoreProduct = $observer->getData('pimcore');
        $product = $observer->getData('product');

        $relatedIds = $pimcoreProduct->getData('related_products');
        $upSellIds = $pimcoreProduct->getData('upsell_products');
        $crossSellIds = $pimcoreProduct->getData('crosssell_products');

        $links = [];

        if (null !== $relatedIds) {
            $links = array_merge($links, $this->createProductLinks($relatedIds, 'related', $pimcoreProduct->getSku()));
        }
        if (null !== $upSellIds) {
            $links = array_merge($links, $this->createProductLinks($upSellIds, 'upsell', $pimcoreProduct->getSku()));
        }
        if (null !== $crossSellIds) {
            $links = array_merge($links, $this->createProductLinks($crossSellIds, 'crosssell', $pimcoreProduct->getSku()));
        }
        if (!count($links)) {
            return;
        }
        $product->setProductLinks($links);
        $this->productRepository->save($product);
    }

    /**
     * @param $relatedProductsIds
     *
     * @return Collection
     */
    private function getCollectionOfRelatedProducts($relatedProductsIds): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('pimcore_id');
        $collection->addFieldToFilter('pimcore_id', ['in' => $relatedProductsIds]);

        return $collection;
    }

    /**
     * Creates Products Links for the given sku and product ids
     *
     * @param int[] $productIds
     * @param string $linkType
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    protected function createProductLinks($productIds, $linkType, $sku)
    {
        $collection = $this->getCollectionOfRelatedProducts($productIds);
        $links = [];
        $collection->setFlag('has_stock_status_filter', true);

        if (!$collection->getSize()) {
            return $links;
        }
        foreach ($collection->getItems() as $item) {
            $links[] = $this->linkInterfaceFactory->create()
                ->setSku($sku)
                ->setLinkedProductSku($item->getSku())
                ->setLinkType($linkType);
        }
        return $links;
    }
}
