<?php

require 'class.sugarRegisterHandler.php';

try{
  $user = 'thomas';
  $pass = 'H3steper!';

  $registerHandler = new sugarRegisterHandler($user, $pass);
  $cases = $registerHandler->getCases();

  error_log(print_r($cases, 1));
}
catch(Exception $e) {
  $error = $e->getMessage();
}

if(isset($error)){
  error_log(print_r($error, 1));
}
