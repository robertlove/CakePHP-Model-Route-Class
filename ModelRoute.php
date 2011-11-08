<?php
/**
 * CakePHP Model Route
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
 * @category   CategoryName
 * @package    PackageName
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
     * Fields
     *
     * The fields used to identify a route. Defaults to "name" and "value".
     *
     * @var array
     */
    public $fields = array(
        'name' => 'name',
        'value' => 'value'
    );

    /**
     * Model
     *
     * An instance of the route model named in $modelName.
     *
     * @var object
     */
    public $Model = null;

    /**
     * Model Name
     *
     * The name of the route model. Defaults to "Route".
     *
     * @var string
     */
    public $modelName = 'Route';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        App::import('Model', $this->modelName);
        $this->Model = new $this->modelName();
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
        $value = implode('/', array_filter($params));
        $name = $this->Model->field($this->fields['name'], array(
            $this->fields['value'] => $value
        ));
        if ($name) {
            return $name;
        }
        return false;
    }

    /**
     * Parse
     *
     * @param string $url The url to attempt to parse
     * @return mixed Boolean false on failure, otherwise an array of parameters
     */
    public function parse($url)
    {
        $value = $this->Model->field($this->fields['value'], array(
            $this->fields['name'] => substr($url, 1)
        ));
        if ($value) {
            $values = explode('/', $value);
            $count = count($values);
            if ($count >= 2) {
                $params['controller'] = $values[0];
                $params['action'] = $values[1];
                $params['plugin'] = null;
                $params['pass'] = $params['named'] = array();
                for ($i = 2; $i < $count; $i++) {
                    $params['pass'][] = $values[$i];
                }
                return $params;
            }
        }
        return false;
    }
}