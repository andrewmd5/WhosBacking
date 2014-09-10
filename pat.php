<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 9/10/14
 * Time: 3:22 AM
 */
if (!empty($_POST["url"])) {
    ini_set('max_execution_time', 300); //300 seconds = 5 minutes
    include_once('simple_html_dom.php');
    $pat_url = urldecode($_POST["url"]);
    $output = "---------------------------";


    function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents)
            return $contents;
        else
            return False;
    }


    function getPaterons($remote_html)
    {
        GLOBAL $output;
        GLOBAL $id;
        GLOBAL $total;




        $last   = True;
        // Create DOM from URL or file
        $html = str_get_html($remote_html);
        // Create a DOM object from a string

        // Find all links
        foreach ($html->find('a') as $element) {

            $link = $element->href;
            $name = $element->plaintext;


            if ($last == True) {
                $last = False;
            } else {
                $output .= "<br>";
                $output .= 'Name: ' . $name . '<br>';
                $output .= 'Profile: ' . $link . '<br>';
                $output .= "---------------------------";
                $output .=  "<br>";
                $last = True;
            }
        }
        $total++;
        spider($id, $total);



    }
    function getBetween($content,$start,$end)
    {
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }


    function getPatreonUserID($url) {
        $remote = curl_get_file_contents($url);
        if ($remote == False) {
            return -1;
        }
        $html = str_get_html($remote);
        // Find all links
        foreach ($html->find('a') as $element) {

            $link = $element->href;
            if (strpos($link, '/user?u=') !== FALSE && strpos($link, '&ty=p') !== FALSE) {
                $id = getBetween($link, '/user?u=', '&ty=p');
                return $id;
            }

        }

    }

    function spider($userid, $total) {
        Global $pat_url;
        $url = "http://www.patreon.com/userNext?p=$total&ty=p&srt=&u=$userid";
        $html = curl_get_file_contents($url);
        if(empty($html))
        {
            echo  "Pateron Backers List for $pat_url - Generated at https://aurous.me/who/<br>";
        } else {
            getPaterons($html);
        }
    }


    $id = getPatreonUserID($pat_url);
    if ($id ==  -1) {
        echo "Cannot find any backers with provided url. Try again.";
        die();
        return;
    }
    $total = 1;
    spider($id, $total);
    echo $output;
} else {
    echo "Please enter a pateron URL";
}
