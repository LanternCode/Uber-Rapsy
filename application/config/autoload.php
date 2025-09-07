<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Auto-load Packages
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/
$autoload['packages'] = array();
$autoload['libraries'] = array('database', 'form_validation', 'session', 'HtmlSanitiser');
$autoload['drivers'] = array();
$autoload['helper'] = array('html', 'url', 'text', 'form','string', 'inflection');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array('SecurityModel', 'LogModel', 'UtilityModel');