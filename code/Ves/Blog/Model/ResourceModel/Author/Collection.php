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
namespace Ves\Blog\Model\ResourceModel\Author;

use \Ves\Blog\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'author_id';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        return parent::_afterLoad();
    }

    protected function _construct()
    {
        $this->_init('Ves\Blog\Model\Author', 'Ves\Blog\Model\ResourceModel\Author');
    }

    /**
     * Returns pairs comment_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('author_id', 'user_name');
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
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        return;
    }

}
