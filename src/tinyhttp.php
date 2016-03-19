<?php

namespace tinyhttp;

class tinyhttp {

  /**
   * an array to hold default request options
   *
   * @var array
   */
  protected static $defaults = [];

  /**
   * parse options
   *
   * @param resource $ch
   * @param array &$options
   * @return void
   */
  protected static function parse_options($ch, array &$options) {
    foreach (static::$defaults as $key => $value) {
      if ($key === 'json' && !array_key_exists('json', $options)) {
        $options['json'] = $value;
        continue;
      }
      if ($key === 'headers') {
        if (!array_key_exists('headers', $options)) {
          $options['headers'] = [];
        }
        $options['headers'] = array_merge($value, $options['headers']);
        continue;
      }
      if (!array_key_exists($key, $options)) {
        $options[$key] = $value;
      }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, static::USER_AGENT);
    curl_setopt($ch, CURLOPT_TIMEOUT, empty($options['timeout']) ? 5000 : $options['timeout']);
    foreach ($options as $key => $value) {
      if ($key === 'headers') {
        static::set_headers($ch, $value);
      }
    }
  }

  /**
   * format http headers and apply them to a curl session
   *
   * @param resource $ch
   * @param array $headers
   */
  protected static function set_headers($ch, array $headers) {
    $h = [];
    foreach ($headers as $header => $value) {
      $h[] = sprintf('%s: %s', $header, $value);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
  }

  /**
   * format query parameters and apply them to the url
   *
   * @param string &$url
   * @param array $options
   * @return void
   */
  protected static function parse_query_params(&$url, array $options) {
    if (!empty($options['query'])) {
      $url .= '?' . http_build_query($options['query']);
    }
  }

  /**
   * format the request body and apply it to a curl session
   *
   * @param resource $ch
   * @param array &$options
   */
  protected static function set_body($ch, array &$options) {
    if ($options['json'] === true) {
      $encoded = json_encode($options['data']);
      $options['headers']['Content-Type'] = 'application/json';
    } else {
      $encoded = http_build_query($options['data']);
      $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
  }

  /**
   * parse the http response and json decode it if necessary
   *
   * @param string $data
   * @param array $options
   * @return string|array
   */
  protected static function parse_response($data, array $options) {
    if ($options['json'] === true) {
      $json = json_decode($data, true);
      if (!is_null($json)) {
        return $json;
      }
    }
    return $data;
  }

  /**
   * set default http request options
   *
   * @param array $options
   */
  public static function set_defaults(array $options) {
    static::$defaults = $options;
  }

  /**
   * clear default http request options
   *
   * @return void
   */
  public static function clear_defaults() {
    static::$defaults = [];
  }

  /**
   * make a GET request
   *
   * @param string $url
   * @param array $options
   * @return string|array
   */
  public static function get($url, array $options = []) {
    $ch = curl_init();
    static::parse_query_params($url, $options);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    static::parse_options($ch, $options);
    return static::parse_response(curl_exec($ch), $options);
  }

  /**
   * make a PUT request
   *
   * @param string $url
   * @param array $options
   * @return string|array
   */
  public static function put($url, array $options = []) {
    $ch = curl_init();
    static::parse_query_params($url, $options);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    static::set_body($ch, $options);
    static::parse_options($ch, $options);
    return static::parse_response(curl_exec($ch), $options);
  }
}
