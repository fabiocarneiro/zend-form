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
 * @subpackage View
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Form\View;

use Zend\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for form view helpers.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage View
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class HelperLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased view helpers
     */
    protected $plugins = array(
        'form'                   => 'Zend\Form\View\Helper\Form',
        'formbutton'             => 'Zend\Form\View\Helper\FormButton',
        'form_button'            => 'Zend\Form\View\Helper\FormButton',
        'formcaptcha'            => 'Zend\Form\View\Helper\FormCaptcha',
        'form_captcha'           => 'Zend\Form\View\Helper\FormCaptcha',
        'captcha/dumb'           => 'Zend\Form\View\Helper\Captcha\Dumb',
        'captcha_dumb'           => 'Zend\Form\View\Helper\Captcha\Dumb',
        'captchadumb'            => 'Zend\Form\View\Helper\Captcha\Dumb',
        'formcaptchadumb'        => 'Zend\Form\View\Helper\Captcha\Dumb',
        'formcaptcha_dumb'       => 'Zend\Form\View\Helper\Captcha\Dumb',
        'form_captcha_dumb'      => 'Zend\Form\View\Helper\Captcha\Dumb',
        'captcha/figlet'         => 'Zend\Form\View\Helper\Captcha\Figlet',
        'captcha_figlet'         => 'Zend\Form\View\Helper\Captcha\Figlet',
        'captchafiglet'          => 'Zend\Form\View\Helper\Captcha\Figlet',
        'formcaptchafiglet'      => 'Zend\Form\View\Helper\Captcha\Figlet',
        'formcaptcha_figlet'     => 'Zend\Form\View\Helper\Captcha\Figlet',
        'form_captcha_figlet'    => 'Zend\Form\View\Helper\Captcha\Figlet',
        'captcha/image'          => 'Zend\Form\View\Helper\Captcha\Image',
        'captcha_image'          => 'Zend\Form\View\Helper\Captcha\Image',
        'captchaimage'           => 'Zend\Form\View\Helper\Captcha\Image',
        'formcaptchaimage'       => 'Zend\Form\View\Helper\Captcha\Image',
        'formcaptcha_image'      => 'Zend\Form\View\Helper\Captcha\Image',
        'form_captcha_image'     => 'Zend\Form\View\Helper\Captcha\Image',
        'captcha/recaptcha'      => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'captcha_recaptcha'      => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'captcharecaptcha'       => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'formcaptcharecaptcha'   => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'formcaptcha_recaptcha'  => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'form_captcha_recaptcha' => 'Zend\Form\View\Helper\Captcha\ReCaptcha',
        'formcheckbox'           => 'Zend\Form\View\Helper\FormCheckbox',
        'form_checkbox'          => 'Zend\Form\View\Helper\FormCheckbox',
        'formelement'            => 'Zend\Form\View\Helper\FormElement',
        'form_element'           => 'Zend\Form\View\Helper\FormElement',
        'formelementerrors'      => 'Zend\Form\View\Helper\FormElementErrors',
        'form_element_errors'    => 'Zend\Form\View\Helper\FormElementErrors',
        'formfile'               => 'Zend\Form\View\Helper\FormFile',
        'form_file'              => 'Zend\Form\View\Helper\FormFile',
        'formhidden'             => 'Zend\Form\View\Helper\FormHidden',
        'form_hidden'            => 'Zend\Form\View\Helper\FormHidden',
        'formimage'              => 'Zend\Form\View\Helper\FormImage',
        'form_image'             => 'Zend\Form\View\Helper\FormImage',
        'forminput'              => 'Zend\Form\View\Helper\FormInput',
        'form_input'             => 'Zend\Form\View\Helper\FormInput',
        'formlabel'              => 'Zend\Form\View\Helper\FormLabel',
        'form_label'             => 'Zend\Form\View\Helper\FormLabel',
        'formmulticheckbox'      => 'Zend\Form\View\Helper\FormMultiCheckbox',
        'form_multicheckbox'     => 'Zend\Form\View\Helper\FormMultiCheckbox',
        'form_multi_checkbox'    => 'Zend\Form\View\Helper\FormMultiCheckbox',
        'formpassword'           => 'Zend\Form\View\Helper\FormPassword',
        'form_password'          => 'Zend\Form\View\Helper\FormPassword',
        'formradio'              => 'Zend\Form\View\Helper\FormRadio',
        'form_radio'             => 'Zend\Form\View\Helper\FormRadio',
        'formreset'              => 'Zend\Form\View\Helper\FormReset',
        'form_reset'             => 'Zend\Form\View\Helper\FormReset',
        'formsearch'             => 'Zend\Form\View\Helper\FormSearch',
        'form_search'            => 'Zend\Form\View\Helper\FormSearch',
        'formselect'             => 'Zend\Form\View\Helper\FormSelect',
        'form_select'            => 'Zend\Form\View\Helper\FormSelect',
        'formsubmit'             => 'Zend\Form\View\Helper\FormSubmit',
        'form_submit'            => 'Zend\Form\View\Helper\FormSubmit',
        'formtext'               => 'Zend\Form\View\Helper\FormText',
        'form_text'              => 'Zend\Form\View\Helper\FormText',
        'formtextarea'           => 'Zend\Form\View\Helper\FormTextarea',
        'form_textarea'          => 'Zend\Form\View\Helper\FormTextarea',
    );
}
