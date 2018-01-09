<?php

namespace Lof\MpFaq\Model\ResourceModel\Setting;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\MpFaq\Model\Setting', 'Lof\MpFaq\Model\ResourceModel\Setting');
    }

}