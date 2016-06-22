<?php

class Nectarine_Debug_Errors
{
    /**
     * @var Nectarine_Debug_Errors
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected static $autoladFunctions;

    /**
     * @var array
     */
    protected $whoopsErrorHandler;

    /**
     * @var array
     */
    protected $whoopsExceptionHandler;

    /**
     * Singleton instance
     * @return Nectarine_Debug_Errors
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Nectarine_Debug_Errors();
        }
        return self::$instance;
    }

    /**
     * @param Exception $e
     */
    public static function mageExceptionHandle($e)
    {
        self::$instance->exceptionHandle($e);
    }

    /**
     * attachment
     */
    public function register()
    {
        $this->registerWhoops();
        $this->whoopsErrorHandler = set_error_handler(array($this, 'errorHandle'));
        $this->whoopsExceptionHandler = set_exception_handler(array($this, 'exceptionHandle'));
    }

    /**
     * Register Whoops
     */
    protected function registerWhoops()
    {
        self::$autoladFunctions = spl_autoload_functions();
        foreach (self::$autoladFunctions as $function) {
            spl_autoload_unregister($function);
        }
        spl_autoload_register(array(__CLASS__, 'autoloadWhoops'), true, true);
        $whoops = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->setEditor(function ($file, $line) {
            return "phpstorm:/$file:$line";
        });
        $whoops->pushHandler($handler);
        $whoops->register();
    }

    /**
     * Error Handler
     */
    public function errorHandle()
    {
        $args = func_get_args();
        if (!(error_reporting() & $args[0]))
            return;

        Nectarine_Debug_Toolbar::getInstance()->addError($args);
        // call_user_func_array($this->whoopsErrorHandler, $args);
    }

    /**
     * @param Exception $e
     */
    public function exceptionHandle(Exception $e)
    {
        try {
            call_user_func($this->whoopsExceptionHandler, $e);
        } catch (Exception $e) {
            print_r($e->getMessage());
            die('ExceptionHandler Exception');
        }
    }

    /**
     * Handles autoloading of classes.
     * @param string $class A class name.
     */
    public static function autoloadWhoops($class)
    {
        if (0 !== strpos($class, 'Whoops') && 0 !== strpos($class, 'Symfony\Component\VarDumper\Cloner\VarCloner')) {
            foreach (self::$autoladFunctions as $function) {
                call_user_func($function, $class);
            }
        }

        if (is_file($file = __DIR__ . '/lib/' . str_replace('\\', '/', $class) . '.php')) {
            require $file;
        }
    }

}