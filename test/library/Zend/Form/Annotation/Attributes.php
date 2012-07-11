<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Form
 */

namespace Zend\Form\Annotation;

/**
 * Attributes annotation
 *
 * Expects an array of attributes. The value is used to set any attributes on 
 * the related form object (element, fieldset, or form).
 *
 * @Annotation
 * @package    Zend_Form
 * @subpackage Annotation
 */
class Attributes extends AbstractArrayAnnotation
{
    /**
     * Retrieve the attributes
     * 
     * @return null|array
     */
    public function getAttributes()
    {
        return $this->value;
    }
}
