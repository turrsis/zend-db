<?php
namespace Zend\Db\Sql\Helper;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\Stdlib\ArrayUtils;

class HelperManager extends AbstractPluginManager
{
    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = [
        'nestedset'  => 'Zend\Db\Sql\Helper\Tree\NestedSet',
    ];
    protected $shared = [
        'nestedset' => false,
    ];

    protected $config = null;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        $optionsName = null;
        if (is_string($options)) {
            $optionsName = $options;
            $options = [];
        } elseif (is_array($name)) {
            list($name, $optionsName) = $name;
        }

        if ($optionsName) {
            $config = $this->getConfig();
            if (isset($config['options'][$optionsName])) {
                $options = ArrayUtils::merge($options, $config['options'][$optionsName]);
            }
            if (isset($options['helpers'][$name])) {
                $name = $options['helpers'][$name];
            }
        }
        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    public function validatePlugin($plugin)
    {
        return;
    }

    protected function getConfig()
    {
        if ($this->config === null) {
            $config = $this->getServiceLocator()->get('config');
            if (isset($config['sql_helper'])) {
                $this->config = $config['sql_helper'];
            } else {
                $this->config = [];
            }
        }
        return $this->config;
    }
}
