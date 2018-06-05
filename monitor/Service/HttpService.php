<?php
    
    namespace monitor\Service;
    class HttpService extends Service
    {
        public $protocol    = 'http';
        public $host        = null;
        public $route       = null;
        public $method      = 'get';
        public $port        = 80;
        public $ip          = null;
        public $userAgent   = null;
        public $timeout     = 60;
        public $referer     = null;
        public $data        = [];
        public $logTemplate = "[{datetime}][{title}][{status}]\nError: {error}\nQuery: {params.protocol}://{params.host}:{params.port}/{params.route}\nMethod: {params.method}\nIp: {params.ip}\nData: {params.data}\nHeaders:\n{response.headers}\n--------------------";
        public $validators  = [
            'response_codes' => [200],
            'execution_time' => 10
        ];
        
        protected $_response        = null;
        protected $_responseHeaders = [];
        protected $_responseDetails = [];
        protected $_i               = 0;
        
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
        
        public function getResult($forLog = false){
            $result = [
                'title'     => $this->title,
                'status'    => $this->_status,
                'params'    => [
                    'protocol'  => $this->protocol,
                    'host'      => $this->host,
                    'port'      => $this->port,
                    'route'     => $this->route,
                    'ip'        => $this->ip,
                    'method'    => $this->method,
                    'userAgent' => $this->userAgent,
                    'timeout'   => $this->timeout,
                    'referer'   => $this->referer,
                    'data'      => $this->data
                ],
                'response'  => [
                    'headers' => $this->_responseHeaders,
                    'details' => $this->_responseDetails,
                    'body'    => $this->_response
                ],
                'error'     => $this->_error,
                'old_state' => $this->_oldState
            ];
            
            if($forLog){
                if(empty($result['error'])){
                    $result['error'] = '-';
                }else{
                    $result['error'] = implode(' AND ', $result['error']);
                }
                
                if(!empty($result['params']['data'])){
                    $result['params']['data'] = urldecode(http_build_query($result['params']['data']));
                }
                
                foreach($result['params'] as $key => $value){
                    if(empty($value)){
                        $result['params'][$key] = '-';
                    }
                }
                
                $headersString = "";
                foreach($result['response']['headers'] as $i => $headers){
                    $headersString .= "----\n";
                    foreach($headers as $k => $v){
                        if(is_numeric($k)){
                            $headersString .= $v . "\n";
                        }else{
                            $headersString .= $k . ": " . $v . "\n";
                        }
                    }
                }
                $result['response']['headers'] = $headersString;
            }
            
            return $result;
        }
        
        /**
         * @return bool
         */
        protected function doRequest(){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $this->protocol . '://' . $this->host . $this->route);
            curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_COOKIEFILE, '');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl,
                        CURLOPT_HEADERFUNCTION,
                        [
                            &$this,
                            'parseHeaderLine'
                        ]);
            
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
                    curl_setopt($curl,
                                CURLOPT_URL,
                                $this->protocol . '://' . $this->host . $this->route . '?' . http_build_query($this->data));
                }
            }
            
            if(!is_null($this->referer)){
                curl_setopt($curl, CURLOPT_REFERER, $this->referer);
            }
            
            $startTime       = time();
            $this->_response = curl_exec($curl);
            $execution_time  = time() - $startTime;
            
            $info = curl_getinfo($curl);
            
            $this->_responseDetails = [
                'code'           => $info['http_code'],
                'type'           => $info['content_type'],
                'execution_time' => $execution_time,
            ];
            
            if($this->_response === false){
                $this->setStatus(self::STATUS_ERROR);
                $this->addError(curl_error($curl));
                curl_close($curl);
                
                return false;
            }
            curl_close($curl);
            
            return true;
        }
        
        /**
         * @param resource $curl
         * @param string   $headerLine
         *
         * @return int
         */
        protected function parseHeaderLine($curl, $headerLine){
            $hl = trim($headerLine, "\r\n");
            if(strlen($hl) > 0){
                if(strpos($hl, ':') === false){
                    $this->_responseHeaders[$this->_i][0] = $hl;
                }else{
                    $t                                              = explode(':', $hl);
                    $key = array_shift($t);
                    $this->_responseHeaders[$this->_i][trim($key)] = implode(':', $t);
                }
            }else{
                $this->_i++;
            }
            
            
            return strlen($headerLine);
        }
        
        protected function validateResponseCodes($allowedCodes){
            if(!in_array($this->_responseDetails['code'], $allowedCodes)){
                $this->setStatus(self::STATUS_ERROR);
                $this->addError('Response code [' . $this->_responseDetails['code'] . ']');
                
                return false;
            }
            $this->setStatus(self::STATUS_SUCCESS);
            
            return true;
        }
        
        protected function validateExecutionTime($maxExecutionTime){
            if($this->_responseDetails['execution_time'] > $maxExecutionTime){
                $this->setStatus(self::STATUS_WARNING);
                $this->addError('Exceeded waiting time! max_execution_time is [' . $maxExecutionTime . '], real_execution_time is [' . $this->_responseDetails['execution_time'] . ']');
                
                return false;
            }
            
            $this->setStatus(self::STATUS_SUCCESS);
            
            return true;
        }
    }