<?php
/*
Plugin Name: WooCommerce WhatsApp Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce with an WhatsApp gateway.
Version: 1.0
Author: WooThemes
Author URI: http://woothemes.com/
Copyright: © 2009-2011 WooThemes.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

function sb_wc_test_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Gateway_wc_whatsapp extends WC_Payment_Gateway
    {

        public function __construct()
        {
            $this->id = 'wc_whatsapp';
            $this->icon = 'https://www.windsorfitness.mx/wp-content/plugins/its-migs/images/Visa-MasterCard.png';
            $this->has_fields = false;
            $this->method_title = __('Orden de WhatsApp', 'woocommerce');
            $this->method_description = __('Realiza tu pedido mediante WhatsApp.', 'woocommerce');
            $this->init_form_fields();
            $this->init_settings();
            $this->title = 'Orden de WhatsApp';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * See the reference of the API Settings in WooCommerce
         * https://woocommerce.com/document/settings-api/
         */

        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Activar pedidos de WhatsApp', 'woocommerce'),
                    'default' => 'yes',
                ),
                'wa_number' => array(
                    'title' => __('Numero de WhatsApp', 'woocommerce'),
                    'type' => 'text',
                    'id' => 'number',
                    'class' => 'whatsapp-number',
                    'css' => 'CSS rules added line to the input',
                    'placeholder' => '',
                    'default' => __('', 'noob-pay-woo'),
                    'desc_tip' => true,
                    'description' => __('Add a new title for the Noob Payments Gateway that customers will see when they are in the checkout page.', 'noob-pay-woo'),
                ),
                'description' => array(
                    'title' => __('Noob Payments Gateway Description', 'noob-pay-woo'),
                    'type' => 'textarea',
                    'default' => __('Please remit your payment to the shop to allow for the delivery to be made', 'noob-pay-woo'),
                    'desc_tip' => true,
                    'description' => __('Add a new title for the Noob Payments Gateway that customers will see when they are in the checkout page.', 'noob-pay-woo'),
                ),
                'instructions' => array(
                    'title' => __('Instructions', 'noob-pay-woo'),
                    'type' => 'textarea',
                    'default' => __('Default instructions', 'noob-pay-woo'),
                    'desc_tip' => true,
                    'description' => __('Instructions that will be added to the thank you page and odrer email', 'noob-pay-woo'),
                ),
            );
        }

        public function admin_options()
        {
            ?>
            <h2><?php _e('Orden de WhatsApp', 'woocommerce');?></h2>
            <p>Activa esta opcion para activar la opcion de recibir pedidos por WhatsApp.</p>
                <table class="form-table">
                    <?php $this->generate_settings_html();?>
                </table>
            <?php
}

        public function process_payment($order_id)
        {
            global $woocommerce;

            $wa_options = get_option('woocommerce_wc_whatsapp_settings');

            // Get the WhatsApp Number for send the Orders and Sanitize the field.
            $number = preg_replace('/[^0-9]/', '', $wa_options['wa_number']);

            $order = wc_get_order($order_id);

            // if ( $order->get_total() > 0 ) {
            // Mark as on-hold (we're awaiting the cheque).
            // } else {
            // $order->payment_complete();
            // }

            // $this->clear_payment_with_api();

            //Optional
            //$order = wc_get_order( $order_id );
            //$order->update_status( 'on-hold',  __( 'Awaiting Noob Payment', 'noob-pay-woo') );

            // Get $product object from $order / $order_id

            /*
            $items = $order->get_items();

            foreach ($items as $item) {

                $product = $item->get_product();

                // Now you have access to (see above)...
                $product_name = $product->get_name();
                $product_quantity = $product->get_quantity();

            }
            */
        
            $url_wa = 'https://api.whatsapp.com/send?phone='.$number.'&text=hola';
            $order->update_status('on-hold', __('Esperando finalizar orden en WhatsApp', 'noob-pay-woo'));
            //$order->payment_complete();
            // The text for the note
            $note = __("El pedido se esta realizando mediante WhatsApp.");
            // Add the note
            $order->add_order_note($note);

            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();

            return array(
                'result' => 'success',
                //'redirect' => add_query_arg('key', $order->order_key, add_query_arg('order', $order->id, get_permalink(woocommerce_get_page_id('thanks')))),
                //'redirect' => $order->get_checkout_order_received_url()
                'redirect' => $url_wa,
            );
        }

    }

    function add_wc_whatsapp_gateway($methods)
    {
        if (current_user_can('administrator') || WP_DEBUG) {
            $methods[] = 'WC_Gateway_wc_whatsapp';
        }

        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_wc_whatsapp_gateway');

}

add_filter('plugins_loaded', 'sb_wc_test_init');

function admin_style()
{
    wp_enqueue_style('admin-styles', plugin_dir_url(__FILE__) . 'css/admin.css');
    wp_enqueue_script('admin-scripts', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), time(), false);
    wp_enqueue_script('admin-scripts-inputmask', plugin_dir_url(__FILE__) . 'js/jquery.mask.min.js', array('jquery'), time(), false);
}
add_action('admin_enqueue_scripts', 'admin_style');
