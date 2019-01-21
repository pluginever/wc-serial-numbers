<?php
/**
 * Version 1.0.0
 */
if ( ! class_exists( 'Pluginever_Framework_Updater' ) ):
    /**
     * Allows plugins to use their own update API.
     *
     * @author Easy Digital Downloads
     * @version 1.6.14
     */
    class Pluginever_Framework_Updater {
        private $api_url = '';
        private $api_data = array();
        private $name = '';
        private $slug = '';
        private $version = '';
        private $wp_override = false;
        private $cache_key = '';

        /**
         * Class constructor.
         *
         * @uses plugin_basename()
         * @uses hook()
         *
         * @param string $_api_url The URL pointing to the custom API endpoint.
         * @param string $_plugin_file Path to the plugin file.
         * @param array  $_api_data Optional data to send with API calls.
         */
        public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
            global $edd_plugin_data;
            $this->api_url                  = trailingslashit( $_api_url );
            $this->api_data                 = $_api_data;
            $this->name                     = plugin_basename( $_plugin_file );
            $this->slug                     = basename( $_plugin_file, '.php' );
            $this->version                  = $_api_data['version'];
            $this->wp_override              = isset( $_api_data['wp_override'] ) ? (bool) $_api_data['wp_override'] : false;
            $this->beta                     = ! empty( $this->api_data['beta'] ) ? true : false;
            $this->cache_key                = md5( serialize( $this->slug . $this->api_data['license'] . $this->beta ) );
            $edd_plugin_data[ $this->slug ] = $this->api_data;
            // Set up hooks.
            $this->init();
        }

        /**
         * Set up WordPress filters to hook into WP's update process.
         *
         * @uses add_filter()
         *
         * @return void
         */
        public function init() {
            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
            add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
            remove_action( 'after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10 );
            add_action( 'after_plugin_row_' . $this->name, array( $this, 'show_update_notification' ), 10, 2 );
            add_action( 'admin_init', array( $this, 'show_changelog' ) );
        }

        /**
         * Check for Updates at the defined API endpoint and modify the update array.
         *
         * This function dives into the update API just when WordPress creates its update array,
         * then adds a custom API call and injects the custom plugin data retrieved from the API.
         * It is reassembled from parts of the native WordPress plugin update code.
         * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
         *
         * @uses api_request()
         *
         * @param array $_transient_data Update array build by WordPress.
         *
         * @return array Modified update array with custom plugin data.
         */
        public function check_update( $_transient_data ) {
            global $pagenow;
            if ( ! is_object( $_transient_data ) ) {
                $_transient_data = new stdClass;
            }
            if ( 'plugins.php' == $pagenow && is_multisite() ) {
                return $_transient_data;
            }
            if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $this->name ] ) && false === $this->wp_override ) {
                return $_transient_data;
            }
            $version_info = $this->get_cached_version_info();
            if ( false === $version_info ) {
                $version_info = $this->api_request( 'plugin_latest_version', array(
                    'slug' => $this->slug,
                    'beta' => $this->beta
                ) );
                $this->set_version_info_cache( $version_info );
            }
            if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {
                if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {
                    $_transient_data->response[ $this->name ] = $version_info;
                }
                $_transient_data->last_checked           = current_time( 'timestamp' );
                $_transient_data->checked[ $this->name ] = $this->version;
            }

            return $_transient_data;
        }

        /**
         * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
         *
         * @param string $file
         * @param array  $plugin
         */
        public function show_update_notification( $file, $plugin ) {
            if ( is_network_admin() ) {
                return;
            }
            if ( ! current_user_can( 'update_plugins' ) ) {
                return;
            }
            if ( ! is_multisite() ) {
                return;
            }
            if ( $this->name != $file ) {
                return;
            }
            // Remove our filter on the site transient
            remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 10 );
            $update_cache = get_site_transient( 'update_plugins' );
            $update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();
            if ( empty( $update_cache->response ) || empty( $update_cache->response[ $this->name ] ) ) {
                $version_info = $this->get_cached_version_info();
                if ( false === $version_info ) {
                    $version_info = $this->api_request( 'plugin_latest_version', array(
                        'slug' => $this->slug,
                        'beta' => $this->beta
                    ) );
                    $this->set_version_info_cache( $version_info );
                }
                if ( ! is_object( $version_info ) ) {
                    return;
                }
                if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {
                    $update_cache->response[ $this->name ] = $version_info;
                }
                $update_cache->last_checked           = current_time( 'timestamp' );
                $update_cache->checked[ $this->name ] = $this->version;
                set_site_transient( 'update_plugins', $update_cache );
            } else {
                $version_info = $update_cache->response[ $this->name ];
            }
            // Restore our filter
            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
            if ( ! empty( $update_cache->response[ $this->name ] ) && version_compare( $this->version, $version_info->new_version, '<' ) ) {
                // build a plugin list row, with update notification
                $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
                # <tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange">
                echo '<tr class="plugin-update-tr" id="' . $this->slug . '-update" data-slug="' . $this->slug . '" data-plugin="' . $this->slug . '/' . $file . '">';
                echo '<td colspan="3" class="plugin-update colspanchange">';
                echo '<div class="update-message notice inline notice-warning notice-alt">';
                $changelog_link = self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->name . '&slug=' . $this->slug . '&TB_iframe=true&width=772&height=911' );
                if ( empty( $version_info->download_link ) ) {
                    printf(
                        __( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'easy-digital-downloads' ),
                        esc_html( $version_info->name ),
                        '<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
                        esc_html( $version_info->new_version ),
                        '</a>'
                    );
                } else {
                    printf(
                        __( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.', 'easy-digital-downloads' ),
                        esc_html( $version_info->name ),
                        '<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
                        esc_html( $version_info->new_version ),
                        '</a>',
                        '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->name, 'upgrade-plugin_' . $this->name ) ) . '">',
                        '</a>'
                    );
                }
                do_action( "in_plugin_update_message-{$file}", $plugin, $version_info );
                echo '</div></td></tr>';
            }
        }

        /**
         * Updates information on the "View version x.x details" page with custom data.
         *
         * @uses api_request()
         *
         * @param mixed  $_data
         * @param string $_action
         * @param object $_args
         *
         * @return object $_data
         */
        public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
            if ( $_action != 'plugin_information' ) {
                return $_data;
            }
            if ( ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {
                return $_data;
            }
            $to_send   = array(
                'slug'   => $this->slug,
                'is_ssl' => is_ssl(),
                'fields' => array(
                    'banners' => array(),
                    'reviews' => false
                )
            );
            $cache_key = 'edd_api_request_' . md5( serialize( $this->slug . $this->api_data['license'] . $this->beta ) );
            // Get the transient where we store the api request for this plugin for 24 hours
            $edd_api_request_transient = $this->get_cached_version_info( $cache_key );
            //If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
            if ( empty( $edd_api_request_transient ) ) {
                $api_response = $this->api_request( 'plugin_information', $to_send );
                // Expires in 3 hours
                $this->set_version_info_cache( $api_response, $cache_key );
                if ( false !== $api_response ) {
                    $_data = $api_response;
                }
            } else {
                $_data = $edd_api_request_transient;
            }
            // Convert sections into an associative array, since we're getting an object, but Core expects an array.
            if ( isset( $_data->sections ) && ! is_array( $_data->sections ) ) {
                $new_sections = array();
                foreach ( $_data->sections as $key => $value ) {
                    $new_sections[ $key ] = $value;
                }
                $_data->sections = $new_sections;
            }
            // Convert banners into an associative array, since we're getting an object, but Core expects an array.
            if ( isset( $_data->banners ) && ! is_array( $_data->banners ) ) {
                $new_banners = array();
                foreach ( $_data->banners as $key => $value ) {
                    $new_banners[ $key ] = $value;
                }
                $_data->banners = $new_banners;
            }

            return $_data;
        }

        /**
         * Disable SSL verification in order to prevent download update failures
         *
         * @param array  $args
         * @param string $url
         *
         * @return object $array
         */
        public function http_request_args( $args, $url ) {
            $verify_ssl = $this->verify_ssl();
            if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
                $args['sslverify'] = $verify_ssl;
            }

            return $args;
        }

        /**
         * Calls the API and, if successfull, returns the object delivered by the API.
         *
         * @uses get_bloginfo()
         * @uses wp_remote_post()
         * @uses is_wp_error()
         *
         * @param string $_action The requested action.
         * @param array  $_data Parameters for the API action.
         *
         * @return false|object
         */
        private function api_request( $_action, $_data ) {
            global $wp_version;
            $data = array_merge( $this->api_data, $_data );
            if ( $data['slug'] != $this->slug ) {
                return;
            }
            if ( $this->api_url == trailingslashit( home_url() ) ) {
                return false; // Don't allow a plugin to ping itself
            }
            $api_params = array(
                'edd_action' => 'get_version',
                'license'    => ! empty( $data['license'] ) ? $data['license'] : '',
                'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
                'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
                'version'    => isset( $data['version'] ) ? $data['version'] : false,
                'slug'       => $data['slug'],
                'author'     => $data['author'],
                'url'        => home_url(),
                'beta'       => ! empty( $data['beta'] ),
            );
            $verify_ssl = $this->verify_ssl();
            $request    = wp_remote_post( $this->api_url, array(
                'timeout'   => 15,
                'sslverify' => $verify_ssl,
                'body'      => $api_params
            ) );


            if ( ! is_wp_error( $request ) ) {
                $request = json_decode( wp_remote_retrieve_body( $request ) );
            }
            if ( $request && isset( $request->sections ) ) {
                $request->sections = maybe_unserialize( $request->sections );
            } else {
                $request = false;
            }
            if ( $request && isset( $request->banners ) ) {
                $request->banners = maybe_unserialize( $request->banners );
            }
            if ( ! empty( $request->sections ) ) {
                foreach ( $request->sections as $key => $section ) {
                    $request->$key = (array) $section;
                }
            }

            return $request;
        }

        public function show_changelog() {
            global $edd_plugin_data;
            if ( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
                return;
            }
            if ( empty( $_REQUEST['plugin'] ) ) {
                return;
            }
            if ( empty( $_REQUEST['slug'] ) ) {
                return;
            }
            if ( ! current_user_can( 'update_plugins' ) ) {
                wp_die( __( 'You do not have permission to install plugin updates', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
            }
            $data         = $edd_plugin_data[ $_REQUEST['slug'] ];
            $beta         = ! empty( $data['beta'] ) ? true : false;
            $cache_key    = md5( 'edd_plugin_' . sanitize_key( $_REQUEST['plugin'] ) . '_' . $beta . '_version_info' );
            $version_info = $this->get_cached_version_info( $cache_key );
            if ( false === $version_info ) {
                $api_params = array(
                    'edd_action' => 'get_version',
                    'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
                    'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
                    'slug'       => $_REQUEST['slug'],
                    'author'     => $data['author'],
                    'url'        => home_url(),
                    'beta'       => ! empty( $data['beta'] )
                );
                $verify_ssl = $this->verify_ssl();
                $request    = wp_remote_post( $this->api_url, array(
                    'timeout'   => 15,
                    'sslverify' => $verify_ssl,
                    'body'      => $api_params
                ) );
                if ( ! is_wp_error( $request ) ) {
                    $version_info = json_decode( wp_remote_retrieve_body( $request ) );
                }
                if ( ! empty( $version_info ) && isset( $version_info->sections ) ) {
                    $version_info->sections = maybe_unserialize( $version_info->sections );
                } else {
                    $version_info = false;
                }
                if ( ! empty( $version_info ) ) {
                    foreach ( $version_info->sections as $key => $section ) {
                        $version_info->$key = (array) $section;
                    }
                }
                $this->set_version_info_cache( $version_info, $cache_key );
            }
            if ( ! empty( $version_info ) && isset( $version_info->sections['changelog'] ) ) {
                echo '<div style="background:#fff;padding:10px;">' . $version_info->sections['changelog'] . '</div>';
            }
            exit;
        }

        public function get_cached_version_info( $cache_key = '' ) {
            if ( empty( $cache_key ) ) {
                $cache_key = $this->cache_key;
            }
            $cache = get_option( $cache_key );
            if ( empty( $cache['timeout'] ) || current_time( 'timestamp' ) > $cache['timeout'] ) {
                return false; // Cache is expired
            }

            return json_decode( $cache['value'] );
        }

        public function set_version_info_cache( $value = '', $cache_key = '' ) {
            if ( empty( $cache_key ) ) {
                $cache_key = $this->cache_key;
            }
            $data = array(
                'timeout' => strtotime( '+3 hours', current_time( 'timestamp' ) ),
                'value'   => json_encode( $value )
            );
            update_option( $cache_key, $data, 'no' );
        }

        /**
         * Returns if the SSL of the store should be verified.
         *
         * @since  1.6.13
         * @return bool
         */
        private function verify_ssl() {
            return (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this );
        }
    }
endif;

if ( ! class_exists( 'Pluginever_Framework_License' ) ):
    class Pluginever_Framework_License {
        private $file;
        private $item_name;
        private $version;
        private $parent_slug;

        private $license_key;
        private $license_key_name;
        private $license_status;
        private $license_details;

        private $author = 'Pluginever';
        private $api_url = 'https://www.pluginever.com';
        private $api_data = [];

        public $license_page;

        protected $my_account_page_url = 'https://www.pluginever.com/my-account/';
        protected $activate_license_guide = 'https://www.pluginever.com/docs/general/how-to-activate-license/';

        /**
         * License constructor.
         *
         * @param        $file
         * @param        $item_name
         * @param        $version
         * @param string $parent_slug
         */
        public function __construct( $file, $item_name, $version, $parent_slug = 'plugins.php' ) {
            $this->file        = $file;
            $this->item_name   = $item_name;
            $this->version     = $version;
            $this->parent_slug = $parent_slug;

            $this->license_key_name = $this->get_unique_key();
            $this->license_details  = $this->get_license_details();

            $this->license_key    = $this->get_license_details( 'key' );
            $this->license_status = $this->get_license_details( 'license' );

            $this->license_page_slug = sanitize_title( "{$item_name}-license" );
            $this->license_page_url  = admin_url( "admin.php?page={$this->license_page_slug}" );

            $this->api_data = array(
                'item_name' => $this->item_name,
                'version'   => $this->version,
                'author'    => $this->author,
                'url'       => home_url(),
                'license'   => $this->license_key
            );

            $this->init();
            $this->init_updater();

        }

        /**
         * Register all hooks
         */
        public function init() {
            $action = $this->get_unique_key( 'activate_license' );
            add_action( 'admin_menu', array( &$this, 'register_license_menu' ) );
            add_action( 'admin_notices', array( $this, 'handle_admin_notice' ) );
            add_action( "admin_post_$action", array( $this, 'handle_license_activation' ) );
            add_action( 'wp_scheduled_delete', [ $this, 'check_license_daily' ] );
        }

        /**
         * Initiate plugin updater
         */
        protected function init_updater() {
            new Pluginever_Framework_Updater(
                $this->api_url,
                $this->file,
                $this->api_data
            );
        }


        /**
         * Generate an unique id for the plugin
         *
         * @since v1.0.0
         *
         * @param string $string
         *
         * @return string
         */
        protected function get_unique_key( $string = 'license' ) {
            return preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( "{$this->item_name}_{$this->author}_{$string}" ) ) );
        }

        /**
         * get saved license
         *
         * @param string $key
         *
         * @return string
         */
        protected function get_license_details( $key = null ) {
            $license_details = get_option( $this->license_key_name, [] );
            if ( ! empty( $key ) ) {
                return ! empty( $license_details[ $key ] ) ? $license_details[ $key ] : '';
            }


            return $license_details;
        }

        /**
         * Save license
         *
         * @param $license
         */
        protected function save_license( $license ) {
            update_option( $this->license_key_name, $license );
        }

        /**
         * Delete license
         *
         */
        protected function delete_license() {
            delete_option( $this->license_key_name );
        }

        /**
         * Checks license status
         *
         * @return bool
         */
        public function is_license_valid() {
            return $this->license_status === 'valid';
        }

        /**
         * Checks whether the license is valid or not
         *
         * @since 1.0.0
         *
         * @return bool
         */
        public function check_license_daily() {
            $params = wp_parse_args( array( 'edd_action' => 'check_license' ), $this->api_data );

            $response = wp_remote_post(
                $this->api_url,
                array(
                    'timeout'   => 15,
                    'sslverify' => true,
                    'body'      => $params
                )
            );

            if ( is_wp_error( $response ) ) {
                return false;
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            if ( $license_data->license !== 'valid' ) {
                $this->delete_license();
            }

        }


        /**
         * Activate license key
         *
         * @param $license_key
         *
         * @return bool
         */
        public function activate_license( $license_key ) {
            $params = wp_parse_args(
                array(
                    'edd_action' => 'activate_license',
                    'license'    => $license_key,
                ),
                $this->api_data
            );

            $response = wp_remote_post( $this->api_url, array(
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => $params
            ) );

            if ( is_wp_error( $response ) ) {
                return false;
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            return $license_data;

        }


        /**
         * Register license activation page
         */
        public function register_license_menu() {
            add_submenu_page( $this->parent_slug, $this->item_name, __( 'License', 'ever' ), 'manage_options', $this->license_page_slug, array(
                $this,
                'render_license_page'
            ) );
        }

        /**
         * Handle admin notice
         */
        public function handle_admin_notice() {
            if ( ! $this->is_license_valid() ) {
                $this->show_notice( "<b>Warning!</b> You didn't activate <strong>{$this->item_name}</strong> yet. To activate the plugin please <a href='{$this->license_page_url}'>Enter your license key</a> or <a href='https://www.pluginever.com/docs/general/how-to-activate-license/' target='_blank'> check how to activate license.</a>" );
            }

            if ( isset( $_GET['page'] ) && $_GET['page'] == $this->license_page_slug
                 && isset( $_GET['activation'] ) && isset( $_GET['msg'] ) ) {
                $type = 'error';
                if ( $_GET['activation'] == 'true' ) {
                    $type = 'success';
                }

                $this->show_notice( esc_html( $_GET['msg'] ), $type );
            }
        }

        /**
         * Show admin notice
         *
         * @param        $message
         * @param string $type
         */
        protected function show_notice( $message, $type = 'error' ) {
            printf( '<div class="notice notice-%1$s"><p>%2$s</p></div>', esc_attr( $type ), $message );
        }

        /**
         * Get products from pluginever
         *
         * @return array
         */
        public function get_promotional_products() {
            $transient_key = $this->get_unique_key( 'products' );
            $products      = get_transient( $transient_key );

            if ( empty( $products ) ) {
                $host     = untrailingslashit( $this->api_url );
                $response = wp_remote_get( "$host/edd-api/products", array(
                    'timeout'   => 15,
                    'sslverify' => true
                ) );

                if ( is_wp_error( $response ) ) {
                    return [];
                }

                $response_body = json_decode( wp_remote_retrieve_body( $response ) );
                $products      = isset( $response_body->products ) ? $response_body->products : [];

                if ( ! empty( $products ) ) {
                    set_transient( $this->get_unique_key( 'products' ), $products, 12 * HOUR_IN_SECONDS );
                }
            }

            return $products;
        }


        /**
         * Handle license submission
         */
        public function handle_license_activation() {

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'No cheat!', 'ever' ) );
            }

            if ( ! wp_verify_nonce( $_POST['nonce'], $this->get_unique_key( 'nonce' ) ) ) {
                wp_die( __( 'session expired try again', 'ever' ) );
            }

            $license_key = esc_attr( $_POST['license_key'] );

            $params = wp_parse_args(
                array(
                    'edd_action' => 'activate_license',
                    'license'    => $license_key,
                ),
                $this->api_data
            );

            $response = wp_remote_post( $this->api_url, array(
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => $params
            ) );


            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

                if ( is_wp_error( $response ) ) {
                    $message = $response->get_error_message();
                } else {
                    $message = __( 'An error occurred, please try again.' );
                }

            } else {

                $license_data = (array) json_decode( wp_remote_retrieve_body( $response ), true );

                if ( false === $license_data['success'] ) {

                    switch ( $license_data['error'] ) {

                        case 'expired' :

                            $message = sprintf(
                                __( 'Your license key expired on %s.' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data['expires'], current_time( 'timestamp' ) ) )
                            );
                            break;

                        case 'revoked' :

                            $message = __( 'Your license key has been disabled.' );
                            break;

                        case 'missing' :

                            $message = __( 'Invalid license, Please use correct license key.' );
                            break;

                        case 'invalid' :
                        case 'site_inactive' :

                            $message = __( 'Your license is not active for this URL.' );
                            break;

                        case 'item_name_mismatch' :

                            $message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $license_data['item_name'] );
                            break;

                        case 'no_activations_left':

                            $message = __( 'Your license key has reached its activation limit.' );
                            break;

                        default :

                            $message = __( 'An error occurred, please try again.' );
                            break;
                    }

                }

            }


            if ( ! empty( $message ) ) {
                $redirect = add_query_arg( array(
                    'activation' => 'false',
                    'msg'        => $message
                ), $this->license_page_url );
                $this->delete_license();
            } else {

                $license_data['key'] = $license_key;

                $this->save_license( $license_data );

                $redirect = add_query_arg( array(
                    'activation' => 'true',
                    'msg'        => __( 'License has been activated successfully!', 'ever' )
                ), $this->license_page_url );
            }

            wp_redirect( esc_url_raw( $redirect ) );
            exit();

        }


        /**
         * Render license Page
         */
        public function render_license_page() {
            ob_start();
            $products = $this->get_promotional_products();
            //30571b4a1e0ca42a5d6d5f1e64b0e6a4
            ?>


            <div class="wrap">
                <h2><?php echo sprintf( 'License - %s', $this->item_name ); ?></h2>


                <div class="pluginever-license-page">
                    <h2>Activate License</h2>

                    <p><?php _e( "Thank you for choosing <strong>{$this->item_name}</strong>. Activate the plugin license by putting your license key below to get auto update,
                    support and to be notified when we release any new feature.Login into your PluginEver <strong><a href='{$this->my_account_page_url}' target='_blank'>Account</a></strong> 
                    to get your license key. You can read more about how to activate license <strong><a href='{$this->activate_license_guide}' target='_blank'>here</a></strong>.", 'ever' ); ?>
                    </p>


                    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                        <table class="form-table license-form">
                            <tbody>

                            <tr valign="top">
                                <th scope="row" valign="top"><?php _e( 'License status', 'ever' ); ?></th>
                                <td>
                                    <?php if ( ! $this->is_license_valid() ): ?>
                                        <span class="license-status-inactive"><?php _e( 'INACTIVE', 'ever' ); ?></span>
                                        &nbsp; - &nbsp; <?php _e( 'you are <strong>not</strong> receiving updates.', 'ever' ); ?>

                                    <?php else: ?>
                                        <span class="license-status-active"><?php _e( 'ACTIVE', 'ever' ); ?></span>
                                        &nbsp; - &nbsp; <?php _e( 'you are receiving updates.', 'ever' ); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>


                            <?php if ( $this->is_license_valid() ): ?>
                                <tr valign="top">
                                    <th scope="row" valign="top">Validity</th>
                                    <td><?php echo date( 'F j, Y', strtotime( $this->get_license_details( 'expires' ) ) ); ?></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row" valign="top">License Type</th>
                                    <td><?php echo empty( $this->get_license_details( 'license_limit' ) ) ? 'Unlimited' : $this->get_license_details( 'license_limit' ) . ' Site(s)'; ?></td>
                                </tr>
                            <?php endif; ?>


                            <tr valign="top">
                                <th scope="row" valign="top"><?php _e( 'License Key	', 'ever' ); ?></th>
                                <td>
                                    <input type="text" class="regular-text textinput"
                                           name="license_key"
                                           value="<?php echo $this->license_key; ?>"
                                           placeholder="<?php _e( 'Paste your license key here...', 'ever' ); ?>">
                                </td>
                            </tr>

                            <tr valign="top">
                                <th></th>
                                <td>
                                    <?php if ( ! $this->is_license_valid() ): ?>

                                        <button name="<?php echo esc_attr( 'activate-license' ); ?>" type="submit"
                                                class="button button-secondary license-activate" value="activate">

                                            <?php echo __( 'Activate License', 'ever' ); ?>

                                        </button>
                                    <?php else: ?>

                                    <?php endif; ?>
                                </td>
                            </tr>


                            </tbody>
                        </table>
                        <?php wp_nonce_field( $this->get_unique_key( 'nonce' ), 'nonce' ); ?>
                        <input type="hidden" name="action"
                               value="<?php echo $this->get_unique_key( 'activate_license' ); ?>">
                    </form>

                    <hr class="sep">

                    <div class="promotional-products-wrap">
                        <?php if ( ! empty( $products ) ): ?>
                            <h2>You might also like</h2>
                            <div class="promotional-products">
                                <?php foreach ( $products as $key => $product ) : ?>

                                    <div class="promotional-product">
                                        <div class="banner"
                                             style="background-image:url('<?php echo $product->info->thumbnail; ?>');">
                                            <img src="<?php echo $product->info->thumbnail; ?>" alt="">
                                        </div>
                                        <div class="content">
                                            <h4 class="title">
                                                <?php echo $product->info->title; ?>
                                            </h4>

                                            <p> <?php echo $product->info->excerpt; ?></p>

                                            <a href="<?php echo $product->info->link; ?>" target="_blank"
                                               class="button button-secondary">Check Now</a>

                                        </div>

                                    </div>

                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div><!--.promotional-products-wrap-->


                </div><!--.pluginever-license-page-->

            </div>


            <style>
                .pluginever-license-page * {
                    box-sizing: border-box
                }

                .pluginever-license-page {
                    margin-top: 20px;
                    max-width: 760px
                }

                .license-form th {
                    vertical-align: top;
                    text-align: left;
                    padding: 20px 10px 20px 0;
                    width: 200px;
                    line-height: 1.3;
                    font-weight: 600
                }

                .sep {
                    margin: 40px 0
                }

                .promotional-products {
                    display: flex;
                    flex-flow: row wrap
                }

                .promotional-product {
                    max-width: calc(25% - 22px);
                    min-width: 350px;
                    flex: 1 1 auto;
                    background: #fff;
                    border: 1px solid rgba(0, 0, 0, .1);
                    margin: 10px 10px 25px;
                    display: flex;
                    flex-flow: column nowrap;
                    justify-content: space-between
                }

                .banner {
                    width: 100%;
                    height: 200px;
                    background-position: center center;
                    overflow: hidden
                }

                .content {
                    height: auto;
                    font-size: 14px;
                    font-weight: 400;
                    color: #777;
                    line-height: 26px;
                    padding: 15px 20px
                }

                .content .title {
                    padding: 0;
                    margin: 0;
                    font-size: 17px;
                    font-weight: 600;
                    color: #333;
                    line-height: 1.4
                }

                .banner img {
                    max-width: 100%
                }

                .license-status-active {
                    color: #fff;
                    background: green;
                    padding: 3px 6px
                }

                .license-status-inactive {
                    color: #fff;
                    background: red;
                    padding: 3px 6px
                }
            </style>
            <?php
            $html = ob_get_contents();
            ob_get_clean();

            echo $html;

        }

    }
endif;
