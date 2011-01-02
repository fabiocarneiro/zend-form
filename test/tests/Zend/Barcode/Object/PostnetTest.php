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
 * @package    Zend_Barcode
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Barcode\Object;
use Zend\Barcode;

/**
 * @category   Zend
 * @package    Zend_Barcode
 * @subpackage UnitTests
 * @group      Zend_Barcode
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class PostnetTest extends TestCommon
{
    protected function _getBarcodeObject($options = null)
    {
        return new Barcode\Object\Postnet($options);
    }

    public function testType()
    {
        $this->assertSame('postnet', $this->_object->getType());
    }

    public function testChecksum()
    {
        $this->assertSame(5, $this->_object->getChecksum('012345'));
        $this->assertSame(0, $this->_object->getChecksum('01234'));
    }

    public function testSetText()
    {
        $this->_object->setText('012345');
        $this->assertSame('012345', $this->_object->getRawText());
        $this->assertSame('0123455', $this->_object->getText());
        $this->assertSame('0123455', $this->_object->getTextToDisplay());
    }

    public function testSetTextWithoutGoodNumberOfCharacters()
    {
        $this->setExpectedException('\Zend\Barcode\Object\Exception');
        $this->_object->setText('1234');
        $this->_object->getText();
    }

    public function testSetTextWithoutChecksumHasNoEffect()
    {
        $this->_object->setText('012345');
        $this->_object->setWithChecksum(false);
        $this->assertSame('012345', $this->_object->getRawText());
        $this->assertSame('0123455', $this->_object->getText());
        $this->assertSame('0123455', $this->_object->getTextToDisplay());
    }

    public function testSetTextWithSpaces()
    {
        $this->_object->setText(' 012345 ');
        $this->assertSame('012345', $this->_object->getRawText());
        $this->assertSame('0123455', $this->_object->getText());
        $this->assertSame('0123455', $this->_object->getTextToDisplay());
    }

    public function testSetTextWithChecksumNotDisplayed()
    {
        $this->_object->setText('012345');
        $this->_object->setWithChecksumInText(false);
        $this->assertSame('012345', $this->_object->getRawText());
        $this->assertSame('0123455', $this->_object->getText());
        $this->assertSame('0123455', $this->_object->getTextToDisplay());
    }

    public function testBadTextDetectedIfChecksumWished()
    {
        $this->setExpectedException('\Zend\Barcode\Object\Exception');
        $this->_object->setText('a');
        $this->_object->setWithChecksum(true);
        $this->_object->getText();
    }

    public function testCheckGoodParams()
    {
        $this->_object->setText('012345');
        $this->assertTrue($this->_object->checkParams());
    }


    public function testGetKnownWidthWithoutOrientation()
    {
        $this->_object->setText('012345');
        $this->assertEquals(186, $this->_object->getWidth());
        $this->_object->setWithQuietZones(false);
        $this->assertEquals(146, $this->_object->getWidth(true));
    }

    public function testCompleteGeneration()
    {
        $this->_object->setText('012345');
        $this->_object->draw();
        $instructions = $this->loadInstructionsFile('Postnet_012345_instructions');
        $this->assertEquals($instructions, $this->_object->getInstructions());
    }

    public function testCompleteGenerationWithBorder()
    {
        $this->_object->setText('012345');
        $this->_object->setWithBorder(true);
        $this->_object->draw();
        $instructions = $this->loadInstructionsFile(
                'Postnet_012345_border_instructions');
        $this->assertEquals($instructions, $this->_object->getInstructions());
    }

    public function testCompleteGenerationWithOrientation()
    {
        $this->_object->setText('012345');
        $this->_object->setOrientation(60);
        $this->_object->draw();
        $instructions = $this->loadInstructionsFile(
                'Postnet_012345_oriented_instructions');
        $this->assertEquals($instructions, $this->_object->getInstructions());
    }

    public function testCompleteGenerationWithBorderWithOrientation()
    {
        $this->_object->setText('012345');
        $this->_object->setOrientation(60);
        $this->_object->setWithBorder(true);
        $this->_object->draw();
        $instructions = $this->loadInstructionsFile(
                'Postnet_012345_border_oriented_instructions');
        $this->assertEquals($instructions, $this->_object->getInstructions());
    }

    public function testGetDefaultHeight()
    {
        // Checksum activated => text needed
        $this->_object->setText('012345');
        $this->assertEquals(20, $this->_object->getHeight(true));
    }
}
