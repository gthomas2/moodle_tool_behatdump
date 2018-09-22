<?php

namespace tool_behatdump\interfaces;

defined('MOODLE_INTERNAL') || die;

interface rest_methods {
    const GET = 'get';
    const POST = 'post';
    const DELETE = 'delete';
    const PATCH = 'patch';
    const PUT = 'put';
}