<?php
    
    namespace monitor\Messenger;
    
    
    class Messenger
    {
        protected $_messageTemplate;
        protected $_messageLayout;
        protected $_mailer = null;
        
        protected $_messageBlocks = [];
        
        public function __construct($params){
            $this->_messageTemplate = $params['message_template'];
            $this->_messageLayout   = $params['message_layout'];
            unset($params['message_template'], $params['message_layout']);
            $this->_mailer = new SMTPMail($params);
        }
        
        public function createMessage($data){
            ob_start();
            extract($data);
            include $this->_messageTemplate;
            $message                = ob_get_clean();
            $this->_messageBlocks[] = $message;
        }
        
        public function sendMessage($params){
            if(!empty($this->_messageBlocks)){
                $body = $this->compose();
                
                foreach($params['recipients'] as $recipient){
                    $this->_mailer->send($params['from'], $recipient, $params['subject'], $body);
                }
            }
        }
        
        protected function compose(){
            ob_start();
            $messages = $this->_messageBlocks;
            include $this->_messageLayout;
            
            return ob_get_clean();
        }
    }