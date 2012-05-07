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
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\View\Helper;

/**
 * Test class for Zend_View_Helper_FormLabel.
 * Generated by PHPUnit_Util_Skeleton on 2007-05-16 at 16:09:28.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class FormLabelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->view = new \Zend\View\Renderer\PhpRenderer();
        $this->helper = new \Zend\View\Helper\FormLabel();
        $this->helper->setView($this->view);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    public function testFormLabelWithSaneInput()
    {
        $label = $this->helper->__invoke('foo', 'bar');
        $this->assertEquals('<label for="foo">bar</label>', $label);
    }

    public function testFormLabelWithInputNeedingEscapesUsesViewEscaping()
    {
        $label = $this->helper->__invoke('<&foo', '</bar>');
        $expected = '<label for="' . $this->view->escape('<&foo') . '">' . $this->view->escape('</bar>') . '</label>';
        $this->assertEquals($expected, $label);
    }

    public function testViewIsSetAndSameAsCallingViewObject()
    {
        $view = $this->helper->getView();
        $this->assertTrue($view instanceof \Zend\View\Renderer\RendererInterface);
        $this->assertSame($this->view, $view);
    }

    public function testAttribsAreSet()
    {
        $label = $this->helper->__invoke('foo', 'bar', array('class' => 'baz'));
        $this->assertEquals('<label for="foo" class="baz">bar</label>', $label);
    }

    public function testNameAndIdForZF2154()
    {
        $label = $this->helper->__invoke('name', 'value', array('id' => 'id'));
        $this->assertEquals('<label for="id">value</label>', $label);
    }

    /**
     * @group ZF-2473
     */
    public function testCanDisableEscapingLabelValue()
    {
        $label = $this->helper->__invoke('foo', '<b>Label This!</b>', array('escape' => false));
        $this->assertContains('<b>Label This!</b>', $label);
        $label = $this->helper->__invoke(array('name' => 'foo', 'value' => '<b>Label This!</b>', 'escape' => false));
        $this->assertContains('<b>Label This!</b>', $label);
        $label = $this->helper->__invoke(array('name' => 'foo', 'value' => '<b>Label This!</b>', 'attribs' => array('escape' => false)));
        $this->assertContains('<b>Label This!</b>', $label);
    }

    /**
     * @group ZF-6426
     */
    public function testHelperShouldAllowSuppressionOfForAttribute()
    {
        $label = $this->helper->__invoke('foo', 'bar', array('disableFor' => true));
        $this->assertNotContains('for="foo"', $label);
    }

    /**
     * @group ZF-8265
     */
    public function testShouldNotRenderDisableForAttributeIfForIsSuppressed()
    {
        $label = $this->helper->__invoke('foo', 'bar', array('disableFor' => true));
        $this->assertNotContains('disableFor=', $label, 'Output contains disableFor attribute!');
    }
}

