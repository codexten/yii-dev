<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 4/12/18
 * Time: 8:36 PM
 */

namespace codexten\yii\dev\components;

use Yii;

class File extends \hidev\components\File
{
    private $_data = [];

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns the file object.
     * Instantiates it if necessary.
     *
     * @return \codexten\yii\dev\base\File
     * @throws \yii\base\InvalidConfigException
     */
    public function getFile()
    {
        if (!is_object($this->_file)) {
            $this->_file = Yii::createObject(array_merge([
                'class' => \codexten\yii\dev\base\File::class,
                'template' => $this->getTemplate(),
                'data' => $this->getData(),
                'goal' => $this,
                'path' => $this->_path ?: $this->id,
            ], is_string($this->_file)
                ? ['path' => $this->_file]
                : (array)$this->_file
            ));
        }

        return $this->_file;
    }
}