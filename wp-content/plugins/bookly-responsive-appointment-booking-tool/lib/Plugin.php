<?php
namespace Bookly\Lib;

use Bookly\Backend;
use Bookly\Frontend;

/**
 * Class Plugin
 * @package Bookly\Lib
 */
abstract class Plugin extends Base\Plugin
{
    protected static $prefix = 'bookly_';
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;
    protected static $embedded;

    /**
     * @inheritdoc
     */
    public static function init()
    {
        // Init ajax.
        Backend\Components\Dashboard\Appointments\Ajax::init();
        Backend\Components\Dashboard\Appointments\Widget::init();
        Backend\Components\Dialogs\Appointment\Delete\Ajax::init();
        Backend\Components\Dialogs\Appointment\Edit\Ajax::init();
        Backend\Components\Dialogs\Customer\Delete\Ajax::init();
        Backend\Components\Dialogs\Customer\Edit\Ajax::init();
        Backend\Components\Dialogs\Payment\Ajax::init();
        Backend\Components\Dialogs\Sms\Ajax::init();
        Backend\Components\Dialogs\Staff\Edit\Ajax::init();
        Backend\Components\Gutenberg\BooklyForm\Block::init();
        Backend\Components\Notices\CollectStatsAjax::init();
        Backend\Components\Notices\LiteRebrandingAjax::init();
        Backend\Components\Notices\NpsAjax::init();
        Backend\Components\Notices\PoweredByAjax::init();
        Backend\Components\Notices\SubscribeAjax::init();
        Backend\Components\Support\ButtonsAjax::init();
        Backend\Components\TinyMce\Tools::init();
        Backend\Modules\Appearance\Ajax::init();
        Backend\Modules\Appointments\Ajax::init();
        Backend\Modules\Calendar\Ajax::init();
        Backend\Modules\Customers\Ajax::init();
        Backend\Modules\Debug\Ajax::init();
        Backend\Modules\Messages\Ajax::init();
        Backend\Modules\Notifications\Ajax::init();
        Backend\Modules\Payments\Ajax::init();
        Backend\Modules\Services\Ajax::init();
        Backend\Modules\Settings\Ajax::init();
        Backend\Modules\Shop\Ajax::init();
        Backend\Modules\Sms\Ajax::init();
        Backend\Modules\Staff\Ajax::init();
        Frontend\Modules\Booking\Ajax::init();

        if ( ! is_admin() ) {
            // Init short code.
            Frontend\Modules\Booking\ShortCode::init();
        }
    }

    /**
     * @inheritdoc
     */
    public static function run()
    {
        // l10n.
        load_plugin_textdomain( 'bookly', false, self::getSlug() . '/languages' );

        parent::run();
    }

    /**
     * @inheritdoc
     */
    public static function registerHooks()
    {
        parent::registerHooks();

        if ( is_admin() ) {
            Backend\Backend::registerHooks();
        } else {
            Frontend\Frontend::registerHooks();
        }

        if ( get_option( 'bookly_gen_collect_stats' ) ) {
            // Store admin preferred language.
            add_filter( 'wp_authenticate_user', function ( $user ) {
                if ( $user instanceof \WP_User && $user->has_cap( Utils\Common::getRequiredCapability() ) && isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
                    $locale = strtok( $_SERVER['HTTP_ACCEPT_LANGUAGE'], ',;' );
                    update_option( 'bookly_admin_preferred_language', $locale );
                }

                return $user;
            }, 99, 1 );
        }

        // Gutenberg category
        add_filter( 'block_categories', function ( $categories, $post ) {
            return array_merge( array(
                array(
                    'slug'  => 'bookly-blocks',
                    'title' => 'Bookly',
                ), ),
                $categories
            );
        }, 10, 2 );

        add_action( 'after_plugin_row', function ( $plugin_file, $plugin_data, $status ) {
            /** @var \Bookly\Lib\Base\Plugin[] $bookly_plugins */
            $bookly_plugins = apply_filters( 'bookly_plugins', array() );
            $slug = dirname( $plugin_file );

            if ( array_key_exists( $slug, $bookly_plugins ) ) {
                $plugin_class = $bookly_plugins[ $slug ];
                $bookly_update_plugins = get_site_transient( 'bookly_update_plugins' );
                $key = 'support_required';
                if ( isset( $bookly_update_plugins[ $slug ][ $key ]['last_version'] ) ) {
                    $data = $bookly_update_plugins[ $slug ][ $key ];
                    if ( version_compare( $data['last_version'], $plugin_data['Version'], '>' ) ) {
                        echo '<tr class="plugin-update-tr active bookly-js-plugin">
                              <td colspan="3" class="plugin-update colspanchange">
                                  <div class="update-message notice inline notice-error notice-alt">
                                    <p>
                                    ' . esc_html__( 'Important', 'bookly' ) . '!<br>
                                    ' . esc_html__( 'Though, every new version is thoroughly tested to its highest quality before deploying, we can\'t guarantee that after update the plugin will work properly on all WordPress configurations and completely protect it from the influence of other plugins.', 'bookly' ) . '<br>
                                    ' . sprintf( __( 'There is a small risk that some issues may appear as a result of updating the plugin. Please note that, according to %1$s Envato rules %2$s, we will be able to help you only if you have active item support period.', 'bookly' ),
                                        '<a href="https://themeforest.net/page/item_support_policy" target="_blank">',
                                        '</a>'
                                        ) . '<br>
                                    ' . sprintf( __( 'You can renew support %1$s here %3$s or %2$s I\'ve already renewed support. %3$s', 'bookly' ),
                                        '<a href="' . esc_url( array_key_exists( 'renew_support', $data ) ? $data['renew_support'] : 'https://codecanyon.net/user/ladela' ) . '" target="_blank">',
                                        '<a href="#" data-bookly-plugin="' . $plugin_class::getRootNamespace() . '" data-csrf="' . Utils\Common::getCsrfToken() . '">',
                                        '</a>'
                                        ). ' <span class="spinner" style="float: none; margin: -2px 0 0 2px"></span><br>
                                    </p>
                                </div>
                              </td>
                          </tr>';
                    } else {
                        unset( $bookly_update_plugins[ $slug ][ $key ] );
                        set_site_transient( 'bookly_update_plugins', $bookly_update_plugins );
                    }
                }
            }
        }, 10, 3 );

        add_action( 'pre_current_active_plugins', function () {
            $version   = Plugin::getVersion();
            $resources = plugins_url( 'backend/resources', Plugin::getMainFile() );
            wp_enqueue_script( 'bookly-plugins-page', $resources . '/js/plugins.js', array( 'jquery' ), $version );
        } );

        // Register and schedule routines.
        Routines::init();
    }
}