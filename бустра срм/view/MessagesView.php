<?php

class MessagesView extends View
{
    public function fetch()
    {
    	return $this->design->fetch('messages.tpl');
    }
    
}