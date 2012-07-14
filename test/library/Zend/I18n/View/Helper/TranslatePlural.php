<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_I18n
 */

namespace Zend\I18n\View\Helper;

use Zend\I18n\Exception;
use Zend\I18n\Translator\Translator;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper for translating plural messages.
 *
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage View
 */
class TranslatePlural extends AbstractHelper
{
    /**
     * Translator instance.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * Set translator.
     *
     * @param  Translator $translator
     * @return Translate
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Translate a plural message.
     *
     * @param  string  $singular
     * @param  string  $plural
     * @param  integer $number
     * @param  string  $textDomain
     * @param  string  $locale
     * @return string
     * @throws Exception\RuntimeException
     */
    public function __invoke(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null
    ) {
        if ($this->translator === null) {
            throw new Exception\RuntimeException('Translator has not been set');
        }

        return $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }
}
