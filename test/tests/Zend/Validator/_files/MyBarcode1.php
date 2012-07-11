<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\Barcode;

use Zend\Validator\Barcode\AbstractAdapter;

class MyBarcode1 extends AbstractAdapter
{
    public function __construct()
    {
        $this->setLength(-1);
        $this->setCharacters(0);
        $this->setChecksum('invalid');
    }
}
