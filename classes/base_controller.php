<?php

namespace tool_behatdump;

defined('MOODLE_INTERNAL') || die;

use tool_behatdump\interfaces\rest_methods;

use coding_exception;
use stdClass;
use Exception;

/**
 * Controller for working with REST APIs.
 * See why I don't use core webservices for REST APIs + AJAX here:
 * https://medium.com/@brudinie/why-im-not-using-moodle-web-services-for-ajax-e8e207f5b1d1.
 * Class controller
 * @package tool_behatdump
 */
abstract class base_controller implements rest_methods{

    protected $method;
    protected $action;
    protected $data;

    /**
     * Implement this in your controller class to return an instance of your controller.
     * @param null|string $method
     * @param null|string $action
     * @param null|array|stdClass $data
     * @return mixed
     */
    abstract public function instance ($method = null, $action = null, $data = null);

    /**
     * controller constructor.
     * @param null|string $method
     * @param null|string $action
     * @param null|array|stdClass $data
     * @throws \coding_exception
     */
    protected function __construct($method = null, $action = null, $data = null) {
        if ($action === null) {
            $action = required_param('action', PARAM_ALPHANUMEXT);
        }

        if ($method === null) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }

        if ($method === self::PUT || $method === self::PATCH) {
            // Using $_POST is a bit hacky but it means we get to use moodle optional_param, required_param, etc..
            parse_str(file_get_contents('php://input'), $_POST);
        }

        if (empty($data)) {
            $data = $this->get_raw_request_data($method);
        }

        if (is_array($data)) {
            $data = (object) $data;
        } else if(!$data instanceof stdClass) {
            throw new coding_exception('$data must be an array or an instance of stdClass');
        }

        $this->method = $method;
        $this->action = $action;
        $this->data = $data;
    }

    /**
     * E.g. __NAMESPACE__.'\\webservice\\' - must always end in backslash.
     * @return string
     */
    abstract protected function service_namespace();

    /**
     * @return array
     */
    protected function request_headers() {
        static $return = null;

        if ($return !== null) {
            return $return;
        }
        $return = [];
        $wlist = [
          'CONTENT_TYPE'
            // TODO - consider adding to this white list.
        ];
        foreach($_SERVER as $key => $val) {
            if (stripos($key, "HTTP_") === 0) {
                $return[$key] = $val;
            } else if (in_array($key, $wlist)) {
                $return[$key] = $val;
            }
        }
        return $return;
    }

    /**
     * Utility function to see if a request header key is equal to a specific value
     * @param string $key
     * @param string $val
     * @return bool
     */
    protected function header_key_equals($key, $val) {
        $headers = $this->request_headers();
        if (!isset($headers[$key])) {
            throw new coding_exception('Request headers do not contain key "'.$key.'"', var_export($headers, true));
        }
        $headerval = $headers[$key];

        $headerval = strtolower($headerval);
        $headerval = trim($headerval);
        $val = strtolower($val);
        $val = trim($val);
        return $headerval === $val;
    }

    /**
     * Note this gets raw request data - you are responsible for cleaning the variables in your service!
     * @return mixed|stdClass
     * @throws coding_exception
     */
    protected function get_raw_request_data($method) {
        $data = new stdClass();

        $bodymethods = [self::POST, self::PUT, self::PATCH];
        if (in_array($method, $bodymethods)) {
            if ($this->header_key_equals('CONTENT_TYPE', 'application/json')) {
                $json = file_get_contents('php://input');
                if (empty($json)) {
                    return $data;
                }
                $data = json_decode($json);
                if ($data === null) {
                    throw new coding_exception('Request data does not appear to contain valid json', $json);
                }
            } else {
                $data = $_POST;
            }
        } else {
            $data = $_GET;
        }

        return $data;
    }

    protected function call_method_action($method, $action, $data = null) {
        $possibleservice = $this->service_namespace().$action;
        if (class_exists($possibleservice)) {
            $service = new $possibleservice();
            return $service->invoke_method($method, $action, $data);
        }
        $methodname = $method.'_'.$action;
        if (method_exists($this, $methodname)) {
            return $this->$methodname($data);
        }
        throw new coding_exception('Failed to call a suitable method for '.$methodname);
    }

    public function invoke() {
        try {
            $result = $this->call_method_action($this->method, $this->action, $this->data);
            echo json_encode($result);
            die;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            throw $e;
        }
    }

}