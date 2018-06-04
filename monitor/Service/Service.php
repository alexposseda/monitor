<?php
    
    namespace monitor\Service;
    
    use monitor\Monitor;
    
    abstract class Service
    {
        const STATUS_ERROR   = 'error';
        const STATUS_WARNING = 'warning';
        const STATUS_SUCCESS = 'success';
        
        public $validators  = [];
        public $title;
        public $log         = false;
        public $logTemplate = '[{date}][{title}] {message}';
        
        protected $_hash     = null;
        protected $_error    = [];
        protected $_status   = null;
        protected $_oldState = null;
        
        /**
         * Service constructor.
         *
         * @param string $title
         * @param array  $params
         * @param array  $validators
         * @param bool   $log
         */
        public function __construct($title, $params, $validators = [], $log = false){
            $this->_hash = md5(serialize($params));
            $this->title = $title;
            $this->log   = $log;
            
            foreach($params as $key => $value){
                if(isset($this->$key)){
                    $this->$key = $value;
                }
            }
            
            $this->validators = array_merge($this->validators, $validators);
            
            $this->_oldState = $this->getState();
        }
        
        /**
         * @param array $data
         *
         * @return self
         * @throws \Exception
         */
        public static function getService($data){
            if(!isset($data['type'])){
                throw new \Exception('Service type not defined!');
            }
            $serviceClass = __NAMESPACE__ . '\\' . ucfirst($data['type']) . 'Service';
            if(!class_exists($serviceClass)){
                throw new \Exception('Undefined service [' . $data['type'] . ']');
            }
            
            if(!isset($data['title'])){
                throw new \Exception('Service Config error! param [title] is required');
            }
            
            if(!isset($data['params'])){
                throw new \Exception('Service Config error! param [params] is required');
            }
            
            if(!isset($data['log'])){
                $data['log'] = false;
            }
            
            if(!isset($data['validators'])){
                $data['validators'] = [];
            }
            
            return new $serviceClass($data['title'], $data['params'], $data['validators'], $data['log']);
        }
        
        /**
         * @param string $error
         */
        protected function addError($error){
            $this->_error[] = $error;
        }
        
        abstract public function run();
        
        abstract public function getResult();
        
        /**
         * @return bool
         * @throws \Exception
         */
        protected function validate(){
            foreach($this->validators as $validatorName => $validatorParams){
                $t  = explode('_', $validatorName);
                $vn = 'validate';
                foreach($t as $v){
                    $vn .= ucfirst($v);
                }
                
                if(!method_exists($this, $vn)){
                    throw new \Exception('Validator [' . $validatorName . '] not defined!');
                }
                
                $this->$vn($validatorParams);
            }
            
            return empty($this->_error);
        }
        
        /**
         * @return bool
         */
        public function statusChanged(){
            if(is_null($this->_oldState)){
                return true;
            }
            
            if($this->_oldState['status'] == $this->_status){
                return false;
            }
            
            return true;
        }
        
        protected function saveState(){
            $file = Monitor::getStorageDir() . $this->_hash . '.json';
            $data = [
                'status'  => $this->_status,
                'updated' => time(),
            ];
            
            if(!empty($this->_error)){
                $data['error'] = $this->_error;
            }
            file_put_contents($file, json_encode($data));
        }
        
        /**
         * @return array|null
         */
        protected function getState(){
            $file = Monitor::getStorageDir() . $this->_hash . '.json';
            if(file_exists($file)){
                $json = file_get_contents($file);
                
                return json_decode($json);
            }
            
            return null;
        }
        
        /**
         * @return string
         */
        public function getStatus(){
            return $this->_status;
        }
        
        /**
         * @return null|string
         */
        public function getHash(){
            return $this->_hash;
        }
        
        /**
         * @return array
         */
        public function getError(){
            return $this->_error;
        }
    }