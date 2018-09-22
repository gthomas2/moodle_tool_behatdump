<?php

namespace tool_behatdump\webservice;

defined('MOODLE_INTERNAL') || die;

use moodle_exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveCallbackFilterIterator;
use SplFileInfo;
use stdClass;

class dumplist extends base_webservice {

    protected function get_dumplist_input_params() {
        return [
            'query' => self::PARAM_JSON,
            'query.limit' => PARAM_INT,
            'query.offset' => PARAM_INT,
            'query.sort' => PARAM_ALPHANUMEXT,
            'query.order' => PARAM_ALPHA,
            'query.filter' => PARAM_TEXT
        ];
    }

    private function get_scenario_step_from_file($filename) {
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('-_', $filename);
        $scenario = array_shift($parts);
        $step = implode('-_', $parts);
        $scenario = str_replace('-', ' ', $scenario);
        $step = str_replace('-', ' ', $step);
        return [$scenario, $step];
    }

    private function get_artifact_link($dir, $filename) {
        global $CFG;

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $images = ['png','jpg','jpeg','gif', 'webp'];
        // Serving the dump from the behat site allows scripts to work.
        $moodleurl = new \moodle_url($CFG->behat_wwwroot.'/admin/tool/behatdump/artifact.php', ['dir' => $dir, 'filename' => $filename]);
        if (in_array($ext, $images)) {
            $artifactstr = get_string('viewimage', 'tool_behatdump');
        } else if ($ext === 'html') {
            $artifactstr = get_string('viewhtml', 'tool_behatdump');
        } else {
            $artifactstr = get_string('unknownartifact', 'tool_behatdump');
        }
        return '<a class="artifactlink" target="_blank" href="'.$moodleurl.'">'.$artifactstr.'</a>';
    }

    private function query_sort(array &$bydirstep, $field, $direction = 'desc') {
        if ($field === 'date') {
            $field = 'datesort';
        }
        usort($bydirstep, function($a, $b) use ($field, $direction) {
            if ($a[$field] == $b[$field]) {
                return 0;
            }
            if ($direction === 'desc') {
                return ($a[$field] < $b[$field]) ? -1 : 1;
            } else {
                return ($a[$field] > $b[$field]) ? -1 : 1;
            }
        });
    }

    private function get_step_files(RecursiveIteratorIterator $ri, stdClass $query = null) {

        $bydirstep = [];
        $dir = '';
        $dirtime = null;

        /**
         * @var SplFileInfo $val
         */
        foreach ($ri as $key => $val) {
            $filename = $val->getFilename();
            if ($val->isDir()) {
                $dir = $filename;
                $dirtime = $val->getMTime();
            }
            if ($val->isFile() && stripos($filename, '.') !== 0) {
                list ($scenario, $step) = $this->get_scenario_step_from_file($filename);
                $dirkey = md5($dir.$scenario.$step);
                if (!isset($bydirstep[$dirkey])) {
                    $bydirstep[$dirkey] = [
                        'dir' => $dir,
                        'scenario' => $scenario,
                        'step' => $step,
                        'datesort' => $dirtime,
                        'date' => userdate($dirtime),
                        'artifacts' => ''
                    ];
                }

                $bydirstep[$dirkey]['artifacts'].= $this->get_artifact_link($dir, $filename);
            }
        }

        if ($query) {
            $this->query_sort($bydirstep, $query->sort, $query->order);
        } else {
            $this->query_sort($bydirstep, 'dir', 'asc');
        }

        return array_values($bydirstep);
    }

    public function get_dumplist($data) {
        global $CFG;
        if (empty($CFG->behat_faildump_path)) {
            throw new moodle_exception('error:behat_faildump_path_empty', 'tool_behatdump');
        }

        $fi = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($CFG->behat_faildump_path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

        $files = $this->get_step_files($fi, $data->query);
        $total = count($files);

        if ($data->query) {
            $files = array_slice($files, $data->query->offset, $data->query->limit);
        }

        $data = [
            'columns' => [
                [
                    'title' => 'Dir',
                    'field' => 'dir',
                    'sortable' => true,
                    'thComp' => null,
                    'tdComp' => null
                ],
                [
                    'title' => 'Scenario',
                    'field' => 'scenario',
                    'sortable' => true,
                    'thComp' => null,
                    'tdComp' => null
                ],
                [
                    'title' => 'Step',
                    'field' => 'step',
                    'sortable' => true,
                    'thComp' => null,
                    'tdComp' => null
                ],
                [
                    'title' => 'Artifacts',
                    'field' => 'artifacts',
                    'sortable' => false,
                    'thComp' => null,
                    'tdComp' => 'tdHTML'
                ],
                [
                    'title' => 'Date',
                    'field' => 'date',
                    'sortable' => true,
                    'thComp' => null,
                    'tdComp' => null
                ]
            ],
            'data' => $files,
            'total' => $total,
            'query' => [
                'limit' => 10,
                'offset' => 0,
                'sort' => null,
                'order' => null,
                'filter' => null
            ]
        ];
        return $data;
    }
}
