<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Session
 */

namespace Zend\Session\Config;

use Zend\Session\Config\ConfigInterface;
use Zend\Session\Exception;
use Zend\Validator\Hostname as HostnameValidator;
use Traversable;

/**
 * Standard session configuration
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage Configuration
 */
class StandardConfig implements ConfigInterface
{
    /**
     * session.name
     *
     * @var string
     */
    protected $name;

    /**
     * session.save_path
     *
     * @var string
     */
    protected $savePath;

    /**
     * session.cookie_lifetime
     *
     * @var int
     */
    protected $cookieLifetime;

    /**
     * session.cookie_path
     *
     * @var string
     */
    protected $cookiePath;

    /**
     * session.cookie_domain
     *
     * @var string
     */
    protected $cookieDomain;

    /**
     * session.cookie_secure
     *
     * @var bool
     */
    protected $cookieSecure;

    /**
     * session.cookie_httponly
     *
     * @var bool
     */
    protected $cookieHttpOnly;

    /**
     * remember_me_seconds
     *
     * @var int
     */
    protected $rememberMeSeconds;

    /**
     * session.use_cookies
     *
     * @var bool
     */
    protected $useCookies;

    /**
     * All options
     *
     * @var array
     */
    protected $options = array();


    /**
     * Set many options at once
     *
     * If a setter method exists for the key, that method will be called;
     * otherwise, a standard option will be set with the value provided via
     * {@link setOption()}.
     *
     * @param  array|Traversable $options
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter provided to %s must be an array or Traversable',
                __METHOD__
            ));
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                $this->setOption($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set an individual option
     *
     * Keys are normalized to lowercase. After setting internally, calls
     * {@link setStorageOption()} to allow further processing.
     *
     *
     * @param  string $option
     * @param  mixed $value
     * @return StandardConfig
     */
    public function setOption($option, $value)
    {
        $option                 = strtolower($option);
        $this->options[$option] = $value;
        $this->setStorageOption($option, $value);
        return $this;
    }

    /**
     * Get an individual option
     *
     * Keys are normalized to lowercase. If the option is not found, attempts
     * to retrieve it via {@link getStorageOption()}; if a value is returned
     * from that method, it will be set as the internal value and returned.
     *
     * Returns null for unfound options
     *
     * @param  string $option
     * @return mixed
     */
    public function getOption($option)
    {
        $option = strtolower($option);
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        $value = $this->getStorageOption($option);
        if (null !== $value) {
            $this->setOption($option, $value);
            return $value;
        }

        return null;
    }

    /**
     * Check to see if an internal option has been set for the key provided.
     *
     * @param  string $option
     * @return bool
     */
    public function hasOption($option)
    {
        $option = strtolower($option);
        return array_key_exists($option, $this->options);
    }

    /**
     * Set storage option in backend configuration store
     *
     * Does nothing in this implementation; others might use it to set things
     * such as INI settings.
     *
     * @param  string $storageName
     * @param  mixed $storageValue
     * @return StandardConfig
     */
    public function setStorageOption($storageName, $storageValue)
    {
        return $this;
    }

    /**
     * Retrieve a storage option from a backend configuration store
     *
     * Used to retrieve default values from a backend configuration store.
     *
     * @param  string $storageOption
     * @return mixed
     */
    public function getStorageOption($storageOption)
    {
        return null;
    }

    /**
     * Set session.save_path
     *
     * @param  string $savePath
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException on invalid path
     */
    public function setSavePath($savePath)
    {
        if (!is_dir($savePath)) {
            throw new Exception\InvalidArgumentException('Invalid save_path; not a directory');
        }
        if (!is_writable($savePath)) {
            throw new Exception\InvalidArgumentException('Invalid save_path; not writable');
        }

        $this->savePath = $savePath;
        $this->setStorageOption('save_path', $savePath);
        return $this;
    }

    /**
     * Set session.save_path
     *
     * @return string|null
     */
    public function getSavePath()
    {
        if (null === $this->savePath) {
            $this->savePath = $this->getStorageOption('save_path');
        }
        return $this->savePath;
    }

    /**
     * Set session.name
     *
     * @param  string $name
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        if (empty($this->name)) {
            throw new Exception\InvalidArgumentException('Invalid session name; cannot be empty');
        }
        $this->setStorageOption('name', $this->name);
        return $this;
    }

    /**
     * Get session.name
     *
     * @return null|string
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = $this->getStorageOption('name');
        }
        return $this->name;
    }

    /**
     * Set session.gc_probability
     *
     * @param  int $gcProbability
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setGcProbability($gcProbability)
    {
        if (!is_numeric($gcProbability)) {
            throw new Exception\InvalidArgumentException('Invalid gc_probability; must be numeric');
        }
        $gcProbability = (int) $gcProbability;
        if (1 > $gcProbability || 100 < $gcProbability) {
            throw new Exception\InvalidArgumentException('Invalid gc_probability; must be a percentage');
        }
        $this->setOption('gc_probability', $gcProbability);
        $this->setStorageOption('gc_probability', $gcProbability);
        return $this;
    }

    /**
     * Set session.gc_divisor
     *
     * @param  int $gcDivisor
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setGcDivisor($gcDivisor)
    {
        if (!is_numeric($gcDivisor)) {
            throw new Exception\InvalidArgumentException('Invalid gc_divisor; must be numeric');
        }
        $gcDivisor = (int) $gcDivisor;
        if (1 > $gcDivisor) {
            throw new Exception\InvalidArgumentException('Invalid gc_divisor; must be a positive integer');
        }
        $this->setOption('gc_divisor', $gcDivisor);
        $this->setStorageOption('gc_divisor', $gcDivisor);
        return $this;
    }

    /**
     * Set gc.maxlifetime
     *
     * @param  int $gcMaxlifetime
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setGcMaxlifetime($gcMaxlifetime)
    {
        if (!is_numeric($gcMaxlifetime)) {
            throw new Exception\InvalidArgumentException('Invalid gc_maxlifetime; must be numeric');
        }

        $gcMaxlifetime = (int) $gcMaxlifetime;
        if (1 > $gcMaxlifetime) {
            throw new Exception\InvalidArgumentException('Invalid gc_maxlifetime; must be a positive integer');
        }

        $this->setOption('gc_maxlifetime', $gcMaxlifetime);
        $this->setStorageOption('gc_maxlifetime', $gcMaxlifetime);
        return $this;
    }

    /**
     * Set session.cookie_lifetime
     *
     * @param  int $cookieLifetime
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setCookieLifetime($cookieLifetime)
    {
        if (!is_numeric($cookieLifetime)) {
            throw new Exception\InvalidArgumentException('Invalid cookie_lifetime; must be numeric');
        }
        if (0 > $cookieLifetime) {
            throw new Exception\InvalidArgumentException(
                'Invalid cookie_lifetime; must be a positive integer or zero'
            );
        }

        $this->cookieLifetime = (int) $cookieLifetime;
        $this->setStorageOption('cookie_lifetime', $this->cookieLifetime);
        return $this;
    }

    /**
     * Get session.cookie_lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        if (null === $this->cookieLifetime) {
            $this->cookieLifetime = $this->getStorageOption('cookie_lifetime');
        }
        return $this->cookieLifetime;
    }

    /**
     * Set session.cookie_path
     *
     * @param  string $cookiePath
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setCookiePath($cookiePath)
    {
        $cookiePath = (string) $cookiePath;

        $test = parse_url($cookiePath, PHP_URL_PATH);
        if ($test != $cookiePath || '/' != $test[0]) {
            throw new Exception\InvalidArgumentException('Invalid cookie path');
        }

        $this->cookiePath = $cookiePath;
        $this->setStorageOption('cookie_path', $cookiePath);
        return $this;
    }

    /**
     * Get session.cookie_path
     *
     * @return string
     */
    public function getCookiePath()
    {
        if (null === $this->cookiePath) {
            $this->cookiePath = $this->getStorageOption('cookie_path');
        }
        return $this->cookiePath;
    }

    /**
     * Set session.cookie_domain
     *
     * @param  string $cookieDomain
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setCookieDomain($cookieDomain)
    {
        if (!is_string($cookieDomain)) {
            throw new Exception\InvalidArgumentException('Invalid cookie domain: must be a string');
        }

        $validator = new HostnameValidator(HostnameValidator::ALLOW_ALL);

        if (!empty($cookieDomain) && !$validator->isValid($cookieDomain)) {
            throw new Exception\InvalidArgumentException(
                'Invalid cookie domain: ' . implode('; ', $validator->getMessages())
            );
        }

        $this->cookieDomain = $cookieDomain;
        $this->setStorageOption('cookie_domain', $cookieDomain);
        return $this;
    }

    /**
     * Get session.cookie_domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        if (null === $this->cookieDomain) {
            $this->cookieDomain = $this->getStorageOption('cookie_domain');
        }
        return $this->cookieDomain;
    }

    /**
     * Set session.cookie_secure
     *
     * @param  bool $cookieSecure
     * @return StandardConfiguration
     */
    public function setCookieSecure($cookieSecure)
    {
        $this->cookieSecure = (bool) $cookieSecure;
        $this->setStorageOption('cookie_secure', $this->cookieSecure);
        return $this;
    }

    /**
     * Get session.cookie_secure
     *
     * @return bool
     */
    public function getCookieSecure()
    {
        if (null === $this->cookieSecure) {
            $this->cookieSecure = $this->getStorageOption('cookie_secure');
        }
        return $this->cookieSecure;
    }

    /**
     * Set session.cookie_httponly
     *
     * case sensitive method lookups in setOptions means this method has an
     * unusual casing
     *
     * @param  bool $cookieHttpOnly
     * @return StandardConfig
     */
    public function setCookieHttpOnly($cookieHttpOnly)
    {
        $this->cookieHttpOnly = (bool) $cookieHttpOnly;
        $this->setStorageOption('cookie_httponly', $this->cookieHttpOnly);
        return $this;
    }

    /**
     * Get session.cookie_httponly
     *
     * @return bool
     */
    public function getCookieHttpOnly()
    {
        if (null === $this->cookieHttpOnly) {
            $this->cookieHttpOnly = $this->getStorageOption('cookie_httponly');
        }
        return $this->cookieHttpOnly;
    }

    /**
     * Set session.use_cookies
     *
     * @param  bool $useCookies
     * @return StandardConfiguration
     */
    public function setUseCookies($useCookies)
    {
        $this->useCookies = (bool) $useCookies;
        $this->setStorageOption('use_cookies', $this->useCookies);
        return $this;
    }

    /**
     * Get session.use_cookies
     *
     * @return bool
     */
    public function getUseCookies()
    {
        if (null === $this->useCookies) {
            $this->useCookies = $this->getStorageOption('use_cookies');
        }
        return $this->useCookies;
    }

    /**
     * Set session.entropy_file
     *
     * @param  string $entropyFile
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setEntropyFile($entropyFile)
    {
        if (is_dir($entropyFile) || !is_readable($entropyFile)) {
            throw new Exception\InvalidArgumentException('Invalid entropy_file provided');
        }
        $this->setOption('entropy_file', $entropyFile);
        $this->setStorageOption('entropy_file', $entropyFile);
        return $this;
    }

    /**
     * set session.entropy_length
     *
     * @param  int $entropyLength
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setEntropyLength($entropyLength)
    {
        if (!is_numeric($entropyLength)) {
            throw new Exception\InvalidArgumentException('Invalid entropy_length; must be numeric');
        }
        if (0 > $entropyLength) {
            throw new Exception\InvalidArgumentException('Invalid entropy_length; must be a positive integer or zero');
        }

        $this->setOption('entropy_length', $entropyLength);
        $this->setStorageOption('entropy_length', $entropyLength);
        return $this;
    }

    /**
     * Set session.cache_expire
     *
     * @param  int $cacheExpire
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheExpire($cacheExpire)
    {
        if (!is_numeric($cacheExpire)) {
            throw new Exception\InvalidArgumentException('Invalid cache_expire; must be numeric');
        }

        $cacheExpire = (int) $cacheExpire;
        if (1 > $cacheExpire) {
            throw new Exception\InvalidArgumentException('Invalid cache_expire; must be a positive integer');
        }

        $this->setOption('cache_expire', $cacheExpire);
        $this->setStorageOption('cache_expire', $cacheExpire);
        return $this;
    }

    /**
     * Set session.hash_bits_per_character
     *
     * @param  int $hashBitsPerCharacter
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setHashBitsPerCharacter($hashBitsPerCharacter)
    {
        if (!is_numeric($hashBitsPerCharacter)) {
            throw new Exception\InvalidArgumentException('Invalid hash bits per character provided');
        }
        $hashBitsPerCharacter = (int) $hashBitsPerCharacter;
        $this->setOption('hash_bits_per_character', $hashBitsPerCharacter);
        $this->setStorageOption('hash_bits_per_character', $hashBitsPerCharacter);
        return $this;
    }

    /**
     * Set remember_me_seconds
     *
     * @param  int $rememberMeSeconds
     * @return StandardConfiguration
     * @throws Exception\InvalidArgumentException
     */
    public function setRememberMeSeconds($rememberMeSeconds)
    {
        if (!is_numeric($rememberMeSeconds)) {
            throw new Exception\InvalidArgumentException('Invalid remember_me_seconds; must be numeric');
        }

        $rememberMeSeconds = (int) $rememberMeSeconds;
        if (1 > $rememberMeSeconds) {
            throw new Exception\InvalidArgumentException('Invalid remember_me_seconds; must be a positive integer');
        }

        $this->rememberMeSeconds = $rememberMeSeconds;
        $this->setStorageOption('remember_me_seconds', $rememberMeSeconds);
        return $this;
    }

    /**
     * Get remember_me_seconds
     *
     * @return int
     */
    public function getRememberMeSeconds()
    {
        if (null === $this->rememberMeSeconds) {
            $this->rememberMeSeconds = $this->getStorageOption('remember_me_seconds');
        }
        return $this->rememberMeSeconds;
    }

    /**
     * Cast configuration to an array
     *
     * @return array
     */
    public function toArray()
    {
        $options = $this->options;
        $extraOpts = array(
            'cookie_domain'       => $this->getCookieDomain(),
            'cookie_httponly'     => $this->getCookieHttpOnly(),
            'cookie_lifetime'     => $this->getCookieLifetime(),
            'cookie_path'         => $this->getCookiePath(),
            'cookie_secure'       => $this->getCookieSecure(),
            'name'                => $this->getName(),
            'remember_me_seconds' => $this->getRememberMeSeconds(),
            'save_path'           => $this->getSavePath(),
            'use_cookies'         => $this->getUseCookies(),
        );
        return array_merge($options, $extraOpts);
    }

    /**
     * Intercept get*() and set*() methods
     *
     * Intercepts getters and setters and passes them to getOption() and setOption(),
     * respectively.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException on non-getter/setter method
     */
    public function __call($method, $args)
    {
        if ('get' == substr($method, 0, 3)) {
            // Call to a getter; return matching option.
            // Transform method from MixedCase to underscore_separated.
            $option = substr($method, 3);
            $key    = $this->getCamelCaseToUnderscoreFilter()->filter($option);
            return $this->getOption($key);
        }
        if ('set' == substr($method, 0, 3)) {
            // Call to a setter; return matching option.
            // Transform method from MixedCase to underscore_separated.
            $option = substr($method, 3);
            $key    = $this->getCamelCaseToUnderscoreFilter()->filter($option);
            $value  = array_shift($args);
            return $this->setOption($key, $value);
        }
        throw new Exception\BadMethodCallException(sprintf('Method "%s" does not exist', $method));
    }
}
