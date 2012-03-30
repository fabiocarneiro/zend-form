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
 * @package    Zend_InputFilter
 * @subpackage UnitTest
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;
use Zend\Validator;

class InputFilterTest extends TestCase
{
    public function testInputFilterIsEmptyByDefault()
    {
        $filter = new InputFilter();
        $this->assertEquals(0, count($filter));
    }

    public function testAddingInputsIncreasesCountOfFilter()
    {
        $filter = new InputFilter();
        $foo    = new Input('foo');
        $filter->add($foo);
        $this->assertEquals(1, count($filter));
        $bar    = new Input('bar');
        $filter->add($bar);
        $this->assertEquals(2, count($filter));
    }

    public function testAddingInputWithNameInjectsNameInInput()
    {
        $filter = new InputFilter();
        $foo    = new Input('foo');
        $filter->add($foo, 'bar');
        $test   = $filter->get('bar');
        $this->assertSame($foo, $test);
        $this->assertEquals('bar', $foo->getName());
    }

    public function testCanAddInputFilterAsInput()
    {
        $parent = new InputFilter();
        $child  = new InputFilter();
        $parent->add($child, 'child');
        $this->assertEquals(1, count($parent));
        $this->assertSame($child, $parent->get('child'));
    }

    public function getInputFilter()
    {
        $filter = new InputFilter();

        $foo = new Input();
        $foo->getFilterChain()->attachByName('stringtrim')
                              ->attachByName('alpha');
        $foo->getValidatorChain()->addValidator(new Validator\StringLength(3, 6));

        $bar = new Input();
        $bar->getFilterChain()->attachByName('stringtrim');
        $bar->getValidatorChain()->addValidator(new Validator\Digits());

        $filter->add($foo, 'foo')
               ->add($bar, 'bar')
               ->add($this->getChildInputFilter(), 'nest');

        return $filter;
    }

    public function getChildInputFilter()
    {
        $filter = new InputFilter();

        $foo = new Input();
        $foo->getFilterChain()->attachByName('stringtrim')
                              ->attachByName('alpha');
        $foo->getValidatorChain()->addValidator(new Validator\StringLength(3, 6));

        $bar = new Input();
        $bar->getFilterChain()->attachByName('stringtrim');
        $bar->getValidatorChain()->addValidator(new Validator\Digits());

        $filter->add($foo, 'foo')
               ->add($bar, 'bar');
        return $filter;
    }

    public function testCanValidateEntireDataset()
    {
        $filter = $this->getInputFilter();
        $validData = array(
            'foo' => ' bazbat ',
            'bar' => '12345',
            'nest' => array(
                'foo' => ' bazbat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($validData);
        $this->assertTrue($filter->isValid());

        $invalidData = array(
            'foo' => ' baz bat ',
            'bar' => 'abc45',
            'nest' => array(
                'foo' => ' baz bat ',
                'bar' => '123ab',
            ),
        );
        $filter->setData($invalidData);
        $this->assertFalse($filter->isValid());
    }

    public function testCanValidatePartialDataset()
    {
        $filter = $this->getInputFilter();
        $validData = array(
            'foo' => ' bazbat ',
            'bar' => '12345',
        );
        $filter->setValidationGroup('foo', 'bar');
        $filter->setData($validData);
        $this->assertTrue($filter->isValid());

        $invalidData = array(
            'bar' => 'abc45',
            'nest' => array(
                'foo' => ' 123bat ',
                'bar' => '123ab',
            ),
        );
        $filter->setValidationGroup('bar', 'nest');
        $filter->setData($invalidData);
        $this->assertFalse($filter->isValid());
    }

    public function testCanRetrieveInvalidInputsOnFailedValidation()
    {
        $filter = $this->getInputFilter();
        $invalidData = array(
            'foo' => ' bazbat ',
            'bar' => 'abc45',
            'nest' => array(
                'foo' => ' baz bat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($invalidData);
        $this->assertFalse($filter->isValid());
        $invalidInputs = $filter->getInvalidInput();
        $this->assertArrayNotHasKey('foo', $invalidInputs);
        $this->assertArrayHasKey('bar', $invalidInputs);
        $this->assertInstanceOf('Zend\InputFilter\Input', $invalidInputs['bar']);
        $this->assertArrayHasKey('nest', $invalidInputs);
        $this->assertInternalType('array', $invalidInputs['nest']);
        $nestInvalids = $invalidInputs['nest'];
        $this->assertArrayHasKey('foo', $nestInvalids);
        $this->assertInstanceOf('Zend\InputFilter\Input', $nestInvalids['foo']);
        $this->assertArrayNotHasKey('bar', $invalidInputs);
    }

    public function testCanRetrieveValidInputsOnFailedValidation()
    {
        $filter = $this->getInputFilter();
        $invalidData = array(
            'foo' => ' bazbat ',
            'bar' => 'abc45',
            'nest' => array(
                'foo' => ' baz bat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($invalidData);
        $this->assertFalse($filter->isValid());
        $validInputs = $filter->getValidInput();
        $this->assertArrayHasKey('foo', $invalidInputs);
        $this->assertInstanceOf('Zend\InputFilter\Input', $invalidInputs['foo']);
        $this->assertArrayNotHasKey('bar', $invalidInputs);
        $this->assertArrayHasKey('nest', $invalidInputs);
        $this->assertInternalType('array', $invalidInputs['nest']);
        $nestValids = $validInputs['nest'];
        $this->assertArrayNotHasKey('foo', $nestInvalids);
        $this->assertArrayHasKey('bar', $invalidInputs);
        $this->assertInstanceOf('Zend\InputFilter\Input', $nestInvalids['bar']);
    }

    public function testValuesRetrievedAreFiltered()
    {
        $filter = $this->getInputFilter();
        $validData = array(
            'foo' => ' bazbat ',
            'bar' => '12345',
            'nest' => array(
                'foo' => ' bazbat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($validData);
        $this->assertTrue($filter->isValid());
        $expected = array(
            'foo' => 'bazbat',
            'bar' => '12345',
            'nest' => array(
                'foo' => 'bazbat',
                'bar' => '12345',
            ),
        );
        $this->assertEquals($expected, $filter->getValues());
    }

    public function testCanGetRawInputValues()
    {
        $filter = $this->getInputFilter();
        $validData = array(
            'foo' => ' bazbat ',
            'bar' => '12345',
            'nest' => array(
                'foo' => ' bazbat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($validData);
        $this->assertTrue($filter->isValid());
        $this->assertEquals($validData, $filter->getRawValues());
    }

    public function testCanGetValidationMessages()
    {
        $filter = $this->getInputFilter();
        $invalidData = array(
            'foo' => ' bazbat ',
            'bar' => 'abc45',
            'nest' => array(
                'foo' => ' baz bat ',
                'bar' => '12345',
            ),
        );
        $filter->setData($invalidData);
        $this->assertFalse($filter->isValid());
        $messages = $filter->getMessages();
        foreach ($invalidData as $key => $value) {
            $this->assertArrayHasKey($key, $messages);
            $currentMessages = $messages[$key];
            switch ($key) {
                case 'foo':
                    $this->assertArrayHasKey(Validator\StringLength::TOO_LONG, $currentMessages);
                    break;
                case 'bar':
                    $this->assertArrayHasKey(Validator\Digits::NOT_DIGITS, $currentMessages);
                    break;
                case 'nest':
                    foreach ($value as $k => $v) {
                        $this->assertArrayHasKey($k, $messages[$key]);
                        $currentMessages = $messages[$key][$k];
                        switch ($k) {
                            case 'foo':
                                $this->assertArrayHasKey(Validator\StringLength::TOO_LONG, $currentMessages);
                                break;
                            case 'bar':
                                $this->assertArrayHasKey(Validator\Digits::NOT_DIGITS, $currentMessages);
                                break;
                            default:
                                $this->fail(sprintf('Invalid key "%s" encountered in messages array', $k));
                        }
                    }
                    break;
                default:
                    $this->fail(sprintf('Invalid key "%s" encountered in messages array', $k));
            }
        }
    }

    /**
     * Idea for this one is that one input may only need to be validated if another input is present.
     */
    public function testCanConditionallyInvokeValidators()
    {
        $this->markTestIncomplete();
    }

    /**
     * Idea for this one is that validation may need to rely on context -- e.g., a "password confirmation" 
     * field may need to know what the original password entered was in order to compare.
     */
    public function testValidationCanUseContext()
    {
        $this->markTestIncomplete();
    }

    public function testValidationSkipsFieldsMarkedNotRequiredWhenNoDataPresent()
    {
        $this->markTestIncomplete();
    }

    public function testValidationAllowsEmptyValuesToRequiredInputWhenAllowEmptyFlagIsTrue()
    {
        $this->markTestIncomplete();
    }

    public function testValidationMarksInputInvalidWhenRequiredAndAllowEmptyFlagIsFalse()
    {
        $this->markTestIncomplete();
    }
}
