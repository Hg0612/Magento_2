<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Avatar extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'avatar';

    const ALT_FIELD = 'user_name';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param ContextInterface
     * @param UiComponentFactory
     * @param \Magento\Catalog\Helper\Image
     * @param \Magento\Framework\UrlInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param array
     * @param array
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if (isset($dataSource['data']['items']))
        {            
            $fieldName = $this->getData("name");
            foreach ($dataSource['data']['items'] as &$item) {
                if(!isset($item[self::NAME])) continue;
                if( $item[self::NAME] ){
                    $thumbnailUrl = $path.$item[self::NAME];
                    $item[$fieldName . '_src'] = $thumbnailUrl;
                    $item[$fieldName . '_alt'] = __('Avatar');
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'vesblog/author/edit',
                        ['author_id' => $item['author_id'], 'store' => $this->context->getRequestParam('store')]
                        );
                    $item[$fieldName . '_orig_src'] = $thumbnailUrl;
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
