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
namespace Lof\CouponCode\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'lof_coupon_code_log'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_coupon_code_log')
        )->addColumn(
            'log_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'log id'
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Rule Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true],
            'Order id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Customer id'
        )
        ->addColumn(
            'full_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Customer full name'
        )->addColumn(
            'generated_link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'generated qrcode link'
        )->addColumn(
            'email_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Email address '
        )->addColumn(
            'coupon_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Coupon code'
        )->addColumn(
            'discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            [12, 4],
            ['nullable' => true, 'default' => '0.0000'],
            'Discount Amount'
        )->addColumn(
            'coupon_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => '1'],
            'Coupon Type'
        )->addColumn(
            'ip_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'ip address'
        )->addColumn(
            'client_info',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Client info'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Created time'
        )->addIndex(
                $setup->getIdxName('lof_coupon_code_log', ['log_id']),
                ['log_id']
                );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}