<?php
define('CODECEPT_ROOT', codecept_root_dir());
define('CODECEPT_DATA_DIR', codecept_data_dir());
define('APP_DIR', CODECEPT_ROOT . 'src' . DIRECTORY_SEPARATOR);
define('TEST_STUB_DIR', CODECEPT_DATA_DIR . '_stub' . DIRECTORY_SEPARATOR);
define('TEST_OUTPUT_DIR', CODECEPT_DATA_DIR . '_output' . DIRECTORY_SEPARATOR);

error_reporting(-1);

setlocale(LC_ALL, 'en_GB.utf-8');

if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
    mb_substitute_character('none');
}

clearstatcache();

if (extension_loaded('xdebug')) {
    ini_set('xdebug.cli_color', 1);
    ini_set('xdebug.collect_params', 0);
    ini_set('xdebug.dump_globals', 'on');
    ini_set('xdebug.show_local_vars', 'on');
    ini_set('xdebug.max_nesting_level', 100);
    ini_set('xdebug.var_display_max_depth', 4);
}
