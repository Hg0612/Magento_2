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

namespace Lof\MpFaq\Model\ResourceModel;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_mpfaq_category', 'category_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Lof\Faq\Model\Question $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID];

            $select->join(
                ['cbs' => $this->getTable('lof_faq_category_store')],
                $this->getMainTable() . '.category_id = cbs.category_id',
                ['store_id']
            )->where(
                'is_active = ?',
                1
            )->where(
                'cbs.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsUniqueBlockToStores(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->_storeManager->hasSingleStore()) {
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->getConnection()->select()->from(
            ['cb' => $this->getMainTable()]
        )->join(
            ['cbs' => $this->getTable('lof_faq_category_store')],
            'cb.category_id = cbs.category_id',
            []
        )->where(
            'cb.identifier = ?',
            $object->getData('identifier')
        )->where(
            'cbs.store_id IN (?)',
            $stores
        );

        if ($object->getId()) {
            $select->where('cb.category_id <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('lof_faq_category_store'),
            'store_id'
        )->where(
            'category_id = :category_id'
        );

        $binds = [':category_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * Perform operations before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $title = $object->getData('title');
        $category_id = $object->getData('category_id');

        if (strtoupper(trim($title)) == 'NONE') {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Category title is invalid')
            );
        }

        $select = $this->getConnection()
                    ->select()
                    ->from('lof_mpfaq_category')
                    ->where('title LIKE ?', $title)
                    ->where('category_id <> ?', $category_id);

        if ($this->getConnection()->fetchRow($select)) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __('A category with the same title already exists')
            );
        }

        return $this;
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['parent_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('lof_mpfaq_category'), $condition);

        $condition = ['category_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('lof_mpfaq_question'), $condition);

        return parent::_beforeDelete($object);
    }
}
