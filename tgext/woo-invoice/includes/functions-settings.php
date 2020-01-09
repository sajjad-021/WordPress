<?php
/*
* @Author 		pickplugins
* Copyright: 	pickplugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

global $wooin;


$wooin_shop_info = array(

    'page_nav' 	=> __( 'Shop info', 'woo-invoice' ),
    'priority'  => 10,
	'page_settings' => array(
		
		'section_general' => array(
			'title' 	=> 	__('Shop Information','woo-invoice'),
			'description' 	=> __("Set your shop's basic information here",'woo-invoice'),
			'options' 	=> array(
				array(
					'id'		=> 'wooin_shop_name',
					'title'		=> __('Shop name','woo-invoice'),
					'details'	=> __('Add your shop name here.','woo-invoice'),
					'type'		=> 'text',
					'placeholder'	=> __('Cofee house','woo-invoice'),
				),
				array(
					'id'		=> 'wooin_shop_tagline',
					'title'		=> __('Shop tagline','woo-invoice'),
					'details'	=> __('Add a perfect tagline for your business','woo-invoice'),
					'type'		=> 'text',
					'placeholder'	=> __('Take cofee remove stress','woo-invoice'),
				),
				array(
					'id'		=> 'wooin_shop_address',
					'title'		=> __('Shop address','woo-invoice'),
					'details'	=> __('Add your shop address, so that anyone can find easily','woo-invoice'),
					'type'		=> 'textarea',
					'placeholder'	=> __('Jahaj Company Moor, Rangpur','woo-invoice'),
				),
				array(
					'id'		=> 'wooin_shop_image',
					'title'		=> __('Shop logo','woo-invoice'),
					'details'	=> __('Add your shop logo, it may be visible at the invoice','woo-invoice'),
					'type'		=> 'media',
				),
			)
		),
		
	),
);


$wooin_settings = array(

    'page_nav' 	=> __( 'Settings', 'woo-invoice' ),
    'priority'  => 15,
	'page_settings' => array(
		
		'section_general' => array(
			'title' 	=> 	__('General','woo-invoice'),
			'description' 	=> __("Update some basic settings here",'woo-invoice'),
			'options' 	=> array(
				array(
					'id'		=> 'wooin_invoice_attachment',
					'title'		=> __('Attach invoice','woo-invoice'),
					'details'	=> __('Do you want to send the invoice as attachment on WooCommerce email?','woo-invoice'),
					'type'		=> 'select',
					'args'	    => array( 
                        'yes' => __('Yes','woo-invoice'),
                        'no' => __('No','woo-invoice'), 
                    ),
				),
				array(
					'id'		=> 'wooin_attachment_on_type',
					'title'		=> __('Select E-Mail type','woo-invoice'), 
					'details'	=> __('For which email type you want to send the invoice as attachment.','woo-invoice') . ' ' .
                                   __('This will not measure if <b>Attach Invoice</b> is set not enable!','woo-invoice') . ' ' . 
                                   __('Leave unchecked all, if you want to send attachments always!','woo-invoice'),
					'type'		=> 'checkbox',
					'args'	    => $wooin->get_email_types()
                ),
			)
		),
		
	),
);


$wooin_templates = array(
	'page_nav' => __( 'Templates', 'woo-invoice' ),
	'show_submit' => true,
	'page_settings' => array(),
	'priority' => 20,
);


$wooin_help = array(

    'page_nav' 	=> __( 'Help', 'woo-invoice' ),
    'priority' => 30,
    'show_submit' => false,
	'page_settings' => array(
		
		'section_general' => array(
			'options' 	=> array(


				array(
					'id'		=> 'use_forum',
					'title'		=> __('Ask Question','woo-invoice'),
					'details'	=> 'Please ask any question on our forum if you have any issue <br>
                    <a href="https://www.pickplugins.com/questions/">https://www.pickplugins.com/questions/</a>'
				),

                array(
					'id'		=> 'documentation',
					'title'		=> __('Documentation','woo-invoice'),
                    'details'	=> 'We are updating our documentation for this plugins, pelase check and learn more things we added. <br>
                    <a href="https://www.pickplugins.com/documentation/woocommerce-invoice/">http://pickplugins.com/docs/documentation/woocommerce-invoices/</a>'
				),
				
				array(
					'id'		=> 'demo',
					'title'		=> __('Demo','woo-invoice'),
                    'details'	=> 'Please see the live demo on our server <br>
                    <a href="http://www.pickplugins.com/demo/woo-invoice/">http://www.pickplugins.com/demo/woo-invoice/</a>'
				),
				
			)
		),
		
	),
);


$args = array(
	'add_in_menu'       => true,
	'menu_type'         => 'submenu',
	'menu_title'        => __( 'Woo Invoice', 'woo-invoice' ),
	'page_title'        => __( 'Woo Invoice', 'woo-invoice' ),
	'menu_page_title'   => __( 'WooCommerce Invoice - Settings', 'woo-invoice' ),
	'capability'        => "manage_options",
	'parent_slug'       => "woocommerce",
	'menu_slug'         => "invoice-options",
    'pages' 	        => array(
		'wooin_shop_info'   => $wooin_shop_info,
		'wooin_settings'    => $wooin_settings,
		'wooin_templates'   => $wooin_templates,
		'wooin_help'        => $wooin_help,
	),
);

$Pick_settings = new Pick_settings( $args );


function action_wooin_templates_function(){

    global $wooin;

    $wooin_invoice_template = get_option( 'wooin_invoice_template', 'flat' );
    $wooin_invoice_template = empty( $wooin_invoice_template ) ? 'flat' : $wooin_invoice_template;

    echo '<div class="template-containers">';
    echo '<br><h2>Select a template for Invoices</h2>';
    ?>
    <p>Please see the documentation <a target="_blank" href="https://www.pickplugins.com/documentation/woocommerce-invoice/filter-hooks/wooin_invoice_templates/"><b>here</b></a> how you can add your own template by filter hook.</p>
    <?php

    foreach( $wooin->get_templates() as $template_name => $template ) :

        $label      = isset( $template['label'] ) ? $template['label'] : '';
        $thumb      = isset( $template['thumb'] ) ? $template['thumb'] : '';
        $active     = $wooin_invoice_template == $template_name ? 'active' : '';
        $checekd = $wooin_invoice_template == $template_name ? 'checked' : '';



        printf( '<div class="template-single %s">', $active );
        printf( '<div class="template-label">%s</div>', $label );
        printf( '<div class="template-thumb" style="background:url(%s);"></div>', $thumb );
        printf( '<input type="radio" name="wooin_invoice_template" value="%s" %s />', $template_name, $checekd );
        printf( '</div>' );


    endforeach;

    echo "</div>";
}
add_action( 'wooin_templates', 'action_wooin_templates_function' );



function wooin_filter_options( $whitelist_options ){

    $whitelist_options['wooin_templates'][] = 'wooin_invoice_template';
    return $whitelist_options;
}
add_filter( 'pick_settings_filter_whitelist_options', 'wooin_filter_options', 10, 1 );