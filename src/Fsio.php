<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer;

use Producer\Exception;

/**
 *
 * Filesystem input/output.
 *
 * @package producer/producer
 *
 */
class Fsio
{
    /**
     *
     * The intended filesystem root; this is easy to subvert with '../' in file
     * names.
     *
     * @var string
     *
     */
    protected $root;

    /**
     *
     * Constructor.
     *
     * @param string $root The intended filesystem root; this is easy to
     * subvert with '../' in file names.
     *
     */
    public function __construct($root)
    {
        $root = DIRECTORY_SEPARATOR . ltrim($root, DIRECTORY_SEPARATOR);
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->root = $root;
    }

    /**
     *
     * Prefix the path to a file or directory with the root.
     *
     * @param string $spec The file or directory, relative to the root.
     *
     * @return string
     *
     */
    public function path($spec)
    {
        $spec = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $spec);
        return $this->root . trim($spec, DIRECTORY_SEPARATOR);
    }

    /**
     *
     * Equivalent of file_get_contents(), with error capture.
     *
     * @param string $file The file to read from.
     *
     * @return string
     *
     */
    public function get($file)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = file_get_contents($file);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    /**
     *
     * Equivalent of file_put_contents(), with error capture.
     *
     * @param string $file The file to write to.
     *
     * @param string $data The data to write to the file.
     *
     */
    public function put($file, $data)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = file_put_contents($file, $data);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    /**
     *
     * Equivalent of parse_ini_file(), with error capture.
     *
     * @param string $file The file to read from.
     *
     * @param bool $sections Process sections within the file?
     *
     * @param int $mode The INI scanner mode.
     *
     * @return array
     *
     * @see parse_ini_file()
     *
     */
    public function parseIni($file, $sections = false, $mode = INI_SCANNER_NORMAL)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = parse_ini_file($file, $sections, $mode);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    /**
     *
     * Returns a glob.
     *
     */
    public function glob($pattern, $flags = 0)
    {
        $pattern = $this->path($pattern);
        $glob = glob($pattern, $flags);
        foreach ($glob as $key => $val) {
            $glob[$key] = substr($val, strlen($this->root));
        }
        return $glob;
    }

    /**
     *
     * Checks to see if one of the arguments is a readable file within the root.
     *
     * @param string $file The file to check.
     *
     * @return bool
     *
     */
    public function isFile($file)
    {
        $path = $this->path($file);
        return file_exists($path) && is_readable($path);
    }

    /**
     *
     * Deletes a file, if it exists.
     *
     * @param string $file The file to delete.
     *
     * @return mixed
     *
     */
    public function unlink($file)
    {
        if ($this->isFile($file)) {
            return unlink($this->path($file));
        }
    }

    /**
     *
     * Checks to see if the argument is a directory within the root.
     *
     * @param string $dir The directory to check.
     *
     * @return bool
     *
     */
    public function isDir($dir)
    {
        $dir = $this->path($dir);
        return is_dir($dir);
    }

    /**
     *
     * Makes a directory within the root.
     *
     * @param string $dir The directory to make.
     *
     * @param string $mode The permissions.
     *
     * @param string $deep Create intervening directories?
     *
     */
    public function mkdir($dir, $mode = 0777, $deep = true)
    {
        $dir = $this->path($dir);

        $level = error_reporting(0);
        $result = mkdir($dir, $mode, $deep);
        error_reporting($level);

        if ($result !== false) {
            return;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    /**
     *
     * Removes a directory, if it exists.
     *
     * @param string $dir The directory to remove.
     *
     * @return mixed
     *
     */
    public function rmdir($dir)
    {
        if ($this->isDir($dir)) {
            return rmdir($this->path($dir));
        }
    }

    /**
     *
     * Gets the system temporary directory, with an optional subdirectory;
     * creates the subdirectory if needed.
     *
     * @param string $sub The temporary subdirectory.
     *
     * @return string The path to the temporary directory.
     *
     */
    public function sysTempDir($sub = null)
    {
        $sub = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sub);

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR
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

        $error = error_get_last();
        throw new Exception($error['message']);
    }
}
