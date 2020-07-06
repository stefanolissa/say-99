<?php

/*
  Plugin Name: Say 99
  Plugin URI: http://www.thenewsletterplugin.com/extensions/autoresponder-extension
  Description: None
  Version: 1.0.0
  Author: Stefano Lissa
  Author URI: http://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

if (!defined('SAY99_CRON'))
    define('SAY99_CRON', true);
if (!defined('SAY99_LOG_APPEND'))
    define('SAY99_LOG_APPEND', false);
if (!defined('SAY99_ADMIN'))
    define('SAY99_ADMIN', true);
if (!defined('SAY99_AJAX'))
    define('SAY99_AJAX', false);
if (!defined('SAY99_MAIN'))
    define('SAY99_MAIN', false);
if (!defined('SAY99_ANALYZE_FUNCTIONS'))
    define('SAY99_ANALYZE_FUNCTIONS', false);

function echo_mem() {
    echo memory_get_usage(), "\n";
}

class Say99 {

    const SEPARATOR = '=========================================================';

    var $log_file;
    var $current_mem;
    var $function_mem;

    public function __construct() {

        wp_mkdir_p(WP_CONTENT_DIR . '/logs/say-99');

        if (defined('DOING_CRON') && DOING_CRON) {
            if (!SAY99_CRON)
                return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/cron.txt';
        } elseif (defined('DOING_AJAX') && DOING_AJAX) {
            if (!SAY99_AJAX)
                return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/ajax.txt';
        } elseif (is_admin()) {
            if (!SAY99_ADMIN)
                return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/admin.txt';
        } else {
            if (!SAY99_MAIN)
                return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/main.txt';
        }

        if (!SAY99_LOG_APPEND) {
            unlink($this->log_file);
        }

        $this->log(self::SEPARATOR);
        $this->log(self::SEPARATOR);
        $this->log('Note: we cannot intercept plugins loaded before us');

        add_action('mu_plugin_loaded', function ($plugin) {
            $this->log('mu_plugin_loaded: ' . $plugin);
        });

        add_action('network_plugin_loaded', function ($plugin) {
            $this->log('network_plugin_loaded: ' . $plugin);
        });

        add_action('muplugins_loaded', function () {
            $this->log('muplugins_loaded');
        }, 10000);

        add_action('plugin_loaded', function ($plugin) {
            $this->log('plugin_loaded: ' . $plugin);
        }, 10000);

        add_action('plugins_loaded', function () {
            $this->current_mem = memory_get_usage();
            $this->log(self::SEPARATOR);
            $this->log('plugins_loaded: start');
        }, -1);

        add_action('plugins_loaded', function () {
            $this->log('plugins_loaded: end - ' . size_format(memory_get_usage() - $this->current_mem, 1));
        }, 10000);

        add_action('setup_theme', function () {
            global $wp_filter;
            $this->log(self::SEPARATOR);
            $this->current_mem = memory_get_usage();
            $this->log('setup_theme: start');
            //$this->log_callbacks('setup_theme');
            if (SAY99_ANALYZE_FUNCTIONS) {
                foreach ($wp_filter['setup_theme']->callbacks as $priority => $functions) {
                    $this->function_mem = memory_get_usage();
                    if ($priority == -1 || $priority == 10000)
                        continue;
                    $new_functions = [];
                    foreach ($functions as $function) {
                        $new_functions[] = $function;
                        $x = $this->function_to_string($function);
                        $new_functions[] = ['function' => function() use ($x) {
                                $this->log($x . ' - ' . size_format(memory_get_usage() - $this->function_mem, 1));
                                $this->function_mem = memory_get_usage();
                            }, 'accepted_args' => 1];
                    }
                    $wp_filter['setup_theme']->callbacks[$priority] = $new_functions;
                }
            }
        }, -1);

        add_action('setup_theme', function () {
            $this->log('setup_theme: end - ' . size_format(memory_get_usage() - $this->current_mem, 1));
            $this->log('Theme functions.php will be loaded');
            //$this->log_callbacks('setup_theme');
        }, 10000);

        add_action('after_setup_theme', function () {
            global $wp_filter;

            $this->current_mem = memory_get_usage();

            $this->log(self::SEPARATOR);
            $this->log('after_setup_theme: start');
            //$this->log_callbacks('after_setup_theme');
            if (SAY99_ANALYZE_FUNCTIONS) {
                $this->function_mem = memory_get_usage();

                foreach ($wp_filter['after_setup_theme']->callbacks as $priority => $functions) {
                    if ($priority == -1 || $priority == 10000)
                        continue;
                    $new_functions = [];
                    foreach ($functions as $function) {
                        $new_functions[] = $function;
                        $x = $this->function_to_string($function);
                        $new_functions[] = ['function' => function() use ($x) {
                                $this->log($x . ' - ' . size_format(memory_get_usage() - $this->function_mem, 1));
                                $this->function_mem = memory_get_usage();
                            }, 'accepted_args' => 1];
                    }
                    $wp_filter['after_setup_theme']->callbacks[$priority] = $new_functions;
                }
            }
        }, -1);

        add_action('after_setup_theme', function () {
            $this->log('after_setup_theme: end - ' . size_format(memory_get_usage() - $this->current_mem, 1));
            //$this->log_callbacks('after_setup_theme');
        }, 10000);

        add_action('init', function () {
            global $wp_filter;
            $this->log(self::SEPARATOR);
            $this->log('init: start');
            $this->current_mem = memory_get_usage();

            //$this->log_callbacks('init');
            if (SAY99_ANALYZE_FUNCTIONS) {
                $this->function_mem = memory_get_usage();
                foreach ($wp_filter['init']->callbacks as $priority => $functions) {
                    if ($priority == -1 || $priority == 10000)
                        continue;
                    $new_functions = [];
                    foreach ($functions as $function) {
                        $new_functions[] = $function;
                        $x = $this->function_to_string($function);
                        $new_functions[] = ['function' => function() use ($x) {
                                $this->log($x . ' - ' . size_format(memory_get_usage() - $this->function_mem, 1));
                                $this->function_mem = memory_get_usage();
                            }, 'accepted_args' => 1];
                    }
                    $wp_filter['init']->callbacks[$priority] = $new_functions;
                }
            }
        }, -1);

        add_action('init', function () {
            $this->log('init: end - ' . size_format(memory_get_usage() - $this->current_mem, 1));
            //$this->log_callbacks('init');
        }, 10000);

        add_action('wp_loaded', function () {
            $this->log(self::SEPARATOR);
            $this->log('wp_loaded');
        }, 10000);

        add_action('template_redirect', function () {
            $this->log('template_redirect');
        }, 10000);

        add_action('admin_init', function () {
            $this->log('admin_init');
        }, 10000);

        add_action('shutdown', function () {
            $this->log('shutdown');
        }, 10000);

//        add_action('all', function($tag) {
//            static $running = false;
//            
//            if ($tag !== 'init') return;
//            
//            if ($running) return;
//            $running = true;
//            $this->log($args);
//            $running = false;
//        });

        add_filter('transient_doing_cron', function($value, $transient) {
            $this->log('Starting cron (transient_doing_cron)');
        }, 10000, 2);

        add_filter('pre_unschedule_event', function($value, $timestamp, $hook, $args) {
            $this->log('Starting event: ' . $hook);
            return $value;
        }, 10000, 4);

        $this->extra_hooks();
    }

    function extra_hooks() {
        $this->add_hook('td_wp_booster_loaded');
        $this->add_hook('td_wp_booster_legacy');
    }

    function add_hook($hook) {
        add_action($hook, function () use ($hook) {
            $this->current_mem = memory_get_usage();
            $this->log(self::SEPARATOR);
            $this->log($hook . ': start');
        }, -1);

        add_action($hook, function () use ($hook) {
            $this->log($hook . ': end - ' . size_format(memory_get_usage() - $this->current_mem, 1));
        }, 10000);
    }

    function log_callbacks($tag) {
        global $wp_filter;
        if (isset($wp_filter)) {
            $b = "Functions:\n";
            foreach ($wp_filter[$tag]->callbacks as $priority => $functions) {

                foreach ($functions as $function) {
                    //var_dump($function);
                    $b .= '[' . $priority . '] ';
                    if (is_array($function['function'])) {
                        if (is_object($function['function'][0])) {
                            $b .= get_class($function['function'][0]) . '::' . $function['function'][1];
                        } else {
                            $b .= $function['function'][0] . '::' . $function['function'][1];
                        }
                    } else {
                        if (is_object($function['function'])) {
                            $fn = new ReflectionFunction($function['function']);
                            $b .= get_class($fn->getClosureThis()) . '(closure)';
                        } else {
                            $b .= $function['function'];
                        }
                    }
                    $b .= "\n";
                }
            }
            $this->log($b);
        }
    }

    function function_to_string($function) {
        $b = '';
        if (is_array($function['function'])) {
            if (is_object($function['function'][0])) {
                $b .= get_class($function['function'][0]) . '::' . $function['function'][1];
            } else {
                $b .= $function['function'][0] . '::' . $function['function'][1];
            }
        } else {
            if (is_object($function['function'])) {
                $fn = new ReflectionFunction($function['function']);
                if ($fn->getClosureThis() == null) {
                    $b .= 'Anonymous closure';
                } else {
                    $b .= get_class($fn->getClosureThis()) . '(closure)';
                }
            } else {
                $b .= $function['function'];
            }
        }
        return $b;
    }

    function log($text) {
        global $current_user;

        if ($current_user) {
            $user_id = $current_user->ID;
        } else {
            $user_id = 0;
        }

        $time = date('d-m-Y H:i:s ');
        if (is_wp_error($text)) {
            /* @var $text WP_Error */
            $text = $text->get_error_message() . ' (' . $text->get_error_code() . ') - ' . print_r($text->get_error_data(), true);
        } else {
            if (is_array($text) || is_object($text))
                $text = print_r($text, true);
        }

        // The "logs" dir is created on Newsletter constructor.
        file_put_contents($this->log_file, $time . ' - m: ' . size_format(memory_get_usage(), 1) . ', u: ' . $user_id . ' - ' . $text . "\n", FILE_APPEND | FILE_TEXT);
    }

}

new Say99();
