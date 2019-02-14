<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 10/4/18
 * Time: 7:54 PM
 */

return [
    'modules' => array_filter([
        'debug' => empty($params['debug.enabled']) ? null : array_filter([
            'class' => \yii\debug\Module::class,
            'allowedIPs' => explode(',', $params['debug.allowedIps']),
            'historySize' => $params['debug.historySize'],
        ]),
    ]),
];