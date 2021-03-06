<?php namespace WooEvents;

use Utils\Utils;
use Utils\View;
use Utils\PluginContext;
use Utils\WooUtils;

class EventList
{
    /**
     * @var View;
     */
    public $view;

    /**
     * @var PluginContext
     */
    public $context;

    function __construct()
    {
        add_shortcode(Event::$key, [$this, 'shortcode']);
        add_action('vc_before_init', [$this, 'vc']);
    }

    function shortcode($options)
    {
        $options      = vc_map_get_attributes(Event::$key, $options);
        $categories   = explode(',', $options['categories']);
        $events       = Event::all();
        $sorted       = Event::sort($options['order'], $events);
        $withCategory = Event::selectByCategories($categories, $sorted);
        $limited      = Utils::array_take_if($options['enable-limit'], $options['limit'], $withCategory);

        /**
         * Translations
         */
        $options = array_merge($options, [
            'all_text'         => __('All', 'woo-events'),
            'order_text'       => __('Order', 'woo-events'),
            'add_to_cart_text' => __('Add to Cart', 'woo-events'),
        ]);

        $assigns = [
            'categories' => $categories,
            'events'     => Utils::toArray($limited),
            'options'    => $options
        ];

        $this->view
            ->render('eventlist', $assigns)
            ->enqueueStyle('event-list')
            ->enqueueScript('event-list');
    }

    function chosenParamType($settings, $value)
    {
        return $this->view->renderString('multiselect', ['settings' => $settings, 'value' => $value]);
    }

    function vc()
    {
        vc_add_shortcode_param('multiselect', [$this, 'chosenParamType']);
        vc_map([
            'name'     => 'WooCommerce Event List',
            'base'     => Event::$key,
            'class'    => '',
            'category' => 'WooCommerce',
            'params'   => $this->params()
        ]);
    }

    function params()
    {
        return [
            [
                'group'       => 'Query',
                'type'        => 'multiselect',
                'heading'     => 'Product Categories',
                'param_name'  => 'categories',
                'save_always' => true,
                'value'       => WooUtils::getProductCategoryNames()
            ],
            [
                'group'      => 'Query',
                'type'       => 'checkbox',
                'heading'    => 'Enable Limit',
                'param_name' => 'enable-limit'
            ],
            [
                'group'      => 'Query',
                'type'       => 'textfield',
                'heading'    => 'Limit',
                'param_name' => 'limit'
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Order',
                'param_name' => 'order',
                'value'      => ['Ascending', 'Descending'],
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Image Proportion (width:height)',
                'param_name' => 'image_proportion',
                'value'      => ['1:1' => 1, '2:3' => 1.66, '4:3' => 0.75]
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Category Filter',
                'param_name' => 'show_category_filter',
                'value'      => ['Show' => 'show', 'Hide' => false]
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Date',
                'param_name' => 'show_date',
                'value'      => ['Show' => 'show', 'Hide' => false]
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
        ];
    }
}