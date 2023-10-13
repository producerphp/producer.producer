<?php
use PhpStyler\Config;
use PhpStyler\Files;
use PhpStyler\Styler;

return new Config(
    cache: __DIR__ . '/.php-styler.cache',
    files: new Files(
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ),
    styler: new Styler(
        eol: "\n",
    ),
);
