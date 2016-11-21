<?php namespace WooEvents;

class Utils
{
    /**
     * PHP
     */
    static function pluck($array, $key)
    {
        return array_values(array_map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key];
            } else {
                return $item->{$key};
            }
        }, $array));
    }

    /**
     * Wordpress
     */

    /**
     * WooCommerce
     */

    /**
     * @param array $args Additional query arguments
     * @return Array [String] of product categories
     */
    static function getProductCategories($args = [])
    {
        $categories = get_categories(array_merge(['taxonomy' => 'product_cat'], $args));
        return array_values(self::pluck($categories, 'cat_name'));
    }

    /**
     * @param $date String
     * @param $time String
     * @return String date formatted to WooCommerce preferences
     */
    static function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }


    /**
     * Events
     */

    /**
     * @param $categories Array[String] of category names
     * @param $events     Array of events
     * @return Filtered Array of Events
     */
    static function selectEventsByCategories($categories, $events)
    {
        return array_values(array_filter($events, function ($event) use ($categories) {
            return in_array($event['product_cat'], $categories);
        }));
    }

    /**
     * @param $filter 'Show' or 'Only' or 'Hide'
     * @param $events Array of events with end-date
     * @return Filtered Array of events
     */
    static function filterExpiredEvents($filter, $events)
    {
        return array_filter($events, function ($event) use ($filter) {
            $isExpired = self::isExpired($event);

            switch ($filter) {
                case 'Show':
                    return true;
                case 'Only':
                    return $isExpired;
                case 'Hide':
                    return !$isExpired;
            }
        });
    }

    /**
     * @param $event An event with end-date
     * @return bool Whether the event is expired
     */
    static function isExpired($event)
    {
        return time() > strtotime($event['end-date']);
    }

    /**
     * @param $events Array of events
     * @param $order  'Ascending' or 'Descending'
     * @return Sorted Array of events
     */
    static function sortEvents($events, $order)
    {
        $orderModifier = $order == 'Ascending' ? 1 : -1;

        usort($events, function ($a, $b) use ($orderModifier) {
            return (strtotime($a['start-date']) - strtotime($b['start-date'])) * $orderModifier;
        });

        return $events;
    }

    /**
     * @param $events Array of products with woo-events meta
     * @return Array of extended events
     */
    static function prepareEvents($events)
    {
        return array_map(function ($event) {
            $meta                          = Model::getMeta($event->ID);
            $eventArray                    = array_merge((array)$event, $meta);
            $product                       = wc_get_product($eventArray['ID']);
            $eventArray['start-date']      = self::formatDate($meta['start-date'], $meta['start-time']);
            $eventArray['end-date']        = self::formatDate($meta['end-date'], $meta['end-time']);
            $eventArray['price']           = $product->price;
            $eventArray['image']           = wp_get_attachment_image_src(get_post_thumbnail_id($event->ID))[0];
            $eventArray['post_excerpt']    = substr($eventArray['post_content'], 0, 140) . "...";
            $eventArray['product_cat']     = wp_get_post_terms($event->ID, 'product_cat')[0]->name;
            $eventArray['permalink']       = get_permalink($event->ID);
            $eventArray['add_to_cart_url'] = $product->add_to_cart_url();

            return $eventArray;
        }, $events);
    }
}