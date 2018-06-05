<?php
    function __autoload($classname){
        $fileName =  str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
        if(!file_exists($fileName)){
            trigger_error('File ['.$fileName.'] no found!', E_USER_ERROR);
            die('File ['.$fileName.'] no found!');
        }
        
        require_once $fileName;
    }
    
    try{
        $monitor = new \monitor\Monitor(include 'config/monitor.conf.php');
        $monitor->loadServices(include 'config/services.conf.php');
    
        $monitor->run();
    }catch(Exception $e){
        trigger_error($e->getMessage(), E_USER_ERROR);
        die($e->getMessage());
    }