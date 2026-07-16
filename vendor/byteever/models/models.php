<?php
/**
 * Plugin Name:         ByteEver Models
 * Plugin URI:          https://github.com/byteever/models
 * Description:         A lightweight WordPress ORM/Models framework with modern PHP and clean architecture.
 * Version:             1.0.0
 * Requires at least:   6.0
 * Requires PHP:        7.4
 * Author:              ByteEver
 * Author URI:          https://byteever.com
 * License:             GPL v3 or later
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         byteever-models
 * Domain Path:         /languages
 * Network:             false
 *
 * @package             B8\Models
 * @author              ByteEver <info@byteever.com>
 * @copyright           2025 ByteEver
 * @license             GPL-3.0-or-later
 * @link                https://byteever.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) || exit;

// Autoloader.
if ( ! class_exists( \B8\Models\Model::class ) && file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// This package is a library package and doesn't require initialization as a standalone plugin.
// This file exists to allow the package to be recognized as a plugin if needed.
