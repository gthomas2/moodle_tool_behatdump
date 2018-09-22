<?php

namespace tool_behatdump\webservice;

defined('MOODLE_INTERNAL') || die();

use tool_behatdump\interfaces\rest_methods;

use stdClass;
use coding_exception;

abstract class base_webservice  implements rest_methods {

    const PARAM_JSON = 'json';

    /**
     * Defines the input params for a specific method.
     *
     * @param $method
     * @param $action
     * @return array (key => param_type) - e.g. ['age' => PARAM_INT]
     */
    protected function input_params($method, $action) {
        $methodname = $method.'_'.$action.'_input_params';
        if (method_exists($this, $methodname)) {
            return $this->$methodname();
        } else {
            throw new coding_exception('Method for processing params not defined '.$methodname);
        }
    }

    /**
     * Get a property of an object using dot notation instead of fat arrows.
     * @param object $obj
     * @param string $pointprop
     * @return mixed
     */
    private function get_point_prop($obj, $pointprop) {
        $props = explode('.', $pointprop);
        $currentprop = $props[0];
        if (empty($currentprop)) {
            return $obj;
        }
        if (count($props) === 1) {
            return $obj->$currentprop;
        } else {
            array_shift($props);
            $pointprop = implode('.', $props);
            return $this->get_point_prop($obj->$currentprop, $pointprop);
        }
    }

    /**
     * Set a property of an object using dot notation instead of fat arrows.
     * @param stdClass $obj
     * @param string $pointprop
     * @param mixed $val
     * @return mixed
     */
    function set_point_prop($obj, $pointprop, $val) {
        $props = explode('.', $pointprop);
        $currentprop = $props[0];
        if (empty($currentprop)) {
            $obj = $val;
            return $obj;
        }
        if (count($props) === 1) {
            $obj->$currentprop = $val;
            return $obj->$currentprop;
        } else {
            array_shift($props);
            $pointprop = implode('.', $props);
            return $this->set_point_prop($obj->$currentprop, $pointprop, $val);
        }
    }

    /**
     * Clean any data passed in as all data that get's passed in to invoke method comes in raw.
     *
     * @param string $method
     * @param stdClass $data
     * @param null | stdClass $data
     * @param null | array $params
     * @return stdClass $data
     */
    protected function clean_data($method, $action, stdClass $data = null, $params = null) {
        if (empty($data)) {
            return $data;
        }

        if ($params === null) {
            $params = $this->input_params($method, $action);
        }
        $return = (object) [];
        foreach ($params as $key => $paramtype) {
            if (strpos($key, '.')) {
                // Working with json properties.
                $value = $this->get_point_prop($data, $key);
                $this->set_point_prop($data, $key, clean_param($value, $paramtype));
                continue;
            }
            $value = $data->$key;
            if ($paramtype === self::PARAM_JSON) {
                $json = $value;
                if (is_string($json)) {
                    $json = json_decode($json);
                    $data->$key = $json;
                }
            } else {
                $data->$key = clean_param($data->$key, $paramtype);
            }
        }

        foreach ($params as $key => $paramtype) {
            if (strpos($key, '.')) {
                continue;
            }
            $return->$key = $data->$key;
        }

        return $return;
    }

    /**
     * Invoke method for desired method (get, post, patch, etc) and action.
     * @param string $method
     * @param string $action
     * @param null|stdClass $data
     * @return array|stdClass
     * @throws coding_exception
     */
    public function invoke_method($method, $action, $data) {
        $methodname = $method.'_'.$action;
        if (!method_exists($this, $methodname)) {
            $msg = 'Web service "'.get_class($this).'" has not implemented desired method / action '.$methodname;
            throw new coding_exception($msg);
        }
        $params = $this->input_params($method, $action);
        $data = $this->clean_data($method, $action, $data, $params);
        return $this->$methodname($data);
    }

}