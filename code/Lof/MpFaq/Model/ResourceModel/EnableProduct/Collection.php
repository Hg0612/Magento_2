<?php

namespace Lof\MpFaq\Model\ResourceModel\EnableProduct;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'product_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\MpFaq\Model\EnableProduct', 'Lof\MpFaq\Model\ResourceModel\EnableProduct');
    }

}