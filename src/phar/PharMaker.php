<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/17
 * Time: 20:55
 */

namespace sinri\ark\phar;


use FilesystemIterator;
use Phar;

class PharMaker
{
    protected $pharName;
    protected $directory;
    protected $extensions;
    protected $excludeEntries;
    protected $outputDirectory;
    private $bootstrapStub;

    public function __construct()
    {
        $this->pharName = null;
        $this->directory = null;
        $this->extensions = ['php'];
        $this->excludeEntries = [];
        $this->bootstrapStub = null;
    }

    /**
     * @param string $pharName
     */
    public function setPharName($pharName)
    {
        $this->pharName = $pharName;
    }

    /**
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param string $outputDirectory
     */
    public function setOutputDirectory($outputDirectory)
    {
        $this->outputDirectory = $outputDirectory;
    }

    public function setBootstrapStubAsCLIEntrance($entranceFile = "index.php")
    {
        $this->bootstrapStub = "#!/usr/bin/php" . PHP_EOL
            . "<?php" . PHP_EOL
            . "Phar::mapPhar('" . $this->getPharFileName() . "');" . PHP_EOL
            . "require 'phar://" . $this->getPharFileName() . "/" . $entranceFile . "';" . PHP_EOL
            . "__HALT_COMPILER();" . PHP_EOL
            . "?>";
    }

    protected function getPharFileName()
    {
        return $this->pharName . ".phar";
    }

    /**
     * @param string $extension , such as html, css, etc.
     */
    public function addExtension($extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * @param string $entrance such "builder.php"
     */
    public function addExcludeEntrance($entrance)
    {
        $this->excludeEntries[] = $entrance;
    }

    /**
     * Generate a PHAR package file [phar-name].phar
     * By packaging files with certain [extensions]
     * In the target [directory]
     * And exclude the given [entries] such as builder file.
     *
     * @return bool
     */
    public function archive()
    {
        if (!is_string($this->pharName) || strlen($this->pharName) <= 0) {
            return false;
        }
        if (!is_string($this->directory) || strlen($this->directory) <= 0) {
            return false;
        }
        if (!is_string($this->outputDirectory) || strlen($this->outputDirectory) <= 0) {
            return false;
        }

        $file = $this->getPharFileName();

        $phar = new Phar(
            $this->outputDirectory . '/' . $file,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            $file
        );

        // 开始打包
        $phar->startBuffering();

        // 将后缀名相关的文件打包
        if (is_array($this->extensions)) {
            foreach ($this->extensions as $ext) {
                $phar->buildFromDirectory($this->directory, '/\.' . $ext . '$/');
            }
        }

        // 把builder本身摘除
        if (is_array($this->excludeEntries)) {
            foreach ($this->excludeEntries as $entry) {
                //such as $phar->delete('build.php');
                $phar->delete($entry);
            }
        }

        // 设置入口
        if ($this->bootstrapStub !== null) $phar->setStub($this->bootstrapStub);

        $phar->stopBuffering();

        // 打包完成
        return true;
    }
}