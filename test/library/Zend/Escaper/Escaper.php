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
 * @package    Zend_Escaper
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Escaper;

use Zend\Escaper\Exception;

/**
 * Context specific methods for use in secure output escaping
 *
 * @package    Zend_Escaper
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Escaper
{

    /**
     * Current encoding for escaping. If not UTF-8, we convert strings from this encoding
     * pre-escaping and back to this encoding post-escaping.
     * 
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Holds the value of the special flags passed as second parameter to
     * htmlspecialchars(). We modify these for PHP 5.4 to take advantage
     * of the new ENT_SUBSTITUTE flag for correctly dealing with invalid
     * UTF-8 sequences.
     * 
     * @var string
     */
    protected $htmlSpecialCharsFlags = ENT_QUOTES;

    /**
     * List of all encoding supported by this class
     * 
     * @var array
     */
    protected $supportedEncodings = array(
        'iso-8859-1',   'iso8859-1',    'iso-8859-5',   'iso8859-5',
        'iso-8859-15',  'iso8859-15',   'utf-8',        'cp866',
        'ibm866',       '866',          'cp1251',       'windows-1251',
        'win-1251',     '1251',         'cp1252',       'windows-1252',
        '1252',         'koi8-r',       'koi8-ru',      'koi8r',
        'big5',         '950',          'gb2312',       '936',
        'big5-hkscs',   'shift_jis',    'sjis',         'sjis-win',
        'cp932',        '932',          'euc-jp',       'eucjp',
        'eucjp-win',    'macroman'
    );

    public function __construct($encoding = null)
    {
        if (!is_null($encoding)) {
            if (empty($encoding)) {
                throw new Exception\InvalidArgumentException(
                    get_called_class() . ' constructor parameter does not allow a NULL or '
                    . 'blank string value'
                );
            }
            if (!in_array(strtolower($encoding), $this->supportedEncodings)) {
                throw new Exception\InvalidArgumentException(
                    'Value of \'' . $encoding . '\' passed to ' . get_called_class()
                    . ' constructor parameter is invalid. Provide an encoding supported by htmlspecialchars()'
                );
            }
            $this->encoding = $encoding;
        }
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $this->htmlSpecialCharsFlags = ENT_QUOTES | ENT_SUBSTITUTE;
        }
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function escapeHtml($string)
    {
        $result = htmlspecialchars($string, $this->htmlSpecialCharsFlags, $this->encoding);
        return $result;
    }

    public function escapeHtmlAttr($string)
    {
        $string = $this->toUtf8($string);
        if (strlen($string) == 0 || ctype_digit($string)) {
            return $string;
        }
        $result = preg_replace_callback(
            '/[^a-zA-Z0-9,\.\-_]/Su',
            function ($matches) {
                $chr = $matches[0];
                $ord = ord($chr);
                /**
                 * The following replaces characters undefined in HTML with the
                 * hex entity for the Unicode replacement character.
                 */
                if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r")
                || ($ord >= 0x7f && $ord <= 0x9f)) {
                    return '&#xFFFD;';
                }
                /**
                 * Check if the current character to escape has a name entity we should
                 * replace it with while grabbing the hex value of the character.
                 */
                if (strlen($chr) == 1) {
                    $hex = strtoupper(substr('00' . bin2hex($chr), -2));
                } else {
                    $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
                    $hex = strtoupper(substr('0000' . bin2hex($chr), -4));
                }
                $int = hexdec($hex);
                if (array_key_exists($int, $this->htmlNamedEntityMap)) {
                    return sprintf('&%s;', $this->htmlNamedEntityMap[$int]);
                }
                /**
                 * Per OWASP recommendations, we'll use hex entities for any other
                 * characters where a named entity does not exist.
                 */
                return sprintf('&#x%s;', $hex);
            },
            $string
        );
        return $this->fromUtf8($result);
    }

    public function escapeJs($string)
    {
        $string = $this->toUtf8($string);
        if (strlen($string) == 0 || ctype_digit($string)) {
            return $string;
        }
        $result = preg_replace_callback(
            '/[^a-zA-Z0-9,\._]/Su',
            function ($matches) {
                $chr = $matches[0];
                if (strlen($chr) == 1) {
                    return sprintf('\\x%s', strtoupper(substr('00' . bin2hex($chr), -2)));
                } else {
                    $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
                    return sprintf('\\u%s', strtoupper(substr('0000' . bin2hex($chr), -4)));
                }
            },
            $string
        );
        return $this->fromUtf8($result);
    }

    public function escapeUrl($string)
    {
        return rawurlencode($string);
    }

    public function escapeCss($string)
    {
        $string = $this->toUtf8($string);
        if (strlen($string) == 0 || ctype_digit($string)) {
            return $string;
        }
        $result = preg_replace_callback(
            '/[^a-zA-Z0-9]/Su',
            function ($matches) {
                $chr = $matches[0];
                if (strlen($chr) == 1) {
                    $hex = ltrim(strtoupper(bin2hex($chr)), '0');
                    if (strlen($hex) == 0) $hex = '0';
                    return sprintf('\\%s ', $hex);
                } else {
                    $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
                    return sprintf('\\%s ', ltrim(strtoupper(bin2hex($chr)), '0'));
                }
            },
            $string
        );
        return $this->fromUtf8($result);
    }

    protected function toUtf8($string)
    {
        if ($this->getEncoding() === 'utf-8') {
            $result = $string;
        } else {
            $result = $this->convertEncoding($string, 'UTF-8', $this->getEncoding());
        }
        if (!$this->isUtf8($result)) {
            throw new Exception\RuntimeException(sprintf(
                'String to be escaped was not valid UTF-8 or could not be converted: %s', $result
            ));
        }
        return $result;
    }

    protected function fromUtf8($string)
    {
        if ($this->getEncoding() === 'utf-8') {
            return $string;
        }
        return $this->convertEncoding($string, $this->getEncoding(), 'UTF-8');
    }

    protected function isUtf8($string)
    {
        if (strlen($string) == 0) {
            return true;
        } elseif (preg_match('/^./su', $string) == 1) {
            return true;
        }
        return false;
    }

    protected function convertEncoding($string, $to, $from)
    {
        if (function_exists('iconv')) {
            return iconv($from, $to, $string);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $to, $from);
        }
        throw new Exception\RuntimeException(
            get_called_class()
            . ' requires either the iconv or mbstring extension to be installed'
            . ' when escaping for non UTF-8 strings.'
        );
    }

    /**
     * Entity Map mapping Unicode codepoints to any available named HTML entities
     * 
     * @var array
     */
    protected $htmlNamedEntityMap = array(
        34 => 'quot',         /* quotation mark */
        38 => 'amp',          /* ampersand */
        60 => 'lt',           /* less-than sign */
        62 => 'gt',           /* greater-than sign */
        160 => 'nbsp',        /* no-break space */
        161 => 'iexcl',       /* inverted exclamation mark */
        162 => 'cent',        /* cent sign */
        163 => 'pound',       /* pound sign */
        164 => 'curren',      /* currency sign */
        165 => 'yen',         /* yen sign */
        166 => 'brvbar',      /* broken bar */
        167 => 'sect',        /* section sign */
        168 => 'uml',         /* diaeresis */
        169 => 'copy',        /* copyright sign */
        170 => 'ordf',        /* feminine ordinal indicator */
        171 => 'laquo',       /* left-pointing double angle quotation mark */
        172 => 'not',         /* not sign */
        173 => 'shy',         /* soft hyphen */
        174 => 'reg',         /* registered sign */
        175 => 'macr',        /* macron */
        176 => 'deg',         /* degree sign */
        177 => 'plusmn',      /* plus-minus sign */
        178 => 'sup2',        /* superscript two */
        179 => 'sup3',        /* superscript three */
        180 => 'acute',       /* acute accent */
        181 => 'micro',       /* micro sign */
        182 => 'para',        /* pilcrow sign */
        183 => 'middot',      /* middle dot */
        184 => 'cedil',       /* cedilla */
        185 => 'sup1',        /* superscript one */
        186 => 'ordm',        /* masculine ordinal indicator */
        187 => 'raquo',       /* right-pointing double angle quotation mark */
        188 => 'frac14',      /* vulgar fraction one quarter */
        189 => 'frac12',      /* vulgar fraction one half */
        190 => 'frac34',      /* vulgar fraction three quarters */
        191 => 'iquest',      /* inverted question mark */
        192 => 'Agrave',      /* Latin capital letter a with grave */
        193 => 'Aacute',      /* Latin capital letter a with acute */
        194 => 'Acirc',       /* Latin capital letter a with circumflex */
        195 => 'Atilde',      /* Latin capital letter a with tilde */
        196 => 'Auml',        /* Latin capital letter a with diaeresis */
        197 => 'Aring',       /* Latin capital letter a with ring above */
        198 => 'AElig',       /* Latin capital letter ae */
        199 => 'Ccedil',      /* Latin capital letter c with cedilla */
        200 => 'Egrave',      /* Latin capital letter e with grave */
        201 => 'Eacute',      /* Latin capital letter e with acute */
        202 => 'Ecirc',       /* Latin capital letter e with circumflex */
        203 => 'Euml',        /* Latin capital letter e with diaeresis */
        204 => 'Igrave',      /* Latin capital letter i with grave */
        205 => 'Iacute',      /* Latin capital letter i with acute */
        206 => 'Icirc',       /* Latin capital letter i with circumflex */
        207 => 'Iuml',        /* Latin capital letter i with diaeresis */
        208 => 'ETH',         /* Latin capital letter eth */
        209 => 'Ntilde',      /* Latin capital letter n with tilde */
        210 => 'Ograve',      /* Latin capital letter o with grave */
        211 => 'Oacute',      /* Latin capital letter o with acute */
        212 => 'Ocirc',       /* Latin capital letter o with circumflex */
        213 => 'Otilde',      /* Latin capital letter o with tilde */
        214 => 'Ouml',        /* Latin capital letter o with diaeresis */
        215 => 'times',       /* multiplication sign */
        216 => 'Oslash',      /* Latin capital letter o with stroke */
        217 => 'Ugrave',      /* Latin capital letter u with grave */
        218 => 'Uacute',      /* Latin capital letter u with acute */
        219 => 'Ucirc',       /* Latin capital letter u with circumflex */
        220 => 'Uuml',        /* Latin capital letter u with diaeresis */
        221 => 'Yacute',      /* Latin capital letter y with acute */
        222 => 'THORN',       /* Latin capital letter thorn */
        223 => 'szlig',       /* Latin small letter sharp sXCOMMAX German Eszett */
        224 => 'agrave',      /* Latin small letter a with grave */
        225 => 'aacute',      /* Latin small letter a with acute */
        226 => 'acirc',       /* Latin small letter a with circumflex */
        227 => 'atilde',      /* Latin small letter a with tilde */
        228 => 'auml',        /* Latin small letter a with diaeresis */
        229 => 'aring',       /* Latin small letter a with ring above */
        230 => 'aelig',       /* Latin lowercase ligature ae */
        231 => 'ccedil',      /* Latin small letter c with cedilla */
        232 => 'egrave',      /* Latin small letter e with grave */
        233 => 'eacute',      /* Latin small letter e with acute */
        234 => 'ecirc',       /* Latin small letter e with circumflex */
        235 => 'euml',        /* Latin small letter e with diaeresis */
        236 => 'igrave',      /* Latin small letter i with grave */
        237 => 'iacute',      /* Latin small letter i with acute */
        238 => 'icirc',       /* Latin small letter i with circumflex */
        239 => 'iuml',        /* Latin small letter i with diaeresis */
        240 => 'eth',         /* Latin small letter eth */
        241 => 'ntilde',      /* Latin small letter n with tilde */
        242 => 'ograve',      /* Latin small letter o with grave */
        243 => 'oacute',      /* Latin small letter o with acute */
        244 => 'ocirc',       /* Latin small letter o with circumflex */
        245 => 'otilde',      /* Latin small letter o with tilde */
        246 => 'ouml',        /* Latin small letter o with diaeresis */
        247 => 'divide',      /* division sign */
        248 => 'oslash',      /* Latin small letter o with stroke */
        249 => 'ugrave',      /* Latin small letter u with grave */
        250 => 'uacute',      /* Latin small letter u with acute */
        251 => 'ucirc',       /* Latin small letter u with circumflex */
        252 => 'uuml',        /* Latin small letter u with diaeresis */
        253 => 'yacute',      /* Latin small letter y with acute */
        254 => 'thorn',       /* Latin small letter thorn */
        255 => 'yuml',        /* Latin small letter y with diaeresis */
        338 => 'OElig',       /* Latin capital ligature oe */
        339 => 'oelig',       /* Latin small ligature oe */
        352 => 'Scaron',      /* Latin capital letter s with caron */
        353 => 'scaron',      /* Latin small letter s with caron */
        376 => 'Yuml',        /* Latin capital letter y with diaeresis */
        402 => 'fnof',        /* Latin small letter f with hook */
        710 => 'circ',        /* modifier letter circumflex accent */
        732 => 'tilde',       /* small tilde */
        913 => 'Alpha',       /* Greek capital letter alpha */
        914 => 'Beta',        /* Greek capital letter beta */
        915 => 'Gamma',       /* Greek capital letter gamma */
        916 => 'Delta',       /* Greek capital letter delta */
        917 => 'Epsilon',     /* Greek capital letter epsilon */
        918 => 'Zeta',        /* Greek capital letter zeta */
        919 => 'Eta',         /* Greek capital letter eta */
        920 => 'Theta',       /* Greek capital letter theta */
        921 => 'Iota',        /* Greek capital letter iota */
        922 => 'Kappa',       /* Greek capital letter kappa */
        923 => 'Lambda',      /* Greek capital letter lambda */
        924 => 'Mu',          /* Greek capital letter mu */
        925 => 'Nu',          /* Greek capital letter nu */
        926 => 'Xi',          /* Greek capital letter xi */
        927 => 'Omicron',     /* Greek capital letter omicron */
        928 => 'Pi',          /* Greek capital letter pi */
        929 => 'Rho',         /* Greek capital letter rho */
        931 => 'Sigma',       /* Greek capital letter sigma */
        932 => 'Tau',         /* Greek capital letter tau */
        933 => 'Upsilon',     /* Greek capital letter upsilon */
        934 => 'Phi',         /* Greek capital letter phi */
        935 => 'Chi',         /* Greek capital letter chi */
        936 => 'Psi',         /* Greek capital letter psi */
        937 => 'Omega',       /* Greek capital letter omega */
        945 => 'alpha',       /* Greek small letter alpha */
        946 => 'beta',        /* Greek small letter beta */
        947 => 'gamma',       /* Greek small letter gamma */
        948 => 'delta',       /* Greek small letter delta */
        949 => 'epsilon',     /* Greek small letter epsilon */
        950 => 'zeta',        /* Greek small letter zeta */
        951 => 'eta',         /* Greek small letter eta */
        952 => 'theta',       /* Greek small letter theta */
        953 => 'iota',        /* Greek small letter iota */
        954 => 'kappa',       /* Greek small letter kappa */
        955 => 'lambda',      /* Greek small letter lambda */
        956 => 'mu',          /* Greek small letter mu */
        957 => 'nu',          /* Greek small letter nu */
        958 => 'xi',          /* Greek small letter xi */
        959 => 'omicron',     /* Greek small letter omicron */
        960 => 'pi',          /* Greek small letter pi */
        961 => 'rho',         /* Greek small letter rho */
        962 => 'sigmaf',      /* Greek small letter final sigma */
        963 => 'sigma',       /* Greek small letter sigma */
        964 => 'tau',         /* Greek small letter tau */
        965 => 'upsilon',     /* Greek small letter upsilon */
        966 => 'phi',         /* Greek small letter phi */
        967 => 'chi',         /* Greek small letter chi */
        968 => 'psi',         /* Greek small letter psi */
        969 => 'omega',       /* Greek small letter omega */
        977 => 'thetasym',    /* Greek theta symbol */
        978 => 'upsih',       /* Greek upsilon with hook symbol */
        982 => 'piv',         /* Greek pi symbol */
        8194 => 'ensp',       /* en space */
        8195 => 'emsp',       /* em space */
        8201 => 'thinsp',     /* thin space */
        8204 => 'zwnj',       /* zero width non-joiner */
        8205 => 'zwj',        /* zero width joiner */
        8206 => 'lrm',        /* left-to-right mark */
        8207 => 'rlm',        /* right-to-left mark */
        8211 => 'ndash',      /* en dash */
        8212 => 'mdash',       /* em dash */
        8216 => 'lsquo',       /* left single quotation mark */
        8217 => 'rsquo',       /* right single quotation mark */
        8218 => 'sbquo',       /* single low-9 quotation mark */
        8220 => 'ldquo',       /* left double quotation mark */
        8221 => 'rdquo',       /* right double quotation mark */
        8222 => 'bdquo',       /* double low-9 quotation mark */
        8224 => 'dagger',      /* dagger */
        8225 => 'Dagger',      /* double dagger */
        8226 => 'bull',        /* bullet */
        8230 => 'hellip',      /* horizontal ellipsis */
        8240 => 'permil',      /* per mille sign */
        8242 => 'prime',       /* prime */
        8243 => 'Prime',       /* double prime */
        8249 => 'lsaquo',      /* single left-pointing angle quotation mark */
        8250 => 'rsaquo',      /* single right-pointing angle quotation mark */
        8254 => 'oline',       /* overline */
        8260 => 'frasl',       /* fraction slash */
        8364 => 'euro',        /* euro sign */
        8465 => 'image',       /* black-letter capital i */
        8472 => 'weierp',      /* script capital pXCOMMAX Weierstrass p */
        8476 => 'real',        /* black-letter capital r */
        8482 => 'trade',       /* trademark sign */
        8501 => 'alefsym',     /* alef symbol */
        8592 => 'larr',        /* leftwards arrow */
        8593 => 'uarr',        /* upwards arrow */
        8594 => 'rarr',        /* rightwards arrow */
        8595 => 'darr',        /* downwards arrow */
        8596 => 'harr',        /* left right arrow */
        8629 => 'crarr',       /* downwards arrow with corner leftwards */
        8656 => 'lArr',        /* leftwards double arrow */
        8657 => 'uArr',        /* upwards double arrow */
        8658 => 'rArr',        /* rightwards double arrow */
        8659 => 'dArr',        /* downwards double arrow */
        8660 => 'hArr',        /* left right double arrow */
        8704 => 'forall',      /* for all */
        8706 => 'part',        /* partial differential */
        8707 => 'exist',       /* there exists */
        8709 => 'empty',       /* empty set */
        8711 => 'nabla',       /* nabla */
        8712 => 'isin',        /* element of */
        8713 => 'notin',       /* not an element of */
        8715 => 'ni',          /* contains as member */
        8719 => 'prod',        /* n-ary product */
        8721 => 'sum',         /* n-ary summation */
        8722 => 'minus',       /* minus sign */
        8727 => 'lowast',      /* asterisk operator */
        8730 => 'radic',       /* square root */
        8733 => 'prop',        /* proportional to */
        8734 => 'infin',       /* infinity */
        8736 => 'ang',         /* angle */
        8743 => 'and',         /* logical and */
        8744 => 'or',          /* logical or */
        8745 => 'cap',         /* intersection */
        8746 => 'cup',         /* union */
        8747 => 'int',         /* integral */
        8756 => 'there4',      /* therefore */
        8764 => 'sim',         /* tilde operator */
        8773 => 'cong',        /* congruent to */
        8776 => 'asymp',       /* almost equal to */
        8800 => 'ne',          /* not equal to */
        8801 => 'equiv',       /* identical toXCOMMAX equivalent to */
        8804 => 'le',          /* less-than or equal to */
        8805 => 'ge',          /* greater-than or equal to */
        8834 => 'sub',         /* subset of */
        8835 => 'sup',         /* superset of */
        8836 => 'nsub',        /* not a subset of */
        8838 => 'sube',        /* subset of or equal to */
        8839 => 'supe',        /* superset of or equal to */
        8853 => 'oplus',       /* circled plus */
        8855 => 'otimes',      /* circled times */
        8869 => 'perp',        /* up tack */
        8901 => 'sdot',        /* dot operator */
        8968 => 'lceil',       /* left ceiling */
        8969 => 'rceil',       /* right ceiling */
        8970 => 'lfloor',      /* left floor */
        8971 => 'rfloor',      /* right floor */
        9001 => 'lang',        /* left-pointing angle bracket */
        9002 => 'rang',        /* right-pointing angle bracket */
        9674 => 'loz',         /* lozenge */
        9824 => 'spades',      /* black spade suit */
        9827 => 'clubs',       /* black club suit */
        9829 => 'hearts',      /* black heart suit */
        9830 => 'diams',       /* black diamond suit */
    );
}