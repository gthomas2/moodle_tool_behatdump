<?php

namespace tool_behatdump;

defined('MOODLE_INTERNAL') || die;

class controller extends base_controller {

    public function instance($method = null, $action = null, $data = null) {
        static $instance = null;
        if ($instance !== null) {
            return $instance;
        } else {
            $class = __CLASS__;
            return new $class($method, $action, $data);
        }
    }

    protected function service_namespace() {
        return __NAMESPACE__.'\\webservice\\';
    }
}