<?php

namespace PHPFusion\Administration\Settings\Main;

final class MainSettingsSchema
{
    public static function storage(): array
    {
        return [
            'site_identity' => [
                'sitename' => ['type' => 'text', 'required' => TRUE, 'max' => 255],
                'siteemail' => ['type' => 'email', 'required' => TRUE, 'max' => 128],
                'siteusername' => ['type' => 'text', 'required' => TRUE, 'max' => 32],
            ],
            'site_content' => [
                'siteintro' => ['type' => 'html', 'max' => 10000],
                'description' => ['type' => 'html', 'max' => 10000],
                'footer' => ['type' => 'html', 'max' => 10000],
            ],
            'search' => [
                'keywords' => ['type' => 'text', 'max' => 1000],
                'default_search' => ['type' => 'search_module'],
            ],
            'url' => [
                'site_protocol' => ['type' => 'enum', 'values' => ['http', 'https']],
                'site_host' => ['type' => 'host', 'required' => TRUE, 'max' => 255],
                'site_path' => ['type' => 'path', 'required' => TRUE, 'max' => 255],
                'site_port' => ['type' => 'port'],
                'opening_page' => ['type' => 'text', 'required' => TRUE, 'max' => 100],
            ],
            'domains' => [
                'domain_server' => ['type' => 'domains', 'max' => 4000],
            ],
        ];
    }

    public static function page(array $locale, array $values, array $searchOptions): array
    {
        return [
            'tabs' => [
                [
                    'id' => 'general',
                    'title' => $locale['admins_446'],
                    'sections' => ['site_identity', 'site_content', 'search'],
                ],
                [
                    'id' => 'url',
                    'title' => $locale['admins_447'],
                    'sections' => ['url', 'domains'],
                ],
            ],
            'sections' => [
                'site_identity' => [
                    'title' => $locale['admins_401'],
                    'description' => $locale['admins_401a'],
                    'endpoint' => 'site_identity',
                    'form_id' => 'settings-site-identity',
                    'validate_on_change' => TRUE,
                    'submit' => $locale['admins_750'],
                    'fields' => [
                        ['name' => 'sitename', 'type' => 'text', 'label' => $locale['admins_402'], 'value' => $values['sitename'] ?? '', 'required' => TRUE, 'max_length' => 255],
                        ['name' => 'siteemail', 'type' => 'email', 'label' => $locale['admins_405'], 'value' => $values['siteemail'] ?? '', 'required' => TRUE, 'max_length' => 128],
                        ['name' => 'siteusername', 'type' => 'text', 'label' => $locale['admins_406'], 'value' => $values['siteusername'] ?? '', 'required' => TRUE, 'max_length' => 32],
                    ],
                ],
                'site_content' => [
                    'title' => $locale['admins_414'],
                    'description' => $locale['admins_414a'],
                    'endpoint' => 'site_content',
                    'form_id' => 'settings-site-content',
                    'submit' => $locale['admins_750'],
                    'fields' => [
                        ['name' => 'siteintro', 'type' => 'textarea', 'label' => $locale['admins_407'], 'value' => stripslashes((string)($values['siteintro'] ?? '')), 'options' => ['tiptap' => TRUE, 'tiptap_format' => 'html']],
                        ['name' => 'description', 'type' => 'textarea', 'label' => $locale['admins_409'], 'value' => $values['description'] ?? '', 'options' => ['tiptap' => TRUE, 'tiptap_format' => 'html']],
                        ['name' => 'footer', 'type' => 'textarea', 'label' => $locale['admins_412'], 'value' => stripslashes((string)($values['footer'] ?? '')), 'options' => ['tiptap' => TRUE, 'tiptap_format' => 'html']],
                    ],
                ],
                'search' => [
                    'title' => $locale['admins_414c'],
                    'description' => $locale['admins_414d'],
                    'endpoint' => 'search',
                    'form_id' => 'settings-search',
                    'submit' => $locale['admins_750'],
                    'fields' => [
                        ['name' => 'keywords', 'type' => 'textarea', 'label' => $locale['admins_410'], 'value' => $values['keywords'] ?? '', 'options' => ['autosize' => TRUE, 'ext_tip' => $locale['admins_411']]],
                        ['name' => 'default_search', 'type' => 'select', 'label' => $locale['admins_419'], 'value' => $values['default_search'] ?? 'all', 'choices' => $searchOptions],
                    ],
                ],
                'url' => [
                    'title' => $locale['admins_401a'],
                    'description' => $locale['admins_401b'],
                    'endpoint' => 'url',
                    'form_id' => 'settings-url',
                    'validate_on_change' => TRUE,
                    'submit' => $locale['admins_750'],
                    'fields' => [
                        ['name' => 'site_protocol', 'type' => 'select', 'label' => $locale['admins_426'], 'value' => $values['site_protocol'] ?? 'https', 'choices' => ['http' => 'http://', 'https' => 'https://']],
                        ['name' => 'site_host', 'type' => 'text', 'label' => $locale['admins_427'], 'value' => $values['site_host'] ?? '', 'required' => TRUE, 'max_length' => 255],
                        ['name' => 'site_path', 'type' => 'text', 'label' => $locale['admins_429'], 'value' => $values['site_path'] ?? '/', 'required' => TRUE, 'max_length' => 255],
                        ['name' => 'site_port', 'type' => 'number', 'label' => $locale['admins_430'], 'value' => $values['site_port'] ?? '', 'options' => ['number_min' => 1, 'number_max' => 65535, 'ext_tip' => $locale['admins_430_desc']]],
                        ['name' => 'opening_page', 'type' => 'text', 'label' => $locale['admins_413'], 'value' => $values['opening_page'] ?? '', 'required' => TRUE, 'max_length' => 100],
                    ],
                    'aside_template' => __DIR__.'/templates/url-preview.php',
                ],
                'domains' => [
                    'title' => $locale['admins_444'],
                    'description' => $locale['admins_444b'],
                    'endpoint' => 'domains',
                    'form_id' => 'settings-domains',
                    'submit' => $locale['admins_750'],
                    'fields' => [
                        ['name' => 'domain_server', 'type' => 'textarea', 'label' => '', 'value' => str_replace('|', PHP_EOL, (string)($values['domain_server'] ?? '')), 'options' => ['autosize' => TRUE, 'placeholder' => "example1.com\nexample2.com\n", 'ext_tip' => $locale['admins_444a']]],
                    ],
                ],
            ],
        ];
    }
}
