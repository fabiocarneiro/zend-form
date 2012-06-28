<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Json
 */

namespace Zend\Json\Server\Smd;
use Zend\Json\Server\Smd;
use Zend\Json\Server;
use Zend\Json\Server\Exception\InvalidArgumentException;

/**
 * Create Service Mapping Description for a method
 *
 * @todo       Revised method regex to allow NS; however, should SMD be revised to strip PHP NS instead when attaching functions?
 * @package    Zend_Json
 * @subpackage Server
 */
class Service
{
    /**#@+
     * Service metadata
     * @var string
     */
    protected $_envelope  = Smd::ENV_JSONRPC_1;
    protected $_name;
    protected $_return;
    protected $_target;
    protected $_transport = 'POST';
    /**#@-*/

    /**
     * Allowed envelope types
     * @var array
     */
    protected $_envelopeTypes = array(
        Smd::ENV_JSONRPC_1,
        Smd::ENV_JSONRPC_2,
    );

    /**
     * Regex for names
     * @var string
     */
    protected $_nameRegex = '/^[a-z][a-z0-9.\\\\_]+$/i';

    /**
     * Parameter option types
     * @var array
     */
    protected $_paramOptionTypes = array(
        'name'        => 'is_string',
        'optional'    => 'is_bool',
        'default'     => null,
        'description' => 'is_string',
    );

    /**
     * Service params
     * @var array
     */
    protected $_params = array();

    /**
     * Mapping of parameter types to JSON-RPC types
     * @var array
     */
    protected $_paramMap = array(
        'any'     => 'any',
        'arr'     => 'array',
        'array'   => 'array',
        'assoc'   => 'object',
        'bool'    => 'boolean',
        'boolean' => 'boolean',
        'dbl'     => 'float',
        'double'  => 'float',
        'false'   => 'boolean',
        'float'   => 'float',
        'hash'    => 'object',
        'integer' => 'integer',
        'int'     => 'integer',
        'mixed'   => 'any',
        'nil'     => 'null',
        'null'    => 'null',
        'object'  => 'object',
        'string'  => 'string',
        'str'     => 'string',
        'struct'  => 'object',
        'true'    => 'boolean',
        'void'    => 'null',
    );

    /**
     * Allowed transport types
     * @var array
     */
    protected $_transportTypes = array(
        'POST',
    );

    /**
     * Constructor
     *
     * @param  string|array $spec
     * @return void
     * @throws Zend\Json\Server\Exception\InvalidArgumentException if no name provided
     */
    public function __construct($spec)
    {
        if (is_string($spec)) {
            $this->setName($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        }

        if (null == $this->getName()) {
            throw new InvalidArgumentException('SMD service description requires a name; none provided');
        }
    }

    /**
     * Set object state
     *
     * @param  array $options
     * @return Zend\Json\Server\Smd\Service
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            if ('options' == strtolower($key)) {
                continue;
            }
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set service name
     *
     * @param  string $name
     * @return Zend\Json\Server\Smd\Service
     * @throws Zend\Json\Server\Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        $name = (string) $name;
        if (!preg_match($this->_nameRegex, $name)) {
            throw new InvalidArgumentException("Invalid name '{$name} provided for service; must follow PHP method naming conventions");
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set Transport
     *
     * Currently limited to POST
     *
     * @param  string $transport
     * @return Zend\Json\Server\Smd\Service
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->_transportTypes)) {
            throw new InvalidArgumentException("Invalid transport '{$transport}'; please select one of (" . implode(', ', $this->_transportTypes) . ')');
        }

        $this->_transport = $transport;
        return $this;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->_transport;
    }

    /**
     * Set service target
     *
     * @param  string $target
     * @return Zend\Json\Server\Smd\Service
     */
    public function setTarget($target)
    {
        $this->_target = (string) $target;
        return $this;
    }

    /**
     * Get service target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Set envelope type
     *
     * @param  string $envelopeType
     * @return Zend\Json\Server\Smd\Service
     */
    public function setEnvelope($envelopeType)
    {
        if (!in_array($envelopeType, $this->_envelopeTypes)) {
            throw new InvalidArgumentException("Invalid envelope type '{$envelopeType}'; please specify one of (" . implode(', ', $this->_envelopeTypes) . ')');
        }

        $this->_envelope = $envelopeType;
        return $this;
    }

    /**
     * Get envelope type
     *
     * @return string
     */
    public function getEnvelope()
    {
        return $this->_envelope;
    }

    /**
     * Add a parameter to the service
     *
     * @param  string|array $type
     * @param  array $options
     * @param  int|null $order
     * @return Zend\Json\Server\Smd\Service
     */
    public function addParam($type, array $options = array(), $order = null)
    {
        if (is_string($type)) {
            $type = $this->_validateParamType($type);
        } elseif (is_array($type)) {
            foreach ($type as $key => $paramType) {
                $type[$key] = $this->_validateParamType($paramType);
            }
        } else {
            throw new InvalidArgumentException('Invalid param type provided');
        }

        $paramOptions = array(
            'type' => $type,
        );
        foreach ($options as $key => $value) {
            if (in_array($key, array_keys($this->_paramOptionTypes))) {
                if (null !== ($callback = $this->_paramOptionTypes[$key])) {
                    if (!$callback($value)) {
                        continue;
                    }
                }
                $paramOptions[$key] = $value;
            }
        }

        $this->_params[] = array(
            'param' => $paramOptions,
            'order' => $order,
        );

        return $this;
    }

    /**
     * Add params
     *
     * Each param should be an array, and should include the key 'type'.
     *
     * @param  array $params
     * @return Zend\Json\Server\Smd\Service
     */
    public function addParams(array $params)
    {
        ksort($params);
        foreach ($params as $options) {
            if (!is_array($options)) {
                continue;
            }
            if (!array_key_exists('type', $options)) {
                continue;
            }
            $type  = $options['type'];
            $order = (array_key_exists('order', $options)) ? $options['order'] : null;
            $this->addParam($type, $options, $order);
        }
        return $this;
    }

    /**
     * Overwrite all parameters
     *
     * @param  array $params
     * @return Zend\Json\Server\Smd\Service
     */
    public function setParams(array $params)
    {
        $this->_params = array();
        return $this->addParams($params);
    }

    /**
     * Get all parameters
     *
     * Returns all params in specified order.
     *
     * @return array
     */
    public function getParams()
    {
        $params = array();
        $index  = 0;
        foreach ($this->_params as $param) {
            if (null === $param['order']) {
                if (array_search($index, array_keys($params), true)) {
                    ++$index;
                }
                $params[$index] = $param['param'];
                ++$index;
            } else {
                $params[$param['order']] = $param['param'];
            }
        }
        ksort($params);
        return $params;
    }

    /**
     * Set return type
     *
     * @param  string|array $type
     * @return Zend\Json\Server\Smd\Service
     */
    public function setReturn($type)
    {
        if (is_string($type)) {
            $type = $this->_validateParamType($type, true);
        } elseif (is_array($type)) {
            foreach ($type as $key => $returnType) {
                $type[$key] = $this->_validateParamType($returnType, true);
            }
        } else {
            throw new InvalidArgumentException("Invalid param type provided ('" . gettype($type) . "')");
        }
        $this->_return = $type;
        return $this;
    }

    /**
     * Get return type
     *
     * @return string|array
     */
    public function getReturn()
    {
        return $this->_return;
    }

    /**
     * Cast service description to array
     *
     * @return array
     */
    public function toArray()
    {
        $name       = $this->getName();
        $envelope   = $this->getEnvelope();
        $target     = $this->getTarget();
        $transport  = $this->getTransport();
        $parameters = $this->getParams();
        $returns    = $this->getReturn();

        if (empty($target)) {
            return compact('envelope', 'transport', 'parameters', 'returns');
        }

        return $paramInfo = compact('envelope', 'target', 'transport', 'parameters', 'returns');
    }

    /**
     * Return JSON encoding of service
     *
     * @return string
     */
    public function toJson()
    {
        $service = array($this->getName() => $this->toArray());
        return \Zend\Json\Json::encode($service);
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Validate parameter type
     *
     * @param  string $type
     * @return true
     * @throws Zend\Json\Server\Exception
     */
    protected function _validateParamType($type, $isReturn = false)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException("Invalid param type provided ('{$type}')");
        }

        if (!array_key_exists($type, $this->_paramMap)) {
            $type = 'object';
        }

        $paramType = $this->_paramMap[$type];
        if (!$isReturn && ('null' == $paramType)) {
            throw new InvalidArgumentException("Invalid param type provided ('{$type}')");
        }

        return $paramType;
    }
}
