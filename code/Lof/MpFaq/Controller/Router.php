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

namespace Lof\MpFaq\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var \Lof\Faq\Model\Tag
     */
    protected $_tag;

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * Question Factory
     *
     * @var \Lof\Faq\Model\Question $questionFactory
     */
    protected $_questionFactory;

    /**
     * Category Factory
     *
     * @var \Lof\Faq\Model\Category $categoryFactory
     */
    protected $_categoryFactory;

    protected $_faqHelper;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param ActionFactory
     * @param ResponseInterface
     * @param ManagerInterface
     * @param \Lof\Faq\Model\Question
     * @param \Lof\Faq\Model\Category
     * @param \Lof\Faq\Helper\Data
     * @param StoreManagerInterface
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        \Lof\Faq\Model\Question $questionFactory,
        \Lof\Faq\Model\Category $categoryFactory,
        \Lof\Faq\Helper\Data $faqHelper,  
        \Lof\Faq\Model\Tag $tag,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry
        )
    {

        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->response = $response;
        $this->_faqHelper = $faqHelper;
        $this->_questionFactory = $questionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_tag = $tag;
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $registry;
    }
    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        $_faqHelper = $this->_faqHelper;
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'lof_faq_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
                );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                    );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $enable = $_faqHelper->getConfig('general_settings/enable');
            $route = $_faqHelper->getConfig('general_settings/route');
            $url = '';
            if($enable){
                if( $route !='' && $urlKey == $route ){
                    $request->setModuleName('loffaq')
                    ->setControllerName('index')
                    ->setActionName('index');
                    $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
                $store = $this->storeManager->getStore();
                $identifiers = explode('/',$urlKey);
                if($route==''){
                    $url = $identifiers[0];
                }elseif(count($identifiers)==2){
                    $url = $identifiers[1];
                }
                $cat = $this->_categoryFactory->getCollection()
                ->addFieldToFilter('is_active', array('eq' => 1))
                ->addFieldToFilter('identifier', array('eq' => $url))
                ->addStoreFilter($store)->getFirstItem();

                if($cat && $cat->getId()){
                    $this->_coreRegistry->register("current_faq_category", $cat);
                    $request->setModuleName('loffaq')
                    ->setControllerName('category')
                    ->setActionName('view')
                    ->setParam('category_id', $cat->getId());
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }


                $identifiers = explode('/',$urlKey);
                if( ($route == '' && count($identifiers) == 3) || ($route != '' && count($identifiers) == 4) && $route == $identifiers[0]) {
                    $questionId = null;
                    $questionKey = '';
                    if(count($identifiers) == 3){
                        $questionId = $identifiers[2];
                        $questionKey = $identifiers[0];
                    }
                    if(count($identifiers) == 4){
                        $questionId = $identifiers[3];
                        $questionKey = $identifiers[1];
                    }
                    if($questionKey!='' && $questionKey=='question'){
                        $question = $this->_questionFactory->getCollection()
                        ->addFieldToFilter('is_active',1)
                        ->addFieldToFilter('main_table.question_id',$questionId)
                        ->addStoreFilter($store)
                        ->getFirstItem();
                        if($question->getId()){
                            $this->_coreRegistry->register("current_faq_question", $question);
                            $request->setModuleName('loffaq')
                            ->setControllerName('question')
                            ->setActionName('view')
                            ->setParam('question_id', $question->getId());
                            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                            $request->setDispatched(true);
                            $this->dispatched = true;
                            return $this->actionFactory->create(
                                'Magento\Framework\App\Action\Forward',
                                ['request' => $request]
                                );
                        }
                    }
                }

                // add router tag  
                $identifiers = explode('/',$urlKey);
                if( (count($identifiers) == 3) && $route == $identifiers[0] && $identifiers[1] == 'tag') {
                    $alias =  $identifiers[2];   
                    $tagCollection = $this->_tag->getCollection()
                    ->addFieldToFilter("alias", $alias);
                    if(!empty($tagCollection)){ 
                        $tag = $tagCollection->getFirstItem(); 
                        $questionIds = [];
                        foreach ($tagCollection as $k => $v) {
                            $questionIds[] = $v['question_id'];
                        }
                        $this->_coreRegistry->register("questionIds", $questionIds);
                        $this->_coreRegistry->register("tag_key", $alias);
                        $this->_coreRegistry->register("tag_name", $tag->getName());
                        $request->setModuleName('loffaq')
                        ->setControllerName('tag')
                        ->setActionName('view')
                        ->setParam('tag_key', $alias)
                        ->setParam('tag_name', $tag->getName())
                        ->setParam('is_tag', (!empty($questionIds)?true:false) );
                        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                        $request->setDispatched(true);
                        $this->dispatched = true;
                        return $this->actionFactory->create(
                            'Magento\Framework\App\Action\Forward',
                            ['request' => $request]
                            );
                    }
                }



            }
        }
    }
}