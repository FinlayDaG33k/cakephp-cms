<?php
use Cake\Routing\Router;

return [
    'TinymceElfinder' => [
        'title' => 'File Manager',
        'client_options' => [
            'width' => 900,
            'height' => 500,
            'resizable' => 'yes',
            'soundPath' => '/cakephp_tinymce_elfinder/elfinder/sounds'
        ],
        'static_files' => [
            'js' => [
                'jquery' => 'AdminLTE./plugins/jQuery/jQuery-2.1.4.min',
                'jquery_ui' => 'Cms./plugins/jquery-ui/jquery-ui.min'
            ],
            'css' => [
                'jquery_ui' => 'Cms./plugins/jquery-ui/jquery-ui.min',
                'jquery_ui_theme' => 'Cms./plugins/jquery-ui/jquery-ui.theme.min'
            ]
        ],
        'options' => [
            'debug' => true,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED]
                    'URL' => Router::fullBaseUrl() . '/uploads/cms', // upload main folder
                    'path' => WWW_ROOT . 'uploads/cms', // path to files (REQUIRED]
                    'attributes' => [
                        [
                            'pattern' => '!thumbnails!',
                            'hidden' => true
                        ],
                    ],
                    'tmbPath' => 'thumbnails',
                    'uploadOverwrite' => false,
                    'checkSubfolders' => false,
                    'disabled' => []
                ]
            ],
        ]
    ]
];