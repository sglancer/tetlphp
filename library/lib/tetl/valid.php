<?php

/**
 * Validation utilities library
 */
 
class valid extends prototype
{
  
  /**#@+
   * @ignore
   */
  
  // custom input data
  private static $data = array();
  
  // output errors
  private static $error = array();
  
  // validations
  private static $rules = array();
  
  /**#@-*/
  
  
  
  /**
   * Define rules
   *
   * @param     array Validation ruleset
   * @staticvar Replacements
   * @return    void
   */
  final public static function setup(array $test = array())
  {
    static $fix = array(
              '=' => 'eq_',
              '|' => '_or_',
              '!' => 'not_',
              '<' => 'lt_',
              '>' => 'gt_',
            );
    
    
    valid::$error = array();
    valid::$rules = array_fill_keys(array_keys($test), array());
    
    foreach ($test as $field => $rules)
    {
      foreach ((array) $rules as $key => $one)
      {
        if (is_string($one))
        {
          foreach (array_filter(explode(' ', $one)) as $one)
          {
            $name = slug(strtr($one, $fix), '_', SLUG_STRICT | SLUG_TRIM);
            $name = ! is_num($key) ? $key : $name;
            
            valid::$rules[$field][$name] = $one;
          }
        }
        else
        {
          if (is_string($key) && ! is_num($key))
          {
            valid::$rules[$field][$key] = $one;
          }
          else
          {
            valid::$rules[$field] []= $one;
          }
        }
      }
    }
  }


  /**
   * Execute validation
   *
   * @param  array   Custom data
   * @return boolean
   */
  final public static function done(array $set = array())
  {
    valid::$data = $set;
    
    $ok = 0;
    
    foreach (valid::$rules as $key => $set)
    {
      if ( ! valid::wrong($key, (array) $set))
      {
        $ok += 1;
      }
    }
    
    return sizeof(valid::$rules) === $ok;
  }


  /**
  * Retrieve field error
  *
  * @param  string Name or key
  * @param  string Default value
  * @return string
  */
  final public static function error($name = '', $default = 'required')
  {
    if ( ! func_num_args())
    {
      return valid::$error;
    }
    
    return ! empty(valid::$error[$name]) ? valid::$error[$name] : $default;
  }
  
  
  /**
   * Retrieve field value
   *
   * @param  string Name or key
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function data($name = '', $default = FALSE)
  {
    if ( ! func_num_args())
    {
      return valid::$data;
    }
    
    return value(valid::$data, $name, $default);
  }


  
  /**#@+
   * @ignore
   */
  
  // dynamic validation
  final private static function wrong($name, array $set = array())
  {
    $fail = FALSE;
    $test = value(valid::$data, $name);
    
    if ($key = array_search('required', $set))
    {
      unset($set[$key]);
      
      if ( ! trim($test))
      {//FIX
        $error = 'required';
        $fail  = TRUE;
      }
    }
    
    
    if (trim($test))
    {
      foreach ($set as $error => $rule)
      {
        if (is_callable($rule))
        {
          if ( ! call_user_func($rule, $test))
          {
            $fail = TRUE;
            break;
          }
        }
        elseif ( ! is_false(strpos($rule, '|')))
        {
          $fail = TRUE;
          
          foreach (array_filter(explode('|', $rule)) as $callback)
          {
            if (function_exists($callback) && call_user_func($callback, $test))
            {
              $fail = FALSE;
              break;
            }
          }
          
          if ($fail)
          {
            break;
          }
        }
        elseif (preg_match('/^((?:[!=]=?|[<>])=?)(.+?)$/', $rule, $match))
        {
          $expr = array_shift(valid::vars($match[2]));
          
          $test = ! is_num($test) ? "'$test'" : addslashes($test);
          $expr = ! is_num($expr) ? "'$expr'" : addslashes($expr);
          
          $operator = $match[1];
          
          if ( ! trim($match[1], '!='))
          {
            $operator .= '=';
          }
          
          if ( ! @eval("return $expr $operator $test ?: FALSE;"))
          {
            $fail = TRUE;
            break;
          }
        }
        elseif (($rule[0] === '%') && (substr($rule, -1) === '%'))
        {
          $expr = sprintf('/%s/us', str_replace('/', '\/', substr($rule, 1, -1)));
          
          if ( ! @preg_match($expr, $test))
          {
            $fail = TRUE;
            break;
          }
        }
        elseif (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $rule, $match))
        {
          $negate   = substr($match[1], 0, 1) === '!';
          $callback = $negate ? substr($match[1], 1) : $match[1];
          
          if (function_exists($callback))
          {
            if ( ! isset($match[2]))
            {
              $match[2] = NULL;
            }
            
            
            $args = valid::vars($match[2]);
            array_unshift($args, $test);
    
            $value = call_user_func_array($callback, $args);
            
            if (( ! $value && ! $negate) OR ($value && $negate))
            {
              $fail = TRUE;
              break;
            }
          }
        }
        elseif ( ! in_array($test, valid::vars($rule)))
        {
          $fail = TRUE;
          break;
        }
      }
    }
    
    
    if ($fail && ! empty($error))
    {
      valid::$error[$name] = (string) $error;
    }
    
    return $fail;
  }
  
  // dynamic values
  final private static function vars($test)
  {
    $test = array_filter(explode(',', $test));
    
    foreach ($test as $key => $val)
    {
      if (preg_match('/^([\'"]).*\\1$/', $val))
      {
        $test[$key] = substr(trim($val), 1, -1);
      }
      elseif (is_num($val))
      {
        $test[$key] = $val;
      }
      else
      {
        $test[$key] = value(valid::$data, $val);
      }
    }
    
    return $test;
  }
  
  /**#@-*/
}

/* EOF: ./lib/tetl/valid.php */
