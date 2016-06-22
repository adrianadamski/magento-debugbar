<?php

class Handler
{
    /**
     * Start point
     */
    public static function init()
    {
        if (isset($_GET['debug'])) {
            return;
        }

        ob_start();
        if (!file_exists(BP . '/media/js/debugbar.js'))
        	self::generateAsset('js');

        if (!file_exists(BP . '/media/css/debugbar.css'))
        	self::generateAsset('css');

        unset($_SERVER['MAGE_IS_DEVELOPER_MODE']);
        self::includeDummyApp();

        $errors = Nectarine_Debug_Errors::getInstance();
        $errors->register();

        register_shutdown_function('Handler::addDebugToolbar');

        self::enableProfiler();
    }


    /**
     * Include fake Mage_Core_Model_App
     */
    protected static function includeDummyApp()
    {
        if (version_compare(Mage::getVersion(), '1.9', '>=')) {
            require 'Mage/v1.9/App.php';
            return true;
        }

        foreach (explode(PS, get_include_path()) as $includePath) {
            if (file_exists($includePath . '/Mage/Core/Model/App.php')) {
                $code = file_get_contents($includePath . '/Mage/Core/Model/App.php');

                $offset = stripos($code, 'public function run($params)');
                $offset = stripos($code, '{', $offset);
                $newAppCode = substr_replace($code, 'try { ', $offset + 2, 0);

                $endSection = 'return $this;';
                $offset = stripos($newAppCode, $endSection, $offset + 5);

                $newAppCode = substr_replace($newAppCode, '} catch(Exception $e) { call_user_func("Nectarine_Debug_Errors::mageExceptionHandle", $e); }', $offset + strlen($endSection) + 1, 0);
                $newAppCode = str_replace('set_error_handler($handler);', '//Replaced by Nectarine Debug module', $newAppCode);

                eval('?>' . $newAppCode);
                return true;
            }
        }

        return false;
    }

    /**
     * Add DebugToolbar code to body
     */
    public static function addDebugToolbar()
    {
        Nectarine_Debug_Toolbar::getInstance()->setMemoryUsage(memory_get_peak_usage(true));
        Nectarine_Debug_Toolbar::getInstance()->setStopTime(microtime(true));

        $body = ob_get_clean();
        $offset = stripos($body, "</body>");
        if ($offset !== false) {
            ob_start();
            include 'Toolbar/view.php';
            $debugHtml = ob_get_clean();
            $body = substr_replace($body, $debugHtml, $offset - 1, 0);
        }

        echo $body;
    }

    /**
     * Generate assets file by split
     */
    public static function generateAsset($type)
    {
        $dirIt = new RecursiveDirectoryIterator(__DIR__ . '/Toolbar/assets');
        $ite = new RecursiveIteratorIterator($dirIt);
        $files = new RegexIterator($ite, '/^.+\.' . $type . '$/i', RegexIterator::GET_MATCH);

        $input = '';
        foreach ($files as $file) {
            if (file_exists($file[0])) {
                $input .= file_get_contents($file[0]) . " \n";
            }
        }

        if (!is_dir(BP . '/media/' . $type))
            mkdir(BP . '/media/' . $type, 0755, true);

        file_put_contents(BP . '/media/' . $type . '/debugbar.' . $type, $input);
    }

    public static function enableProfiler()
    {
        require 'Mage/Varien/Profiler.php';
        Varien_Profiler::enable();
    }
}

Handler::init();
