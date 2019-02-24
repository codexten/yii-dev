<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 7/25/18
 * Time: 11:01 PM
 */

namespace codexten\yii\dev\console;

use codexten\yii\dev\components\Ext;
use hidev\base\Controller;
use yii\helpers\Console;

/**
 * Class ExtController
 *
 * @property Ext $ext
 *
 * @package eii\dev\console
 * @author Jomon Johnson <jomon@entero.in>
 */
class ExtController extends Controller
{
    public $defaultAction = 'link';
    protected $linkFile = VENDOR_DIR . '/.ext';

    public function actionLink($repo = null)
    {
        if ($_ENV['IS_SERVER'] == 'true') {
            Console::stdout("\n\n\tCan't git pull in SERVER\n\n");

            return;
        }
        if ($repo === null) {
            $this->ext->linkAll();
        } else {
            $this->ext->link($repo);
        }
    }

    public function actionUnlink($repo)
    {
        $this->ext->unLink($repo);
    }

    public function actionUnlinkAll()
    {
        $this->ext->unLink('*');
    }

    public function afterAction($action, $result)
    {
        exec("chown -R www-data:www-data {$this->ext->sourceDir}");

        return parent::afterAction($action, $result);
    }

    /**
     * @return Ext
     */
    protected function getExt()
    {
        return $this->take('ext');
    }
}