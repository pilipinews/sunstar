<?php

namespace Pilipinews\Website\Sunstar;

use Nacmartin\PhpExecJs\PhpExecJs as Executor;
use Nacmartin\PhpExecJs\Runtime\ExternalRuntime;

/**
 * Sunstar Script Evaluator
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Script
{
    /**
     * @var \Nacmartin\PhpExecJs\PhpExecJs
     */
    protected $executor;

    /**
     * Initializes the evaluator instance.
     */
    public function __construct()
    {
        $binaries = array('node', 'nodejs');

        $runtime = new ExternalRuntime(null, $binaries);

        $this->executor = new Executor($runtime);
    }

    /**
     * Evaluates the given Javascript code.
     *
     * @param  string $script
     * @return string
     */
    public function evaluate($script)
    {
        return $this->executor->evalJs($script);
    }
}
