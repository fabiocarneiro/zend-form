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
 * @package    Zend_JSON
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace ZendTest\JSON;
use Zend\JSON;

/**
 * @category   Zend
 * @package    Zend_JSON
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_JSON
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{
    private $_originalUseBuiltinEncoderDecoderValue;

    public function setUp()
    {
        $this->_originalUseBuiltinEncoderDecoderValue = JSON\JSON::$useBuiltinEncoderDecoder;
    }

    public function tearDown()
    {
        JSON\JSON::$useBuiltinEncoderDecoder = $this->_originalUseBuiltinEncoderDecoderValue;
    }

    public function testJSONWithPhpJSONExtension()
    {
        if (!extension_loaded('json')) {
            $this->markTestSkipped('JSON extension is not loaded');
        }
        JSON\JSON::$useBuiltinEncoderDecoder = false;
        $this->_testJSON(array('string', 327, true, null));
    }

    public function testJSONWithBuiltins()
    {
        JSON\JSON::$useBuiltinEncoderDecoder = true;
        $this->_testJSON(array('string', 327, true, null));
    }

    /**
     * Test encoding and decoding in a single step
     * @param array $values   array of values to test against encode/decode
     */
    protected function _testJSON($values)
    {
        $encoded = JSON\JSON::encode($values);
        $this->assertEquals($values, JSON\JSON::decode($encoded));
    }

    /**
     * test null encoding/decoding
     */
    public function testNull()
    {
        $this->_testEncodeDecode(array(null));
    }


    /**
     * test boolean encoding/decoding
     */
    public function testBoolean()
    {
        $this->assertTrue(JSON\Decoder::decode(JSON\Encoder::encode(true)));
        $this->assertFalse(JSON\Decoder::decode(JSON\Encoder::encode(false)));
    }


    /**
     * test integer encoding/decoding
     */
    public function testInteger()
    {
        $this->_testEncodeDecode(array(-2));
        $this->_testEncodeDecode(array(-1));

        $zero = JSON\Decoder::decode(JSON\Encoder::encode(0));
        $this->assertEquals(0, $zero, 'Failed 0 integer test. Encoded: ' . serialize(JSON\Encoder::encode(0)));
    }


    /**
     * test float encoding/decoding
     */
    public function testFloat()
    {
        $this->_testEncodeDecode(array(-2.1, 1.2));
    }

    /**
     * test string encoding/decoding
     */
    public function testString()
    {
        $this->_testEncodeDecode(array('string'));
        $this->assertEquals('', JSON\Decoder::decode(JSON\Encoder::encode('')), 'Empty string encoded: ' . serialize(JSON\Encoder::encode('')));
    }

    /**
     * Test backslash escaping of string
     */
    public function testString2()
    {
        $string   = 'INFO: Path \\\\test\\123\\abc';
        $expected = '"INFO: Path \\\\\\\\test\\\\123\\\\abc"';
        $encoded = JSON\Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Backslash encoding incorrect: expected: ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, JSON\Decoder::decode($encoded));
    }

    /**
     * Test newline escaping of string
     */
    public function testString3()
    {
        $expected = '"INFO: Path\nSome more"';
        $string   = "INFO: Path\nSome more";
        $encoded  = JSON\Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Newline encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, JSON\Decoder::decode($encoded));
    }

    /**
     * Test tab/non-tab escaping of string
     */
    public function testString4()
    {
        $expected = '"INFO: Path\\t\\\\tSome more"';
        $string   = "INFO: Path\t\\tSome more";
        $encoded  = JSON\Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Tab encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, JSON\Decoder::decode($encoded));
    }

    /**
     * Test double-quote escaping of string
     */
    public function testString5()
    {
        $expected = '"INFO: Path \"Some more\""';
        $string   = 'INFO: Path "Some more"';
        $encoded  = JSON\Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Quote encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, JSON\Decoder::decode($encoded));
    }

    /**
     * test indexed array encoding/decoding
     */
    public function testArray()
    {
        $array = array(1, 'one', 2, 'two');
        $encoded = JSON\Encoder::encode($array);
        $this->assertSame($array, JSON\Decoder::decode($encoded), 'Decoded array does not match: ' . serialize($encoded));
    }

    /**
     * test associative array encoding/decoding
     */
    public function testAssocArray()
    {
        $this->_testEncodeDecode(array(array('one' => 1, 'two' => 2)));
    }

    /**
     * test associative array encoding/decoding, with mixed key types
     */
    public function testAssocArray2()
    {
        $this->_testEncodeDecode(array(array('one' => 1, 2 => 2)));
    }

    /**
     * test associative array encoding/decoding, with integer keys not starting at 0
     */
    public function testAssocArray3()
    {
        $this->_testEncodeDecode(array(array(1 => 'one', 2 => 'two')));
    }

    /**
     * test object encoding/decoding (decoding to array)
     */
    public function testObject()
    {
        $value = new \stdClass();
        $value->one = 1;
        $value->two = 2;

        $array = array('__className' => 'stdClass', 'one' => 1, 'two' => 2);

        $encoded = JSON\Encoder::encode($value);
        $this->assertSame($array, JSON\Decoder::decode($encoded));
    }

    /**
     * test object encoding/decoding (decoding to stdClass)
     */
    public function testObjectAsObject()
    {
        $value = new \stdClass();
        $value->one = 1;
        $value->two = 2;

        $encoded = JSON\Encoder::encode($value);
        $decoded = JSON\Decoder::decode($encoded, JSON\JSON::TYPE_OBJECT);
        $this->assertTrue(is_object($decoded), 'Not decoded as an object');
        $this->assertTrue($decoded instanceof \StdClass, 'Not a StdClass object');
        $this->assertTrue(isset($decoded->one), 'Expected property not set');
        $this->assertEquals($value->one, $decoded->one, 'Unexpected value');
    }

    /**
     * Test that arrays of objects decode properly; see issue #144
     */
    public function testDecodeArrayOfObjects()
    {
        $value = '[{"id":1},{"foo":2}]';
        $expect = array(array('id' => 1), array('foo' => 2));
        $this->assertEquals($expect, JSON\Decoder::decode($value));
    }

    /**
     * Test that objects of arrays decode properly; see issue #107
     */
    public function testDecodeObjectOfArrays()
    {
        $value = '{"codeDbVar" : {"age" : ["int", 5], "prenom" : ["varchar", 50]}, "234" : [22, "jb"], "346" : [64, "francois"], "21" : [12, "paul"]}';
        $expect = array(
            'codeDbVar' => array(
                'age'   => array('int', 5),
                'prenom' => array('varchar', 50),
            ),
            234 => array(22, 'jb'),
            346 => array(64, 'francois'),
            21  => array(12, 'paul')
        );
        $this->assertEquals($expect, JSON\Decoder::decode($value));
    }

    /**
     * Test encoding and decoding in a single step
     * @param array $values   array of values to test against encode/decode
     */
    protected function _testEncodeDecode($values)
    {
        foreach ($values as $value) {
            $encoded = JSON\Encoder::encode($value);
            $this->assertEquals($value, JSON\Decoder::decode($encoded));
        }
    }

    /**
     * Test that version numbers such as 4.10 are encoded and decoded properly;
     * See ZF-377
     */
    public function testEncodeReleaseNumber()
    {
        $value = '4.10';

        $this->_testEncodeDecode(array($value));
    }

    /**
     * Tests that spaces/linebreaks prior to a closing right bracket don't throw
     * exceptions. See ZF-283.
     */
    public function testEarlyLineBreak()
    {
        $expected = array('data' => array(1, 2, 3, 4));

        $json = '{"data":[1,2,3,4' . "\n]}";
        $this->assertEquals($expected, JSON\Decoder::decode($json));

        $json = '{"data":[1,2,3,4 ]}';
        $this->assertEquals($expected, JSON\Decoder::decode($json));
    }

    /**
     * Tests for ZF-504
     *
     * Three confirmed issues reported:
     * - encoder improperly encoding empty arrays as structs
     * - decoder happily decoding clearly borked JSON
     * - decoder decoding octal values improperly (shouldn't decode them at all, as JSON does not support them)
     */
    public function testZf504()
    {
        $test = array();
        $this->assertSame('[]', JSON\Encoder::encode($test));

        try {
            $json = '[a"],["a],[][]';
            $test = JSON\Decoder::decode($json);
            $this->fail("Should not be able to decode '$json'");

            $json = '[a"],["a]';
            $test = JSON\Decoder::decode($json);
            $this->fail("Should not be able to decode '$json'");
        } catch (\Exception $e) {
            // success
        }

        try {
            $expected = 010;
            $test = JSON\Decoder::decode('010');
            $this->fail('Octal values are not supported in JSON notation');
        } catch (\Exception $e) {
            // sucess
        }
    }

    /**
     * Tests for ZF-461
     *
     * Check to see that cycling detection works properly
     */
    public function testZf461()
    {
        $item1 = new Item() ;
        $item2 = new Item() ;
        $everything = array() ;
        $everything['allItems'] = array($item1, $item2) ;
        $everything['currentItem'] = $item1 ;

        try {
            $encoded = JSON\Encoder::encode($everything);
        } catch (\Exception $e) {
            $this->fail('Object cycling checks should check for recursion, not duplicate usage of an item');
        }

        try {
            $encoded = JSON\Encoder::encode($everything, true);
            $this->fail('Object cycling not allowed when cycleCheck parameter is true');
        } catch (\Exception $e) {
            // success
        }
    }

    /**
     * Test for ZF-4053
     *
     * Check to see that cyclical exceptions are silenced when
     * $option['silenceCyclicalExceptions'] = true is used
     */
    public function testZf4053()
    {
        $item1 = new Item() ;
        $item2 = new Item() ;
        $everything = array() ;
        $everything['allItems'] = array($item1, $item2) ;
        $everything['currentItem'] = $item1 ;

        $options = array('silenceCyclicalExceptions'=>true);

        JSON\JSON::$useBuiltinEncoderDecoder = true;
        $encoded = JSON\JSON::encode($everything, true, $options);
        $json = '{"allItems":[{"__className":"ZendTest\\\\JSON\\\\Item"},{"__className":"ZendTest\\\\JSON\\\\Item"}],"currentItem":"* RECURSION (ZendTest\\\\JSON\\\\Item) *"}';

        $this->assertEquals($json, $encoded);
    }

    public function testEncodeObject()
    {
        $actual  = new Object();
        $encoded = JSON\Encoder::encode($actual);
        $decoded = JSON\Decoder::decode($encoded, JSON\JSON::TYPE_OBJECT);

        $this->assertTrue(isset($decoded->__className));
        $this->assertEquals('ZendTest\JSON\Object', $decoded->__className);
        $this->assertTrue(isset($decoded->foo));
        $this->assertEquals('bar', $decoded->foo);
        $this->assertTrue(isset($decoded->bar));
        $this->assertEquals('baz', $decoded->bar);
        $this->assertFalse(isset($decoded->_foo));
    }

    public function testEncodeClass()
    {
        $encoded = JSON\Encoder::encodeClass('ZendTest\JSON\Object');

        $this->assertContains("Class.create('ZendTest\\JSON\\Object'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'foo'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'bar'", $encoded);
        $this->assertNotContains("ZAjaxEngine.invokeRemoteMethod(this, 'baz'", $encoded);

        $this->assertContains('variables:{foo:"bar",bar:"baz"}', $encoded);
        $this->assertContains('constants : {FOO: "bar"}', $encoded);
    }

    public function testEncodeClasses()
    {
        $encoded = JSON\Encoder::encodeClasses(array('ZendTest\JSON\Object', 'Zend\JSON\JSON'));

        $this->assertContains("Class.create('ZendTest\\JSON\\Object'", $encoded);
        $this->assertContains("Class.create('Zend\\JSON\\JSON'", $encoded);
    }

    public function testToJSONSerialization()
    {
        $toJSONObject = new ToJSONClass();

        $result = JSON\JSON::encode($toJSONObject);

        $this->assertEquals('{"firstName":"John","lastName":"Doe","email":"john@doe.com"}', $result);
    }

     /**
     * test encoding array with Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testEncodingArrayWithExpr()
    {
        $expr = new JSON\Expr('window.alert("Zend JSON Expr")');
        $array = array('expr'=>$expr, 'int'=>9, 'string'=>'text');
        $result = JSON\JSON::encode($array, false, array('enableJSONExprFinder' => true));
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * test encoding object with Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testEncodingObjectWithExprAndInternalEncoder()
    {
        JSON\JSON::$useBuiltinEncoderDecoder = true;

        $expr = new JSON\Expr('window.alert("Zend JSON Expr")');
        $obj = new \stdClass();
        $obj->expr = $expr;
        $obj->int = 9;
        $obj->string = 'text';
        $result = JSON\JSON::encode($obj, false, array('enableJSONExprFinder' => true));
        $expected = '{"__className":"stdClass","expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * test encoding object with Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testEncodingObjectWithExprAndExtJSON()
    {
        if(!function_exists('json_encode')) {
            $this->markTestSkipped('Test only works with ext/json enabled!');
        }

        JSON\JSON::$useBuiltinEncoderDecoder = false;

        $expr = new JSON\Expr('window.alert("Zend JSON Expr")');
        $obj = new \stdClass();
        $obj->expr = $expr;
        $obj->int = 9;
        $obj->string = 'text';
        $result = JSON\JSON::encode($obj, false, array('enableJSONExprFinder' => true));
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * test encoding object with ToJSON and Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testToJSONWithExpr()
    {
        JSON\JSON::$useBuiltinEncoderDecoder = true;

        $obj = new ToJSONWithExpr();
        $result = JSON\JSON::encode($obj, false, array('enableJSONExprFinder' => true));
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * Regression tests for Zend_JSON_Expr and mutliple keys with the same name.
     *
     * @group ZF-4946
     */
    public function testEncodingMultipleNestedSwitchingSameNameKeysWithDifferentJSONExprSettings()
    {
        $data = array(
            0 => array(
                "alpha" => new JSON\Expr("function(){}"),
                "beta"  => "gamma",
            ),
            1 => array(
                "alpha" => "gamma",
                "beta"  => new JSON\Expr("function(){}"),
            ),
            2 => array(
                "alpha" => "gamma",
                "beta" => "gamma",
            )
        );
        $result = JSON\JSON::encode($data, false, array('enableJSONExprFinder' => true));

        $this->assertEquals(
            '[{"alpha":function(){},"beta":"gamma"},{"alpha":"gamma","beta":function(){}},{"alpha":"gamma","beta":"gamma"}]',
            $result
        );
    }

    /**
     * Regression tests for Zend_JSON_Expr and mutliple keys with the same name.
     *
     * @group ZF-4946
     */
    public function testEncodingMultipleNestedIteratedSameNameKeysWithDifferentJSONExprSettings()
    {
        $data = array(
            0 => array(
                "alpha" => "alpha"
            ),
            1 => array(
                "alpha" => "beta",
            ),
            2 => array(
                "alpha" => new JSON\Expr("gamma"),
            ),
            3 => array(
                "alpha" => "delta",
            ),
            4 => array(
                "alpha" => new JSON\Expr("epsilon"),
            )
        );
        $result = JSON\JSON::encode($data, false, array('enableJSONExprFinder' => true));

        $this->assertEquals('[{"alpha":"alpha"},{"alpha":"beta"},{"alpha":gamma},{"alpha":"delta"},{"alpha":epsilon}]', $result);
    }

    public function testDisabledJSONExprFinder()
    {
        JSON\JSON::$useBuiltinEncoderDecoder = true;

        $data = array(
            0 => array(
                "alpha" => new JSON\Expr("function(){}"),
                "beta"  => "gamma",
            ),
        );
        $result = JSON\JSON::encode($data);

        $this->assertEquals(
            '[{"alpha":{"__className":"Zend\\\\JSON\\\\Expr"},"beta":"gamma"}]',
            $result
        );
    }

    /**
     * @group ZF-4054
     */
    public function testEncodeWithUtf8IsTransformedToPackedSyntax()
    {
        $data = array("Отмена");
        $result = JSON\Encoder::encode($data);

        $this->assertEquals('["\u041e\u0442\u043c\u0435\u043d\u0430"]', $result);
    }

    /**
     * @group ZF-4054
     *
     * This test contains assertions from the Solar Framework by Paul M. Jones
     * @link http://solarphp.com
     */
    public function testEncodeWithUtf8IsTransformedSolarRegression()
    {
        $expect = '"h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad"';
        $this->assertEquals($expect,           JSON\Encoder::encode('hÃ©llÃ¶ wÃ¸rÅ‚d'));
        $this->assertEquals('hÃ©llÃ¶ wÃ¸rÅ‚d', JSON\Decoder::decode($expect));

        $expect = '"\u0440\u0443\u0441\u0441\u0438\u0448"';
        $this->assertEquals($expect,  JSON\Encoder::encode("руссиш"));
        $this->assertEquals("руссиш", JSON\Decoder::decode($expect));
    }

    /**
     * @group ZF-4054
     */
    public function testEncodeUnicodeStringSolarRegression()
    {
        $value    = 'hÃ©llÃ¶ wÃ¸rÅ‚d';
        $expected = 'h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad';
        $this->assertEquals($expected, JSON\Encoder::encodeUnicodeString($value));

        $value    = "\xC3\xA4";
        $expected = '\u00e4';
        $this->assertEquals($expected, JSON\Encoder::encodeUnicodeString($value));

        $value    = "\xE1\x82\xA0\xE1\x82\xA8";
        $expected = '\u10a0\u10a8';
        $this->assertEquals($expected, JSON\Encoder::encodeUnicodeString($value));
    }

    /**
     * @group ZF-4054
     */
    public function testDecodeUnicodeStringSolarRegression()
    {
        $expected = 'hÃ©llÃ¶ wÃ¸rÅ‚d';
        $value    = 'h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad';
        $this->assertEquals($expected, JSON\Decoder::decodeUnicodeString($value));

        $expected = "\xC3\xA4";
        $value    = '\u00e4';
        $this->assertEquals($expected, JSON\Decoder::decodeUnicodeString($value));

        $value    = '\u10a0';
        $expected = "\xE1\x82\xA0";
        $this->assertEquals($expected, JSON\Decoder::decodeUnicodeString($value));
    }

    /**
     * @group ZF-4054
     *
     * This test contains assertions from the Solar Framework by Paul M. Jones
     * @link http://solarphp.com
     */
    public function testEncodeWithUtf8IsTransformedSolarRegressionEqualsJSONExt()
    {
        if(function_exists('json_encode') == false) {
            $this->markTestSkipped('Test can only be run, when ext/json is installed.');
        }

        $this->assertEquals(
            json_encode('hÃ©llÃ¶ wÃ¸rÅ‚d'),
            JSON\Encoder::encode('hÃ©llÃ¶ wÃ¸rÅ‚d')
        );

        $this->assertEquals(
            json_encode("руссиш"),
            JSON\Encoder::encode("руссиш")
        );
    }

    /**
     * @group ZF-4946
     */
    public function testUtf8JSONExprFinder()
    {
        $data = array("Отмена" => new JSON\Expr("foo"));

        JSON\JSON::$useBuiltinEncoderDecoder = true;
        $result = JSON\JSON::encode($data, false, array('enableJSONExprFinder' => true));
        $this->assertEquals('{"\u041e\u0442\u043c\u0435\u043d\u0430":foo}', $result);
        JSON\JSON::$useBuiltinEncoderDecoder = false;

        $result = JSON\JSON::encode($data, false, array('enableJSONExprFinder' => true));
        $this->assertEquals('{"\u041e\u0442\u043c\u0435\u043d\u0430":foo}', $result);
    }

    /**
     * @group ZF-4437
     */
    public function testKommaDecimalIsConvertedToCorrectJSONWithDot()
    {
        $localeInfo = localeconv();
        if($localeInfo['decimal_point'] != ",") {
            $this->markTestSkipped("This test only works for platforms where , is the decimal point separator.");
        }

        JSON\JSON::$useBuiltinEncoderDecoder = true;
        $this->assertEquals("[1.20, 1.68]", JSON\Encoder::encode(array(
            (float)"1,20", (float)"1,68"
        )));
    }

    public function testEncodeObjectImplementingIterator()
    {
        $this->markTestIncomplete('Test is not yet finished.');
    }
    
    /**
     * @group ZF-8663
     */
    public function testNativeJSONEncoderWillProperlyEncodeSolidusInStringValues()
    {
        $source = "</foo><foo>bar</foo>";
        $target = '"<\\/foo><foo>bar<\\/foo>"';
        
        // first test ext/json
        JSON\JSON::$useBuiltinEncoderDecoder = false;
        $this->assertEquals($target, JSON\JSON::encode($source));
    }
    
    /**
     * @group ZF-8663
     */
    public function testBuiltinJSONEncoderWillProperlyEncodeSolidusInStringValues()
    {
        $source = "</foo><foo>bar</foo>";
        $target = '"<\\/foo><foo>bar<\\/foo>"';
        
        // first test ext/json
        JSON\JSON::$useBuiltinEncoderDecoder = true;
        $this->assertEquals($target, JSON\JSON::encode($source));
    }
    
    /**
     * @group ZF-8918
     */
    public function testDecodingInvalidJSONShouldRaiseAnException()
    {
        $this->setExpectedException('Zend\JSON\Exception');
        JSON\JSON::decode(' some string ');
    }

    /**
     * @group ZF-9416
     * Encoding an iterator using the internal encoder should handle undefined keys
     */
    public function testIteratorWithoutDefinedKey()
    {
        $inputValue = new \ArrayIterator(array('foo'));
        $encoded = JSON\Encoder::encode($inputValue);
        $expectedDecoding = '{"__className":"ArrayIterator",0:"foo"}';
        $this->assertEquals($expectedDecoding, $encoded);
    }
}

/**
 * Zend_JSONTest_Item: test item for use with testZf461()
 */
class Item
{
}

/**
 * Zend_JSONTest_Object: test class for encoding classes
 */
class Object
{
    const FOO = 'bar';

    public $foo = 'bar';
    public $bar = 'baz';

    protected $_foo = 'fooled you';

    public function foo($bar, $baz)
    {
    }

    public function bar($baz)
    {
    }

    protected function baz()
    {
    }
}

class ToJSONClass
{
    private $_firstName = 'John';

    private $_lastName = 'Doe';

    private $_email = 'john@doe.com';

    public function toJSON()
    {
        $data = array(
            'firstName' => $this->_firstName,
            'lastName'  => $this->_lastName,
            'email'     => $this->_email
        );

        return JSON\JSON::encode($data);
    }
}

/**
 * ISSUE  ZF-4946
 *
 */
class ToJSONWithExpr
{
    private $_string = 'text';
    private $_int = 9;
    private $_expr = 'window.alert("Zend JSON Expr")';

    public function toJSON()
    {
        $data = array(
            'expr'   => new JSON\Expr($this->_expr),
            'int'    => $this->_int,
            'string' => $this->_string
        );

        return JSON\JSON::encode($data, false, array('enableJSONExprFinder' => true));
    }
}
