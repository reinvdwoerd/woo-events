<?php namespace WooEvents;

class Shortcode
{
    function __construct($mustache)
    {
        $this->m = $mustache;
        add_shortcode(Meta::$key, [$this, 'shortcode']);
        add_action('vc_before_init', [$this, 'vc']);
    }

    function shortcode($options)
    {
        $options = shortcode_atts([
            'layout'               => 'List',
            'order'                => 'Ascending',
            'button_text'          => 'Order',
            'show_category_filter' => true,
            'show_date'            => true,
            'title_color'          => '#000',
            'subtitle_color'       => '#666'
        ], $options);

        $events     = Meta::getEvents();
        $categories = [];
        $complete   = [];

        foreach ($events as $event) {
            $meta                     = Meta::get($event->ID);
            $eventArray               = array_merge((array)$event, $meta);
            $product                  = wc_get_product($eventArray['ID']);
            $eventArray['start-date'] = Utils::formatDate($meta['start-date'], $meta['start-time']);
            $eventArray['end-date']   = Utils::formatDate($meta['end-date'], $meta['end-time']);
            $eventArray['price']      = $product->price;

            array_push($complete, $eventArray);
        }


        $assigns = [
            'categories' => ['All', 'Trips', 'Camping'],
            'events'     => $complete,
            'options'    => $options
        ];

        echo $this->m->render('eventlist', $assigns);
        wp_enqueue_script('woo-event-list', plugin_dir_url(__DIR__) . '/js/event-list.js');
        wp_enqueue_style('event-list', plugin_dir_url(__DIR__) . '/styles/event-list.css');
    }

    function vc()
    {
        vc_map([
            'name'     => 'WooCommerce Event List',
            'base'     => Meta::$key,
            'class'    => '',
            'category' => 'woocommerce',
            'params'   => [
                [
                    'group'      => 'Options',
                    'type'       => 'dropdown',
                    'heading'    => 'Layout',
                    'param_name' => 'layout',
                    'value'      => ['Grid' => 'grid', 'List' => 'list'],
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'dropdown',
                    'heading'    => 'Order',
                    'param_name' => 'order',
                    'value'      => ['Ascending', 'Descending'],
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'textfield',
                    'heading'    => 'Order Button Text',
                    'param_name' => 'button_text',
                    'value'      => 'Order',
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'checkbox',
                    'heading'    => 'Show Category Filter',
                    'param_name' => 'show_category_filter',
                    'value'      => true,
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'checkbox',
                    'heading'    => 'Show Date',
                    'param_name' => 'show_date',
                    'value'      => true,
                ],
                [
                    'group'      => 'Colors',
                    'type'       => 'colorpicker',
                    'heading'    => 'Title Color',
                    'param_name' => 'title_color',
                    'value'      => '#000',
                ],
                [
                    'group'      => 'Colors',
                    'type'       => 'colorpicker',
                    'heading'    => 'Subtitle Color',
                    'param_name' => 'subtitle_color',
                    'value'      => '#666',
                ]
            ]
        ]);
    }
}