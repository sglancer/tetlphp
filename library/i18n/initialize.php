<?php

/**
 * Intialize translation backend
 */

call_user_func(function()
{
  $lang = option('language', 'en');
  
  // locales
  if ( ! IS_CLI)
  {
    $out  = array();
    $test = explode(',', value($_SERVER, 'HTTP_ACCEPT_LANGUAGE'));
    
    
    $out[$lang] = 1;
    
    foreach ($test as $one)
    {
      $one = explode(';q=', $one);
      
      if ($lang = trim($one[0]))
      {//FIX
        $out[$lang] = ! empty($one[1]) ? (float) $one[1] : 1;
      }
    }
    
    arsort($out, SORT_NUMERIC);
    $lang = key($out);  
  }
  
  define('LANG', $lang);
  
  @setlocale(LC_ALL, "$lang.UTF-8");

  require __DIR__.DS.'functions'.EXT;
  require __DIR__.DS.'system'.EXT;
  
  i18n::load_path(__DIR__.DS.'locale');
});

/* EOF: ./i18n/initialize.php */
