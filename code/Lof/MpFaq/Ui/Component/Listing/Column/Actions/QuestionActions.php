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

namespace Lof\MpFaq\Ui\Component\Listing\Column\Actions;

use Lof\MpFaq\Ui\Component\Listing\Column\Actions as AbstractAction;

class QuestionActions extends AbstractAction
{
    /** Url path */
    protected $urlPathEnable = 'lofmpfaq/question/enable';
    protected $urlPathDisable = 'lofmpfaq/question/disable';
    protected $urlPathDelete = 'lofmpfaq/question/delete';
    protected $idFieldName = 'question_id';
}
