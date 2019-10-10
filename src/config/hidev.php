<?php
/**
 * @link https://entero.co.in/
 * @copyright Copyright (c) 2012 Entero Software Solutions Pvt.Ltd
 * @license https://entero.co.in/license/
 * @author Jomon Johnson <jomon@entero.in>
 */

use codexten\yii\dev\components\Ext;
use codexten\yii\dev\components\En;

return [
//    'aliases' => $aliases,
    'components' => [
        'include' => [
            __DIR__ . '/goals.yml',
        ],
        'vcsignore' => [
            'vendor' => 'composer internals',
            '.ext' => 'endev',
        ],
        'ext' => [
            'class' => Ext::class,
        ],
        'en' => [
            'class' => En::class,
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@hidev/views' => ['@yii/views'],
                ],
            ],
        ],
    ],
    'controllerMap' => [
        'ext' => [
            'class' => \codexten\yii\dev\console\ExtController::class,
        ],
    ],
    'container' => [
        'definitions' => [
            //TODO temp fix for https://github.com/hiqdev/hidev/issues/7
            \hidev\base\File::class => [
                'class' => \codexten\yii\dev\base\File::class,
            ],
            \hidev\components\File::class => [
                'class' => \codexten\yii\dev\components\File::class,
            ],
        ],
    ],
];
