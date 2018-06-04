<?php
    
    namespace monitor\Messenger;
    
    use core\Messenger\SMTPMail;
    
    class Messenger
    {
        protected $_messageTemplate;
        protected $_messageLayout;
        protected $_mailer = null;
        
        protected $_messageBlocks = [];
        
        public function __construct($params){
            $this->_mailer          = new SMTPMail($params['mailer']);
            $this->_messageTemplate = $params['message_template'];
            $this->_messageLayout   = $params['message_layout'];
        }
        
        public function createMessage($data){
            ob_start();
            include $this->_messageTemplate;
            $message                = ob_get_clean();
            $this->_messageBlocks[] = $message;
        }
        
        public function sendMessage($params){
            $body = $this->compose();
            
            foreach($params['recipients'] as $recipient){
                $this->_mailer->send($params['from'], $recipient, $params['subject'], $body);
            }
        }
        
        protected function compose(){
            ob_start();
            $messages = $this->_messageBlocks;
            include $this->_messageLayout;
            
            return ob_get_clean();
        }
    }