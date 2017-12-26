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
namespace Ves\Blog\Model\ResourceModel\Post;

use \Ves\Blog\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'post_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'post_collection';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('ves_blog_post_store', 'post_id');
        $this->getCategoriesAfterLoad();
        $this->getCommentsAfterLoad();
        $this->getTagsAfterLoad();
        $this->getProductsAfterLoad();
        return parent::_afterLoad();
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function getCategoriesAfterLoad()
    {
        $items = $this->getColumnValues("post_id");
        if (count($items)) {
            $connection = $this->getConnection();
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_post_category');
            $categories = $connection->fetchAll($select);
            foreach ($this as $item) {
                $tmp = [];
                foreach ($categories as $k => $v) {
                    if($v['post_id'] == $item->getData("post_id")){
                        $select = 'SELECT * FROM ' . $this->getTable('ves_blog_category') . ' WHERE category_id = ' . $v['category_id'];
                        $select = $connection->select()->from(['ves_blog_category' => $this->getTable('ves_blog_category')])
                        ->where('ves_blog_category.category_id = ' . $v['category_id'])
                        ->order('ves_blog_category.cat_position DESC');
                        $category = $connection->fetchRow($select);

                        $tmp[] = $category;
                        unset($categories[$k]);
                    }
                }
                $item->setData('categories', $tmp);
            }
        }
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function getProductsAfterLoad()
    {
        $items = $this->getColumnValues("post_id");
        if (count($items)) {
            $connection = $this->getConnection();
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_post_products_related');
            $products = $connection->fetchAll($select);
            foreach ($this as $item) {
                $select = 'SELECT entity_id FROM ' . $this->getTable('ves_blog_post_products_related') . ' WHERE post_id = ' .  $item->getData("post_id") . ' ORDER BY position DESC ';
                $products = $connection->fetchCol($select);
                $item->setData('products', $products);
            }
        }
    }

    protected function getCommentsAfterLoad()
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_comment') . ' WHERE post_id = ' . $item->getData("post_id").' AND is_active=1';
            $count = count($connection->fetchAll($select));
            $item->setData('comment_count', $count);
        }
    }

    protected function getTagsAfterLoad()
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_post_tag') . ' WHERE post_id = ' . $item->getData("post_id");
            $tags = $connection->fetchAll($select);
            $item->setData('tag', $tags);
        }
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ves\Blog\Model\Post', 'Ves\Blog\Model\ResourceModel\Post');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Returns pairs post_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('post_id', 'title');
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $store_id = "";
        if(is_numeric($store)) {
            $store_id = (int)$store;
        } elseif(is_array($store)) {
            $store_id = implode(",",$store);
        } else {
            $store_id = $store->getId();
        }
        $columnName = 'post_id';
        $this->getSelect()->join(
            ['store_table' => $this->getTable('ves_blog_post_store')],
            'main_table.post_id = store_table.post_id',
            []
        )->where('store_table.store_id in (' . $store_id . ', 0) ')
        ->group(
            'main_table.post_id'
        );
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('ves_blog_post_store', 'post_id');
    }

    /**
     * Add link attribute to filter.
     *
     * @param string $code
     * @param array $condition
     * @return $this
     */
    public function addLinkAttributeToFilter($code, $condition)
    {
        if($code=='position'){
            $connection = $this->getConnection();
            $where = '';
            if(isset($condition['from'])){
                $where .= 'position >= ' . $condition['from'] . ' AND ';
            }
            if(isset($condition['to'])){
                $where .= ' position <= ' . $condition['to'] . ' AND ';
            }
            if($where!=''){
                $where .= ' post_id = ' . $condition['post_id'];
            }
            $select = 'SELECT post_related_id FROM ' . $this->getTable('ves_blog_post_related') . ' WHERE ' . $where;
            $postIds = $connection->fetchCol($select);
            $this->getSelect()->where('post_id IN (?)', $postIds);
        }
        return $this;
    }

    /**
     * Add link attribute to filter.
     *
     * @param string $code
     * @param array $condition
     * @return $this
     */
    public function addLinkCategoryToFilter($code, $condition)
    {
        if($code=='position'){
            $connection = $this->getConnection();
            $where = '';
            if(isset($condition['from'])){
                $where .= 'position >= ' . $condition['from'] . ' AND ';
            }
            if(isset($condition['to'])){
                $where .= ' position <= ' . $condition['to'] . ' AND ';
            }
            if($where!=''){
                $where .= ' category_id = ' . $condition['category_id'];
            }
            $select = 'SELECT post_id FROM ' . $this->getTable('ves_blog_post_category') . ' WHERE ' . $where;
            $postIds = $connection->fetchCol($select);
            $this->getSelect()->where('post_id IN (?)', $postIds);
        }
        return $this;
    }
}
