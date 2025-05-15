<?php
/**
 * Plugin Name: Google Tag Manager for WordPress
 * Description: Adiciona o Google Tag Manager no <head> e no <body> do site com opção de configurar o ID e a URL base do GTM no painel.
 * Version: 1.1
 * Author: Leonardo
 */

require 'plugin-update-checker/plugin-update-checker.php'; 
use YahnisElsts\PluginUpdateChecker\v5\PucFactory; 

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/leoviajar/wc-tagmanager',
    __FILE__,
    'wc-tagmanager.php'
);

// Adiciona o menu ao admin
add_action('admin_menu', 'custom_gtm_inserter_menu');
function custom_gtm_inserter_menu() {
    add_menu_page(
        'GTM Config',
        'GTM Config',
        'manage_options',
        'custom-gtm-inserter',
        'custom_gtm_inserter_config_page',
        'dashicons-tag',
        3
    );
}

// Página de configuração
function custom_gtm_inserter_config_page() {
    ?>
    <div class="wrap">
        <h1>Configuração do Google Tag Manager</h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_gtm_inserter_group');
            do_settings_sections('custom-gtm-inserter');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registra as opções
add_action('admin_init', 'custom_gtm_inserter_settings');
function custom_gtm_inserter_settings() {
    register_setting('custom_gtm_inserter_group', 'custom_gtm_id');
    register_setting('custom_gtm_inserter_group', 'custom_gtm_base_url');

    add_settings_section('custom_gtm_inserter_section', 'Configurações do GTM', null, 'custom-gtm-inserter');

    add_settings_field(
        'custom_gtm_id',
        'ID do GTM (ex: GTM-XXXXXX)',
        'custom_gtm_id_callback',
        'custom-gtm-inserter',
        'custom_gtm_inserter_section'
    );

    add_settings_field(
        'custom_gtm_base_url',
        'URL (opcional, ex: https://server.seudominio.com.br )',
        'custom_gtm_base_url_callback',
        'custom-gtm-inserter',
        'custom_gtm_inserter_section'
    );
}

function custom_gtm_id_callback() {
    $gtm_id = esc_attr(get_option('custom_gtm_id'));
    echo "<input type='text' name='custom_gtm_id' value='{$gtm_id}' placeholder='GTM-XXXXXX' style='width: 300px;' />";
}

function custom_gtm_base_url_callback() {
    $gtm_base_url = esc_attr(get_option('custom_gtm_base_url'));
    echo "<input type='text' name='custom_gtm_base_url' value='{$gtm_base_url}' placeholder='https://www.googletagmanager.com' style='width: 400px;' />";
    echo "<p class='description'>Deixe em branco para usar o padrão (googletagmanager.com ). Se preenchido, use o formato completo com https:// e sem barra no final.</p>";
}

// Adiciona o código do GTM no <head>
add_action('wp_head', 'custom_add_gtm_to_header', 1 ); // Prioridade 1 para carregar cedo
function custom_add_gtm_to_header() {
    $gtm_id = esc_attr(get_option('custom_gtm_id'));
    if (!$gtm_id) return;

    $gtm_base_url = esc_url_raw(get_option('custom_gtm_base_url'));
    if (empty($gtm_base_url)) {
        $gtm_base_url = 'https://www.googletagmanager.com';
    }
    // Remove barra final se houver
    $gtm_base_url = rtrim($gtm_base_url, '/' );

    $gtm_src = $gtm_base_url . '/gtm.js?id=' . $gtm_id;
    ?>
    <!-- Google Tag Manager (Custom Loader) -->
    <script>
    (function(w,d,s,l,i,u){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    u+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo $gtm_id; ?>','<?php echo $gtm_src; ?>');
    </script>
    <!-- End Google Tag Manager (Custom Loader) -->
    <?php
}

// Adiciona o código do GTM no <body>
add_action('wp_body_open', 'custom_add_gtm_to_body');
function custom_add_gtm_to_body() {
    $gtm_id = esc_attr(get_option('custom_gtm_id'));
    if (!$gtm_id) return;

    $gtm_base_url = esc_url_raw(get_option('custom_gtm_base_url'));
    if (empty($gtm_base_url)) {
        $gtm_base_url = 'https://www.googletagmanager.com';
    }
    // Remove barra final se houver
    $gtm_base_url = rtrim($gtm_base_url, '/' );

    $noscript_src = $gtm_base_url . '/ns.html?id=' . $gtm_id;
    ?>
    <!-- Google Tag Manager (noscript - Custom Loader) -->
    <noscript><iframe src="<?php echo $noscript_src; ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript - Custom Loader) -->
    <?php
}
?>
