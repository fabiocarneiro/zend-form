<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage UnitTest
 */

namespace ZendTest\Form\TestAsset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class FieldsetWithInputFilter extends Fieldset implements InputFilterProviderInterface
{
    public function getInputFilterSpecification()
    {
        return array(
            'foo' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    array('name' => 'Zend\Validator\NotEmpty'),
                    array('name' => 'Zend\I18n\Validator\Alnum'),
                ),
            ),
            'bar' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
            ),
        );
    }
}
