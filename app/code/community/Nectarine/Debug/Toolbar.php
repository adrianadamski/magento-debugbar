<?php

class Nectarine_Debug_Toolbar
{
    const SECTION = 'section';
    const CORE_CATEGORY = 'core';
    const CONFIG_CATEGORY = 'core';
    const EAV_CATEGORY = 'database';
    const DB_CATEGORY = 'database';
    const LAYOUT_CATEGORY = 'template';
    const EVENT_CATEGORY = 'event';

    /**
     * @var Nectarine_Debug_Toolbar
     */
    protected static $instance = null;

    /**
     * Singleton instance
     * @return Nectarine_Debug_Toolbar
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Nectarine_Debug_Toolbar();
        }
        return self::$instance;
    }

    /**
     * @var string
     */
    protected $version;

    /**
     * @var float
     */
    protected $stopTime;

    /**
     * @var float
     */
    protected $memoryUsage;

    /**
     * @var array
     */
    protected $errors = array();

    public function __construct()
    {
        $this->version = Mage::getVersion();
    }

    /**
     * @param float $memory
     * @return $this
     */
    public function setMemoryUsage($memory)
    {
        $this->memoryUsage = $memory;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemoryUsage()
    {
        $base = log($this->memoryUsage, 1024);
        $suffixes = array('B', 'KiB', 'MiB');

        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $err
     * @return $this
     */
    public function addError($err)
    {
        $this->errors[] = $err;
        return $this;
    }

    /**
     * @param float $time
     * @return $this
     */
    public function setStopTime($time)
    {
        $this->stopTime = $time;
        return $this;
    }

    /**
     * @return float
     */
    public function getExecutionTime()
    {
        return $this->stopTime - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Return magento root block
     * @return mixed
     */
    public function getRootBlock()
    {
        return Mage::app()->getLayout()->getBlock('root');
    }

    /**
     * @return mixed
     */
    public function getSqlProfiler()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler();
    }

    /**
     * @param float $seconds
     * @return string
     */
    public function convertSeconds($seconds)
    {
        $suffixes = array('s', 'ms', 'µs', 'ps', 'as');
        $base = ceil(abs(log10($seconds)) / 3);
        return round(pow(10, $base * 3) * $seconds) . $suffixes[$base];
    }

    /**
     * @return array
     */
    public function getTimers()
    {
        $timers = Varien_Profiler::getTimers();
        foreach ($timers as $key => $value) {
            $timers[$key]['sum'] = Varien_Profiler::fetch($key, 'sum');
        }
        return $timers;
    }

    /**
     * @param $errorCode
     * @return string
     */
    public function getErrorMessageByCode($errorCode)
    {
        if ($errorCode == 1)
            return 'E_ERROR';
        if ($errorCode == 2)
            return 'E_WARNING';
        if ($errorCode == 4)
            return 'E_PARSE';
        if ($errorCode == 8)
            return 'E_NOTICE';
        if ($errorCode == 16)
            return 'E_CORE_ERROR';
        if ($errorCode == 32)
            return 'E_CORE_WARNING';
        if ($errorCode == 64)
            return 'E_COMPILE_ERROR';
        if ($errorCode == 128)
            return 'E_COMPILE_WARNING';
        if ($errorCode == 256)
            return 'E_USER_ERROR';
        if ($errorCode == 512)
            return 'E_USER_WARNING';
        if ($errorCode == 1024)
            return 'E_USER_NOTICE';
        if ($errorCode == 2048)
            return 'E_STRICT';
        if ($errorCode == 4096)
            return 'E_RECOVERABLE_ERROR';
        if ($errorCode == 8192)
            return 'E_DEPRECATED';
        if ($errorCode == 16384)
            return 'E_USER_DEPRECATED';
        if ($errorCode == 32767)
            return 'E_ALL';
    }

    /**
     * Determines category based on timer name
     *
     * @param $timerName
     * @return string
     */
    public function getCategory($timerName)
    {
        $category = self::CORE_CATEGORY;

        if (strpos($timerName, 'mage::dispatch') === 0 || strpos($timerName, 'column.phtml') > 0) {
            $category = self::SECTION;
        } else if (strpos($timerName, 'Model_Resource') > 0) {
            $category = self::DB_CATEGORY;
        } else if (strpos($timerName, 'EAV') === 0 || strpos($timerName, '_LOAD_ATTRIBUTE_') === 0 || strpos($timerName, '__EAV_') === 0) {
            $category = self::EAV_CATEGORY;
        } else if (strpos($timerName, 'CORE::create_object_of') === 0) {
            $category = self::CORE_CATEGORY;
        } else if (strpos($timerName, 'OBSERVER') === 0 || strpos($timerName, 'DISPATCH EVENT') === 0) {
            $category = self::EVENT_CATEGORY;
        } else if (strpos($timerName, 'BLOCK') === 0) {
            $category = self::LAYOUT_CATEGORY;
        } else if (strpos($timerName, 'init_config') === 0) {
            $category = self::CONFIG_CATEGORY;
        } else if (strpos($timerName, 'layout/') === 0 || strpos($timerName, 'layout_') > 0) {
            $category = self::LAYOUT_CATEGORY;
        } else if (strpos($timerName, 'Mage_Core_Model_Design') === 0) {
            $category = self::LAYOUT_CATEGORY;
        } else if (strpos($timerName, '.phtml') > 0) {
            $category = self::LAYOUT_CATEGORY;
        }

        return $category;
    }
}