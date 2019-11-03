<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockImageAdminUi\Controller\Adminhtml\License;

use Magento\AdobeStockClientApi\Api\ClientInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Backend controller for retrieving license confirmation before purchasing an asset
 */
class Confirmation extends Action
{
    /**
     * Successful image download result code.
     */
    private const HTTP_OK = 200;

    /**
     * Internal server error response code.
     */
    private const HTTP_INTERNAL_ERROR = 500;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdobeStockImageAdminUi::license_images';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetQuota constructor.
     *
     * @param Action\Context $context
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        ClientInterface $client,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $contentId = isset($params['media_id']) ? (int)$params['media_id'] : 0;
            $confirmation = $this->client->getLicenseConfirmation($contentId);
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'result' => [
                    'message' => $confirmation->getMessage(),
                    'canLicense' => $confirmation->getCanLicense()
                ]
            ];

        } catch (\Exception $exception) {
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $logMessage = __('An error occurred during get quota operation: %1', $exception->getMessage());
            $this->logger->critical($logMessage);
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred during get quota operation. Contact support.'),
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
