<?php

namespace App;

use SimpleXMLElement;

class RssLink
{

    /**
     * saveJSON
     * save the RSS link in the JSON file
     * @param string $url
     * @return void
     */
    public static function saveLink(string $url): void
    {
        $link = self::getLinks($url);
        if (!is_null($link)) {
            $allLinks = self::getJSON();
            $allLinks[] = $link;
            self::saveFile($allLinks);
        }
    }

    /**
     * getLinks
     * get the link for the RSS flux
     * @param string $url
     * @return ?string
     */
    private static function getLinks(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $_SESSION['error'] = "URL non valide <br>";
            header("Location: index");
            return null;
            unset($_SESSION['error']);
        }

        $html = file_get_contents($url);
        preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
        $links = $matches[1];
        $final_links = array();
        $link_count = count($links);
        for ($n = 0; $n < $link_count; $n++) {
            $attributes = preg_split('/\s+/s', $links[$n]);

            foreach ($attributes as $attribute) {
                $att = preg_split('/\s*=\s*/s', $attribute, 2);
                if (isset($att[1])) {
                    $att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
                    $final_link[strtolower($att[0])] = $att[1];
                }
            }
            $final_links[$n] = $final_link;
        }
        for ($n = 0; $n < $link_count; $n++) {
            if (strtolower($final_links[$n]['rel']) == 'alternate') {
                if (strtolower($final_links[$n]['type']) == 'application/rss+xml') {

                    $href = $final_links[$n]['href'];
                }
                if (isset($href) and strtolower($final_links[$n]['type']) == 'text/xml') {
                    #kludge to make the first version of this still work
                    $href = $final_links[$n]['href'];
                }
                if (isset($href)) {
                    if (strstr($href, "http://") !== false) { #if it's absolute
                        $full_url = $href;
                    } else { #otherwise, 'absolutize' it
                        $url_parts = parse_url($url);
                        #only made it work for http:// links. Any problem with this?
                        $full_url = "http://$url_parts[host]";
                        if (isset($url_parts['port'])) {
                            $full_url .= ":$url_parts[port]";
                        }
                        if ($href[0] != '/') { #it's a relative link on the domain
                            $full_url .= dirname($url_parts['path']);
                            if (substr($full_url, -1) != '/') {
                                #if the last character isn't a '/', add it
                                $full_url .= '/';
                            }
                        }
                        $full_url .= $href;
                    }

                    return $href;
                }
            }
        }
        $_SESSION['error'] = 'URL sans flux RSS';
        header("Location: index");
        return null;
        unset($_SESSION['error']);
    }

    /**
     * getJSON
     * get the json data in the json file and parse it
     * @return array
     */
    public static function getJSON(): array
    {
        $json = file_get_contents('data.json');
        $array = json_decode($json, true);
        if (is_null($array)) {
            $array = [];
        }
        return $array;
    }

    /**
     * getRSSLinks
     * get the links from the data json file
     * and return the XML data
     * @return array
     */
    public static function getRSSlinks(): array
    {
        $dataJSON = file_get_contents('data.json');
        $data = json_decode($dataJSON);
        $websites = [];
        foreach ($data as $link) {
            $websites[] = self::parseXML($link);
        }
        return $websites;
    }

    /**
     * parseXML
     * return XML data from RSS link
     * @param string $link
     * @return object
     */
    private static function parseXML(string $link): object
    {
        $content = file_get_contents($link);
        $a = new SimpleXMLElement($content);
        return $a->channel;
    }

    /**
     * deleteLink
     * @param int $id
     * @return void
     */
    public static function deleteLink(int $id): void
    {
        $links = self::getJSON();
        unset($links[$id]);
        self::saveFile($links);
    }


    /**
     * saveFile
     * @param array $links
     * @return void
     */
    private static function saveFile(array $links): void
    {
        $jsonLinks = json_encode($links);
        file_put_contents("data.json", $jsonLinks);
    }
}
