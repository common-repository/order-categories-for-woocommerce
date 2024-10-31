<?php

namespace Woocommerce_Order_Categories;

class Settings
{
    public function __construct()
    {
        add_filter('woocommerce_settings_tabs_array', [$this, 'addSettingsTab'], 50);
        add_action('woocommerce_settings_tabs_settings_tab_catorders', [$this, 'settingsTab']);
        add_action('woocommerce_update_options_settings_tab_catorders', [$this, 'updateSettings']);
    }

    public function addSettingsTab($settings_tabs)
    {
        $settings_tabs['settings_tab_catorders'] = __('Category orders', 'catorders');

        return $settings_tabs;
    }

    public function settingsTab()
    {
        woocommerce_admin_fields($this->getSettings());
    }

    public function updateSettings()
    {
        woocommerce_update_options($this->getSettings());
    }

    public function getSettings()
    {
        $settings = [
            'section_title' => [
                'name' => __('General Settings', 'catorders'),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_settings_tab_catorders_section_title',
            ],
            'catorders_mode' => [
                'name' => __('Category Orders Mode', 'catorders'),
                'type' => 'select',
                'options' => [
                    'manual' => __('Users add the categories for the placed order manually', 'catorders'),
                    'automatic' => __('Set the products categories directly to the placed order', 'catorders'),
                ],
                'id' => 'wc_catorders_mode',
            ],
            'wc_previous_order_categories' => [
                'name' => __('Category Orders Autocomplete', 'catorders'),
                'type' => 'select',
                'options' => [
                    'none' => __('Previous Orders will remain as "Uncategorized"', 'catorders'),
                    'automatic' => __('Previous orders will be autocompleted', 'catorders'),
                ],
                'id' => 'wc_previous_order_categories',
            ],
            'section_end' => [
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_catorders_section_end',
            ],
        ];
        
        return apply_filters('wc_settings_tab_catorders_settings', $settings);
    }
}
