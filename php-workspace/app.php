<?php
class App
{
  public static $instance = null;

  public $instance_id = null;
  public $slim;
  public $twig;

  function __construct()
  {
  }

  public static function getInstance()
  {
    if(self::$instance==null)
    {
      self::$instance = new App;
      self::$instance->instance_id = time();
      self::$instance->slim = new \Slim\Slim();
    }
    return self::$instance;
  }
}