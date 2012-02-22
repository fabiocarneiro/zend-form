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
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Dojo\View\Helper;

use Zend\Dojo\View\Helper\DojoForm as DojoFormHelper,
    Zend\Dojo\View\Helper\Dojo as DojoHelper,
    Zend\Registry,
    Zend\View;

/**
 * Test class for Zend_Dojo_View_Helper_Form.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_View
 */
class DojoFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Registry::_unsetInstance();
        DojoHelper::setUseDeclarative();

        $this->view   = $this->getView();
        $this->helper = new DojoFormHelper();
        $this->helper->setView($this->view);
    }

    public function getView()
    {
        $view = new View\Renderer\PhpRenderer();
        \Zend\Dojo\Dojo::enableView($view);
        return $view;
    }

    public function getForm()
    {
        return $this->helper->__invoke('myForm', array('action' => '/foo'), '');
    }

    public function testShouldAllowDeclarativeDijitCreation()
    {
        $html = $this->getForm();
        $this->assertRegexp('/<form[^>]*(dojoType="dijit.form.Form")/', $html, $html);
    }

    public function testShouldAllowProgrammaticDijitCreation()
    {
        DojoHelper::setUseProgrammatic();
        $html = $this->getForm();
        $this->assertNotRegexp('/<form[^>]*(dojoType="dijit.form.Form")/', $html);
        $this->assertNotNull($this->view->plugin('dojo')->getDijit('myForm'));
    }

    public function testOnlyIdShouldBeNecessary()
    {
        DojoHelper::setUseDeclarative();
        $html = $this->view->plugin('dojoform')->__invoke('foo');
        $this->assertRegexp('/<form[^>]*(dojoType="dijit.form.Form")/', $html, $html);
        $this->assertRegexp('/<form[^>]*(id="foo")/', $html, $html);
    }

    public function testShouldNotRenderIdAsHtmlIdWhenIdPassedAsAttrib()
    {
        $html = $this->helper->__invoke('foo', array('id' => 'bar'));
        $this->assertRegexp('/<form[^>]*(id="bar")/', $html);
    }
    
    public function testShouldNotRenderClosingTagIfContentIsFalse()
    {
        $html = $this->helper->__invoke('foo');
        $this->assertNotRegexp('/<\/form>/', $html);
    }

    public function testShouldNotUseDojoIfRegularZendFormIsUsed()
    {
        $html = $this->view->plugin('form')->__invoke('foo');
        $this->assertNotRegexp('/<form[^>]*(dojoType="dijit.form.Form")/', $html);
    }
}
