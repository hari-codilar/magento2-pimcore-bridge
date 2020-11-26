<?php
namespace Divante\PimcoreIntegration\Cron;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Logger\Stream\Logger;
use Divante\PimcoreIntegration\Queue\Processor\AssetQueueProcessor;
use Divante\PimcoreIntegration\Queue\Processor\CategoryQueueProcessor;
use Divante\PimcoreIntegration\Queue\Processor\ProductQueueProcessor;
use Exception;

class QueueConsumer
{
    /**
     * @var AssetQueueProcessor
     */
    private AssetQueueProcessor $assetQueueProcessor;
    /**
     * @var ProductQueueProcessor
     */
    private ProductQueueProcessor $productQueueProcessor;
    /**
     * @var CategoryQueueProcessor
     */
    private CategoryQueueProcessor $categoryQueueProcessor;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * QueueConsumer constructor.
     * @param AssetQueueProcessor $assetQueueProcessor
     * @param ProductQueueProcessor $productQueueProcessor
     * @param CategoryQueueProcessor $categoryQueueProcessor
     * @param Logger $logger
     */
    public function __construct(
        AssetQueueProcessor $assetQueueProcessor,
        ProductQueueProcessor $productQueueProcessor,
        CategoryQueueProcessor $categoryQueueProcessor,
        BridgeLoggerFactory $bridgeLoggerFactory
    ) {

        $this->assetQueueProcessor = $assetQueueProcessor;
        $this->productQueueProcessor = $productQueueProcessor;
        $this->categoryQueueProcessor = $categoryQueueProcessor;
        $this->logger = $bridgeLoggerFactory->getLoggerInstance();
    }

    public function consume()
    {
        try {
            $this->categoryQueueProcessor->process();
            $this->productQueueProcessor->process();
            $this->assetQueueProcessor->process();
        } catch (Exception $e) {
            $this->logger->$e->getMessage();
        }
    }
}
