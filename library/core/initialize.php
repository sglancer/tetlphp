<?php

/**
 * Initialization script
 */

call_user_func(function()
{
  // common spec chars
  define('RFC_CHARS', option('allowed_chars', "$-_.+!*'(),"));


  // default charset
  define('CHARSET', option('encoding', 'UTF-8'));


  // global file permissions
  define('PERMS', option('perms', 0777));
  
  
  // default time zone
  $timezone = option('timezone', 'UTC');
  
  date_default_timezone_set($timezone);
  
  define('TIMEZONE', $timezone);
  
  
  // ----------------------------------------------------------------------------

  // OS temp path
  if ( ! is_dir($temporary_files = option('temporary_files')))
  {
    if (function_exists('sys_get_temp_dir'))
    {
      $temporary_files = @sys_get_temp_dir();
    }
    else
    {
      $old = @tempnam('E', '');
      $temporary_files = dirname($old);
      unlink($old);
    }
  }

  define('TMP', is_dir($temporary_files) && is_writable($temporary_files) ? $temporary_files : LIB.DS.'tmp');

  if ( ! is_dir(TMP))
  {
    mkpath(TMP, PERMS);
  }

  
  
  // initialize language settings
  require LIB.DS.'i18n'.DS.'initialize'.EXT;

  
  // default error and exception hanlders
  set_exception_handler(function($E)
  {
    raise(ln('exception_error', array('message' => $E->getMessage(), 'file' => $E->getFile(), 'number' => $E->getLine())));
  });

  set_error_handler(function($errno, $errmsg, $file, $line, $trace)
  {
    if (($errno & error_reporting()) == $errno)
    {
      raise(ln('error_debug', array('error' => $errmsg, 'file' => $file, 'number' => $line)));
      
      return TRUE;
    }
  });
});

/* EOF: ./core/initialize.php */
