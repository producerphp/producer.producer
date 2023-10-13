<?php
declare(strict_types=1);

namespace Producer\Infra;

use Producer\Infra\Exception;

class Fsio
{
    protected string $root;

    public function __construct(string $root)
    {
        $root = DIRECTORY_SEPARATOR . ltrim($root, DIRECTORY_SEPARATOR);
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->root = $root;
    }

    public function path(string $spec) : string
    {
        $spec = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $spec);
        return $this->root . trim($spec, DIRECTORY_SEPARATOR);
    }

    public function get(string $file) : string
    {
        $file = $this->path($file);
        $level = error_reporting(0);
        $result = file_get_contents($file);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $this->throwLastError();
    }

    public function put(string $file, string $data) : int
    {
        $file = $this->path($file);
        $level = error_reporting(0);
        $result = file_put_contents($file, $data);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $this->throwLastError();
    }

    /**
     * @return mixed[]
     */
    public function parseIni(
        string $file,
        bool $sections = false,
        int $mode = INI_SCANNER_NORMAL,
    ) : array
    {
        $file = $this->path($file);
        $level = error_reporting(0);
        $result = parse_ini_file($file, $sections, $mode);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $this->throwLastError();
    }

    public function isFile(string $file) : bool
    {
        $path = $this->path($file);
        return file_exists($path) && is_readable($path);
    }

    public function unlink(string $file) : void
    {
        if ($this->isFile($file)) {
            unlink($this->path($file));
        }
    }

    public function isDir(string $dir) : bool
    {
        $dir = $this->path($dir);
        return is_dir($dir);
    }

    public function mkdir(string $dir, int $mode = 0777, bool $deep = true) : void
    {
        $dir = $this->path($dir);
        $level = error_reporting(0);
        $result = mkdir($dir, $mode, $deep);
        error_reporting($level);

        if ($result !== false) {
            return;
        }

        $this->throwLastError();
    }

    public function rmdir(string $dir) : void
    {
        if ($this->isDir($dir)) {
            rmdir($this->path($dir));
        }
    }

    public function sysTempDir(string $sub = '') : string
    {
        $sub = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sub);
        $dir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . ltrim($sub, DIRECTORY_SEPARATOR);

        if (is_dir($dir)) {
            return $dir;
        }

        $level = error_reporting(0);
        $result = mkdir($dir, 0777, true);
        error_reporting($level);

        if ($result !== false) {
            return $dir;
        }

        $this->throwLastError();
    }

    /**
     * @return string[]
     */
    public function glob(string $pattern, int $flags = 0) : array
    {
        $list = [];
        $pattern = $this->path($pattern);
        $glob = (array) glob($pattern, $flags);

        foreach ($glob as $val) {
            $list[] = substr((string) $val, strlen($this->root));
        }

        return $list;
    }

    /**
     * @return never
     */
    protected function throwLastError() : void
    {
        /** @var array{type: int, message: string, file: string, line: int} */
        $error = error_get_last();

        throw new Exception($error['message']);
    }
}
