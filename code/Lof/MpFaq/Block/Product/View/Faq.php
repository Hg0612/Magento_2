<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MpFaq
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\MpFaq\Block\Product\View;

class Faq extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Lof\Faq\Helper\Data
     */
    protected $_configHelper;

    protected $_marketplaceHelper;

    public $_sellerSettings;

    /**
     * @var \Lof\Faq\Model\Question
     */
    protected $_questionFactory;

    /**
     * @var \Lof\Faq\Model\Category
     */
    protected $_categoryFactory;

    /**
     * @var \Lof\Faq\Model\ResourceModel\Question\Collection
     */
    public $_collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Lof\Faq\Model\Question
     * @param \Lof\Faq\Model\Category
     * @param \Magento\Framework\App\ResourceConnection
     * @param \Lof\Faq\Helper\Data
     * @param array
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MpFaq\Model\Question $questionFactory,
        \Lof\MpFaq\Model\Category $categoryFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Lof\MpFaq\Helper\Data $configHelper,
        array $data = []
    ) {
        $this->_configHelper      = $configHelper;
        $this->_coreRegistry      = $registry;
        $this->_questionFactory   = $questionFactory;
        $this->_categoryFactory   = $categoryFactory;
        $this->_resource          = $resource;
        parent::__construct($context);
    }

    public function getConfig($key)
    {
        $result = $this->_configHelper->getConfig($key);
        return $result;
    }

    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @param Array
     * @return $this
    */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * @return Array
     */
    public function getCollection(){
        return $this->_collection;
    }

    public function getProduct(){
        return $this->_coreRegistry->registry('current_product');
    }

    public function getCategories(){
        $currentProduct = $this->getProduct();
        $sellerId = $currentProduct->getSellerId();
        $storeId = $this->_storeManager->getStore()->getId();
        $categoryCollection = $this->_categoryFactory->getCollection()
            ->addFieldToFilter('status',1)
            ->addFieldToFilter('main_table.seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('store_id', ['eq' => $storeId]);

        return $categoryCollection;
    }

    public function _toHtml(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminEnable = $this->getConfig('general_settings/enable');

        $currentProduct = $this->getProduct();
        $sellerId = $currentProduct->getSellerId();

        $sellerEnable = $objectManager->create('Lof\MpFaq\Model\EnableSeller')
                             ->load($sellerId)->getSellerId();
        $productEnable = $objectManager->create('Lof\MpFaq\Model\EnableProduct')
                             ->load($currentProduct->getId())->getId();

        if($adminEnable && $sellerEnable && $productEnable){
            return parent::_toHtml();
        }

        return;
    }

    protected function _beforeToHtml()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $currentProduct = $this->getProduct();
        $sellerId = $currentProduct->getSellerId();
        $this->_sellerSettings = $this->_configHelper->getSellerSettings($sellerId);

        $questionCollection = $this->_questionFactory->getCollection()
                                ->addFieldToFilter('main_table.status',1)
                                ->addFieldToFilter('main_table.seller_id', ['eq' => $sellerId])
                                ->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);

        $questionCollection->getSelect()
                            ->join(
                                ['question_product' => $this->_resource->getTableName('lof_mpfaq_question_product')],
                                'question_product.question_id = main_table.question_id'
                            )
                            ->where('question_product.product_id = (?)', $currentProduct->getId())
                            ->order('position ASC');

        $categoryIds = [];
        foreach($questionCollection as $question){
            $categoryIds[] = $question->getCategoryId();
        }

        $categoryCollection = $this->_categoryFactory->getCollection()
                                   ->addFieldToFilter('status',1)
                                   ->addFieldToFilter('store_id', ['eq' => $storeId])
                                   ->addFieldToFilter('category_id', ['in' => $categoryIds]);

        $categoryCollection->getSelect()
                           ->group('category_id')
                           ->order('position ASC');

        $data = [];
        foreach($categoryCollection as $category){
            $categoryId = $category->getId();
            $questions = [];
            foreach ($questionCollection as $id => $question){
                if($question->getCategoryId() == $categoryId){
                    $questions[] = $question;
                }
            }
            $data[] = ['category' => $category, 'questions' => $questions];
        }

        $this->setCollection($data);
        return parent::_beforeToHtml();
    }
}