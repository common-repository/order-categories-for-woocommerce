<?php

namespace Woocommerce_Order_Categories;

class Orders
{
    public function __construct()
    {
        //add categories slugs in woocommerce orders table
        add_filter('manage_edit-shop_order_columns', [$this, 'catorderCustomColumn'], 20);
        add_action('manage_shop_order_posts_custom_column', [$this, 'catorderCustomColumnContent']);

        //add filter in woocommerce orders table
        add_action('restrict_manage_posts', [$this,'createOrderFilter'], 10, 2);
        add_filter('request', [$this,'filterOrdersByCategory'], 10, 1);

        //add custom behavior for woocommerce order categories
        add_action('update_option_wc_catorders_mode', [$this,'changeNewOrdersCategoryAssignment']);
        add_action('update_option_wc_previous_order_categories', [$this,'changePreviousOrdersCategoryAssignment'], 10, 3);
        $this->changeNewOrdersCategoryAssignment();
    }

    public function catorderCustomColumn($columns)
    {
        $newColumns = [];
        foreach ($columns as $columnName => $columnInfo) {
            if ('order_total' === $columnName) {
                $newColumns['order_categories'] = __('Categories', 'catorders');
            }
            $newColumns[$columnName] = $columnInfo;
        }
        return $newColumns;
    }

    public function catorderCustomColumnContent($column)
    {
        if ('order_categories' === $column) {
            global $post;
            $terms = get_the_terms($post->ID, 'order_category');
            $slugs = [];
            foreach ($terms as $term) {
                $slugs[] = $term->slug;
            }
            echo implode(' , ', $slugs);
        }
    }

    public function createOrderFilter($postType, $which)
    {
        if ($postType == 'shop_order') {
            $terms = get_terms([
                'taxonomy'      => 'order_category',
                'hide_empty'    => false,
            ]);
            echo '<select name="_shop_order_category" id="dropdown_shop_order_category">';
            echo '<option value="">'.__('All Categories', 'catorders').'</option>';
            
            foreach ($terms as $term) {
                if (isset($_GET['_shop_order_category']) && $_GET['_shop_order_category'] == $term->slug) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                echo '<option value="'.$term->name.'" '.$selected.'>';
                echo $term->name.'</option>';
            }
            echo '</select>';
        }
    }

    public function filterOrdersByCategory($vars)
    {
        global $typenow;
        if ('shop_order' === $typenow && isset($_GET['_shop_order_category']) && ! empty($_GET['_shop_order_category'])) {
            $vars['tax_query']   = [
                [
                    'taxonomy' => 'order_category',
                    'field'    => 'slug',
                    'terms'    => $_GET['_shop_order_category'],
                ]
            ];
        }
        return $vars;
    }

    public function changeNewOrdersCategoryAssignment()
    {
        if (get_option('wc_catorders_mode') == 'automatic') {
            add_action('woocommerce_checkout_update_order_meta', [$this,'setOrderCategoriesAutomatic'], 20, 2);
            remove_action('woocommerce_checkout_update_order_meta', [$this,'setOrderCategoriesManual']);
        } else {
            add_action('woocommerce_checkout_update_order_meta', [$this,'setOrderCategoriesManual'], 20, 2);
            remove_action('woocommerce_checkout_update_order_meta', [$this,'setOrderCategoriesAutomatic']);
        }
    }

    public function changePreviousOrdersCategoryAssignment($old_value, $new_value, $option)
    {
        $args = [
            'limit' => -1,
        ];
        $orders = wc_get_orders($args);
        foreach ($orders as $order) {
            if ($new_value == 'none') {
                $this->setOrderCategoriesManual($order->get_id(), '');
            } else {
                $this->setOrderCategoriesAutomatic($order->get_id(), '');
            }
        }
    }

    public function setOrderCategoriesManual($orderId, $data)
    {
        wp_set_object_terms($orderId, 'uncategorized', 'order_category');
    }

    public function setOrderCategoriesAutomatic($orderId, $data)
    {
        $termSlugs = $this->getSlugs($orderId);
        $first = true;
        $append = false;
        foreach ($termSlugs as $termSlug) {
            if (term_exists($termSlug, 'order_category') !== null) {
                wp_set_object_terms($orderId, $termSlug, 'order_category', $append);
                if ($first) {
                    $first = false;
                    $append = true;
                }
            }
        }
    }

    public function getSlugs($orderId)
    {
        global $wpdb;
        $query = "SELECT T.slug
            FROM wp_woocommerce_order_items AS OI
            INNER JOIN wp_woocommerce_order_itemmeta AS OIM ON OI.order_item_id = OIM.order_item_id
            INNER JOIN wp_term_relationships AS TR ON OIM.meta_value = TR.object_id
            INNER JOIN wp_terms AS T ON TR.term_taxonomy_id = T.term_id
            INNER JOIN wp_term_taxonomy TT ON T.term_id = TT.term_id
            WHERE OI.order_id = ".$orderId."
            AND OIM.meta_key = '_product_id'
            AND TT.taxonomy = 'product_cat'";
        $slugs = array_unique($wpdb->get_col($query));
        return $slugs;
    }
}
