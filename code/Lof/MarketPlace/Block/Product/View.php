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
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\MarketPlace\Block\Product;
use Magento\Customer\Model\Context as CustomerContext;

class View extends \Magento\Catalog\Block\Product\View
{
   
     /**
     * Group Collection
     */
    protected $_sellerCollection;
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_sellerHelper;

    public $_resource;

    protected $vacation;
    /**
     * @var \Lof\MarketPlace\Model\Orderitems
     */
    protected $orderitems;
    /**
     * @param \Magento\Catalog\Block\Product\Context              $context             
     * @param \Magento\Framework\Url\EncoderInterface             $urlEncoder          
     * @param \Magento\Framework\Json\EncoderInterface            $jsonEncoder         
     * @param \Magento\Framework\Stdlib\StringUtils               $string              
     * @param \Magento\Catalog\Helper\Product                     $productHelper       
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig   
     * @param \Magento\Framework\Locale\FormatInterface           $localeFormat        
     * @param \Magento\Customer\Model\Session                     $customerSession     
     * @param \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository   
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface   $priceCurrency       
     * @param \Lof\HidePrice\Helper\Balance\Spend              $rewardsBalanceSpend 
     * @param \Lof\HidePrice\Helper\Data                       $rewardsData         
     * @param array                                               $data                
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Lof\MarketPlace\Model\Vacation $vacation,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Lof\MarketPlace\Model\Seller $sellerCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        \Lof\MarketPlace\Model\Orderitems $orderitems,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        array $data = []
        ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency
        );
        $this->_sellerCollection = $sellerCollection;
        $this->_sellerHelper = $sellerHelper;
        $this->vacation  = $vacation;
        $this->_resource = $resource;
        $this->orderitems     = $orderitems;
        $this->productCollection = $productCollection;
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function collection() {
         $productCollection = $this->productCollection;
        $collection = $productCollection->create()
            ->addAttributeToSelect('*')
            ->load();
        $collection->getSelect()->join(

                ['lof_marketplace_seller'],
               'lof_marketplace_seller.seller_id = e.seller_id',
                []


        )->columns(['seller_name'=> 'lof_marketplace_seller.name']);
        $sellerName = array();
        foreach ($collection->getData() as $value) {
            $sellerName[] = $value['seller_name'];
            
        }
    }
    public function getSellerCollection(){
        $sellerIds = $this->getSellerId();
        if($sellerIds || count($sellerIds) > 0) {
            $collection = $this->_sellerCollection->getCollection()
                ->setOrder('position','ASC')
                ->addFieldToFilter('status',1);
            $collection->getSelect()->where('seller_id IN (?)', $sellerIds);
            return $collection;
        }
        return false;
    }

    public function getSellerId() {
        $product = $this->getProduct();
        $connection = $this->_resource->getConnection();
        $table_name = $this->_resource->getTableName('lof_marketplace_product');
        $sellerIds = $connection->fetchCol(" SELECT seller_id FROM ".$table_name." WHERE product_id = ".$product->getId());

        return $sellerIds;
    }

    public function getVacation() {
        $vacation = $this->vacation->getCollection()->addFieldToFilter('seller_id',$this->getSellerId())->addFieldToFilter('status',1);
        return $vacation;
    }
    public function checkVacation() {
        $today = (new \DateTime())->format('Y-m-d');
        $vacation = $this->getVacation()->addFieldToFilter('from_date',array('lteq' => $today))->addFieldToFilter('to_date',array('gt' => $today));
        return  $vacation;   
    }

    public function _toHtml(){
        if(!$this->_sellerHelper->getConfig('product_view_page/enable_seller_info')) return;

        return parent::_toHtml();
    }
    /**
     *  get Total Sales
     *
     * @return Credit Total Sales
     */
    public function getTotalSales() {
        $total = 0;
        $orderitems = $this->orderitems->getCollection()->addFieldToFilter('seller_id',$this->getSellerId())->addFieldToFilter('status','complete');
        foreach ($orderitems as $key => $_orderitems) {
            $total = $total + $_orderitems->getProductQty();
        }
        return $total;
    }

}