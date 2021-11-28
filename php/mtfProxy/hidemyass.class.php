<?php

/**
 * Class for collecting proxies list from hidemyass[dot]com
 * Copyright (C) 2016  Bohdan Manko <mailmanbo@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * ProxyList
 *
 * PHP version 7
 *
 * @author   Bohdan Manko <mailmanbo@gmail.com>
 * @license  http://www.gnu.org/licenses/ GPL v3
 * @link     https://github.com/mkbodanu4/hidemyass-proxies-scraper-php
 */

class ProxyList
{
    private $base = "http://proxylist.hidemyass.com/";
    private $params = array(
        array('ac', 'on'), //all countries [remove line below to exclude. don't forget to remove this line if at least one country excluded]
        array('c[]', 'Angola'),
        array('c[]', 'Argentina'),
        array('c[]', 'Armenia'),
        array('c[]', 'Austria'),
        array('c[]', 'Bangladesh'),
        array('c[]', 'Belgium'),
        array('c[]', 'Brazil'),
        array('c[]', 'Canada'),
        array('c[]', 'Chile'),
        array('c[]', 'China'),
        array('c[]', 'Colombia'),
        array('c[]', 'Czech+Republic'),
        array('c[]', 'Ecuador'),
        array('c[]', 'France'),
        array('c[]', 'Germany'),
        array('c[]', 'Hong+Kong'),
        array('c[]', 'India'),
        array('c[]', 'Indonesia'),
        array('c[]', 'Iran'),
        array('c[]', 'Israel'),
        array('c[]', 'Italy'),
        array('c[]', 'Kenya'),
        array('c[]', 'Korea,+Republic+of'),
        array('c[]', 'Latvia'),
        array('c[]', 'Malaysia'),
        array('c[]', 'Mexico'),
        array('c[]', 'Netherlands'),
        array('c[]', 'Netherlands+Antilles'),
        array('c[]', 'New+Zealand'),
        array('c[]', 'Norway'),
        array('c[]', 'Panama'),
        array('c[]', 'Paraguay'),
        array('c[]', 'Puerto+Rico'),
        array('c[]', 'Reunion'),
        array('c[]', 'Romania'),
        array('c[]', 'Russian+Federation'),
        array('c[]', 'Saudi+Arabia'),
        array('c[]', 'Slovenia'),
        array('c[]', 'South+Africa'),
        array('c[]', 'Spain'),
        array('c[]', 'Sweden'),
        array('c[]', 'Switzerland'),
        array('c[]', 'Taiwan'),
        array('c[]', 'Thailand'),
        array('c[]', 'Trinidad+and+Tobago'),
        array('c[]', 'Turkey'),
        array('c[]', 'United+Arab+Emirates'),
        array('c[]', 'United+Kingdom'),
        array('c[]', 'United+States'),
        array('c[]', 'Venezuela'),
        array('c[]', 'Viet+Nam'),
        array('allPorts', '1'),
        array('p', ''), //exclude this ports, comma separated string, if none - empty string
        array('pr[]', 0), //Protocol - HTTP [remove any of lines below to exclude]
        array('pr[]', 1), //Protocol - HTTPS
        array('pr[]', 2), //Protocol - SOCKS4/SOCKS5
        array('a[]', 0), //Anonymity Level - None [remove any of lines below to exclude]
        array('a[]', 1), //Anonymity Level - Low
        array('a[]', 2), //Anonymity Level - Medium
        array('a[]', 3), //Anonymity Level - High
        array('a[]', 4), //Anonymity Level - High + KA
        array('pl', 'off'), //Planetlab include: "on"  Otherwise comment out
        array('sp[]', 1), //Speed - slow [remove any of lines below to exclude]
        array('sp[]', 2), //Speed - medium
        array('sp[]', 3), //Speed - fast
        array('ct[]', 1), //Connection time - slow [remove any of lines below to exclude]
        array('ct[]', 2), //Connection time - medium
        array('ct[]', 3), //Connection time - fast
        array('s', 0), //Sort by: 0 - Date tested, 1 - Response time, 2 - Connection time, 3 - Country A-Z
        array('o', 0), // Order: 0 - ASC, 1 - DESC
        array('pp', 3), //Per page: 0 - 10, 1 - 25, 2 - 50, 3 - 100
        array('sortBy', 'date') //Sort by: "date" - Date tested, "response_time" - Response time, "connection_time" - Connection time, "country" - Country A-Z
    );
    private $cookies;
    private $error = "";
    private $data = null;
    private $info = null;
    private $errors_separator = "\r\n";

    public function __construct($params = false)
    {
        if($params) {
            $this->params = $params;
        }
    }

    /**
     * Init cookies in tmpfs
     */
    private function init_cookies()
    {
        $this->cookies = tempnam ("/tmp", "CURLCOOKIE");
        if(!$this->cookies || !file_exists($this->cookies)) {
            $this->error .= "Can't create temporary cookies file" . $this->errors_separator;
        }
    }

    /**
     * Remove cookies file from tmpfs
     */
    private function close_cookies()
    {
        if(file_exists($this->cookies)) {
            unlink($this->cookies);
        }
    }

    /**
     * Remove all spaces from string
     * @param string $string Input string
     * @return string Output
     */
    private function removeSpaces($string)
    {
        return @str_replace(' ', '', str_replace(array(
            "\r\n",
            "\r",
            "\n"
        ) , "", preg_replace("/[\\n\\r]+/", "", $string))); // Fix url new line...
    }

    /**
     * Get error string
     * @return string
     */
    public function get_error()
    {
        return $this->error;
    }

    public function set_error_separator($separator = "\r\n")
    {
        $this->errors_separator = $separator;
    }

    /**
     * Get HMA base URL
     * @return string
     */
    public function get_base() {
        return $this->base;
    }

    /**
     * Process parameters
     * @param bool|array $raw input parameters
     * @return array|bool|string
     */
    public function get_params($raw = false) {
        $params = $this->params;

        $rawParams = $params;
        for ($i = 0; $i < count($rawParams); $i++) if (is_array($rawParams[$i])) $rawParams[$i] = implode('=', $rawParams[$i]); else break;
        $rawParams = $this->removeSpaces(str_replace('[]', '%5B%5D', implode('&', $rawParams)));

        return (@$raw) ? $rawParams : $params;
    }


    /**
     * Get page contents via cURL
     * @param string $url content URL
     * @param array $info request details
     * @param array|bool $post post data or false
     * @param string|bool $cookies cookies filename
     * @return bool|mixed result
     */
    private function get_contents($url, &$info, $post = false, $cookies = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if ($post && is_array($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            for ($i = 0; $i < count($post); $i++) if (is_array($post[$i])) $post[$i] = implode('=', $post[$i]); else break;
            $post = $this->removeSpaces(str_replace('[]', '%5B%5D', implode('&', $post)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($cookies) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookies);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'DNT: 1',
            'Referer: ' . parse_url($url, PHP_URL_HOST),
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36',
            'Connection: keep-alive',
            'Content-Length: ' . strlen($post)
        ));

        $output = curl_exec($ch);
        if(curl_error($ch)) {
            $this->error .= curl_error($ch) . $this->errors_separator;
        }
        $info = curl_getinfo($ch);
        if ($ch) curl_close($ch);

        if ($info['http_code'] == 301 || $info['http_code'] == 302) {
            $url = $info['redirect_url'];
            $url_parsed = parse_url($url);
            return (isset($url_parsed)) ? $this->get_contents($url, $info, $post, $cookies) : false;
        }

        return $output;
    }

    /**
     * Get raw JSON from hidemyass
     * @return bool|mixed|null
     */
    public function get_raw()
    {
        $this->init_cookies();
        try {
            $this->data = $this->get_contents($this->base, $this->info, $this->params, $this->cookies);
            $this->close_cookies();
            return $this->data;
        } catch (Exception $e) {
            $this->error .= $e->getMessage() . $this->errors_separator;
            $this->close_cookies();
            return false;
        }
    }

    /**
     * Get parsed data as object
     * @param null $rawProxies
     * @param null $responseCode
     * @return bool|object|array
     */
    public function get($rawProxies = null, $responseCode = null)
    {
        if (!empty($rawProxies) && !empty($responseCode)) {
            $this->data = @$rawProxies;
            $this->info['http_code'] = @$responseCode;
        }

        if(!($this->data && $this->info)) {
            $this->get_raw();
        }

        //if data available and HTTP code of result = 200 (success)
        if (($this->data && $this->info && $this->info['http_code'] == 200)) {
            $json = null;

            //try to parse json
            try {
                $json = json_decode($this->data);
            } catch (Exception $e) {
                $this->error .= $e->getMessage() . $this->errors_separator;
            }

            //if json parsed - read needed data and prepare object with proxies list
            if ($json) {
                $table = $json->table;
                $listUrl = $this->base . $json->url;

                //get all proxies rows
                $rows = array();
                preg_match_all("#\<tr class\=\"[\w\d _-]{0,}\" rel=\"[\d]{1,}\">(.*?)\</tr\>#s", $table, $rows);

                if($rows && $rows[1] && count($rows[1])) {
                    $table = $rows[1];
                    $data = array();

                    foreach ($table as $tr) {
                        //get columns
                        $cols = array();
                        preg_match_all("#\<td(.*?)\>(.*?)\</td\>#s", $tr, $cols);

                        if($cols && $cols[2] && count($cols[2])) {
                            //get "hidden" blocks class names
                            preg_match_all("#\<style>(.*?)\</style\>#s", $cols[2][1], $ip_style);
                            preg_match_all("/\.(.*?)\{display\:none\}/Ui", $ip_style[1][0], $ip_styles);

                            //remove "hidden" blocks
                            if(count($ip_styles[1]) > 0) {
                                foreach($ip_styles[1] as $style) {
                                    $cols[2][1] = preg_replace("/\<[spandiv]{1,} class\=\"".$style."\"\>[\d]{1,}\<\/[spandiv]{1,}\>/Ui","",$cols[2][1]);
                                }
                            }

                            //remove style
                            $ip = preg_replace("/(\<style\>[A-Za-z0-9\t\s -_.,:{}]{1,}\<\/style\>)/Ui","", $cols[2][1]);
                            //remove other "hidden" blocks
                            $ip = preg_replace("/\<[spandiv]{1,} style\=\"display\:none\"\>[\d]{1,}\<\/[spandiv]{1,}\>/Ui","",$ip);

                            //get speed and connection time from style
                            preg_match("/width\:[ ]{0,}([\d]{1,})\%/Ui", $cols[2][4], $speed);
                            preg_match("/width\:[ ]{0,}([\d]{1,})\%/Ui", $cols[2][5], $time);

                            //parse other data
                            $data[] = array(
                                'update' => trim(strip_tags($cols[2][0])),
                                'ip' => trim(strip_tags($ip)),
                                'port' => trim(strip_tags($cols[2][2])),
                                'country' => trim(strip_tags($cols[2][3])),
                                'speed' => trim(strip_tags($speed[1])),
                                'time' => trim(strip_tags($time[1])),
                                'type' => trim(strip_tags($cols[2][6])),
                                'anon' => trim(strip_tags($cols[2][7])),
                            );
                        }
                    }

                    $data['listUrl'] = $listUrl;
                    return $data;
                } else {
                    $this->error .= "Empty response" . $this->errors_separator;
                    return false;
                }
            } else {
                $this->error .= "Can't decode JSON" . $this->errors_separator;
                return false;
            }
        } else {
            $this->error .= "No response, please check your server" . $this->errors_separator;
            return false;
        }
    }

    /**
     * Parse raw list
     * @param $rawProxies
     * @param $responseCode
     * @return bool|object
     */
    public function parse($rawProxies, $responseCode) {
        return $this->get($rawProxies, $responseCode);
    }

}
