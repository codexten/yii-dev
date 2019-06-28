<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 10/4/18
 * Time: 9:10 PM
 */

namespace codexten\yii\dev\components;


use Symfony\Component\Yaml\Yaml;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class En extends Component
{
    public function getConfig($type, $repo)
    {
        $ymlConfig = $this->getYmlConfig($repo);
        if (!$ymlConfig) {
            return false;
        }
        $config = ArrayHelper::getValue($ymlConfig, $type, false);
        if (!$config) {
            return false;
        }

        $repoConfig = $this->getRepoConfig($repo);

        return [
            'namespace' => $repoConfig['namespace'],
            $type => $config,
        ];
    }

    protected function getYmlConfig($repo)
    {
        $ymlFile = $this->getYmlFilePath($repo);
        if (!$ymlFile) {
            return false;
        }

        return Yaml::parseFile($ymlFile);
    }

    protected function getYmlFilePath($repo)
    {
        $config = $this->getRepoConfig($repo);
        if (!$config) {
            return false;
        }
        $ymlFile = "{$config['dir']}/src/config/gii.yml";
        if (!file_exists($ymlFile)) {
            $ymlFile = "{$config['dir']}/config/gii.yml";
            if (!file_exists($ymlFile)) {
                return false;
            }
        }

        return $ymlFile;
    }

    public function getParams($repo)
    {
        $ymlConfig = $this->getYmlConfig($repo);
        if (!$ymlConfig) {
            return false;
        }

        return ArrayHelper::getValue($ymlConfig, 'params', []);
    }

    protected function getRepoConfig($repo)
    {
        foreach ($this->getExt()->items as $item) {
            if ($repo == $item['shortName'] || $repo == $item['name']) {
                return $item;
            }
        }

        if ($repo == 'root') {
            return [
                'shortName' => '',
                'version' => '',
                'gitUrl' => '',
                'namespace' => 'core\\',
                'dir' => \Yii::getAlias('@root'),
            ];
        }

        return false;
    }

    /**
     * @return null|object|Ext
     * @throws \yii\base\InvalidConfigException
     */
    public function getExt()
    {
        return \Yii::$app->get('ext');
    }

}
