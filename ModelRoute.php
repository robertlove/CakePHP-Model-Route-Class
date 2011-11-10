<?php
/**
 * Model Route
 *
 * A CakePHP class for forward and reverse routing of routes accessible via a
 * model. For example, routes stored in a database table.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to the MIT License that is available
 * through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/mit-license.php.
 *
 * @author     Robert Love <robert.love@signified.com.au>
 * @copyright  Copyright 2011, Signified (http://signified.com.au/)
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version    1.0
 * @link       https://github.com/Signified/CakePHP-Model-Route-Class
 * @see        CakeRoute::match(), CakeRoute::parse()
 * @since      File available since Release 2.0
 */
class ModelRoute extends CakeRoute
{
    /**
     * Settings for this object.
     *
     * - `fields` The fields to use to identify a route by.
     * - `model` The model name of the Route, defaults to Route.
     * - `scope` Additional conditions to use when looking up a route,
     *    e.g. `array('Route.active' => 1).`
     *
     * @var array
     */
    public $settings = array(
        'fields' => array(
            'name' => 'name',
            'value' => 'value'
        ),
        'model' => 'Route',
        'scope' => array()
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($settings = array())
    {
        $this->settings = Set::merge($this->settings, $settings);
    }

    /**
     * Match
     *
     * @param array $url An array of parameters to check matching with
     * @return mixed Either a string url for the parameters if they match or false
     */
    public function match($url)
    {
        if (empty($url)) {
            return false;
        }
        $params = array(
            $url['plugin'],
            $url['controller'],
            $url['action']
        );
        unset($url['plugin']);
        unset($url['controller']);
        unset($url['action']);
        ksort($url, SORT_NUMERIC);
        foreach ($url as $val) {
            $params[] = $val;
        }
        $conditions = array(
            $this->settings['model'] . '.' . $this->settings['fields']['value'] => implode('/', array_filter($params)),
        );
        if (!empty($this->settings['scope'])) {
            $conditions = array_merge($conditions, $this->settings['scope']);
        }
        App::import('Model', $this->settings['model']);
        $result = ClassRegistry::init($this->settings['model'])->field($this->settings['fields']['name'], $conditions);
        if (empty($result)) {
            return false;
        }
        return $result;
    }

    /**
     * Parse
     *
     * @param string $url The url to attempt to parse
     * @return mixed Boolean false on failure, otherwise an array of parameters
     */
    public function parse($url)
    {
        $conditions = array(
            $this->settings['model'] . '.' . $this->settings['fields']['name'] => substr($url, 1),
        );
        if (!empty($this->settings['scope'])) {
            $conditions = array_merge($conditions, $this->settings['scope']);
        }
        App::import('Model', $this->settings['model']);
        $result = ClassRegistry::init($this->settings['model'])->field($this->settings['fields']['value'], $conditions);
        if (empty($result)) {
            return false;
        }
        $parts = explode('/', $result);
        $count = count($parts);
        if ($count >= 2) {
            $params['controller'] = $parts[0];
            $params['action'] = $parts[1];
            $params['plugin'] = null;
            $params['pass'] = $params['named'] = array();
            for ($i = 2; $i < $count; $i++) {
                $params['pass'][] = $parts[$i];
            }
            return $params;
        }
        return false;
    }
}