<?php

if ( ! defined( 'ABSPATH' ) ) exit;

ob_start();

echo '<h1>' . __('Please, Upgrade to PRO for generating serial numbers Automatically', 'wc-serial-numbers') . '</h1>';

$html = ob_get_clean();

echo '<div class="wrap wsn-container">';
echo apply_filters('generate_serial_number', $html);
echo '</div>';
