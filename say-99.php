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

if (!defined('SAY99_CRON')) define('SAY99_CRON', true);
if (!defined('SAY99_ADMIN')) define('SAY99_ADMIN', true);
if (!defined('SAY99_AJAX')) define('SAY99_AJAX', false);
if (!defined('SAY99_MAIN')) define('SAY99_MAIN', false);


class Say99 {
    var $log_file;
    
    public function __construct() {
        
        wp_mkdir_p(WP_CONTENT_DIR . '/logs/say-99');
        
        if (defined('DOING_CRON') && DOING_CRON) {
            if (!SAY99_CRON) return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/cron.txt';
        } elseif (defined('DOING_AJAX') && DOING_AJAX) {
            if (!SAY99_AJAX) return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/ajax.txt';
        } elseif (is_admin()) {
            if (!SAY99_ADMIN) return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/admin.txt';
        } else {
            if (!SAY99_MAIN) return;
            $this->log_file = WP_CONTENT_DIR . '/logs/say-99/main.txt';
        }
        
        $this->log('------------------------------------------------------------');
        $this->log('Note: we cannot intercept plugins loaded before us');
        
        add_action( 'mu_plugin_loaded', function ($plugin) {
            $this->log('mu_plugin_loaded: ' . $plugin);
        });
        
        add_action( 'network_plugin_loaded', function ($plugin) {
            $this->log('network_plugin_loaded: ' . $plugin);
        });
        
        add_action('muplugins_loaded', function () {
            $this->log('muplugins_loaded');
        }, 1000);
        
        add_action( 'plugin_loaded', function ($plugin) {
            $this->log('plugin_loaded: ' . $plugin);
        }, 1000);
        
        add_action('plugins_loaded', function () {
            $this->log('plugins_loaded');
        }, 1000);
        
        add_action('setup_theme', function () {
            $this->log('setup_theme');
        }, 1000);
        
        add_action('after_setup_theme', function () {
            $this->log('after_setup_theme');
        }, 1000);
        
        add_action('init', function () {
            $this->log('init');
        }, 1000);
        
        add_action('wp_loaded', function () {
            $this->log('wp_loaded');
        }, 1000);
        
        add_action('template_redirect', function () {
            $this->log('template_redirect');
        }, 1000);
        
        add_action('admin_init', function () {
            $this->log('admin_init');
        }, 1000);
        
        add_action('shutdown', function () {
            $this->log('shutdown');
        }, 1000);
        
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
        }, 1000, 2);
        
        add_filter('pre_unschedule_event', function($value, $timestamp, $hook, $args) {
            $this->log('Starting event: ' . $hook);
            return $value;
        }, 1000, 4);
        
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
            if (is_array($text) || is_object($text)) $text = print_r($text, true);
        }
        
        // The "logs" dir is created on Newsletter constructor.
        file_put_contents($this->log_file, $time . ' - m: ' . size_format(memory_get_usage(), 1) . ', u: ' . $user_id . ' - ' . $text . "\n", FILE_APPEND | FILE_TEXT);
    }
    
}

new Say99();
