<?php
class JConfig {
    public $debug = true;
    public $error_reporting = 'maximum';
    public $dbtype = 'mysqli';
    public $host = 'mysql';
    public $user = 'joomla_user';
    public $password = 'joomla_pass';
    public $db = 'joomla_db';
    public $dbprefix = 'j1ma3_';
    public $live_site = '';
    public $secret = 'some-secret-key';
    public $offline = false;
    public $display_offline_message = 1;
    public $offline_message = 'This site is down for maintenance. Please check back again soon.';
    public $offline_image = '';
    public $sitename = 'Joomla Site';
    public $editor = 'tinymce';
    public $captcha = '0';
    public $list_limit = 20;
    public $access = 1;
    public $log_path = '/var/www/html/administrator/logs';
    public $tmp_path = '/var/www/html/tmp';
    public $lifetime = 15;
    public $session_handler = 'database';
}