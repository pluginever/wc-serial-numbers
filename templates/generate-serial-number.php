<?php

if ( ! defined( 'ABSPATH' ) ) exit;

ob_start();

echo '';

$html = ob_get_clean();

echo '<div class="wrap wsn-container">';
echo apply_filters('generate_serial_number', $html);
echo '</div>';
