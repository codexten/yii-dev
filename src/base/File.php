<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 4/12/18
 * Time: 8:36 PM
 */

namespace codexten\yii\dev\base;


use Yii;

class File extends \hidev\base\File
{
    public $fileHandlers = [];
    private $_data = [];

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getHandler()
    {
        if (!is_object($this->_handler)) {
            if (isset($this->fileHandlers[$this->type])) {
                $handlerClass = $this->fileHandlers[$this->type];
            } else {
                $handlerClass = 'hidev\handlers\\' . $this->getCtype() . 'Handler';
            }

            $this->_handler = Yii::createObject([
                'class' => $handlerClass,
                'template' => $this->template,
                'goal' => $this->goal,
            ]);
        }

        return $this->_handler;
    }
}