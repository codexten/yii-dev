<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 5/12/18
 * Time: 9:51 PM
 */

namespace codexten\yii\dev\components;


use hidev\components\Symlink;
use Yii;
use hidev\helpers\FileHelper;

class Directory extends File
{

    public function save()
    {
        FileHelper::mkdir($this->_path);
        foreach ($this->getItems() as $id => $config) {
            $class = Directory::class;
            if (isset($config['template']) || isset($config['copy'])) {
                $class = File::class;
            } elseif (isset($config['symlink'])) {
                $class = Symlink::class;
            }
            $defaults = [
                'class' => $class,
                'path' => $this->_path . '/' . $id,
                'data' => $this->getData(),
            ];

            $config = array_merge($defaults, $config ?: []);
            $object = Yii::createObject($config);
            $object->save();
        }

        $this->modifyFile();
    }

    public function load()
    {
    }

}