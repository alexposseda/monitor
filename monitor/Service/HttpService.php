<?php
    
    namespace monitor\Service;
    class HttpService extends Service
    {
        public $protocol  = 'http://';
        public $host      = null;
        public $route     = null;
        public $method    = 'get';
        public $port      = 80;
        public $ip        = null;
        public $userAgent = null;
        public $timeout   = 60;
        public $referer   = null;
        public $data      = [];
        public $logTemplate = '[{date}][{title}] {message}';
        public $validators = [
            'response_codes' => [200],
            'execution_time' => 10
        ];
        
        protected $_response        = null;
        protected $_responseDetails = [];
    
        /**
         * @throws \Exception
         */
        public function run(){
            if($this->doRequest()){
                $this->validate();
            }
            
            if($this->statusChanged()){
                $this->saveState();
            }
        }
        
        public function getResult(){
            // TODO: Implement getResult() method.
        }
    
        /**
         * @return bool
         */
        protected function doRequest(){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $this->protocol . $this->host . $this->route);
            curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            if(!is_null($this->ip)){
                curl_setopt($curl, CURLOPT_RESOLVE, [$this->host . ':' . $this->port . ':' . $this->ip]);
            }
            
            if(strtolower($this->method) == 'post'){
                curl_setopt($curl, CURLOPT_POST, true);
                
            }
            
            if(!empty($this->data)){
                if($this->method == 'post'){
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                }else{
                    curl_setopt($curl, CURLOPT_URL, $this->protocol . $this->host . $this->route . '?' . http_build_query($this->data));
                }
            }
            
            if(!is_null($this->referer)){
                curl_setopt($curl, CURLOPT_REFERER, $this->referer);
            }
            
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_COOKIEFILE, '');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            
            $startTime       = time();
            $this->_response = curl_exec($curl);
            $execution_time  = time() - $startTime;
            
            $info = curl_getinfo($curl);
            
            curl_close($curl);
            
            $this->_responseDetails = [
                'code'           => $info['http_code'],
                'type'           => $info['content_type'],
                'execution_time' => $execution_time,
            ];
            
            if($this->_response === false){
                $this->addError(curl_error($curl));
                
                return false;
            }
            
            return true;
        }
        
        protected function validateResponseCodes($allowedCodes){
            if(!in_array($this->_responseDetails['code'], $allowedCodes)){
                $this->_status = self::STATUS_ERROR;
                $this->addError('Response code [' . $this->_responseDetails['code'] . ']');
                
                return false;
            }
            
            return true;
        }
        
        protected function validateExecutionTime($maxExecutionTime){
            if($this->_responseDetails['execution_time'] > $maxExecutionTime){
                $this->_status = self::STATUS_WARNING;
                $this->addError('Exceeded waiting time! max_execution_time is [' . $maxExecutionTime . '], real_execution_time is [' . $this->_responseDetails['execution_time'] . ']');
                
                return false;
            }
            
            return true;
        }
    }