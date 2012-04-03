<?php

namespace Zend\Module\Listener;

interface ConfigMerger
{
    /**
     * getMergedConfig
     *
     * @param bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true);

    /**
     * setMergedConfig
     *
     * @param array $config
     * @return ConfigMerger
     */
    public function setMergedConfig(array $config);
}
