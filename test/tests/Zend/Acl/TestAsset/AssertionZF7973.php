<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Acl
 */

namespace ZendTest\Acl\TestAsset;

use Zend\Acl\Assertion\AssertionInterface,
    Zend\Acl;

class AssertionZF7973 implements AssertionInterface {
    public function assert(Acl\Acl $acl, Acl\Role\RoleInterface $role = null, Acl\Resource\ResourceInterface $resource = null, $privilege = null)
    {
        if($privilege != 'privilege') {
            return false;
        }

        return true;
    }
}
