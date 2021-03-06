<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\MediaGalleryUi\Ui\Component\Listing\Columns\SourceIconProvider;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load Media Asset from database by id add all related data to it
 */
class GetDetailsByAssetId
{
    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetById;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SourceIconProvider
     */
    private $sourceIconProvider;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetKeywords;

    /**
     * @var AssetDetailsProviderPool
     */
    private $detailsProviderPool;

    /**
     * @param AssetDetailsProviderPool $detailsProviderPool
     * @param GetAssetsByIdsInterface $getAssetById
     * @param StoreManagerInterface $storeManager
     * @param SourceIconProvider $sourceIconProvider
     * @param GetAssetsKeywordsInterface $getAssetKeywords
     */
    public function __construct(
        AssetDetailsProviderPool $detailsProviderPool,
        GetAssetsByIdsInterface $getAssetById,
        StoreManagerInterface $storeManager,
        SourceIconProvider $sourceIconProvider,
        GetAssetsKeywordsInterface $getAssetKeywords
    ) {
        $this->detailsProviderPool = $detailsProviderPool;
        $this->getAssetById = $getAssetById;
        $this->storeManager = $storeManager;
        $this->sourceIconProvider = $sourceIconProvider;
        $this->getAssetKeywords = $getAssetKeywords;
    }

    /**
     * Get image details by asset ID
     *
     * @param int $assetId
     * @throws LocalizedException
     * @throws Exception
     * @return array
     */
    public function execute(int $assetId): array
    {
        $asset = current($this->getAssetById->execute([$assetId]));

        return [
            'image_url' => $this->getUrl($asset->getPath()),
            'title' => $asset->getTitle(),
            'path' => $asset->getPath(),
            'id' => $assetId,
            'details' => $this->detailsProviderPool->execute($asset),
            'size' => $asset->getSize(),
            'tags' => $this->getKeywords($asset),
            'source' => $asset->getSource() ? $this->sourceIconProvider->getSourceIconUrl($asset->getSource()) : null,
            'content_type' => $asset->getContentType()
        ];
    }

    /**
     * Key asset keywords
     *
     * @param AssetInterface $asset
     * @return string[]
     */
    private function getKeywords(AssetInterface $asset): array
    {
        $assetKeywords = $this->getAssetKeywords->execute([$asset->getId()]);

        if (empty($assetKeywords)) {
            return [];
        }

        $keywords = current($assetKeywords)->getKeywords();

        return array_map(
            function (KeywordInterface $keyword) {
                return $keyword->getKeyword();
            },
            $keywords
        );
    }

    /**
     * Get URL for the provided media asset path
     *
     * @param string $path
     *
     * @return string
     *
     * @throws LocalizedException
     */
    private function getUrl(string $path): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
    }
}
