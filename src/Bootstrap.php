<?php

namespace Woocommerce_Order_Categories;

class Bootstrap
{
    public function __construct()
    {
        $this->initializeOrderCategories();
        $this->initializeSettings();
        $this->initializeOrders();
    }

    public function defaultOptions()
    {
        $defaultOptions = [
            'wc_catorders_mode'         => 'manual',
            'previous_order_categories' => 'none',
        ];

        foreach ($defaultOptions as $option => $value) {
            if (!get_option($option) || '' === get_option($option)) {
                add_option($option, $value);
            }
        }
    }

    public function initializeOrderCategories()
    {
        new OrderCategories();
    }

    public function initializeSettings()
    {
        new Settings();
    }

    public function initializeOrders()
    {
        new Orders();
    }
}
