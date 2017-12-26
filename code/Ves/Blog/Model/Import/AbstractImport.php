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
namespace Ves\Blog\Model\Import;

/**
 * Abstract import model
 */
abstract class AbstractImport extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Connect to bd
     */
    protected $_connect;

    /**
     * @var \Ves\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * @var \Ves\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Ves\Blog\Model\PostFactory $postFactory,
     * @param \Ves\Blog\Model\CategoryFactory $categoryFactory,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Model\PostFactory $postFactory,
        \Ves\Blog\Model\CategoryFactory $categoryFactory,
        \Ves\Blog\Model\CommentFactory $commentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Ves\Blog\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->_postFactory     = $postFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_commentFactory  = $commentFactory;
        $this->_storeManager    = $storeManager;
        $this->messageManager   = $messageManager;
        $this->_objectManager   = $objectManager;
        $this->authSession      = $authSession;
        $this->_helper          = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _connect()
    {
        $con = '';
        try{
            $dbname = $this->getData('dbname');
            $uname  = $this->getData('uname');
            $pwd    = $this->getData('pwd');
            $dbhost = $this->getData('dbhost');
            if($dbname=='' || $uname=='' || $pwd=='' || $dbhost==''){
                throw new \Exception(__("Some fields are required"));
            }
            $con = mysqli_connect($dbhost, $uname, $pwd, $dbname);
            if (mysqli_connect_errno())
            {
                throw new \Exception(__("Failed to connect to MySQL: %1", mysqli_connect_error()));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_connect = $con;
        return $con;
    }

    /**
     * Execute mysql query
     */
    protected function _mysqliQuery($sql)
    {
        $result = mysqli_query($this->_connect, $sql);
        if (!$result) {
            $this->messageManager->addError(__('Mysql error: %1.', mysqli_error($this->_connect)));
        }
        return $result;
    }
}
