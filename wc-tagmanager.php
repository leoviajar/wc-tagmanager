<?php

/**
 * Plugin Name: Google Tag Manager
 * Description: Adiciona o Google Tag Manager no <head> e no <body> do site com opção de configurar o ID no painel.
 * Version: 1.0
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
add_action('admin_menu', 'gtm_inserter_menu');
function gtm_inserter_menu() {
    add_menu_page(
        'GTM',
        'GTM',
        'manage_options',
        'gtm-inserter',
        'gtm_inserter_config_page',
        'dashicons-tag',
        3
    );
}

// Página de configuração
function gtm_inserter_config_page() {
    ?>
    <div class="wrap">
        <h1>Configuração do Google Tag Manager</h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('gtm_inserter_group');
            do_settings_sections('gtm-inserter');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registra a opção
add_action('admin_init', 'gtm_inserter_settings');
function gtm_inserter_settings() {
    register_setting('gtm_inserter_group', 'gtm_id');

    add_settings_section('gtm_inserter_section', 'ID do GTM', null, 'gtm-inserter');

    add_settings_field(
        'gtm_id',
        'Informe seu ID (ex: GTM-XXXXXX)',
        'gtm_id_callback',
        'gtm-inserter',
        'gtm_inserter_section'
    );
}

function gtm_id_callback() {
    $gtm_id = esc_attr(get_option('gtm_id'));
    echo "<input type='text' name='gtm_id' value='$gtm_id' placeholder='GTM-XXXXXX' style='width: 300px;' />";
}

// Adiciona o código do GTM no <head>
add_action('wp_head', 'add_gtm_to_header');
function add_gtm_to_header() {
    $gtm_id = esc_attr(get_option('gtm_id'));
    if (!$gtm_id) return;
    ?>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo $gtm_id; ?>');
    </script>
    <!-- End Google Tag Manager -->
    <?php
}

// Adiciona o código do GTM no <body>
add_action('wp_body_open', 'add_gtm_to_body');
function add_gtm_to_body() {
    $gtm_id = esc_attr(get_option('gtm_id'));
    if (!$gtm_id) return;
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id; ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
?>