<?php

interface NotifierInterface
{
    public function sendMessage(string $message, array $params = []);
}