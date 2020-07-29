<?php
/**
 * Plugin Name: WP Trigger Netlify Build
 * Plugin URI: https://github.com/iamtimsmith/wp-trigger-netlify-build
 * Description: A plugin which takes a webhook url and triggers a build for a netlify site
 * Version: 1.0
 * Author: Tim Smith
 * Author URI: https://www.iamtimsmith.com
 */


/**
 * Create Admin Panel
 */
function wp_trigger_netlify_build_setup_menu() {
  add_menu_page( 'WP Trigger Netlify Build', 'Netlify Build', 'manage_options', 'wp-trigger-netlify-build', 'wp_trigger_netlify_build_options_page', netlify_icon() );
}
add_action( 'admin_menu', 'wp_trigger_netlify_build_setup_menu');

/**
 * Create Settings
 */
function wp_trigger_netlify_build_settings_init() {
  register_setting('wp_trigger_netlify_build', 'wp_trigger_netlify_build_settings');
  add_settings_section(
    'wp_trigger_netlify_build_section',
    __( '', 'wordpress' ),
    '',
    'wp_trigger_netlify_build'
  );
  add_settings_field(
    'wp_trigger_netlify_build_webhook_url',
    __('Netlify Webhook URL', 'wordpress'),
    'wp_trigger_netlify_build_webhook_url_render',
    'wp_trigger_netlify_build',
    'wp_trigger_netlify_build_section'
  );
  add_settings_field(
    'wp_trigger_netlify_build_status_image',
    __('Netlify Status Image', 'wordpress'),
    'wp_trigger_netlify_build_status_image_render',
    'wp_trigger_netlify_build',
    'wp_trigger_netlify_build_section'
  );
  add_settings_field(
    'wp_trigger_netlify_build_status_link',
    __('Netlify Status Link', 'wordpress'),
    'wp_trigger_netlify_build_status_link_render',
    'wp_trigger_netlify_build',
    'wp_trigger_netlify_build_section'
  );
}
add_action( 'admin_init', 'wp_trigger_netlify_build_settings_init' );


function wp_trigger_netlify_build_webhook_url_render() {
  $options = get_option( 'wp_trigger_netlify_build_settings' );
  ?>
  <input type='text' name='wp_trigger_netlify_build_settings[wp_trigger_netlify_build_webhook_url]' value='<?php echo $options['wp_trigger_netlify_build_webhook_url']; ?>' class="regular-text">
  <span class="description"><?php esc_attr_e( 'The URL provided by Netlify for a custom webhook', 'WpAdminStyle' ); ?></span><br>
  <?php
}

function wp_trigger_netlify_build_status_image_render() {
  $options = get_option( 'wp_trigger_netlify_build_settings' );
  ?>
  <input type='text' name='wp_trigger_netlify_build_settings[wp_trigger_netlify_build_status_image]' value='<?php echo $options['wp_trigger_netlify_build_status_image']; ?>' class="regular-text">
  <span class="description"><?php esc_attr_e( 'The URL provided by Netlify for the image that shows site status', 'WpAdminStyle' ); ?></span><br>
  <?php
}

function wp_trigger_netlify_build_status_link_render() {
  $options = get_option( 'wp_trigger_netlify_build_settings' );
  ?>
  <input type='text' name='wp_trigger_netlify_build_settings[wp_trigger_netlify_build_status_link]' value='<?php echo $options['wp_trigger_netlify_build_status_link']; ?>' class="regular-text">
  <span class="description"><?php esc_attr_e( 'The URL provided by Netlify for the link to the site status', 'WpAdminStyle' ); ?></span><br>
  <?php
}

function wp_trigger_netlify_build_options_page() {
  ?>
  <h1>Netlify Settings</h1>
  <p>Set up your plugin to work with your Netlify webhooks.</p>
  <form action='options.php' method='post'>
    <?php
    settings_fields( 'wp_trigger_netlify_build' );
    do_settings_sections( 'wp_trigger_netlify_build' );
    submit_button();
    ?>
  </form>
  <?php
}

function wp_trigger_netlify_build_notice__success() {
    ?>
    <?php if (isset($_GET['settings-updated'])) : ?>
        <div class="notice notice-success">
            <p><?php _e('Your Settings Have Been Updated!', 'WpAdminStyle'); ?></p>
        </div>
    <?php endif; ?>
<?php
}
add_action('admin_notices', 'wp_trigger_netlify_build_notice__success');

 /**
 * Fire Webhook to build Netlify
 */
function wordpress_netlify_enqueue($hook) {
  $options = get_option( 'wp_trigger_netlify_build_settings' );
  wp_enqueue_script( 'wp-trigger-netlify-build', plugin_dir_url( __FILE__ ) . '/js/wp-trigger-netlify-build.js', array(), '20190228', true );
  wp_enqueue_style( 'wp-trigger-netlify-build-styles', plugin_dir_url( __FILE__ ) . '/css/wp-trigger-netlify-build.css');
  wp_localize_script( 'wp-trigger-netlify-build', 'wpTriggerNetlifyBuildVars', $options['wp_trigger_netlify_build_webhook_url'] );
}
add_action( 'admin_enqueue_scripts', 'wordpress_netlify_enqueue' );

/**
 * Create Dashboard Widget for netlify deploy status
 */
function wp_trigger_netlify_build_dashboard_widgets() {
  global $wp_meta_boxes;
  
  wp_add_dashboard_widget('netlify_dashboard_status', 'Netlify Status', 'wp_trigger_netlify_build_dashboard_status');
}
add_action('wp_dashboard_setup', 'wp_trigger_netlify_build_dashboard_widgets');

function wp_trigger_netlify_build_dashboard_status() {
    $options = get_option('wp_trigger_netlify_build_settings');
    $markup = '';
    $markup .= '<a href="' . $options['wp_trigger_netlify_build_status_link'] . '" target="_blank" rel="noopener noreferrer">';
    $markup .= '<img src="' . $options['wp_trigger_netlify_build_status_image'] . '" alt="Netlify Status" />';
    $markup .= '</a>';
    $markup .= '<h1>Trigger a Netlify build manually</h1>';
    $markup .= '<br>';
    $markup .= '<button id="manualNetlifyBuildTrigger" class="button button-primary button-large">Trigger netlify build</button>';
    $markup .= '<script>jQuery("#manualNetlifyBuildTrigger").on("click", function(e) { 
        jQuery.ajax({ 
            type: "POST", 
            url: "' . $options['wp_trigger_netlify_build_webhook_url'] . '", 
            success: function(d) { 
                console.log(d);
                location.reload();
            }
        }); 
    });</script>';
    echo $markup;
}

/**
 * Provide Netlify Icon
 */
function netlify_icon() {
  return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+ICA8cGF0aCBmaWxsPSIjRkZGIiBkPSJNMjguNTg5IDE0LjEzNWwtLjAxNC0uMDA2Yy0uMDA4LS4wMDMtLjAxNi0uMDA2LS4wMjMtLjAxM2EuMTEuMTEgMCAwIDEtLjAyOC0uMDkzbC43NzMtNC43MjYgMy42MjUgMy42MjYtMy43NyAxLjYwNGEuMDgzLjA4MyAwIDAgMS0uMDMzLjAwNmgtLjAxNWMtLjAwNS0uMDAzLS4wMS0uMDA3LS4wMi0uMDE3YTEuNzE2IDEuNzE2IDAgMCAwLS40OTUtLjM4MXptNS4yNTgtLjI4OGwzLjg3NiAzLjg3NmMuODA1LjgwNiAxLjIwOCAxLjIwOCAxLjM1NSAxLjY3NC4wMjIuMDY5LjA0LjEzOC4wNTQuMjA5bC05LjI2My0zLjkyM2EuNzI4LjcyOCAwIDAgMC0uMDE1LS4wMDZjLS4wMzctLjAxNS0uMDgtLjAzMi0uMDgtLjA3IDAtLjAzOC4wNDQtLjA1Ni4wODEtLjA3MWwuMDEyLS4wMDUgMy45OC0xLjY4NHptNS4xMjcgNy4wMDNjLS4yLjM3Ni0uNTkuNzY2LTEuMjUgMS40MjdsLTQuMzcgNC4zNjktNS42NTItMS4xNzctLjAzLS4wMDZjLS4wNS0uMDA4LS4xMDMtLjAxNy0uMTAzLS4wNjJhMS43MDYgMS43MDYgMCAwIDAtLjY1NS0xLjE5M2MtLjAyMy0uMDIzLS4wMTctLjA1OS0uMDEtLjA5MiAwLS4wMDUgMC0uMDEuMDAyLS4wMTRsMS4wNjMtNi41MjYuMDA0LS4wMjJjLjAwNi0uMDUuMDE1LS4xMDguMDYtLjEwOGExLjczIDEuNzMgMCAwIDAgMS4xNi0uNjY1Yy4wMDktLjAxLjAxNS0uMDIxLjAyNy0uMDI3LjAzMi0uMDE1LjA3IDAgLjEwMy4wMTRsOS42NSA0LjA4MnptLTYuNjI1IDYuODAxbC03LjE4NiA3LjE4NiAxLjIzLTcuNTYuMDAyLS4wMWMuMDAxLS4wMS4wMDMtLjAyLjAwNi0uMDI5LjAxLS4wMjQuMDM2LS4wMzQuMDYxLS4wNDRsLjAxMi0uMDA1YTEuODUgMS44NSAwIDAgMCAuNjk1LS41MTdjLjAyNC0uMDI4LjA1My0uMDU1LjA5LS4wNmEuMDkuMDkgMCAwIDEgLjAyOSAwbDUuMDYgMS4wNHptLTguNzA3IDguNzA3bC0uODEuODEtOC45NTUtMTIuOTQyYS40MjQuNDI0IDAgMCAwLS4wMS0uMDE0Yy0uMDE0LS4wMTktLjAyOS0uMDM4LS4wMjYtLjA2LjAwMS0uMDE2LjAxMS0uMDMuMDIyLS4wNDJsLjAxLS4wMTNjLjAyNy0uMDQuMDUtLjA4LjA3NS0uMTIzbC4wMi0uMDM1LjAwMy0uMDAzYy4wMTQtLjAyNC4wMjctLjA0Ny4wNTEtLjA2LjAyMS0uMDEuMDUtLjAwNi4wNzMtLjAwMWw5LjkyMSAyLjA0NmEuMTY0LjE2NCAwIDAgMSAuMDc2LjAzM2MuMDEzLjAxMy4wMTYuMDI3LjAxOS4wNDNhMS43NTcgMS43NTcgMCAwIDAgMS4wMjggMS4xNzVjLjAyOC4wMTQuMDE2LjA0NS4wMDMuMDc4YS4yMzguMjM4IDAgMCAwLS4wMTUuMDQ1Yy0uMTI1Ljc2LTEuMTk3IDcuMjk4LTEuNDg1IDkuMDYzem0tMS42OTIgMS42OTFjLS41OTcuNTkxLS45NDkuOTA0LTEuMzQ3IDEuMDNhMiAyIDAgMCAxLTEuMjA2IDBjLS40NjYtLjE0OC0uODY5LS41NS0xLjY3NC0xLjM1Nkw4LjczIDI4LjczbDIuMzQ5LTMuNjQzYy4wMTEtLjAxOC4wMjItLjAzNC4wNC0uMDQ3LjAyNS0uMDE4LjA2MS0uMDEuMDkxIDBhMi40MzQgMi40MzQgMCAwIDAgMS42MzgtLjA4M2MuMDI3LS4wMS4wNTQtLjAxNy4wNzUuMDAyYS4xOS4xOSAwIDAgMSAuMDI4LjAzMkwyMS45NSAzOC4wNXpNNy44NjMgMjcuODYzTDUuOCAyNS44bDQuMDc0LTEuNzM4YS4wODQuMDg0IDAgMCAxIC4wMzMtLjAwN2MuMDM0IDAgLjA1NC4wMzQuMDcyLjA2NWEyLjkxIDIuOTEgMCAwIDAgLjEzLjE4NGwuMDEzLjAxNmMuMDEyLjAxNy4wMDQuMDM0LS4wMDguMDVsLTIuMjUgMy40OTN6bS0yLjk3Ni0yLjk3NmwtMi42MS0yLjYxYy0uNDQ0LS40NDQtLjc2Ni0uNzY2LS45OS0xLjA0M2w3LjkzNiAxLjY0NmEuODQuODQgMCAwIDAgLjAzLjAwNWMuMDQ5LjAwOC4xMDMuMDE3LjEwMy4wNjMgMCAuMDUtLjA1OS4wNzMtLjEwOS4wOTJsLS4wMjMuMDEtNC4zMzcgMS44Mzd6TS44MzEgMTkuODkyYTIgMiAwIDAgMSAuMDktLjQ5NWMuMTQ4LS40NjYuNTUtLjg2OCAxLjM1Ni0xLjY3NGwzLjM0LTMuMzRhMjE3NS41MjUgMjE3NS41MjUgMCAwIDAgNC42MjYgNi42ODdjLjAyNy4wMzYuMDU3LjA3Ni4wMjYuMTA2LS4xNDYuMTYxLS4yOTIuMzM3LS4zOTUuNTI4YS4xNi4xNiAwIDAgMS0uMDUuMDYyYy0uMDEzLjAwOC0uMDI3LjAwNS0uMDQyLjAwMkg5Ljc4TC44MzEgMTkuODkxem01LjY4LTYuNDAzbDQuNDkxLTQuNDkxYy40MjIuMTg1IDEuOTU4LjgzNCAzLjMzMiAxLjQxNCAxLjA0LjQ0IDEuOTg4Ljg0IDIuMjg2Ljk3LjAzLjAxMi4wNTcuMDI0LjA3LjA1NC4wMDguMDE4LjAwNC4wNDEgMCAuMDZhMi4wMDMgMi4wMDMgMCAwIDAgLjUyMyAxLjgyOGMuMDMuMDMgMCAuMDczLS4wMjYuMTFsLS4wMTQuMDIxLTQuNTYgNy4wNjNjLS4wMTIuMDItLjAyMy4wMzctLjA0My4wNS0uMDI0LjAxNS0uMDU4LjAwOC0uMDg2LjAwMWEyLjI3NCAyLjI3NCAwIDAgMC0uNTQzLS4wNzRjLS4xNjQgMC0uMzQyLjAzLS41MjIuMDYzaC0uMDAxYy0uMDIuMDAzLS4wMzguMDA3LS4wNTQtLjAwNWEuMjEuMjEgMCAwIDEtLjA0NS0uMDUxbC00LjgwOC03LjAxM3ptNS4zOTgtNS4zOThsNS44MTQtNS44MTRjLjgwNS0uODA1IDEuMjA4LTEuMjA4IDEuNjc0LTEuMzU1YTIgMiAwIDAgMSAxLjIwNiAwYy40NjYuMTQ3Ljg2OS41NSAxLjY3NCAxLjM1NWwxLjI2IDEuMjYtNC4xMzUgNi40MDRhLjE1NS4xNTUgMCAwIDEtLjA0MS4wNDhjLS4wMjUuMDE3LS4wNi4wMS0uMDkgMGEyLjA5NyAyLjA5NyAwIDAgMC0xLjkyLjM3Yy0uMDI3LjAyOC0uMDY3LjAxMi0uMTAxLS4wMDMtLjU0LS4yMzUtNC43NC0yLjAxLTUuMzQxLTIuMjY1em0xMi41MDYtMy42NzZsMy44MTggMy44MTgtLjkyIDUuNjk4di4wMTVhLjEzNS4xMzUgMCAwIDEtLjAwOC4wMzhjLS4wMS4wMi0uMDMuMDI0LS4wNS4wM2ExLjgzIDEuODMgMCAwIDAtLjU0OC4yNzMuMTU0LjE1NCAwIDAgMC0uMDIuMDE3Yy0uMDExLjAxMi0uMDIyLjAyMy0uMDQuMDI1YS4xMTQuMTE0IDAgMCAxLS4wNDMtLjAwN2wtNS44MTgtMi40NzItLjAxMS0uMDA1Yy0uMDM3LS4wMTUtLjA4MS0uMDMzLS4wODEtLjA3MWEyLjE5OCAyLjE5OCAwIDAgMC0uMzEtLjkxNWMtLjAyOC0uMDQ2LS4wNTktLjA5NC0uMDM1LS4xNDFsNC4wNjYtNi4zMDN6bS0zLjkzMiA4LjYwNmw1LjQ1NCAyLjMxYy4wMy4wMTQuMDYzLjAyNy4wNzYuMDU4YS4xMDYuMTA2IDAgMCAxIDAgLjA1N2MtLjAxNi4wOC0uMDMuMTcxLS4wMy4yNjN2LjE1M2MwIC4wMzgtLjAzOS4wNTQtLjA3NS4wNjlsLS4wMTEuMDA0Yy0uODY0LjM2OS0xMi4xMyA1LjE3My0xMi4xNDcgNS4xNzMtLjAxNyAwLS4wMzUgMC0uMDUyLS4wMTctLjAzLS4wMyAwLS4wNzIuMDI3LS4xMWEuNzYuNzYgMCAwIDAgLjAxNC0uMDJsNC40ODItNi45NC4wMDgtLjAxMmMuMDI2LS4wNDIuMDU2LS4wODkuMTA0LS4wODlsLjA0NS4wMDdjLjEwMi4wMTQuMTkyLjAyNy4yODMuMDI3LjY4IDAgMS4zMS0uMzMxIDEuNjktLjg5N2EuMTYuMTYgMCAwIDEgLjAzNC0uMDRjLjAyNy0uMDIuMDY3LS4wMS4wOTguMDA0em0tNi4yNDYgOS4xODVsMTIuMjgtNS4yMzdzLjAxOCAwIC4wMzUuMDE3Yy4wNjcuMDY3LjEyNC4xMTIuMTc5LjE1NGwuMDI3LjAxN2MuMDI1LjAxNC4wNS4wMy4wNTIuMDU2IDAgLjAxIDAgLjAxNi0uMDAyLjAyNUwyNS43NTYgMjMuN2wtLjAwNC4wMjZjLS4wMDcuMDUtLjAxNC4xMDctLjA2MS4xMDdhMS43MjkgMS43MjkgMCAwIDAtMS4zNzMuODQ3bC0uMDA1LjAwOGMtLjAxNC4wMjMtLjAyNy4wNDUtLjA1LjA1Ny0uMDIxLjAxLS4wNDguMDA2LS4wNy4wMDFsLTkuNzkzLTIuMDJjLS4wMS0uMDAyLS4xNTItLjUxOS0uMTYzLS41MnoiLz48L3N2Zz4=';
}

function wp_trigger_netlify_build_admin_bar_status($admin_bar)
{
    $options = get_option('wp_trigger_netlify_build_settings');
    $netlifyStatus = '<img style="transform: translateY(5px)" src="' . $options['wp_trigger_netlify_build_status_image'] . '" alt="Netlify Status" />';

    $admin_bar->add_menu(array(
        'id'    => 'netlifyStatus',
        'title' => $netlifyStatus,
        'href'  => $options['wp_trigger_netlify_build_status_link'],
        'meta'  => array(
            'title' => __('Netlify status'),
        ),
    ));
}
add_action('admin_bar_menu', 'wp_trigger_netlify_build_admin_bar_status', 10);
