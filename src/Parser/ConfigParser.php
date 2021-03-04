<?php

namespace MultiIvr\Parser;

use MultiIvr\Config\Config;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;

/**
 * Interface ConfigParser
 * @package MultiIvr\Parser
 */
interface ConfigParser
{
    /**
     * @param string $configRawData
     * @return Config
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function parse(string $configRawData): Config;
}
