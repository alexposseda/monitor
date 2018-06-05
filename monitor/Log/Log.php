<?php
    
    namespace monitor\Log;
    class Log
    {
        protected $_dir      = null;
        protected $_template = '[{date}] {message}';
        protected $_logFile  = 'monitor';
        
        /**
         * Log constructor.
         *
         * @param array $params
         *
         * @throws \Exception
         */
        public function __construct($params = []){
            if(!isset($params['dir'])){
                throw new \Exception('Log Config error! param [dir] is required');
            }
            $this->_dir = $params['dir'];
            if(!is_dir($this->_dir)){
                if(!mkdir($this->_dir, 0777, true)){
                    throw new \Exception('Connot create directory!');
                }
            }
            
            if(isset($params['template'])){
                $this->_template = $params['template'];
            }
        }
        
        /**
         * @param array|string $data
         * @param null|string  $target
         * @param null|string  $tpl
         */
        public function push($data, $target = null, $tpl = null){
            if(!is_array($data)){
                $data = [
                    'datetime'    => date('Y-m-d H:i:s'),
                    'message' => $data
                ];
            }else{
                $data['datetime'] = date('Y-m-d H:i:s');
            }
            
            $template = (is_null($tpl)) ? $this->_template : $tpl;
            $str      = self::pasteDataToTpl($template, $data) . "\n";
            if(is_null($target)){
                $fileName = $this->_logFile;
            }else{
                $fileName = $target;
            }
            $fileName .= '-' . date('Y-m-d') . '.log';
            file_put_contents($this->_dir . DIRECTORY_SEPARATOR . $fileName, $str, FILE_APPEND);
        }
        
        /**
         * @param string $tpl
         * @param array  $data
         * @param string $pref
         *
         * @return mixed
         */
        public static function pasteDataToTpl($tpl, $data, $pref = ''){
            foreach($data as $k => $v){
                if(is_array($v)){
                    $tpl = self::pasteDataToTpl($tpl, $v, $pref . $k . '.');
                }else{
                    $tpl = str_replace('{' . $pref . $k . '}', $v, $tpl);
                }
            }
            
            return $tpl;
        }
    }