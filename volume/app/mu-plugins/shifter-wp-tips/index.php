<?php
/*
Plugin Name: Shifter tips
Plugin URI: https://github.com/getshifter/shifter-wp-tips
Description: Show tips or help content about Shifter in your wp-admin
Version: 1.0.1
Author: DigitalCube
Author URI: https://getshifter.io
License: GPL2
*/
if ( ! is_admin() ) return;
require_once('tips.php');
$Shifter_Tips = new Shifter_Tips();
$Shifter_Tips->initialize();
