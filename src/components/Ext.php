<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 7/27/18
 * Time: 3:13 PM
 */

namespace codexten\yii\dev\components;


use entero\helpers\ArrayHelper;
use hidev\base\GettersTrait;
use Kir\StringUtils\Matching\Wildcards\Pattern;
use yii\base\Component;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * Class Ext
 *
 * @property array $items
 *
 * @package codexten\yii\dev\components
 * @author Jomon Johnson <jomon@entero.in>
 */
class Ext extends Component
{
    use GettersTrait;

    public $vendors = ['eii', 'entero', 'codexten'];
    public $vendorDir = '@vendor';
    public $sourceDir = '/en';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->vendorDir = \Yii::getAlias($this->vendorDir);
        parent::init();
    }


    protected function fetchComposer()
    {
        foreach ($this->items as $package => $config) {
            $this->runCommand($package, "git fetch composer && git fetch --tags composer");
        }
    }

    protected function runCommand($package, $cmd)
    {
        $oldDir = getcwd();
        $sourceDir = $this->getPackageSourceDir($package);
        chdir($sourceDir);
        Console::output("\tRunning {$cmd} in {$sourceDir}", Console::FG_GREEN);
        exec($cmd);
        chdir($oldDir);
    }

    public function link($search)
    {
        $repos = $this->getRepos($search);
        if (empty($repos)) {
            Console::output("{$search} not found");

            return false;
        }
        foreach ($repos as $repo) {
            $packageName = $repo['name'];
            $this->createBk($packageName);
            $this->createSymlink($packageName);
            $this->removeBk($packageName);
            $this->writeLink($repo['shortName']);
            //        $this->fetchComposer();
        }
    }

    public function unLink($search)
    {
        $repos = $this->getRepos($search);
        if (empty($repos)) {
            Console::output("{$search} not found");

            return false;
        }
        foreach ($repos as $repo) {
            $packageName = $repo['name'];
            $this->removeSymlink($packageName);
            $this->removeLink($repo['shortName']);
        }
        Console::output("Please composer update to see changes");
    }

    public function linkAll()
    {
        $links = $this->getLinks();
        foreach ($links as $link) {
            $this->link($link);
        }
    }

    protected function getRepo($repo)
    {
        foreach ($this->items as $item) {
            if ($repo == $item['shortName'] || $repo == $item['name']) {
                return $item;
            }
        }

        return false;
    }

    protected function getRepos($search)
    {
        $repos = [];
        foreach ($this->items as $item) {
            if (Pattern::create($search)->match($item['shortName'])) {
                $repos[] = $item;
            }
        }

        return $repos;
    }

    protected function createSymlink($package)
    {
        Console::output("Creating Symlink for {$package} .....");
        $link = $this->getPackageSourceDir($package);
        $target = $this->getTarget($package);
        $packageDir = $this->getPackageDir($package);

        if (!file_exists($link)) {
            $version = $this->getVersion($package);
            $branch = $this->getBranch($version);
            $gitUrl = $this->getGitUrl($package);
            mkdir($link, 0755, true);
            $this->runCommand($package, "git clone {$gitUrl} {$link} -b {$branch}");
            $this->runCommand($package, "git tag -l ");
            $this->runCommand($package, "git checkout {$branch}");
        }

        exec("rm -rf {$packageDir}");
        exec("ln -s {$link} {$target}");
    }

    protected function removeSymlink($package)
    {
        $packageDir = $this->getPackageDir($package);
        if (!is_link($packageDir)) {
            return false;
        }

        Console::output("Removing Symlink for {$package} .....");
        $sourceDir = $this->getPackageSourceDir($package);
        $bkDir = "{$packageDir}-bk";
        exec("rm -rf {$packageDir}");
        exec("cp -R {$sourceDir} {$bkDir}");
        exec("mv {$bkDir} {$packageDir}");
        exec("rm -rf {$packageDir}/.git");
    }

    protected function getGitUrl($package)
    {
        return $this->items[$package]['gitUrl'];
    }

    protected function getTarget($package)
    {
        $vendorName = $this->getVendorName($package);

        return "{$this->vendorDir}/$vendorName";
    }

    protected function getVendorName($string)
    {
        $string = explode('/', $string);

        return $string[0];
    }

    /**
     * @return array
     */
    protected function getItems()
    {
        $packages = Json::decode(file_get_contents("{$this->vendorDir}/composer/installed.json"));
        $items = [];

        foreach ($packages as $package) {
            $packageName = $package['name'];
            $version = $package['version'];
            $gitUrl = $package['source']['url'];
            if (strpos($version, "dev") !== false) {
                foreach ($this->vendors as $vendor) {
                    if (strpos($gitUrl, $vendor) !== false) {
                        $items[$packageName] = [
                            'name' => $package['name'],
                            'shortName' => $this->filterPackageName($packageName),
                            'version' => $this->filterVersionName($version),
                            'gitUrl' => $gitUrl,
                            'namespace' => isset($package['autoload']) ? $this->getNamespace($package['autoload']) : '',
                            'dir' => \Yii::getAlias("@vendor/{$package['name']}"),
                        ];
                    }
                }
            }
        }

        return $items;
    }

    protected function getNamespace(array $autoloads)
    {
        foreach ($autoloads as $psr) {
            foreach ($psr as $namespace => $dir) {
                if ($dir == 'src') {
                    return $namespace;
                }
            }
        }

        return false;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    protected function filterPackageName($string)
    {
        $string = explode('/', $string);

        return $string[1];
    }

    protected function filterVersionName($string)
    {
        return $string;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    protected function getBranch($string)
    {
        $string = str_replace('dev-', '', $string);
        $string = str_replace('-dev', '', $string);

        return $string;
    }

    protected function getVersion($package)
    {
        return $this->items[$package]['version'];
    }

    protected function createBk($package, $sufix = '-bk')
    {
        Console::output("Creating backup for {$package} .....");
        $packageDir = $this->getPackageDir($package);
        exec("cp -R {$packageDir} {$packageDir}{$sufix}");
    }

    protected function removeBk($package, $sufix = '-bk')
    {
        Console::output("Removing back of {$package} .....");
        $packageDir = $this->getPackageDir($package);
        exec("rm -R {$packageDir}{$sufix}");
    }

    /**
     * @param $package
     *
     * @return string
     */
    protected function getPackageDir($package)
    {
        return "{$this->vendorDir}/$package";
    }

    /**
     * @param $package
     *
     * @return string
     */
    protected function getPackageSourceDir($package)
    {
        $version = $this->items[$package]['version'];
        $vendor = $this->getVendorName($package);
        $package = $this->filterPackageName($package);

        return "{$this->sourceDir}/{$version}/{$vendor}/{$package}";
    }

    protected function getLinks()
    {
        $links = @file_get_contents(\Yii::getAlias('@root/.ext'));
        if ($links) {
            return explode(',', $links);
        }

        return [];
    }

    protected function writeLink($repo)
    {
        $links = $this->getLinks();
        foreach ($links as $key => $link) {
            if ($link == $repo) {
                unset($links[$key]);
            }
        }
        $links[] = $repo;
        $links = implode(',', $links);
        file_put_contents(\Yii::getAlias('@root/.ext'), $links);
    }

    protected function removeLink($repo)
    {
        $links = $this->getLinks();
        foreach ($links as $key => $link) {
            if ($link == $repo) {
                unset($links[$key]);
            }
        }
        $links = implode(',', $links);
        file_put_contents(\Yii::getAlias('@root/.ext'), $links);
    }
}
