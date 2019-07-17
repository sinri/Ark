<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:41
 */

namespace sinri\ark\io;


use sinri\ark\core\ArkHelper;

class WebInputIPHelper
{
    const IP_TYPE_V4 = "IPv4";
    const IP_TYPE_V6 = "IPv6";

    private $ip_address;

    public function __construct()
    {
    }

    /**
     * Validate IP Address
     *
     * @param string $ip IP address
     * @param string $which IP protocol, IPv4 or IPv6
     * @return    bool
     * @since 1.3.9 @see CodeIgniter Core
     */
    public function validateIP($ip, $which = '')
    {
        switch (strtolower($which)) {
            case self::IP_TYPE_V4:
                $which = FILTER_FLAG_IPV4;
                break;
            case self::IP_TYPE_V6:
                $which = FILTER_FLAG_IPV6;
                break;
            default:
                $which = NULL;
                break;
        }

        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $which);
    }


    /**
     * @param string $ip
     * @return string|bool IP_TYPE_Vx or FALSE when not validated
     */
    public function determineVersionOfIP($ip)
    {
        if (!$this->validateIP($ip)) return false;
        $v = strpos($ip, ":") === false ? self::IP_TYPE_V4 : self::IP_TYPE_V6;
        return $v;
    }

    /**
     * Fetch the IP Address
     *
     * Determines and validates the visitor's IP address.
     *
     * @param array $proxy_ips
     * @return string IP address
     * @see CodeIgniter Core
     */
    public function detectVisitorIP($proxy_ips = [])
    {
        $ip_address = ArkHelper::readTarget($_SERVER, 'REMOTE_ADDR');

        if ($proxy_ips) {
            $spoof = false;
            foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header) {
                if (($spoof = ArkHelper::readTarget($_SERVER, $header)) !== NULL) {
                    // Some proxies typically list the whole chain of IP
                    // addresses through which the client has reached us.
                    // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
                    sscanf($spoof, '%[^,]', $spoof);

                    if (!$this->validateIP($spoof)) {
                        $spoof = NULL;
                    } else {
                        break;
                    }
                }
            }

            if ($spoof) {
                for ($i = 0, $c = count($proxy_ips); $i < $c; $i++) {
                    // Check if we have an IP address or a subnet
                    if (strpos($proxy_ips[$i], '/') === FALSE) {
                        // An IP address (and not a subnet) is specified.
                        // We can compare right away.
                        if ($proxy_ips[$i] === $ip_address) {
                            $ip_address = $spoof;
                            break;
                        }

                        continue;
                    }

                    // We have a subnet ... now the heavy lifting begins
                    isset($separator) OR $separator = $this->validateIP($ip_address, self::IP_TYPE_V6) ? ':' : '.';

                    // If the proxy entry doesn't match the IP protocol - skip it
                    if (strpos($proxy_ips[$i], $separator) === FALSE) {
                        continue;
                    }

                    // Convert the REMOTE_ADDR IP address to binary, if needed
                    if (!isset($ip, $s_print_f)) {
                        if ($separator === ':') {
                            // Make sure we're have the "full" IPv6 format
                            $ip = explode(':',
                                str_replace('::',
                                    str_repeat(':', 9 - substr_count($ip_address, ':')),
                                    $ip_address
                                )
                            );

                            for ($j = 0; $j < 8; $j++) {
                                $ip[$j] = intval($ip[$j], 16);
                            }

                            $s_print_f = '%016b%016b%016b%016b%016b%016b%016b%016b';
                        } else {
                            $ip = explode('.', $ip_address);
                            $s_print_f = '%08b%08b%08b%08b';
                        }

                        $ip = vsprintf($s_print_f, $ip);
                    }

                    // Split the net_mask length off the network address
                    $net_addr = null;
                    $mask_len = null;
                    sscanf($proxy_ips[$i], '%[^/]/%d', $net_addr, $mask_len);

                    // Again, an IPv6 address is most likely in a compressed form
                    if ($separator === ':') {
                        $net_addr = explode(
                            ':',
                            str_replace(
                                '::',
                                str_repeat(':', 9 - substr_count($net_addr, ':')),
                                $net_addr
                            )
                        );
                        for ($i = 0; $i < 8; $i++) {
                            $net_addr[$i] = intval($net_addr[$i], 16);
                        }
                    } else {
                        $net_addr = explode('.', $net_addr);
                    }

                    // Convert to binary and finally compare
                    if (strncmp($ip, vsprintf($s_print_f, $net_addr), $mask_len) === 0) {
                        $ip_address = $spoof;
                        break;
                    }
                }
            }
        }

        if (!$this->validateIP($ip_address)) {
            $this->ip_address = '0.0.0.0';
        }

        $this->ip_address = $ip_address;

        return $this->ip_address;
    }
}