<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\MarketPlace\Helper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    const XML_CATALOG_PRODUCT_TYPE_RESTRICTION  = 'vendors/catalog/product_type_restriction';
    const XML_CATALOG_ATTRIBUTE_SET_RESTRICTION = 'vendors/catalog/attribute_set_restriction';
    /**
     * Group Collection
     */
    protected $_groupCollection;
    /**
     * Group Collection
     */
    protected $_commissionCollection;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * Seller config node per website
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \Lof\MarketPlace\App\Area\FrontNameResolver
     */
    protected $_frontNameResolver;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_priceCurrency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var \Lof\MarketPlace\Helper\DataRule
     */
    protected $dataRule;

    protected $customer;
     /**
     * Blocks that will use template files from adminhtml area
     * @var array
     */
    
    protected $_blocksUseTemplateFromAdminhtml;
     /**
     * Modules that will use template files from adminhtml area
     * @var array
     */
    protected $_modulesUseTemplateFromAdminhtml;
	/**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Lof\MarketPlace\Model\ResourceModel\Group\Collection $groupCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Lof\MarketPlace\Model\Group $groupCollection,
        \Lof\MarketPlace\Model\Commission $commissionCollection,
        \Lof\MarketPlace\Helper\DataRule $dataRule,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Lof\MarketPlace\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Catalog\Model\ProductFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        PriceCurrencyInterface $priceFormatter,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Customer\Model\Session $customerSession,
         array $modulesUseTemplateFromAdminhtml=[],
        array $blocksUseTemplateFromAdminhtml =[]
        ) {
        parent::__construct($context);
        $this->dataRule = $dataRule;
        $this->customer = $customer;
        $this->_localeDate = $localeDate;
        $this->_filterProvider          = $filterProvider;
        $this->_storeManager            = $storeManager;
        $this->_groupCollection         = $groupCollection;
        $this->_commissionCollection    = $commissionCollection;
        $this->customerSession          = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_frontNameResolver = $frontNameResolver;
        $this->_objectManager  = $objectManager;
        $this->_priceCurrency = $priceCurrency;
        $this->priceFormatter = $priceFormatter;
        $this->_blocksUseTemplateFromAdminhtml = $blocksUseTemplateFromAdminhtml;
        $this->_modulesUseTemplateFromAdminhtml = $modulesUseTemplateFromAdminhtml;

    }
    /**
     * Get current currency code
     *
     * @return string
     */ 
    public function getCurrentCurrencyCode()
    {
      return $this->_priceCurrency->getCurrency()->getCurrencyCode();
    }
     public function getPriceFomat($price) {
        $currencyCode = $this->getCurrentCurrencyCode();
        return $this->priceFormatter->format(
                    $price,
                    false,
                    null,
                    null,
                    $currencyCode
                );
    }
    public function getGroupList(){
        $result = array();
        $collection = $this->_groupCollection->getCollection()->addFieldToFilter('status', '1');
        foreach ($collection as $sellerGroup) {
            $result[$sellerGroup->getId()] = $sellerGroup->getName();
        }
        return $result;
    }
    public function getCommissionList(){
        $result = array();
        $collection = $this->_commissionCollection->getCollection()->addFieldToFilter('status', '1');
        foreach ($collection as $sellerGroup) {
            $result[$sellerGroup->getId()] = $sellerGroup->getCommissionTitle();
        }
        return $result;
    }



    public function getCommission($sellerId,$productId) {
        $commission = 100;
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance ();

        $sellerProduct = $objectManager->get ( 'Lof\MarketPlace\Model\SellerProduct' )->load ( $productId, 'product_id' );
        $sellerCommission = $this->dataRule->getRuleProducts($sellerId,$productId);
        $productCommission = $sellerProduct->getCommission();
        $configCommission = $this->getConfig('seller_settings/commission');
        if(!empty($productCommission) && $productCommission > 0) {
            $commission = $productCommission;
        }elseif ($sellerCommission) {
            $commission = $sellerCommission->getData();
        }elseif (!empty($configCommission) && is_numeric($configCommission)) {
            $commission = $configCommission;
        }
        return $commission;
    }
    /**
     * Product Consition validation
     *
     * @param Commission $commission
     * @return bool
    */
    protected function _validateProductConsition($productId,$commissionId) {
        if(isset($productId)) { 
            if(!$this->dataRule->getRuleProducts($productId,$commissionId)) {
                return false;;
            }
        }
        return true;
    }

    public function getSellerId() {
       
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance ();
        $seller = $objectManager->get ( 'Lof\MarketPlace\Model\Seller' )->load ( $this->getCustomerId(), 'customer_id' );
        return $seller->getData('seller_id');
    }
    public function getSellerIdByProduct($product_id) {
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance ();
        $seller = $objectManager->get ( 'Lof\MarketPlace\Model\SellerProduct' )->load ($product_id, 'product_id' );
        return $seller->getSellerId();
    }
    public function getSellerByCustomer() {
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance ();
        $seller = $objectManager->get ( 'Lof\MarketPlace\Model\Seller' )->load ( $this->getCustomerId(), 'customer_id' );
        return $seller->getData();
    }
    public function getCustomerBySeller($seller_id) {
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance ();
        $seller = $objectManager->get('Lof\MarketPlace\Model\Seller')->load($seller_id,'seller_id');
        $customer =  $objectManager->create('Magento\Customer\Model\Customer')->load($seller->getCustomerId());
        return $customer;
    }
    public function getStore() {
      return $this->_storeManager->getStore();
    }
    public function getCurrentStoreId()
    {
        // give the current store id
        return $this->_storeManager->getStore()->getStoreId();
    }

    public function getWebsiteId()
    {
        // give the current store id
        return $this->_storeManager->getStore(true)->getWebsite()->getId();
    }

   /**
     * Get product type restriction
     * @return \Magento\Framework\App\Config\mixed
     */
    public function getProductTypeRestriction(){
        return explode(",",$this->scopeConfig->getValue(self::XML_CATALOG_PRODUCT_TYPE_RESTRICTION));
    }

    /**
     * Get attribute set restriction
     * @return \Magento\Framework\App\Config\mixed
     */
    public function getAttributeSetRestriction(){
        return explode(",",$this->scopeConfig->getValue(self::XML_CATALOG_ATTRIBUTE_SET_RESTRICTION));
    }
    /**
     * Return seller config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'lofmarketplace/'.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }
     public function getStoreConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }
    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }
    public function isLoggedIn() {
        return $this->customerSession->isLoggedIn();
    }
     public function getCustomerName() {
        $customer = $this->customerSession->getCustomer();
        return $customer->getData('firstname').' '.$customer->getData('lastname');
    }
    public function getCustomerEmail() {
        $customer = $this->customerSession->getCustomer();
        return $customer->getData('email');
    }
    public function getCustomerId() {
        $customer = $this->customerSession->getCustomer();
        return $customer->getId();
    }
      public function nicetime($timestamp, $detailLevel = 1)
    {
        $periods = ['sec', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade'];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];

        $now = time();
        $timestamp = strtotime($timestamp);
        // check validity of date
        if (empty($timestamp)) {
            return 'Unknown time';
        }

        // is it future date or past date
        if ($now > $timestamp) {
            $difference = $now - $timestamp;
            $tense = 'ago';
        } else {
            $difference = $timestamp - $now;
            $tense = 'from now';
        }

        if ($difference == 0) {
            return '1 sec ago';
        }

        $remainders = [];

        for ($j = 0; $j < count($lengths); ++$j) {
            $remainders[$j] = floor(fmod($difference, $lengths[$j]));
            $difference = floor($difference / $lengths[$j]);
        }

        $difference = round($difference);

        $remainders[] = $difference;

        $string = '';

        for ($i = count($remainders) - 1; $i >= 0; --$i) {
            if ($remainders[$i]) {
                // on last detail level get next period and round current
                if ($detailLevel == 1 && isset($remainders[$i-1]) && $remainders[$i-1] > $lengths[$i-1]/2) {
                    $remainders[$i]++;
                }
                $string .= $remainders[$i].' '.$periods[$i];

                if ($remainders[$i] != 1) {
                    $string .= 's';
                }

                $string .= ' ';

                --$detailLevel;

                if ($detailLevel <= 0) {
                    break;
                }
            }
        }

        return $string.$tense;
    }
    public function getFormatDate($date, $type = 'full'){
        $result = '';
        switch ($type) {
            case 'full':
            $result = $this->formatDate($date, \IntlDateFormatter::FULL);
            break;
            case 'long':
            $result = $this->formatDate($date, \IntlDateFormatter::LONG);
            break;
            case 'medium':
            $result = $this->formatDate($date, \IntlDateFormatter::MEDIUM);
            break;
            case 'short':
            $result = $this->formatDate($date, \IntlDateFormatter::SHORT);
            break;
        }
        return $result;
    }
     public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }
     /**
     * @return object
     */
    public function getStatus($id)
    {
        $data = '';
        if($id == 0) {
            $data = __('Close');
        } elseif ($id == 1) {
            $data = __('Open');
        } elseif ($id == 2) {
            $data = __('Processing');
        } elseif ($id == 3) {
            $data = __('Done');
        }
        
        return $data;
    }
      public function getStatusRating($id)
    {
        $data = '';
        if ($id == 1) {
            $data = __('Approved');
        } elseif ($id == 2) {
            $data = __('Pending');
        } elseif ($id == 3) {
            $data = __('Not Approved');
        }
        
        return $data;
    }
    
    public function arrayStatusRating() {
        $data = [];
        $data[] = [];

        $data[0]['value'] = 'reject';
        $data[0]['label'] = __('Reject');

        $data[1]['value'] = 'pending';
        $data[1]['label'] = __('Pending');

        $data[2]['value'] = 'accept';
        $data[2]['label'] = __('Accept');

        return $data;
    }
     public function statusRating() {
        $data = [];

        $data['reject'] = __('Reject');
        $data['pending'] = __('Pending');
        $data['accept'] = __('Accept');
    

        return $data;
    }
    public function arrayStatus() {
        $data = [];
        $data[] = [];

        $data[0]['value'] = 0;
        $data[0]['label'] = __('Close');

        $data[1]['value'] = 1;
        $data[1]['label'] = __('Open');

        $data[2]['value'] = 2;
        $data[2]['label'] = __('Processing');

        $data[3]['value'] = 3;
        $data[3]['label'] = __('Done');

        return $data;
    }
    /**
     * 
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProduct()
    {
      $collection = $this->productCollectionFactory->create()->load();
      return $collection;
    }

    public function getProductById($product_id) {
        $collection = $this->productCollectionFactory->create()->load($product_id);
      return $collection;
    }

    public function getCustomerById($customer_id) {
        $collection = $this->customer->create()->load($customer_id);
      return $collection;
    }
    /**
     * Return Backend area front name
     *
     * @param bool $checkHost
     * @return bool|string
     */
    public function getAreaFrontName($checkHost = false)
    {
        return $this->_frontNameResolver->getFrontName($checkHost);
    }

    public function getMediaUrl()
    {
        $storeMediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $storeMediaUrl;

    }//end getMediaUrl()
      /**
     * Get current url
     */
    public function getCurrentUrls() {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
 
    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
    
    /**
     * Get Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }
    
    /**
     * Get current url for store
     *
     * @param bool|string $fromStore Include/Exclude from_store parameter from URL
     * @return string     
     */
    public function getStoreUrl($fromStore = true)
    {
        return $this->_storeManager->getStore()->getCurrentUrl($fromStore);
    }
    
    /**
     * Check if store is active
     *
     * @return boolean
     */
    public function isStoreActive()
    {
        return $this->_storeManager->getStore()->isActive();
    }
    /**
     * Get all modules that the extension will use the tempalte from adminhtml area instead of vendors area
     * @return array:
     */
    public function getModulesUseTemplateFromAdminhtml(){
        return $this->_modulesUseTemplateFromAdminhtml;
    }
     /**
     * Get all block classes that the extension will use the tempalte from adminhtml area instead of vendors area
     */
    public function getBlocksUseTemplateFromAdminhtml(){
        return $this->_blocksUseTemplateFromAdminhtml;
    }
    public function getTableKey($key)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $tablePrefix = (string) $this->_objectManager->get('Magento\Framework\App\DeploymentConfig')
            ->get(\Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX);
        $exists = $resource->getConnection('core_write')->showTableStatus($tablePrefix.'permission_variable');
        if($exists) {
            return $key;
        }else{
            return "{$key}";
        }
    }
    
}