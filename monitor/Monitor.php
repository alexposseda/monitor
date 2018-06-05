<?php
    
    namespace monitor;
    
    use monitor\Log\Log;
    use monitor\Messenger\Messenger;
    use monitor\Service\Service;
    
    class Monitor
    {
        protected static $_storageDir = __DIR__ . '/../storage/';
        public           $version     = '1.0';
        public           $report      = [];
        
        protected $_log       = null;
        protected $_messenger = null;
        protected $_services  = [];
    
        /**
         * Monitor constructor.
         *
         * @param array $config
         *
         * @throws \Exception
         */
        public function __construct($config){
            $logConfig  = (isset($config['log'])) ? $config['log'] : [];
            $this->_log = new Log($logConfig);
            
            if(isset($config['storage'])){
                self::$_storageDir = $config['storage'];
            }
            
            if(isset($config['version'])){
                $this->version = $config['version'];
            }
            
            if(!isset($config['messenger'])){
                throw new \Exception('Messenger config not found!');
            }
            
            $this->_messenger = new Messenger($config['messenger']);
            
            if(!isset($config['report'])){
                throw new \Exception('Report config not found!');
            }
            
            $this->report = $config['report'];
        }
    
        /**
         * @param array $servicesConfig
         */
        public function loadServices($servicesConfig){
            foreach($servicesConfig as $serviceConfig){
                try{
                    $this->_services[] = Service::getService($serviceConfig);
                }catch(\Exception $e){
                    $this->_log->push($e->getMessage());
                    continue;
                }
            }
        }
        
        public function run(){
            foreach($this->_services as $service){
                try{
                    $service->run();
                }catch(\Exception $e){
                    $this->_log->push($e->getMessage());
                    continue;
                }
                
                if($service->log){
                    $this->_log->push($service->getResult(true), $service->getHash(), $service->logTemplate);
                }
                
                if($service->statusChanged()){
                    $this->_messenger->createMessage($service->getResult());
                }
            }
            
            $this->_messenger->sendMessage($this->report);
        }
    
        /**
         * @return string
         */
        public static function getStorageDir(){
            return self::$_storageDir;
        }
    }