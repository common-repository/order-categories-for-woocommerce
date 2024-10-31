<?php

    namespace Woocommerce_Order_Categories;

    class OrderCategories
    {
        public function __construct()
        {
            add_action('init', [$this, 'createNewTaxonomy']);
        }
        
        public function createNewTaxonomy()
        {
            if (!taxonomy_exists('order_category')) {
                $catorderLabels = [
                    'name'              => _x('Order Categories', 'taxonomy general name', 'catorders'),
                    'singular_name'     => _x('Order Category', 'taxonomy singular name', 'catorders'),
                    'search_items'      => __('Search Order Categories', 'catorders'),
                    'all_items'         => __('All Order Categories', 'catorders'),
                    'parent_item'       => __('Parent Order Categories', 'catorders'),
                    'parent_item_colon' => __('Parent Order Category:', 'catorders'),
                    'edit_item'         => __('Edit Order Category', 'catorders'),
                    'update_item'       => __('Update OrderCategory', 'catorders'),
                    'add_new_item'      => __('Add New  Order Category', 'catorders'),
                    'new_item_name'     => __('New Order Category Name', 'catorders'),
                    'menu_name'         => __('Order Category', 'catorders'),
                ];
        
                $catorderArgs = [
                    'hierarchical'      => true,
                    'labels'            => $catorderLabels,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'rewrite'           => ['slug' => 'order_category'],
                ];
                register_taxonomy('order_category', ['shop_order'], $catorderArgs);
                $terms = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                ));
                foreach ($terms as $term) {
                    if($term->parent != 0) {
                        $name = '__'.$term->name;
                    } else {
                        $name = $term->name;
                    }
                    wp_insert_term(
                        $name,
                        'order_category',
                        [   'description'   =>  $term->description,
                            'slug'          =>  $term->slug]
                    );
                }
            }
        }
    }
