<?php 
class mtfFrequency {
  public function __construct($uuid, $time) {
    $this->uuid = 'mtffrequency_' . $uuid;
    $this->time = $time;
  }
  public function isFree() {
    @session_start();
    
    if (!empty($_SESSION[$this->uuid])) {
      if (time() < $_SESSION[$this->uuid]['expires']) {
        return FALSE;
      } else {
        unset($_SESSION[$this->uuid]); 
      }
    } else {
      $_SESSION[$this->uuid] = array(
        'expires' => time() + $this->time
      );
    }

    return TRUE;
  }
}
?>