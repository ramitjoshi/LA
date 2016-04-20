<?php

//error_reporting(0);


function getGlobalVar($name, $default = null)
{
    $rs = mysql__("select * from global_vars");
    $rw = mysql_fetch_array($rs);
    return (isset($rw[$name])) ? $rw[$name] : $default;
}

function loadGlobalVars()
{
    $rs = mysql__("select * from global_vars");
    while ($rw = mysql_fetch_array($rs)) {
        $_SESSION['global_vars']['daily_free_post_limit_pr1'] = $rw['daily_free_post_limit_pr1'];
        $_SESSION['global_vars']['daily_free_post_limit_pr2'] = $rw['daily_free_post_limit_pr2'];
        $_SESSION['global_vars']['daily_free_post_limit_pr3'] = $rw['daily_free_post_limit_pr3'];
        $_SESSION['global_vars']['daily_free_post_limit_pr4'] = $rw['daily_free_post_limit_pr4'];
        $_SESSION['global_vars']['daily_free_post_limit_pr5'] = $rw['daily_free_post_limit_pr5'];
        $_SESSION['global_vars']['daily_free_post_limit_pr6'] = $rw['daily_free_post_limit_pr6'];
        $_SESSION['global_vars']['daily_free_post_limit_pr7'] = $rw['daily_free_post_limit_pr7'];
        $_SESSION['global_vars']['daily_free_post_limit_pr8'] = $rw['daily_free_post_limit_pr8'];
        $_SESSION['global_vars']['daily_free_post_limit_pr9'] = $rw['daily_free_post_limit_pr9'];
        $_SESSION['global_vars']['daily_free_post_limit_pr10'] = $rw['daily_free_post_limit_pr10'];
        $_SESSION['global_vars']['paypal_grace_period_days'] = $rw['paypal_grace_period_days'];
        $_SESSION['global_vars']['vat_percent'] = $rw['vat_percent'];

        $_SESSION['global_vars']['announcement'] = $rw['announcement'];
        $_SESSION['global_vars']['announcement_status'] = $rw['announcement_status'];

        $_SESSION['global_vars']['post_limit_per_website'] = $rw['post_limit_per_website'];
        $_SESSION['global_vars']['single_quota_cost'] = $rw['single_quota_cost'];
        $_SESSION['global_vars']['affiliate_commision_percent'] = $rw['affiliate_commision_percent'];
        $_SESSION['global_vars']['upgrade_price'] = $rw['upgrade_price'];
        $_SESSION['global_vars']['paypal_email'] = $rw['paypal_email'];

        $_SESSION['global_vars']['main_paypal_email'] = $rw['main_paypal_email'];
        $_SESSION['global_vars']['main_scrill_email'] = $rw['main_scrill_email'];
        $_SESSION['global_vars']['main_skrill_secretword'] = $rw['main_skrill_secretword'];
        $_SESSION['global_vars']['main_skrill_api_password'] = $rw['main_skrill_api_password'];
        $_SESSION['global_vars']['main_2co_email'] = $rw['main_2co_email'];
        $_SESSION['global_vars']['main_2co_secretword'] = $rw['main_2co_secretword'];
        $_SESSION['global_vars']['main_2co_api_login'] = $rw['main_2co_api_login'];
        $_SESSION['global_vars']['main_2co_api_password'] = $rw['main_2co_api_password'];
        $_SESSION['global_vars']['n_days_dead_site_ping_limit'] = $rw['n_days_dead_site_ping_limit'];

        $_SESSION['global_vars']['dsc_q1'] = $rw['dsc_q1'];
        $_SESSION['global_vars']['dsc_q2'] = $rw['dsc_q2'];
        $_SESSION['global_vars']['dsc_q3'] = $rw['dsc_q3'];
        $_SESSION['global_vars']['dsc_q4'] = $rw['dsc_q4'];
        $_SESSION['global_vars']['dsc_q5'] = $rw['dsc_q5'];

        $_SESSION['global_vars']['dsc_p1'] = $rw['dsc_p1'];
        $_SESSION['global_vars']['dsc_p2'] = $rw['dsc_p2'];
        $_SESSION['global_vars']['dsc_p3'] = $rw['dsc_p3'];
        $_SESSION['global_vars']['dsc_p4'] = $rw['dsc_p4'];
        $_SESSION['global_vars']['dsc_p5'] = $rw['dsc_p5'];

        $_SESSION['global_vars']['forbidden_words'] = $rw['forbidden_words'];
        $_SESSION['global_vars']['forbidden_sites'] = $rw['forbidden_sites'];
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function wpPostXMLRPC2($title, $body, $rpcurl, $username, $password, $category = array(), $tags = '', $encoding = "UTF-8", $proxity = false)
{
    global $UserAgentArray, $proxy;
    $decription = '';
    $keywords = '';
    $aiosptitle = '';
    $timeout = 60;

    //Get categories
    $cats = array();
    $params = array(0, $username, $password);
    $request = xmlrpc_encode_request('metaWeblog.getCategories', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    if (!$result && !$proxity) {
        return wpPostXMLRPC2($title, $body, $rpcurl, $username, $password, $category = array(), $tags = '', $encoding = "UTF-8", true);
    }
    preg_match_all('/\<member\>\<name\>categoryName\<\/name\>\<value\>\<string\>(\w+)\<\/string\>\<\/value\>\<\/member\>/', $result, $matches, PREG_SET_ORDER);
    for ($i = 0; $i < count($matches); $i++) {
        array_push($cats, $matches[$i][1]);
    }

    //Find nonexistent categories
    $diff = array_diff($category, $cats);

    zlog__("Category needed: " . print_r($category, TRUE) . "\n");
    zlog__("Categories exist: " . print_r($cats, TRUE) . "\n");

    //Create nonexistent categories
    $n_cr_cat_errors = 0;
    if (!empty($diff) && strlen($diff[0])) {
        foreach ($diff as $cat) {
            $newcat = array('name' => $cat, 'slug' => strtolower($cat), 'description' => $cat);
            $params = array(0, $username, $password, $newcat);
            $request = xmlrpc_encode_request('wp.newCategory', $params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_URL, $rpcurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            if ($proxity) {
                $ch = setupProxy($ch);
            }
            $result = curl_exec($ch);
            curl_close($ch);

            zlog__("Creating category, result: $result \n");

            if (strpos($result, 'do not have the right to') > 0)
                $n_cr_cat_errors++;
        }
    }

    $ret_arr = array();

    zlog__(" n_cr_cat_errors : $n_cr_cat_errors \n");

    if ($n_cr_cat_errors == 0) {
        //sleep(3);
        $title = htmlentities($title, ENT_NOQUOTES, $encoding);
        $tags = htmlentities($tags, ENT_NOQUOTES, $encoding);
        $content = array(
            'title' => $title,
            'description' => $body,
            'mt_allow_comments' => 1, // 1 to allow comments
            'mt_allow_pings' => 1, // 1 to allow trackbacks
            'post_type' => 'post',
            'mt_keywords' => $tags,
            'categories' => $category,
            'custom_fields' => array(
                array('key' => '_aioseop_description', 'value' => $decription),
                array('key' => '_aioseop_keywords', 'value' => $keywords),
                array('key' => '_aioseop_title', 'value' => $aiosptitle)
            ),
        );

        $params = array(0, $username, $password, $content, true);
        $request = xmlrpc_encode_request('metaWeblog.newPost', $params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_URL, $rpcurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        if ($proxity) {
            $ch = setupProxy($ch);
        }
        $results = curl_exec($ch);
        curl_close($ch);

        $ret_arr[0] = $results;
        $ret_arr[1] = '';

        zlog__("Publish: (" . print_r($results, TRUE) . ") ");
    } else {
        $ret_arr[0] = '';
        $ret_arr[1] = "Please note that we currently do not have the correct permissions to publish. Please provide us with admin or editor rights.";
    }

//    zlog__("\n\nPublishFunction ret_arr: ".print_r($ret_arr, TRUE),1);
    return $ret_arr;
}

function wpRecentPostXMLRPC2($title, $rpcurl, $username, $password, $proxity = false)
{
    global $UserAgentArray, $proxy;
    $timeout = 30;
    $params = array(0, $username, $password, 20);
    $request = xmlrpc_encode_request('metaWeblog.getRecentPosts', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $results = curl_exec($ch);
    curl_close($ch);
    if (!$results && !$proxity) {
        return wpRecentPostXMLRPC2($title, $rpcurl, $username, $password, true);
    }

    if (!$results)
        return false;

    $results = xmlrpc_decode($results);
    foreach ($results as $item) {
        if ($item['title'] == $title) {
            return $item;
        }
    }
    return true;
}

function wpDeletePostByUrl($id, $url)
{
    $match = array();
    preg_match('/(.*)\?p=(\d+)/i', $url, $match);
    $result = false;
    if (count($match) > 2) {
        $site_url = $match[1];
        $post_id = $match[2];
        $rs = mysql__("select * from sites where url='$site_url' limit  0,1");
        $publish_site_info = mysql_fetch_array($rs);
        if ($publish_site_info) {
            $result = wpDeletePostXMLRPC2($post_id, correct_xmlrpc_url($publish_site_info['url']), $publish_site_info['login'], $publish_site_info['password']);
            if (!$result) {
                $result = (wpGetPostXMLRPC2($post_id, correct_xmlrpc_url($publish_site_info['url']), $publish_site_info['login'], $publish_site_info['password'])) ? true : false;
            }
            if ($result) {
                $sql2 = "update articles_published set deleted = '1' where id='$id'";
                mysql_query($sql2);
            }
        }
    }
    return $result;
}

function wpDeletePostXMLRPC2($postid, $rpcurl, $username, $password, $proxity = false)
{
    global $UserAgentArray, $proxy;
    $timeout = 60;
    $params = array(0, $postid, $username, $password);
    $request = xmlrpc_encode_request('metaWeblog.deletePost', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $results = curl_exec($ch);
    curl_close($ch);
    if (!$results && !$proxity) {
        return wpDeletePostXMLRPC2($postid, $rpcurl, $username, $password, true);
    }
    if (!$results)
        return false;

    return true;
}

function wpGetPostXMLRPC2($postid, $rpcurl, $username, $password, $proxity = false)
{
    global $UserAgentArray;
    $timeout = 10;
    $params = array($postid, $username, $password);
    $request = xmlrpc_encode_request('metaWeblog.getPost', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $results = curl_exec($ch);
    curl_close($ch);
    if (!$results && !$proxity) {
        return wpGetPostXMLRPC2($postid, $rpcurl, $username, $password, $password, true);
    }
    if (!$results)
        return false;

    return xmlrpc_decode($results);
}

function wpEditPostXMLRPC2($postid, $rpcurl, $username, $password, $title, $body, $proxity = false)
{
    global $UserAgentArray;
    $encoding = "UTF-8";
    $timeout = 10;
    $title = htmlentities($title, ENT_NOQUOTES, $encoding);
    $content = array(
        'title' => $title,
        'description' => $body,
    );
    $params = array($postid, $username, $password, $content, true);
    $request = xmlrpc_encode_request('metaWeblog.editPost', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $results = curl_exec($ch);
    curl_close($ch);
    if (!$results && !$proxity) {
        return wpEditPostXMLRPC2($postid, $rpcurl, $username, $password, $title, $body, true);
    }
    if (!$results)
        return false;

    return true;
}

function GoogleUniqueContentCheckOK($searchString, &$status = array(), $noproxy = false)
{
    global $UserAgentArray, $proxy;

    $response = false;
    $googleString = $status['url'] = 'http://www.google.com/search?q=' . urlencode('"' . $searchString . '"') . '';
    $ch = curl_init($googleString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
//    $proxy = false;
    $address = false;
    if (isset($proxy) && is_array($proxy) && $noproxy) {
        $ch = setupProxy($ch);
    }
    $response = curl_exec($ch);
    $nposfound = 0;
//        if ($_SERVER['REMOTE_ADDR']=="171.4.97.186"){
//            print_r($response);
//        var_dump(curl_error($ch));
//        print_r($_SERVER);
//            exit;
//        }
//
    if (!$response && $address && !$noproxy) {
        //zlog__($address,1,'proxy');
        return GoogleUniqueContentCheckOK($searchString, $status, true);
    }

    //zlog__("Google Responce: ".$response);

    $pos = stripos($response, 'No results found for');
    if ($pos > 0)
        $nposfound++;
    $pos = stripos($response, 'did not match any documents');
    if ($pos > 0)
        $nposfound++;
    if (!$nposfound) {
        $match = array();
        preg_match('/of about \<b\>([0-9,]+)\<\/b\> for/i', $response, $match);
        if (isset($match[1])) {
            zlog__("\nproblematic google query", 1, 'google');
            if ((int)str_replace(',', '', $match[1]) > 1000)
                $nposfound++;
        }
    }
    $pos = stripos($response, 'detected unusual traffic from');
    if ($pos == 0)
        $pos = stripos($response, 'but your computer or network may be sending automated queries');
    if ($pos > 0 || !$response) {
        if (BingUniqueContentCheckOK($searchString, $status)) {
            $nposfound++;
        }
    }
    if ($response == '')
        $nposfound++;

    if ($nposfound == 0) {
        zlog__("Checking google by " . $address . ": [ $searchString ] \n" . "$googleString\n$response\n\n\n", 1);
        zlog__("\"$searchString\"", 1, 'google');
    }
    if ($nposfound > 0)
        return TRUE;
    else
        return FALSE; 
}

function BingUniqueContentCheckOK($searchString, &$status = array())
{
    global $UserAgentArray, $proxy;
    $searchString = substr($searchString, 0, 120);
    $url = $status['url'] = "http://www.bing.com/search?q=" . urlencode('"' . $searchString . '"');
    
	//header("Location: $url"); die();  
	
	//$url = "http://www.bing.com/search?q=".urlencode('"'.$searchString.'"')."&go=&qs=n&sk=&form=QBLH&filt=all";
    //      http://www.bing.com/search?q=%22test%22&                          go=&qs=n&sk=&form=QBLH&filt=all
    //header("Location: $url"); die(); 

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
//    $ch = setupProxy($ch);
    $response = curl_exec($ch);

    //echo "test";
//    echo $response; //die();
    $nposfound = 0;

    $pos = stripos($response, 'No results found for');
    if ($pos > 0)
        $nposfound++;
    $pos = stripos($response, 'no results found');
    if ($pos > 0)
        $nposfound++;
    //$pos = stripos($response, 'did not match any documents');   if ($pos>0) $nposfound++;
//    echo $pos; die();

   if ($nposfound > 0)
        return 1;
    else
        return 0;    
}

function wpLoginCheck($rpcurl, $username, $password, $proxity = false)
{
    global $UserAgentArray, $proxy;

    $rpcurl .= "xmlrpc.php";

    log_action($_SESSION['user_id'], "wpLoginCheck site $rpcurl, rpcurl: $rpcurl");

    $cats = array();
    $params = array(0, $username, $password);
    $request = xmlrpc_encode_request('wp.getOptions', $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($proxity) {
        $ch = setupProxy($ch);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    if (!$result && !$proxity) {
        return wpLoginCheck($rpcurl, $username, $password, true);
    }

    //echo "<pre>"; print_r($result); die();
    //echo sizeof($result); die();
    //$pos = strpos($result, '<string>Site URL</string>');
    $pos = strpos($result, '<string>Software Version</string>');
    //echo $pos; die();
//    if ($_SERVER['REMOTE_ADDR']=="171.4.97.186"){
//        print_r($result);
//        print_r($pos);
//        exit;
//    }

    log_action($_SESSION['user_id'], "wpLoginCheck (wp.getOptions) site $rpcurl, result: $result");

    if ($pos > 0)
        return TRUE;
    else {
        return FALSE;
    }
}

function fixBlogUrl($url)
{
    $url = trim($url);
    if ($url[strlen($url) - 1] != '/')
        $url = $url . '/';

    $parsed_url = @parse_url($url);

    $path = $parsed_url['path'];
    for ($i = 1; $i < 100; $i++)
        $path = str_replace('//', '/', $path);

    $url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . $path;

    //echo $url; die();
    return $url;
}

function check_article_tags($keystr)
{
    /*
      2-10 tags, else error.
      2-20 character limit per tag
     */

    $newkeystr = "";
    $error_str = "";
    $n_errors = 0;
    $n_tags = 0;
    $ret_arr = array();
    $ret_arr['publishable'] = 0;

    $pcs = explode(",", $keystr);
    for ($i = 0; $i < sizeof($pcs); $i++) {
        //echo "[".strlen($pcs[$i])."]";

        $pcs[$i] = trim($pcs[$i]);
        if ((strlen($pcs[$i]) > 0) and (strlen($pcs[$i]) < 41)) {
            $n_tags++;
        } else {
            if (strlen($pcs[$i]) > 40) {
                $error_str .= "&middot; Tag '" . $pcs[$i] . "' is too long (max 40 characters per tag).<br>";
                $pcs[$i] = '';
                $n_errors++;
            } else {
                //$error_str .= "Tag '<b>".$pcs[$i]."</b>' is too short (min 3 characters per tag). ";
                //usualy this happens when no tags are entered...
            }
        }

        if (strlen($pcs[$i]) != '') {
            if (strlen($newkeystr) == '')
                $newkeystr .= $pcs[$i];
            else
                $newkeystr .= ", " . $pcs[$i];
        }
    }

    if (($n_tags > 1) and ($n_tags < 21)) {
        $ret_arr['publishable'] = 1;
    } else {
        $n_errors++;
        if ($n_tags < 2)
            $error_str .= "&middot; Too few tags require minimum 3-4 tags.<br>"; 
        else
            $error_str .= "&middot; Too many tags (max 20 tags).<br>";
    }

    if (count_suspicious_symbols_in_text($keystr) > 1) {
        $n_errors++;
        $error_str .= "&middot; Tags contain illegal symbols.<br>";
    }

    $ret_arr['errors'] = $n_errors;
    $ret_arr['error_str'] = $error_str;
    $ret_arr['suggested_keystr'] = $newkeystr;

    return $ret_arr;
}

function check_for_spun_content($text)
{
    $forbidden_characters = array('{', '}', '[', ']', '|', '~');
    $forbidden_chars_found = 0;
    for ($i = 0; $i < sizeof($forbidden_characters); $i++) {
        if (strpos($text, $forbidden_characters[$i]))
            $forbidden_chars_found++;
    }

    if ($forbidden_chars_found > 0)
        return TRUE;
    else
        return FALSE;
}

function check_article_text($text)
{
    $text = trim($text);
    //added by Deep on 18-12-2014
    $text = str_replace("&nbsp;", " ", $text);
    $text = preg_replace('/\s+/', ' ', $text);


    while (stripos($text, '  ') > 0)
        $text = str_replace('  ', ' ', $text);
    while (stripos($text, '< a') > 0)
        $text = str_replace('< a', '<a', $text);

    $text_stripped = strip_tags($text);
    $words = explode(" ", $text_stripped);

    $ret_arr = array();
    $ret_arr['nwords'] = sizeof($words);
    //$ret_arr['nlinks'] = substr_count($text, '<a');
    $ret_arr['errors'] = 0;
    $ret_arr['error_str'] = '';
    $ret_arr['publishable'] = 1;

    if ($ret_arr['nwords'] < 200) {
        $ret_arr['errors']++;
        $ret_arr['error_str'] .= "&middot; Article is too short, only " . $ret_arr['nwords'] . " words require minimum 200 words.<br>";
    } else {
        if ($ret_arr['nwords'] > 10000) {
            $ret_arr['errors']++;
            $ret_arr['error_str'] .= "&middot; Article is too long, maximum 10000 words allowed.<br>";
        }
    }


    if (strpos($text, '%') == 0) {
        $ret_arr['errors']++;
        $ret_arr['error_str'] .= "&middot; Article can't start with a link<br>";
    }

    if (count_suspicious_symbols_in_text($text) > 1) {
        $ret_arr['errors']++;
        $ret_arr['error_str'] .= "&middot; Article contains illegal symbols.<br>";
    }

    /*
      if (check_for_spun_content($text_stripped))
      {
      $ret_arr['errors']++;
      $ret_arr['error_str'] .= "Sorry, we do not accept spun content. Please refrain from using these characters in post text: { } [ ] | ~";
      }
     */

    if ($ret_arr['errors'] > 0)
        $ret_arr['publishable'] = 0;

    return $ret_arr;
}

function check_article_title($text)
{
    $text = trim($text);
    $text = strip_tags($text);

    $ret_arr = array();
    $ret_arr['publishable'] = 1;
    $ret_arr['errors'] = 0;
    $ret_arr['error_str'] = '';

    /*
      if (check_for_spun_content($text))
      {
      $ret_arr['errors']++;
      $ret_arr['error_str'] .= "Sorry, we do not accept spun content. Please refrain from using these characters in post title: { } [ ] | ~";
      }
     */

    if (strlen($text) < 15) {
        $ret_arr['errors']++;
        $ret_arr['error_str'] = "&middot; Post title is too short require minimum 15 symbols.<br>";
    } else {
        if (strlen($text) > 100) { 
            $ret_arr['errors']++;
            $ret_arr['error_str'] = "&middot; Post title is too long (max 100 symbols).<br>";
        }
    }

    if (count_suspicious_symbols_in_text($text) > 1) {
        $ret_arr['errors']++;
        $ret_arr['error_str'] .= "&middot; Title contains illegal symbols.<br>";
    }

    if ($ret_arr['errors'] > 0)
        $ret_arr['publishable'] = 0;

    return $ret_arr;
}

function zclvar($var)
{
    return isset($_REQUEST[$var]) ? mysql_real_escape_string($_REQUEST[$var]) : false;
}

function zclvar2($var)
{
    return mysql_real_escape_string($var);
}

function mysql_die_action($text) 
{
    global $GVars;

    //echo "MYSQL ERROR!!! TEXT:[ $text ]<br>";
//    $fp = fopen($GVars['dir_slash'] . '!_mysql_errors_' . date("Y_m_d") . '.txt', 'a');

    $fp = @fopen($GVars['document_root'] . 'error_log' . $GVars['dir_slash'] . '!_mysql_errors_' . date("Y_m_d") . '.txt', 'a');
    @fwrite($fp, $GVars['timenow'] . "\n" . $text . "\n\n- - - - - - - - - - - - - - - - - - - - - - - - - -\n\n");
    @fclose($fp); 
    //@mail($GVars['admin_email'], 'LinkAuthority MySQL Error!', $text, $mail_headers);
    $var = var_export($_SERVER, 1);
    zmail($GVars['admin_email'], $GVars['site_public_email'], "LA MySQL Err " . $_SERVER["SCRIPT_NAME"], "IP:" . $_SERVER['REMOTE_ADDR'] . " / SHELL:" .
        @$_SERVER['SHELL'] . " / USER:" . @$_SERVER['USER'] . "<br>" . $text . "<br>" . $var); 

    die();
}

function zlog__($text, $write = false, $group = '')
{
    global $GVars;
    $group = ($group) ? '.' . $group : '';
    if ($write) {
        $fp = fopen($GVars['document_root'] . 'error_log' . $GVars['dir_slash'] . '!_zlog__' . date("Y_m_d") . $group . '.txt', 'a');
        fwrite($fp, $GVars['timenow'] . "\n" . $text . "\n\n");
        fclose($fp);
    }

    //@mail($GVars['admin_email'], 'LinkAuthority MySQL Error!', $text, $mail_headers);
    //zmail($GVars['admin_email'], $GVars['site_public_email'], 'LinkAuthority Error!', $text);
}

function mysql__($sql)
{ //while($rw = mysql_fetch_array($rs))
    global $GVars;
    $_SESSION['lastsql'] = $sql;

    /*
      // log all mysql commands
      $fp = fopen($GVars['document_root'].'error_log'.$GVars['dir_slash'].'!_mysql_log_'.date("Y_m_d").'.txt', 'a');
      //echo $GVars['document_root'].'error_log'.$GVars['dir_slash'].'!_mysql_log_'.date("Y_m_d").'.txt'; die();
      fwrite($fp, $GVars['timenow']."\n".$sql."\n\n");
      fclose($fp);
     */
    $result = mysql_query($sql);
    if (!$result) {
        $i = 1;
        while (mysql_errno() == 2006 and $i <= 7) {
            $i++;
            mysql_close();
            sleep(2);
            $link = mysql_connect($GVars['dbHost'], $GVars['dbUser'], $GVars['dbPwd']);
            if (!$link) {
                continue;
            }
            mysql_select_db($GVars['dbDB']);

            $result = mysql_query($sql) or die(mysql_error());
            if ($result) {
                return $result;
            } else {
                echo "error found";
            }
        }
        echo $sql;

        mysql_die_action($sql . " \n" . mysql_error() . "[$i#" . mysql_errno() . "]");

        die();
    } else
        return $result;
}

function checkEmail($email)
{
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
        return false;
    else
        return true;
}

function get_page_title($active_item)
{
    switch ($active_item) {
        case "default": {
            echo "LinkAuthority.com";
        }
            break;
        case "1": {
            echo "High Quality One Way Backlinks Service - LinkAuthority.com";
        }
            break;
        case "2": {
            echo "FAQ";
        }
            break;
        case "4": {
            echo "Contact";
        }
            break;
        case "5": {
            echo "Signup";
        }
            break;
        case "6": {
            echo "Forgot password?";
        }
            break;
        case "7": {
            echo "Privacy Policy";
        }
            break;
        case "8": {
            echo "Terms and Conditions";
        }
            break;
    }
}

function ht_top($active_item = 1, $active_item2 = 0) {
global $GVars;
global $q;
global $menu_arr;
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/png" href="images/favicon.png"/>
    <title>
        <?= $_SESSION['this_page_title']; ?>
    </title>
		<!-- Icons -->
	<link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
	<link rel="stylesheet" href="fonts/font-awesome/css/font-awesome.min.css">
    

	<!-- Plugins -->
	<link rel="stylesheet" href="styles/plugins/c3.css">
	<link rel="stylesheet" href="styles/plugins/waves.css">
	<link rel="stylesheet" href="styles/plugins/perfect-scrollbar.css">

	<link rel="stylesheet" href="style.css" type="text/css"/>
	<!-- Css/Less Stylesheets -->
	<link rel="stylesheet" href="styles/bootstrap.min.css">
	<link rel="stylesheet" href="styles/main.min.css">
	<link rel="stylesheet" href="styles/angular-material.min.css">
	<!--<link rel="stylesheet" href="styles/plugins/summernote.css">-->
	<link rel="stylesheet" href="styles/plugins/bootstrap-datepicker.css">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
	<link href="flora/css/froala_editor.min.css" rel="stylesheet" type="text/css">
	
 	<link href='http://fonts.googleapis.com/css?family=Roboto:400,500,700,300' rel='stylesheet' type='text/css'>

	<!-- Match Media polyfill for IE9 -->
	<!--[if IE 9]> <script src="scripts/ie/matchMedia.js"></script>  <![endif]--> 
	
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	
<!-- Start of linkauthority Zendesk Widget script -->
<script>/*<![CDATA[*/window.zEmbed||function(e,t){var n,o,d,i,s,a=[],r=document.createElement("iframe");window.zEmbed=function(){a.push(arguments)},window.zE=window.zE||window.zEmbed,r.src="javascript:false",r.title="",r.role="presentation",(r.frameElement||r).style.cssText="display: none",d=document.getElementsByTagName("script"),d=d[d.length-1],d.parentNode.insertBefore(r,d),i=r.contentWindow,s=i.document;try{o=s}catch(c){n=document.domain,r.src='javascript:var d=document.open();d.domain="'+n+'";void(0);',o=s}o.open()._l=function(){var o=this.createElement("script");n&&(this.domain=n),o.id="js-iframe-async",o.src=e,this.t=+new Date,this.zendeskHost=t,this.zEQueue=a,this.body.appendChild(o)},o.write('<body onload="document._l();">'),o.close()}("//assets.zendesk.com/embeddable_framework/main.js","linkauthority.zendesk.com");/*]]>*/</script>

<!-- End of linkauthority Zendesk Widget script -->
</head>
<body id="app" class="app off-canvas nav-expand">
	<!-- header -->
	<header class="site-head" id="site-head">
		<ul class="list-unstyled left-elems">
			<!-- nav trigger/collapse -->
			
			<li>
				<a href="javascript:;" class="nav-trigger ion ion-drag" onclick="check_movement();"></a>
			</li>
			
			<!-- #end nav-trigger -->

			<!-- Search box -->
			<li>
				<div class="form-search hidden-xs">
					<!--
					<form id="site-search" action="javascript:;">
						<input type="search" class="form-control" placeholder="Type here for search...">
						<button type="submit" class="ion ion-ios-search-strong"></button>
					</form>
					-->
					<form id="site-search"  method="get" action="search.php">
							<input type="search" class="form-control" placeholder="Type here for search..." name="q">
							<button type="submit" class="ion ion-ios-search-strong"></button>
					</form>
					
				</div>
			</li>	<!-- #end search-box -->

			<!-- site-logo for mobile nav -->
			<li>
				<div class="site-logo visible-xs">
					<a href="javascript:;" class="text-uppercase h3">
						<span class="text">Welcome</span>
					</a>
				</div>
			</li> <!-- #end site-logo -->

			<!-- fullscreen -->
			
			<li class="fullscreen hidden-xs">
				<a href="javascript:void(0);"><i class="ion ion-qr-scanner"></i></a>

			</li>
			
			<!-- #end fullscreen -->
			<?php 
			$uri=$_SERVER['PHP_SELF']; 
			$urii=substr(strrchr($uri, "/"), 1);
			?>
			<?php
			if($urii=="main.php")
			{
			?>
				<?php
				$total_ann="SELECT * FROM announcement";
				$res_ann=mysql_query($total_ann);
				while($row=mysql_fetch_array($res_ann))
				{
					$total_a[]=$row['id'];
				}
				
				
				$total_an=count($total_a);
				$user_ann="SELECT * FROM user_chk_announcement where user_id='".$_SESSION['user_id']."'";
				$res_user_ann=mysql_query($user_ann);
				$count=mysql_num_rows($res_user_ann);
				$read_ann=$total_an-$count; 
				
				?>
				<?php
				if($read_ann!=0)
				{
				?>
				<li class="notify-drop hidden-xs dropdown">
					
					
					<a href="javascript:;" data-toggle="dropdown" onclick="scroll1()"> 
						<i class="ion ion-speakerphone"></i>
						<span class="badge badge-danger badge-xs circle ann_notify"><?php echo $read_ann; ?></span>
					</a>
					
				</li> 
				<?php
				}
			}
			else
			{ 
			?>
			<?php
				$total_ann="SELECT * FROM announcement";
				$res_ann=mysql_query($total_ann);
				while($row=mysql_fetch_array($res_ann))
				{
					$total_a[]=$row['id'];
				}
				
				
				$total_an=count($total_a);
				$user_ann="SELECT * FROM user_chk_announcement where user_id='".$_SESSION['user_id']."'";
				$res_user_ann=mysql_query($user_ann);
				$count=mysql_num_rows($res_user_ann);
				$read_ann=$total_an-$count; 
				
				?>
				<?php
				if($read_ann!=0)
				{
				?>
				<li class="notify-drop hidden-xs dropdown">
					
					
					<a href="main.php#Announcements"> 
						<i class="ion ion-speakerphone"></i>
						<span class="badge badge-danger badge-xs circle ann_notify"><?php echo $read_ann; ?></span>
					</a>
					
				</li> 
				<?php
				}
			
			}
			?>
			
			
			<!-- notification drop -->
			<!--
			<li class="notify-drop hidden-xs dropdown">
				<a href="javascript:;" data-toggle="dropdown">
					<i class="ion ion-speakerphone"></i>
					<span class="badge badge-danger badge-xs circle">3</span>
				</a>

				<div class="panel panel-default dropdown-menu">
					<div class="panel-heading">
						You have 3 new notifications 
						<a href="javascript:;" class="right btn btn-xs btn-pink mt-3">Show All</a>
					</div>
					<div class="panel-body">
						<ul class="list-unstyled">
							<li class="clearfix">
								<a href="javascript:;">
									<span class="ion ion-archive left bg-success"></span>
									<div class="desc">
										<strong>App downloaded</strong>
										<p class="small text-muted">1 min ago</p>
									</div>
								</a>
							</li>
							<li class="clearfix">
								<a href="javascript:;">
									<span class="ion ion-alert-circled left bg-danger"></span>
									<div class="desc">
										<strong>Application Error</strong>
										<p class="small text-muted">4 hours ago</p>
									</div>
								</a>
							</li>
							<li class="clearfix">
								<a href="javascript:;">
									<span class="ion ion-person left bg-info"></span>
									<div class="desc">
										<strong>New User Registered</strong>
										<p class="small text-muted">2 days ago</p>
									</div>
								</a>
							</li>
						</ul>
					</div>
				</div>

			</li>
			-->
			<!-- #end notification drop -->
			<?php
			
			$free_status="select * from user_free_quota_status where user_id='".$_SESSION['user_id']."'";
			$res_free_status=mysql_query($free_status);
			while($row_free=mysql_fetch_array($res_free_status))
			{
				 $status=$row_free['status'];
			}
			
			$bhw_status="select * from user_bhw_request where user_id='".$_SESSION['user_id']."'";
			$res_bhw_status=mysql_query($bhw_status);
			$count_bhw_status=mysql_num_rows($res_bhw_status);

			
			$cr_time="select * from users where id='".$_SESSION['user_id']."'";
			$res_cr_time=mysql_query($cr_time);
			while($row_cr=mysql_fetch_array($res_cr_time))
			{
				$user_cr=$row_cr['cr_time'];
			}
			
			
			$date1 = $user_cr;
			$date2 = date("Y-m-d H:i:s");

			$diff = abs(strtotime($date2) - strtotime($date1));

			$cr_days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			
			$ar_publish="SELECT COUNT(*) 'total' FROM articles_published WHERE user_id='".$_SESSION['user_id']."'";
			$res_ar_publish=mysql_query($ar_publish);
			while($row_ar_publish=mysql_fetch_array($res_ar_publish))
			{
				$count_pub=$row_ar_publish['total']; 
			}  
			
			if(($cr_days<=10) || ($count_pub==0))
			{
				if(($status=="") || ($status==0)) 
				{
				?>
				<li>
					<button type="button" class="btn btn-default waves-effect" data-toggle="modal" data-target="#free_quota" onclick="jQuery('.free_quota_res').empty();" style="margin-left: 100px;border-radius:50px;">Claim Free Quota</button> 
				</li>  
				<?php	
				}
				else if($status==1)
				{
				?>
				<li>
					<button type="button" class="btn btn-default waves-effect" data-toggle="modal" data-target="#free_quota_2" onclick="jQuery('.free_quota_res').empty();" style="margin-left: 100px;border-radius:50px;">Want more Quota?</button> 
				</li>
				<?php
				}
				
			}	
			else
			{
				if($count_bhw_status==0)
				{ 
				?> 
				<li> 
					<button type="button" class="btn btn-default waves-effect" data-toggle="modal" data-target="#free_quota_1" style="margin-left: 100px;border-radius:50px;" onclick="jQuery('.free_quota_res').empty();">Claim Free Quota</button> 
				</li> 
				<?php
				}
			} 
			
			
			?>
		</ul>
		
		<ul class="list-unstyled right-elems">
			<!-- profile drop -->
			<li><a class="h-link" href="view_list.php"><span class="fa fa-pencil-square-o">&nbsp;&nbsp;</span>Purchased Content</a></li>
			<li><a class="h-link" href="profile.php"><span class="fa fa-user">&nbsp;&nbsp;</span>My Profile</a></li>
			<li class="profile-drop dropdown">
				
				<a style="color:#fff;" href="javascript:;" class="ion ion-grid h-link" data-toggle="dropdown"></a>
				
				<ul class="dropdown-menu dropdown-menu-right">
					<!--
					<li><a href="view_list.php"><span class="ion ion-person">&nbsp;&nbsp;</span>Purchased Content</a></li>
					<li><a href="profile.php"><span class="ion ion-settings">&nbsp;&nbsp;</span>My Profile</a></li>
					
					<li class="divider"></li>
					-->
					<li><a href="http://linkauthority.zendesk.com"><span class="ion ion-lock-combination">&nbsp;&nbsp;</span>Support</a></li>
					<li><a href="affiliation.php"><span class="ion ion-lock-combination">&nbsp;&nbsp;</span>Affilation</a></li>
					
					<?php
					if ($_SESSION['user_admin'] > 0)  
					{
					?>
					<li><a href="admin.php"><span class="ion ion-lock-combination">&nbsp;&nbsp;</span>Admin</a></li>
					<?php
					}
					?>
					<li><a href="exit.php?cx=1"><span class="ion ion-power">&nbsp;&nbsp;</span>Exit</a></li>
				</ul>
			</li>
			<!-- #end profile-drop -->

			<!-- sidebar contact -->
			<!--
			<li class="floating-sidebar">
				<a href="javascript:;">
					<i class="ion ion-grid"></i>
				</a>
				<div class="sidebar-wrap" data-perfect-scrollbar>
					<ul class="nav nav-tabs nav-justified">
						<li class="active">
							<a href="#sidebar-chat-tab" data-toggle="tab">Chat</a>
						</li>
						<li>
							<a href="#sidebar-info-tab" data-toggle="tab">Info</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="sidebar-chat-tab">
							<div class="chat-tab tab clearfix">
								<h5 class="title mt0 mb20">Online</h5>
								<div class="user-container mb15">
									<img src="images/sample/1.jpg" alt="">
									<div class="desc">
										<p class="mb0">John Wick</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;San Franciso, USA</p>

									</div>
									<span class="ion ion-record avail right on"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/2.jpg" alt="">
									<div class="desc">
										<p class="mb0">George K.</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;California, USA</p>
									</div>
									<span class="ion ion-record avail right on"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/3.jpg" alt="">
									<div class="desc">
										<p class="mb0">Shello Dse.</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;Berlin, Germany</p>
									</div>
									<span class="ion ion-record avail right away"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/4.jpg" alt="">
									<div class="desc">
										<p class="mb0">Lucas Tower</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;Paris, France</p>
									</div>
									<span class="ion ion-record avail right away"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/5.jpg" alt="">
									<div class="desc">
										<p class="mb0">Hey Win</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;Hongkong, China</p>
									</div>
									<span class="ion ion-record avail right on"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/6.jpg" alt="">
									<div class="desc">
										<p class="mb0">Kelvin L.</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;Moscow, Russia</p>
									</div>
									<span class="ion ion-record avail right on"></span>
								</div>

								<h5 class="title mt0 mb20">Offline</h5>

								<div class="user-container mb15">
									<img src="images/sample/7.jpg" alt="">
									<div class="desc">
										<p class="mb0">Martin Xx.</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;xxx, yyy</p>
									</div>
									<span class="ion ion-record avail right off"></span>
								</div>

								<div class="user-container mb15">
									<img src="images/sample/2.jpg" alt="">
									<div class="desc">
										<p class="mb0">Lorem Ipsum</p>
										<p class="xsmall"><span class="ion ion-location"></span>&nbsp;Virginia, USA</p>
									</div>
									<span class="ion ion-record avail right off"></span>
								</div>
							</div>
						</div>

						<div class="tab-pane" id="sidebar-info-tab">
							<div class="info-tab tab clearfix">
								<h5 class="title mt0 mb20">Work in Progress</h5>
								<ul class="list-unstyled mb15 clearfix">
									<li>
										<div class="clearfix mb10">
											<small class="left">App Upload</small>
											<small class="right">80%</small>
										</div>
										<div class="progress-xs progress">
											<div class="progress-bar progress-bar-primary" style="width: 80%;"></div>
										</div>
									</li>
									<li>
										<div class="clearfix mb10">
											<small class="left">Creating Assets</small>
											<small class="right">50%</small>
										</div>
										<div class="progress-xs progress">
											<div class="progress-bar progress-bar-danger" style="width: 50%;"></div>
										</div>
									</li>
									<li>
										<div class="clearfix mb10">
											<small class="left">New UI 2.0</small>
											<small class="right">90%</small>
										</div>
										<div class="progress-xs progress">
											<div class="progress-bar progress-bar-success" style="width: 90%;"></div>
										</div>
									</li>
								</ul>

								<h5 class="title mt0 mb20">Settings</h5>
								<div class="clearfix mb15">
									<div class="left">
										<p>Show me online</p>
									</div>

									<div class="right">
										<div class="ui-toggle ui-toggle-success ui-toggle-xs">
											<label>
												<input type="checkbox" checked> 
												<span></span>
											</label>
										</div>
									</div>
								</div>

								<div class="clearfix mb15">
									<div class="left">
										<p>Notifications</p>
									</div>

									<div class="right">
										<div class="ui-toggle ui-toggle-success ui-toggle-xs">
											<label>
												<input type="checkbox"> 
												<span></span>
											</label>
										</div>
									</div>
								</div>

								<div class="clearfix mb15">
									<div class="left">
										<p>Enable History</p>
									</div>

									<div class="right">
										<div class="ui-toggle ui-toggle-success ui-toggle-xs">
											<label>
												<input type="checkbox" checked> 
												<span></span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div> 
				</div> 
			</li>
			-->

		</ul>
		<input type="hidden" name="move_menuu" id="move_menuu" value="<?php echo $_SESSION['cl']; ?>">
	</header>
	<!-- #end header -->
	<!-- main-container -->
	<div class="main-container clearfix">
		<!-- main-navigation -->
		
		<aside class="nav-wrap nav-expand" id="site-nav" data-perfect-scrollbar> 
			
            <div class="nav-head1">
				<!-- site logo -->
				<a href="main.php" class="site-logo text-uppercase">
					<img src="images/dash-icon.png" alt="" title="" /> 
					<span class="text">Welcome</span>
				</a>
			</div>

			<!-- Site nav (vertical) -->

			<nav class="site-nav clearfix" role="navigation">
				<!--
				<div class="profile clearfix mb15">
					<img src="images/admin.jpg" alt="admin">
					<div class="group">
						<h5 class="name">Robert Smith</h5>
						<small class="desig text-uppercase">UX Designer</small>
					</div>
				</div>
				-->
				<!-- navigation -->
				<?php 
				
				$uri=$_SERVER['PHP_SELF']; 
				$urii=substr(strrchr($uri, "/"), 1);
				?>
				<ul class="list-unstyled clearfix nav-list mb15">
					<li <?php if($urii=="main.php") { echo 'class="active"'; };  ?>>
						<a href="main.php" md-ink-ripple="">
							<i class="fa fa-tachometer"></i>
							<span class="text">Dashboard</span>
                            <div class="md-ripple-container"></div> 
						</a>
					</li>
					<li <?php if($urii=="projects.php") { echo 'class="active"'; };  ?>>
						<a href="projects.php" md-ink-ripple="" >
							<i class="fa fa-folder-open"></i>
							<span class="text">My Projects</span>
                            <div class="md-ripple-container"></div>
						</a>
					</li>

					<li <?php if($urii=="sites.php") { echo 'class="active"'; };  ?>>
						<a href="sites.php" md-ink-ripple="" >
							<i class="fa fa-briefcase"></i>
							<span class="text">My Sites</span>
                            <div class="md-ripple-container"></div>
							
						</a>
					</li>
					<li <?php if($urii=="purchase.php") { echo 'class="active"'; };  ?>>
						<a href="purchase.php" md-ink-ripple="" >
							<i class="fa fa-shopping-cart"></i>
							<span class="text">Purchase Quota</span>
                            <span class="badge badge-xs badge-danger">hot</span>
                            <div class="md-ripple-container"></div>
							
						</a>
					</li>
					
					<?php
					if ($_SESSION['user_admin'] > 0) 
					{
					?>
					<li <?php if($urii=="niche-authority.php") { echo 'class="active"'; };  ?>>
						<a href="niche-authority.php" md-ink-ripple="" >
							<i class="fa fa-link"></i>
							<span class="text">Niche Backlinks</span>
                            <span class="badge badge-xs badge-success">new</span>
                            <div class="md-ripple-container"></div>
							
						</a>
					</li>
					<li class="case_studies">
						<a href="javascript:void(0);" md-ink-ripple="" >
							<i class="ion ion-icecream"></i>
							<span class="text">Case Studies</span>
                            <span class="badge badge-xs badge-success">new</span>
                            <div class="md-ripple-container"></div>
							
						</a>
						<ul class="inner-drop list-unstyled case_stud_ul">
							<?php
							$sql_chk="select * from case_studies";
							$res=mysql_query($sql_chk);
							while($row=mysql_fetch_array($res))
							{
							$case_id=$row['id']; 
							$title=$row['tab_name'];
							$tab_url=$row['tab_url'];
							?>
							<li class="waves-effect case_<?php echo $case_id; ?>"><a href="<?php echo $tab_url; ?>"><?php echo $title; ?></a></li>
							<?php
							}
							?>
							<li class="waves-effect"><a href="add-case.php">Add New</a></li>    
						</ul>
					</li>
					<?php
					}
					else
					{
					?>
					<li class="case_studies">
						<a href="javascript:void(0);" md-ink-ripple="" >
							<i class="ion ion-icecream"></i>
							<span class="text">Case Studies</span>
                            <span class="badge badge-xs badge-success" style="margin-left:113px !important;">new</span>
                            <i class="arrow ion-chevron-left" style="margin-top:10px;"></i>
                            <div class="md-ripple-container"></div>
							
						</a>
						<ul class="inner-drop list-unstyled case_stud_ul">
							<?php
							$sql_chk="select * from case_studies";
							$res=mysql_query($sql_chk);
							while($row=mysql_fetch_array($res))
							{
							$case_id=$row['id']; 
							$title=$row['tab_name'];
							$tab_url=$row['tab_url']; 
							?>
							<li class="waves-effect case_<?php echo $case_id; ?>"><a href="<?php echo $tab_url; ?>"><?php echo $title; ?></a></li>
							<?php
							}
							?>
							 
						</ul> 
					</li>
					<?php
					}
					?>	
					<?php
					if ($_SESSION['user_admin'] > 0) 
					{
					?>
					<li <?php if($urii=="psites.php") { echo 'class="active"'; };  ?>>
						<a href="psites.php" md-ink-ripple="" >
							<i class="fa fa-newspaper-o"></i>
							<span class="text">P. sites</span>
							<div class="md-ripple-container"></div>
						</a>
					</li>
					<?php
					}
					?>
					<li <?php if($urii=="resource.php") { echo 'class="active"'; };  ?>>
						<a href="resource.php" md-ink-ripple="" >
							<i class="fa fa-database"></i>
							<span class="text">Resources</span>
							<div class="md-ripple-container"></div>
						</a>
					</li>
					<li class="tutorial_li waves-effect <?php if($urii=="faq.php") { echo 'active open'; };  ?>">
						<a href="javascript:void(0);" md-ink-ripple="">
							<i class="fa fa-question-circle"></i>
							<span class="text">Tutorials</span>
							 <i class="arrow ion-chevron-left" style="margin-top:10px;"></i>
							<div class="md-ripple-container"></div>
							
						</a>
						<ul class="inner-drop list-unstyled tutorial_ul" >
						<li <?php if($urii=="faq.php") { echo 'class="active"'; };  ?>>
							<a href="faq.php" md-ink-ripple="">FAQ</a>
						</li>
						<?php
						$sql_chk="select * from tutorials";
						$res=mysql_query($sql_chk);
						while($row=mysql_fetch_array($res))
						{
						$case_id=$row['id']; 
						$title=$row['tab_name'];
						$tab_url=$row['tab_url'];
						?>
						<li class="waves-effect case_<?php echo $case_id; ?>"><a href="<?php echo $tab_url; ?>"><?php echo $title; ?></a></li>
						<?php 
						}
						?>
						<?php
						if ($_SESSION['user_admin'] > 0) 
						{
						?>
							<li class="waves-effect waves-effect"><a href="add-tutorial.php">Add New</a></li>
						<?php
						}
						?>		
					
						</ul>
					</li>
					
				</ul> <!-- #end navigation -->
			</nav>

			<!-- nav-foot -->
			
			
			
			<footer class="nav-foot">
				<p><?php echo date('Y'); ?> &copy; <span>Imark Infotech</span></p>
			</footer>

		</aside>
		<!-- #end main-navigation -->

		<!-- content-here -->
		<div class="content-container" id="content">
			<!-- dashboard page -->
			<div class="page page-dashboard">

				<div class="page-wrap">
            <?php
            }

            function ht_bot() {
            ?>
			</div> <!-- #end page-wrap -->
			</div>
			<!-- #end dashboard page -->
		</div>

	</div> <!-- #end main-container -->

	<!-- theme settings --> 
	
	<div class="site-settings clearfix hidden-xs">
		<div class="settings clearfix"> 
			<div class="trigger ion ion-settings left" style="display:none;"></div>
			<div class="wrapper left" style="display:none;">
				<ul class="list-unstyled other-settings">
					<li class="clearfix mb10">
						<div class="left small">Nav Horizontal</div>
						<div class="md-switch right">
							<label>
								<input type="checkbox" id="navHorizontal"> 
								<span>&nbsp;</span> 
							</label>
						</div>
						
						
					</li>
					<li class="clearfix mb10">
						<div class="left small">Fixed Header</div>
						<div class="md-switch right">
							<label>
								<input type="checkbox"  id="fixedHeader"> 
								<span>&nbsp;</span> 
							</label>
						</div>
					</li>
					<li class="clearfix mb10">
						<div class="left small">Nav Full</div>
						<div class="md-switch right">
							<label>
								<input type="checkbox"  id="navFull"> 
								<span>&nbsp;</span> 
							</label>
						</div>
					</li>
				</ul>
				<hr/>
				<ul class="themes list-unstyled" id="themeColor">
					<li data-theme="theme-zero" class="active"></li>
					<li data-theme="theme-one"></li>
					<li data-theme="theme-two"></li>
					<li data-theme="theme-three"></li>
					<li data-theme="theme-four"></li>
					<li data-theme="theme-five"></li>
					<li data-theme="theme-six"></li>
					<li data-theme="theme-seven"></li>
				</ul>
			</div>
		</div>
	</div>
	
	<!-- #end theme settings -->
	<!-- Modal -->
	<div class="modal fade" id="free_quota" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
	  <div class="modal-dialog" role="document" style="width: 450px;">
		<div class="modal-content" style="padding:20px 50px;">
		  <div class="modal-body">
			<h1 class="site-logo h2 mb5 mt5 text-center text-uppercase text-bold">Get Free Quota </h1>
			<p style="text-align:center;margin-top: 20px;">Want to test drive link Authority?</p>
			<p style="text-align:center;margin-top: 20px;">We are offering new members FREE quota for a limited time.</p>
			<div class="free_quota_res"></div>
		  </div>
		  <div class="modal-footer">
			<!--<img src="images/loader.gif" id="free_quota_loader" style="display:none;"  />-->
			<button type="button" class="btn btn-primary btn-block text-uppercase btn-lg" onclick="request_free_quota('<?php echo $_SESSION['user_id'] ?>');">Thank you, I feel ecstatic :)</button>
		  </div>
		</div>
	  </div>
	</div>
	
	<div class="modal fade" id="free_quota_1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
	  <div class="modal-dialog" role="document" style="width: 450px;">
		<div class="modal-content" style="padding:20px 50px;">
		  <div class="modal-body">
			<h1 class="site-logo h2 mb5 mt5 text-center text-uppercase text-bold">earn 15 posting credits</h1>
			<p style="text-align:center;margin-top: 20px;">To earn your credits, simply go to BHW thread linked <a href="http://www.blackhatworld.com/blackhat-seo/seo-link-building/730403-linkauthority-com-back-powerful-protected-high-quality-link-network-v-2-0-a.html" target="_blank">here</a> and post an honest review. If we like your review, your account will be credited with quota of 15.</p>
			<p style="text-align:center;margin-top: 20px;">Don't forget to create a support ticket after leaving your review.</p>
			
			<div class="free_quota_res"></div>
		  </div>
		  <div class="modal-footer">
			<!--<img src="images/loader.gif" id="free_quota_loader" style="display:none;"  />--> 
			<button type="button" class="btn btn-primary btn-block text-uppercase btn-lg" onclick="take_to_bhw('<?php echo $_SESSION['user_id'] ?>');" >Take Me To BHW</button> 
		  </div>
		</div>
	  </div>
	</div>
	
	<div class="modal fade" id="free_quota_2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
	  <div class="modal-dialog" role="document" style="width: 450px;">
		<div class="modal-content" style="padding:20px 50px;">
		  <div class="modal-body">
			<h1 class="site-logo h2 mb5 mt5 text-center text-uppercase text-bold">earn 15 posting credits</h1>
			<p style="text-align:center;margin-top: 20px;">To earn your credits, simply go to BHW thread linked <a href="http://www.blackhatworld.com/blackhat-seo/seo-link-building/730403-linkauthority-com-back-powerful-protected-high-quality-link-network-v-2-0-a.html" target="_blank">here</a> and post an honest review. If we like your review, your account will be credited with quota of 15.</p>
			
			<p style="text-align:center;margin-top: 20px;">Don't forget to create a support ticket after leaving your review.</p>
			
			<div class="free_quota_res"></div>
		  </div>
		  <div class="modal-footer">
			<!--<img src="images/loader.gif" id="free_quota_loader" style="display:none;"  />--> 
			<button type="button" class="btn btn-primary btn-block text-uppercase btn-lg" onclick="update_status_2('<?php echo $_SESSION['user_id'] ?>');"; >Confirm request</button> 
		  </div> 
		</div>
	  </div>
	</div>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.0/css/jquery.dataTables.min.css" type="text/css" />
<script src="https://cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script> 
<script type="text/javascript">
    adroll_adv_id = "BRPDHME6GZHBVNLJE3TZR2";
    adroll_pix_id = "6LV2SGL2ZBGXVG25AD5UNI";
    (function () {
        var oldonload = window.onload;
        window.onload = function () {
            __adroll_loaded = true;
            var scr = document.createElement("script");
            var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
            scr.setAttribute('async', 'true');
            scr.type = "text/javascript";
            scr.src = host + "/j/roundtrip.js";
            ((document.getElementsByTagName('head') || [null])[0] ||
            document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
            if (oldonload) {
                oldonload()
            }
        };
    }());
	
jQuery(document).ready(function($)
{ 
	jQuery('table').find('td').removeAttr('align','center');
	
	var oTable = $('#Payment_table').dataTable({
	  "processing": true,
	  "serverSide": true,
	  "ajax": {
		  "url": "ajax_provider.php?method=PaymentList",
		  "type": "POST"
	  },
	  "sPaginationType": "four_button",
	  "aoColumnDefs": [{
			  "aTargets": [0]
		  }],
	  "pagingType": "full_numbers",
	  "oLanguage": {
		  "sLengthMenu": "Show _MENU_ Rows",
		  "sSearch": ""
	  },
	  "aaSorting": [
		  [0, 'desc']
	  ],
	  "aLengthMenu": [
		  [10, 25, 50, 100, -1],
		  [10, 25, 50, 100, "All"]
	  ],
	  "iDisplayLength": 10
	});
	jQuery('#Payment_table_wrapper .dataTables_filter input').addClass("form-control input-sm").attr("placeholder", "Search");
	// modify table search input
	jQuery('#Payment_table_wrapper .dataTables_length select').addClass("m-wrap small");
	

	jQuery('#announce_form').validate({
		
		rules: {
			announce_title: {
				required: true
			}
		}, 
		
		submitHandler: function(form) {
						
			jQuery(form).ajaxSubmit({
				type: "POST",
				data: jQuery(form).serialize(),
				url: 'ajax/insert_announce.php', 
				success: function(data) 
				{
					jQuery(data).insertAfter('.announce_result>.alert:last');
					
				}
			});
		}
		
	});
	
	jQuery('#reff_form').validate({
		
		rules: {
			reff_title: { 
				required: true
			}
		}, 
		
		submitHandler: function(form) {
						
			jQuery(form).ajaxSubmit({
				type: "POST",
				data: jQuery(form).serialize(),
				url: 'ajax/insert_reff.php', 
				success: function(data) 
				{
					jQuery(data).insertAfter('.reff_result>.alert:last');
					
				}
			});
		}
		
	});
	
	jQuery('#single_user_an').validate({
		
		rules: {
			on_login_message: {
				required: true
			}
		}, 
		
		submitHandler: function(form) {
						
			jQuery(form).ajaxSubmit({
				type: "POST",
				data: jQuery(form).serialize(),
				url: 'ajax/insert_user_announce.php',  
				success: function(data) 
				{
					//jQuery(data).insertAfter('.announce_result>.alert:last');
					jQuery('.single_user_ann').empty().append(data);
				}
			});
		}
		
	});
	
	
});	

function del_reff(e)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/del_reff.php", 
		data:{e:e,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				if(resp==1)
				{
					jQuery('.reff_'+e).fadeOut(1000); 
					jQuery('.reff_'+e).remove();  
				}
			}
			
		}
    });
}

function del_announc(e)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/del_announce.php",
		data:{e:e,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				if(resp==1)
				{
					jQuery('.announc_'+e).fadeOut(1000); 
					jQuery('.announc_'+e).remove(); 
				}
			}
			
		}
    });
}

function del_single_announc(e)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/del_single_announc.php", 
		data:{e:e,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				
				if(resp==1)
				{
					jQuery('.sgl_ann').fadeOut(1000);
					jQuery('.sgl_ann').remove();  
				}
			}
			
		}
    });	
}

function hide_single_user_ann(e)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/hide_single_user_ann.php", 
		data:{e:e,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				
				if(resp==1)
				{
					jQuery('.sgl_ann').fadeOut(1000);
					jQuery('.sgl_ann').remove();  
				}
			}
			
		}
    });
}
function check_movement()
{
	if(jQuery('#site-nav').hasClass('nav-expand'))
	{
		var cl=1;
	}
	else
	{
		var cl=0;
	}
	
	jQuery.ajax({
		type: "GET",
		url:"ajax/check_movement.php", 
		data:{cl:cl,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				jQuery('#move_menuu').val(resp);
				$('#site-nav').removeClass('nav-offcanvas');
			}
			
		}
    });
	
}

function request_free_quota(user_id)
{
	var client_ip="<?php echo $_SERVER['REMOTE_ADDR']; ?>";
	jQuery('#free_quota_loader').show();
	jQuery.ajax({
		type: "GET",
		url:"ajax/grant_free_quota.php", 
		data:{client_ip:client_ip,user_id:user_id,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				jQuery('#free_quota_loader').hide();
				if(resp==1)
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-success">We have received your request. Your Free Quota Request will be updated within 30 mins</div>');
				}
				else
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-warning">We have received multiple request from your side. Your account can be blocked.</div>');
				}
			}
			
		}
    });
	
}

function update_status_2(user_id)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/update_status_2.php", 
		data:{user_id:user_id,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				
				if(resp==1)
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-success">Dont forget to create a support ticket after leaving your review.</div>'); 
					jQuery('.free_quota_res').empty().append('<div class="alert alert-success">You will be redirected in 5 seconds...</div>');
					setTimeout(explode, 5000);
				}
				else if(resp==2)
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-warning">To claim additional quota, you need to have minimum of 7 posts published.</div>');
				}
				else 
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-warning">We have already received your request, please post a review on BHW to claim your free quota</div>');  
				}
			}
			
		}
    });
}

function take_to_bhw(user_id)
{
	jQuery.ajax({
		type: "GET",
		url:"ajax/take_to_bhw.php",  
		data:{user_id:user_id,format:'raw'},
		success:function(resp){
			if( resp !="")
			{
				if(resp==1)
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-success">Dont forget to create a support ticket after leaving your review.</div>'); 
					jQuery('.free_quota_res').empty().append('<div class="alert alert-success">You will be redirected in 5 seconds...</div>');
					setTimeout(explode, 5000);
				}
				else 
				{
					jQuery('.free_quota_res').empty().append('<div class="alert alert-warning">We have already received your request, please post a review on BHW to claim your free quota</div>');  
				}
			}
			
		}
    });
}

function explode()
{
	window.location.href="http://www.blackhatworld.com/blackhat-seo/seo-link-building/730403-linkauthority-com-back-powerful-protected-high-quality-link-network-v-2-0-a.html";
}
	
</script>

 

<script>
jQuery(document).ready(function($) 
{
	var oTable = $('#sitetable3').dataTable({
		"sPaginationType": "four_button",
		"aoColumnDefs": [{
				"aTargets": [0]
			}],
		"pagingType": "full_numbers",
		"oLanguage": {
			"sLengthMenu": "Show _MENU_ Rows",
			"sSearch": ""
		},
		"aaSorting": [
			[0, 'desc']
		],
		"aLengthMenu": [
			[10, 25, 50, 100],
			[10, 25, 50, 100]
		],
		"iDisplayLength": 10
	});
	$('#sitetable3_table_wrapper .dataTables_filter input').addClass("form-control input-sm").attr("placeholder", "Search");
	// modify table search input
	$('#sitetable3_table_wrapper .dataTables_length select').addClass("m-wrap small");
	
		
});</script> 



<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="flora/js/froala_editor.min.js"></script>
  <!--[if lt IE 9]>
    <script src="flora/js/froala_editor_ie8.min.js"></script>
  <![endif]-->
  <script src="flora/js/plugins/tables.min.js"></script>
  <script src="flora/js/plugins/urls.min.js"></script>
  <script src="flora/js/plugins/lists.min.js"></script>
  <script src="flora/js/plugins/colors.min.js"></script>
  <script src="flora/js/plugins/font_family.min.js"></script>
  <script src="flora/js/plugins/font_size.min.js"></script>
  <script src="flora/js/plugins/block_styles.min.js"></script>
  <script src="flora/js/plugins/media_manager.min.js"></script>
  <script src="flora/js/plugins/video.min.js"></script>
  <script src="flora/js/plugins/char_counter.min.js"></script>
  <script src="flora/js/plugins/entities.min.js"></script>
  
  <script>
      jQuery(function($)
	  { 
        $('.edittt').editable({
		
		colorsStep: 20,
		countCharacters: true,
		inlineMode: false,
		height: 500 	
		//initOnClick:true 
		});
		
		$('.edittt-admin').editable({
		
		colorsStep: 20,
		countCharacters: true,
		inlineMode: false,
		height: 200 	
		//initOnClick:true 
		});
		
		$('.edittt-1').editable({ 
		
		colorsStep: 20,
		countCharacters: true,
		inlineMode: false,
		height: 300 	
		//initOnClick:true 
		});
		
		jQuery('.froala-box>div:last').remove();
		
      });
  </script>
  
<!-- Vendors -->
<!-- Dev only -->
<!-- build:remove -->
<script src="scripts/dev/less.min.js"></script>	
<!-- /build -->

<!-- Vendors -->
<!-- build:js scripts/vendors.js -->
<script src="scripts/vendors/jquery.min.js"></script> 
<script src="scripts/vendors/bootstrap.min.js"></script>

<!-- /build -->
<script src="scripts/plugins/d3.min.js"></script>
<script src="scripts/plugins/c3.min.js"></script>
<script src="scripts/plugins/screenfull.js"></script>
<script src="scripts/plugins/perfect-scrollbar.min.js"></script>
<script src="scripts/plugins/jquery.easypiechart.min.js"></script>
<script src="scripts/plugins/waves.min.js"></script>
<script src="scripts/app.js"></script> 
<script src="scripts/c3.init.js"></script>
 <script src="scripts/plugins/bootstrap-datepicker.min.js"></script>
<!--<script src="scripts/plugins/summernote.min.js"></script>-->
<script type="text/javascript" src="js/jquery.validate.js"></script>
<script type="text/javascript" src="js/form.js"></script>

<!--
<script src="https://www.google.com/jsapi" type="text/javascript"></script>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<script src="ranking/js/dashboard.js" type="text/javascript"></script>
<script src="ranking/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="ranking/js/highcharts.js"></script>
<script src="ranking/js/exporting.js"></script> 
-->

<?php
$base_url=basename($_SERVER['REQUEST_URI']);
if($base_url=="keyword-reports.php")
{ 
?>
<script src="https://www.google.com/jsapi" type="text/javascript"></script>
<script src="ranking/js/reports.js" type="text/javascript"></script>
<script src="ranking/js/jspdf.min.js" type="text/javascript"></script> 
<?php
}
else if($base_url=="keyword-search.php")
{
?>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<script src="ranking/js/jquery.gcomplete.0.1.2.js" type="text/javascript"></script>
<?php 
}
else if($base_url=="google-ranking.php")
{
?>
<script src="https://www.google.com/jsapi" type="text/javascript"></script>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<script src="ranking/js/dashboard.js" type="text/javascript"></script>
<script src="ranking/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="ranking/js/highcharts.js"></script>
<script src="ranking/js/exporting.js"></script> 
<?php 
}
?> 

<script>
jQuery(document).ready(function($)
{
	var val=$('#move_menuu').val();
	if(val!="")
	{
		if(val==1)
		{
			
			$('#app').removeClass('nav-expand');
			$('.main-container').removeClass('nav-expand');
			$('#site-nav').removeClass('nav-expand');
			
			$('#content').removeClass('nav-expand');   
		}
		else
		{
			$('#app').addClass('nav-expand');
			$('.main-container').addClass('nav-expand');
			$('#site-nav').addClass('nav-expand');
			$('#content').addClass('nav-expand');  
		}
	}
	
});
</script>
<?php 
require_once('google_analytics_code.php');
?>
</body>
</html>
<?php
}

function ht_top_index($active_item = 1, $active_item2 = 0, $show_metatext = 0) {
header('Content-Type:text/html; charset=UTF-8');
global $menu_arr_public;
global $GVars;
global $q;

if ($active_item > 0)
    $menu_arr_public[$active_item]['active'] = 1;

$page_width = 950;
?>
    <!DOCTYPE HTML>
	<html lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="960, maximum-scale=1, user-scalable=0"/>
<!--	
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
 -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<link rel="icon" type="image/png" href="common/images_nw/fav-icon.png" />
            <title><?php
$top_word = $active_item;
if ($active_item2 > 0)
    $top_word = $active_item2;

get_page_title($top_word);
?></title>
            <?php
if ($show_metatext == 1) {
    ?>
    <meta name="description" content="LinkAuthority is a one of a kind service giving your website the trust and value it needs with high quality one way links to help your site gain the exposure it needs in the search engine rankings."/>
<?php
}
?>
			<!-- <style type="text/css" media="all">@import url("common/css/style.css"); </style>   -->
           <link rel="stylesheet" type="text/css" href="common/newtheme_css/style.css" />
		   <link rel="stylesheet" type="text/css" href="common/newtheme_css/min.css" />
		   <link rel="stylesheet" type="text/css" href="common/newtheme_css/animations.css" />
		   <link rel="stylesheet" type="text/css" href="common/newtheme_css/jquery.gritter.css" />
		   <link rel="stylesheet" type="text/css" href="common/newtheme_css/bootstrap-parsley.css" />
		
            <?php if ($active_item == 1) { ?>
    <link rel="canonical" href="http://www.linkauthority.com/"/>
<?php } ?>
            <!--[if IE 6]>
            <link href="common/css/ie.css" rel="stylesheet" type="text/css" />
            <![endif]-->
            <!--[if IE 6]><script  type="text/javascript" src="common/js/png1.js"></script><![endif]-->
            <!--[if IE]><script  type="text/javascript" src="common/js/ieh5fix.js"></script><![endif]-->
			<script type="text/javascript" src="common/newtheme_js/jquery-1.10.2.js"></script>
			<script type="text/javascript" src="common/newtheme_js/common.js"></script>
			<script type="text/javascript" src="common/newtheme_js/min_3.JS"></script>
			<script type="text/javascript" src="common/newtheme_js/jquery.gritter.min.js"></script>
			<script type="text/javascript" src="common/newtheme_js/parsley.min.js"></script>
			<script type="text/javascript" src="common/newtheme_js/code.js"></script>
			<script>
				$(document).ready(function(){
							
						/*  $("#login_form").parsley( 'validate' );
						  $("#signupform_footer").parsley('validate');
						  $("#bottom_signup_form").parsley( 'validate' );
						  $("#password_restore_form").parsley( 'validate' );
						  $("#form_password_restore").parsley('validate');*/
						  // on hover case 
						  $(".resource-menu").hover(
							function () {
								$(".sub-menu-res").css({"display":"block"});
							}
						  );
						  // removing hover effects
						  $('body').click(function() {
								$(".sub-menu-res").css({"display":"none"});
						    });
							
						 $('body').on('scroll', function (e){
								$(".sub-menu-res").css({"display":"none"});
						});		
				   
				});
			
				function message(msg){
									
										$.gritter.add({
												title: 'Link Authority',
												text: msg,
												image: 'common/images_nw/la_logo.png',
												sticky: false,	
											});
									}
			</script>
        </head>
        <body><div class='main-wrapper'>
		<div class="modal-overlay"></div>
            <header class="active">
  <section class="header-wrapper">
     <div class="left-header">
	    <a href="http://www.linkauthority.com/"><img class="logo-icon-main" src="http://i.imgur.com/8Igf0Uy.png" alt=""></a>
	 </div> 
	 
	   <nav class="nav-wrap">
			<ul>
			 <li class="our-range-scroll"><a href="#myDiv">HOW IT WORKS</a></li>
			 <li class="who-use-scroll"><a href="#">WHO USES IT</a></li>
			 <li class="features-scroll"><a href="#">FEATURES</a></li>
			 <li class="resource-menu"><a href="#">RESOURCES</a>
			    <div class="sub-menu-res">
				    <ul>
					  <li>
					     <h1>Case Studies</h1>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/wgBY9gW.jpg"/></div>
							<div class="right-case-std"><a href="clickbank-niche-sites.html">How to rank clickbank niche within 7 weeks</a>
							   <span>November 15th, 2014</span>
							</div>
						 </div>
						 
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/up0PVqA.png"/></div>
							<div class="right-case-std"><a  href="health-niche-3k-month.html">Health Niche: Make 3K/month On Autopilot</a>
							   <span>March 15th, 2015</span>
							</div>
						 </div>
						 
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/FbG6vbq.png"/></div>
							<div class="right-case-std"><a href="javascript:void(0)">Coming soon - case study will be live before below mentioned date</a>
							   <span>December 27th, 2014</span>
							</div>
						 </div>
						 
						 <div class="view-res-btn"><a href="clickbank-niche-sites.html">View all</a></div>
						 
					  </li>
					  
					  <li>					  
					   <h1>Ebooks</h1>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/kBAq5XM.png"/></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">The definitive guide to multi-tier link building</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/yXe2HKr.png"/></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">Ultimate anchor text guide – never get hit by PENGUIN again!</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/2049Zu2.png"></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">The right way to build a powerful private blog network</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>	
						 <!--
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/kBAq5XM.png"/></div>
							<div class="right-case-std"><a class="trigger-modal" data-modal-id=".modal-loginpdf"   href="javascript:void(0)">The definitive guide to multi-tier link building</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/yXe2HKr.png"/></div>
							<div class="right-case-std"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">Ultimate anchor text guide – never get hit by PENGUIN again!</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/2049Zu2.png"></div>
							<div class="right-case-std"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">The right way to build a powerful private blog network</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>	
						
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/2049Zu2.png"></div>
							<div class="right-case-std"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">The right way to build a powerful private blog network</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>		
						 --> 		
						 <div class="view-res-btn"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">View all</a></div>
                        </li>
					  
					  <li> <h1>Tutorials</h1>
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/RioQ2Jf.png"></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">Start here with Linkauthority.com</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						 
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/JcICUWk.png"></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button"> Main discussions - SEO offsite and onsite </a> 
							   <span>November 22nd, 2014</span> 
							</div>
						 </div>
						 
						 <div class="case-std-con">
						    <div class="left-case-std"><img alt="" src="http://i.imgur.com/kNXNckZ.png"></div>
							<div class="right-case-std"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">DIY Blueprints SEO - learn more about the evolving SEO trend from live experiments</a>
							   <span>November 22nd, 2014</span>
							</div>
						 </div>
						 
						  <div class="view-res-btn"><a class="clickable trigger-modal" data-modal-id=".modal-login" id="login-button">View all</a></div>
						 </li>
					</ul>
				</div>
			 </li>			 
			</ul>
		</nav>
		   
		   <div class="right-login-sec">
		    <div class="top-right-button"><a class="button orange filled small clickable trigger-modal" data-modal-id=".modal-signup" id="free-trial-button">TRY IT FOR FREE</a></div>
		    <div class="login-btn"><a class="button green hollow small clickable trigger-modal" data-modal-id=".modal-login" id="login-button">log-in</a></div>		
			
		  </div>
	 
  </section>
</header>
                    <?php
}

// ending of header here


// for new registration from purchase content page
function ht_top_purchase_content($active_item = 1, $active_item2 = 0, $show_metatext = 0)
{
    header('Content-Type:text/html; charset=UTF-8');
    global $menu_arr_public;
    global $GVars;
    global $q;

    if ($active_item > 0)
        $menu_arr_public[$active_item]['active'] = 1;

    $page_width = 950;
    ?>
    <!DOCTYPE HTML>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="icon" type="image/png" href="common/images_nw/fav-icon.png"/>
        <title><?php
            $top_word = $active_item;
            if ($active_item2 > 0)
                $top_word = $active_item2;

            get_page_title($top_word);
            ?></title>
        <?php
        if ($show_metatext == 1) {
            ?>
            <meta name="description" content="LinkAuthority is a one of a kind service giving your website the trust and value it needs with high quality one way links to help your site gain the exposure it needs in the search engine rankings."/>
        <?php
        }
        ?>
        <!-- <style type="text/css" media="all">@import url("common/css/style.css"); </style>   -->
        <link rel="stylesheet" type="text/css" href="common/newtheme_css/style.css"/>
        <link rel="stylesheet" type="text/css" href="common/newtheme_css/min.css"/>
        <link rel="stylesheet" type="text/css" href="common/newtheme_css/animations.css"/>
        <link rel="stylesheet" type="text/css" href="common/newtheme_css/jquery.gritter.css"/>
        <link rel="stylesheet" type="text/css" href="common/newtheme_css/bootstrap-parsley.css"/>

        <?php if ($active_item == 1) { ?>
            <link rel="canonical" href="http://www.linkauthority.com/"/>
        <?php } ?>
        <!--[if IE 6]>
        <link href="common/css/ie.css" rel="stylesheet" type="text/css"/>
        <![endif]-->
        <!--[if IE 6]>
        <script type="text/javascript" src="common/js/png1.js"></script><![endif]-->
        <!--[if IE]>
        <script type="text/javascript" src="common/js/ieh5fix.js"></script><![endif]-->
        <script type="text/javascript" src="common/newtheme_js/jquery-1.10.2.js"></script>
        <script type="text/javascript" src="common/newtheme_js/min_3.JS"></script>
        <script type="text/javascript" src="common/newtheme_js/jquery.gritter.min.js"></script>
        <script type="text/javascript" src="common/newtheme_js/parsley.min.js"></script>
        <script>
            $(document).ready(function () {
                // add button functionality
                $("#add_button").click(function () {
                    var current_value = $("#custom-field-add").data("currentvalue");
                    var max_value = $("#custom-field-add").data("maxvalue");
                    if (current_value < max_value) {
                        current_value = current_value + 1;
                        var append_div_tx = "<div id='customDiv_" + current_value + "' class='full-width-custom-class'><input class='small-input' type='text' placeholder='Enter Keyword' name='keyword" + current_value + "' id='keyword" + current_value + "' /> <input type='text' class='small-input' placeholder='Enter Url' name='url" + current_value + "' id=url" + current_value + "' /></div>";
                        $("#custom-field-add").append(append_div_tx);

                        //console.log(current_value);
                        $("#custom-field-add").data("currentvalue", current_value);
                    }
                });
                // remove button functionality
                $("#sub_button").click(function () {
                    var current_value = $("#custom-field-add").data("currentvalue");
                    if (current_value > 3) {
                        $('#customDiv_' + current_value + '').remove();
                        current_value = current_value - 1;
                        //console.log(current_value);
                        $("#custom-field-add").data("currentvalue", current_value);
                    }
                });
                // getting value from form
                $("#registration_button_1").click(function () {
                    //console.log("working");
                    var full_array = new Object();
                    full_array["emailid"] = $("#custom-field-add").data("useremail");
                    full_array["total"] = $("#custom-field-add").data("currentvalue");
                    full_array['plan'] = $("#custom-field-add").data("subscribeplan"); // get amount paid
                    full_array['txt_id'] = $("#custom-field-add").data("txt_id");  // getting text id
                    full_array['ptype'] = $("#custom-field-add").data("ptype");
                    full_array["country"] = $("#country_1").val();
                    full_array['captcha'] = $("#result_num_1").val();
                    full_array["password"] = $("#password_1").val();
                    full_array["bhw_user"] = $("#bhw_user").val();
                    var key_word = new Object();// getting keywords and url
                    for (var i = 1; i <= full_array["total"]; i++) {
                        key_word["key" + i + ""] = $("#keyword" + i + "").val();
                        key_word["url" + i + ""] = $("#url" + i + "").val();
                    }
                    full_array["detail"] = key_word;
                    full_array = JSON.stringify(full_array);
                    var flag = 1;
                    //	 console.log(full_array); 
                    // ajax request to check 
                    $.ajax({
                        url: "add_user.php",
                        type: "POST",
                        dataType: "json", // change it to json
                        data: {full_array: full_array, flag: flag},
                        success: function (result) {
                            if (result["flag"] == "false") {
                                message(result["msg"]);
                            } else {
                                window.location.href = 'http://www.linkauthority.com';
                            }
                            /* var result=eval('(' + result + ')');
                             message(result["message"]);
                             if(result["flag"]==1)
                             {
                             $("#signupForm").submit();
                             }*/
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            console.log("Status:" + textStatus);
                            console.log("Error:" + errorThrown);
                        }
                    });

                });
                $(".check-selected").click(function () {
                    var value_get = $(this).data("relt");

                    $(".check-selected").removeClass("active");
                    // alert(value_get);	
                    if (value_get == 'current_user_nw') {
                        $(this).addClass("active");

                        $("#current_user_nw").css("display", "block");
                        $("#signup_user_nw").css("display", "none");
                    } else {
                        $(this).addClass("active");
                        $("#signup_user_nw").css("display", "block");
                        $("#current_user_nw").css("display", "none");

                    }

                });

                //---------------------------------------------------------------------------------------------------------------------------------------------------------------
                // for exiting user
                $("#add_button_nw1").click(function () {

                    var current_value = $("#custom-field-add_nw1").data("currentvalue");
                    var max_value = $("#custom-field-add_nw1").data("maxvalue");
                    if (current_value < max_value) {
                        current_value = current_value + 1;
                        var append_div_tx = "<div id='customDiv_nw" + current_value + "' class='full-width-custom-class'><input class='small-input' type='text' placeholder='Enter Keyword' name='keywordnw" + current_value + "' id='keywordnw" + current_value + "' /> <input class='small-input' type='text' placeholder='Enter Url' name='urlnw" + current_value + "' id=urlnws" + current_value + "' /></div>";
                        $("#custom-field-add_nw1").append(append_div_tx);

                        //console.log(current_value);
                        $("#custom-field-add_nw1").data("currentvalue", current_value);
                    }
                });
                // remove button functionality
                $("#sub_button_nw1").click(function () {
                    var current_value = $("#custom-field-add_nw1").data("currentvalue");
                    if (current_value > 3) {
                        $('#customDiv_nw' + current_value + '').remove();
                        current_value = current_value - 1;
                        //console.log(current_value);
                        $("#custom-field-add_nw1").data("currentvalue", current_value);
                    }
                });
                // getting value from form
                $("#registration_button__nw1").click(function () {
                    //console.log("working");
                    var full_array = new Object();
                    full_array["emailid"] = $("#custom-field-add_nw1").data("useremail");
                    full_array["total"] = $("#custom-field-add_nw1").data("currentvalue");
                    full_array['plan'] = $("#custom-field-add_nw1").data("subscribeplan"); // get amount paid
                    full_array['txt_id'] = $("#custom-field-add_nw1").data("txt_id");  // getting text id
                    full_array['ptype'] = $("#custom-field-add_nw1").data("ptype");

                    full_array["user_emailid"] = $("#email_id_nw").val();
                    full_array["password"] = $("#password_nw").val();

                    full_array['captcha'] = $("#result_num_nw1").val();

                    var key_word = new Object();// getting keywords and url
                    for (var i = 1; i <= full_array["total"]; i++) {
                        key_word["key" + i + ""] = $("#keywordnw" + i + "").val();
                        key_word["url" + i + ""] = $("#urlnw" + i + "").val();
                    }
                    full_array["detail"] = key_word;
                    full_array = JSON.stringify(full_array);
                    var flag = 3;
                    //	 console.log(full_array); 
                    // ajax request to check 
                    $.ajax({
                        url: "add_user.php",
                        type: "POST",
                        dataType: "json", // change it to json
                        data: {full_array: full_array, flag: flag},
                        success: function (result) {
                            if (result["flag"] == "false") {
                                message(result["msg"]);
                            } else {
                                window.location.href = 'http://www.linkauthority.com';
                            }
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            console.log("Status:" + textStatus);
                            console.log("Error:" + errorThrown);
                        }
                    });

                });

            });

            function message(msg) {

                $.gritter.add({
                    title: 'Link Authority',
                    text: msg,
                    image: 'common/images_nw/la_logo.png',
                    sticky: false,
                });
            }


        </script>
    </head>
    <body><div class='main-wrapper'>
    <div class="modal-overlay"></div>
    <header class="active">
        <section class="header-wrapper">
            <div class="left-header1">
                <a href="http://www.linkauthority.com/"><img class="logo-icon-main1" src="http://i.imgur.com/Pt25W9I.png" alt=""></a>
            </div>

        </section>
    </header>
<?php
}


// new function start
function ht_bot_index()
{
    ?>
    <!-- login  modal  -->
    <div class="modal effect vwo-signup-login-common modal-login">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img src="http://i.imgur.com/VyrAGQn.png"></div>
            <h1>Link Authority Affiliate System</h1>

            <p>Any One can become linkauthority affiliate just login into your dashboard and click on "AFFILIATION" in footer</p>

            <form id="login_form" action="login.php" method="post" parsley-validate>
                <div class="form-item">
                    <label class="label">Email address</label>
                    <input type="hidden" name="a" value=''/>
                    <input name="email" parsley-type="email" class="input-text" require_onced type="email" placeholder="Email"/>

                </div>
                <div class="form-item">
                    <label class="label">Password</label>
                    <input name="password" type="password" class="input-text" placeholder="Password" require_onced/>

                </div>
                <div class="form-submit-block">
                    <input type="checkbox" class="checkbox" name="rememberme" id="checkbox-remember" value="true">
                    <label for="checkbox-remember">Remember me</label>
                </div>
                <div class="form-submit-block submit-button loginButton">
                    <input type="submit" value="login"/>
                    <a href="" class="forgot-pwd clickable trigger-modal" data-modal-id=".modal-forgotpassword">Forgot Password?</a>

                    <div class="clr"></div>
                </div>

            </form>
            <div class="not-registered-block">
                <span class="not-registered">Not a registered user?</span>
                <a class="button grey filled small clickable trigger-modal" data-modal-id=".modal-signup">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- finish here -->

    <!-- PDF login  modal  -->
    <div class="modal effect vwo-signup-login-common modal-loginpdf">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img src="http://i.imgur.com/VyrAGQn.png"></div>
            <form id="login_form" action="login.php" method="post" parsley-validate>
                <div class="form-item">
                    <label class="label">Email address</label>
                    <input type="hidden" name="a" value='pdf_viewer'/>
                    <input name="email" parsley-type="email" class="input-text" require_onced type="email" placeholder="Email"/>

                </div>
                <div class="form-item">
                    <label class="label">Password</label>
                    <input name="password" type="password" class="input-text" placeholder="Password" require_onced/>

                </div>
                <div class="form-submit-block">
                    <input type="checkbox" class="checkbox" name="rememberme" id="checkbox-remember" value="true">
                    <label for="checkbox-remember">Remember me</label>
                </div>
                <div class="form-submit-block submit-button loginButton">
                    <input type="submit" value="login"/>
                    <a href="" class="forgot-pwd clickable trigger-modal" data-modal-id=".modal-forgotpassword">Forgot Password?</a>

                    <div class="clr"></div>
                </div>

            </form>
            <div class="not-registered-block">
                <span class="not-registered">Not a registered user?</span>
                <a class="button grey filled small clickable trigger-modal" data-modal-id=".modal-signup">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- finish here -->

    <!-- forget password  modal  -->
    <div class="modal effect vwo-signup-login-common modal-forgotpassword">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img alt="" src="http://i.imgur.com/VyrAGQn.png"/></div>

            <form method="post" id="password_restore_form" action="password_restore.php" parsley-validate>
                <input type="hidden" name="a" value="1"/>

                <div class="form-item">
                    <label class="label">Email address</label>
                    <input name="email" type="email" parsley-type="email" require_onced placeholder="Email" class="input-text"/>
                </div>

                <div class="form-item">
                    <label class="label">Captcha</label>
                    <input class="field" type="text" require_onced name="captcha" class="forms" style="width:92%;"/>
                    <img alt="" src='captcha_ram.php' style="width:120px;border: 1px solid #999999; margin-top: 20px; margin-left: 54px;"/>  
                </div>

                <div class="form-submit-block submit-button forgot">
                    <input class="" type="submit" value="Restore Your Password"/>
                </div>
            </form>
        </div>
    </div>
    <!-- finish here   -->

    </div>
    <footer>
        <div class="footer-wrapper">
            <div class="left-footer">
                <div class="footer-links">
                    <ul>
                        <li><a href="#home-div">HOME</a>|</li>
                        <li><a href="#myDiv">FEATURE TOUR</a>|</li>
                        <li><a href="http://linkauthority.zendesk.com/" target="_blank">SUPPORT</a></li>
                    </ul>
                </div>
                <div class="copyright">Copyright <?php echo date(Y); ?> Linkauthority.com</div>
            </div>
            <div class="right-footer">
                <div class="email-footer"><img alt="" src="http://i.imgur.com/mMUDGuV.png">Mail: support@linkauthority.com</div>
                <div class="privacy-footer">
                    <ul>
                        <li><a href="privacy_policy.php">Privacy Policy</a>|</li>
                        <li><a href="terms_and_conditions.php">Terms and Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
	
    <script type="text/javascript">
        adroll_adv_id = "BRPDHME6GZHBVNLJE3TZR2";
        adroll_pix_id = "6LV2SGL2ZBGXVG25AD5UNI";
        (function () {
            var oldonload = window.onload;
            window.onload = function () {
                __adroll_loaded = true;
                var scr = document.createElement("script");
                var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
                scr.setAttribute('async', 'true');
                scr.type = "text/javascript";
                scr.src = host + "/j/roundtrip.js";
                ((document.getElementsByTagName('head') || [null])[0] ||
                document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
                if (oldonload) {
                    oldonload()
                }
            };
        }());
    </script>
    <!--footer ends-->
	
<?php
require_once('google_analytics_code.php');
?>
    </body>
    </html>
<?php
}

// for other page footer private and term pages
function ht_bot_index_other()
{
    ?>

    <!-- login  modal  -->
    <div class="modal effect vwo-signup-login-common modal-login">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img src="common/images_nw/logo.png"></div>
            <h1>Link Authority Affiliate System</h1>

            <p>Any One can become linkauthority affiliate just login into your dashboard and click on "AFFILIATION" in footer</p>

            <form id="login_form" action="login.php" method="post" parsley-validate>
                <div class="form-item">
                    <label class="label">Email address</label>
                    <input type="hidden" name="a" value=''/>
                    <input name="email" parsley-type="email" class="input-text" require_onced type="email" placeholder="Email"/>

                </div>
                <div class="form-item">
                    <label class="label">Password</label>
                    <input name="password" type="password" class="input-text" placeholder="Password" require_onced/>

                </div>
                <div class="form-submit-block">
                    <input type="checkbox" class="checkbox" name="rememberme" id="checkbox-remember" value="true">
                    <label for="checkbox-remember">Remember me</label>
                </div>
                <div class="form-submit-block submit-button loginButton">
                    <input type="submit" value="login"/>
                    <a href="" class="forgot-pwd clickable trigger-modal" data-modal-id=".modal-forgotpassword">Forgot Password?</a>

                    <div class="clr"></div>
                </div>

            </form>
            <div class="not-registered-block">
                <span class="not-registered">Not a registered user?</span>
                <a class="button grey filled small clickable trigger-modal" data-modal-id=".modal-signup">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- finish here -->

    <!-- PDF login  modal  -->
    <div class="modal effect vwo-signup-login-common modal-loginpdf">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img src="common/images_nw/logo.png"></div>
            <form id="login_form" action="login.php" method="post" parsley-validate>
                <div class="form-item">
                    <label class="label">Email address</label>
                    <input type="hidden" name="a" value='pdf_viewer'/>
                    <input name="email" parsley-type="email" class="input-text" require_onced type="email" placeholder="Email"/>

                </div>
                <div class="form-item">
                    <label class="label">Password</label>
                    <input name="password" type="password" class="input-text" placeholder="Password" require_onced/>

                </div>
                <div class="form-submit-block">
                    <input type="checkbox" class="checkbox" name="rememberme" id="checkbox-remember" value="true">
                    <label for="checkbox-remember">Remember me</label>
                </div>
                <div class="form-submit-block submit-button loginButton">
                    <input type="submit" value="login"/>
                    <a href="" class="forgot-pwd clickable trigger-modal" data-modal-id=".modal-forgotpassword">Forgot Password?</a>

                    <div class="clr"></div>
                </div>

            </form>
            <div class="not-registered-block">
                <span class="not-registered">Not a registered user?</span>
                <a class="button grey filled small clickable trigger-modal" data-modal-id=".modal-signup">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- finish here -->

    <!-- forget password  modal  -->
    <div class="modal effect vwo-signup-login-common modal-forgotpassword">
        <div class="content">
            <div class="button-close trigger-modal-close"></div>
            <div class="logo"><img alt="" src="common/images_nw/logo.png"/></div>

            <form method="post" id="password_restore_form" action="password_restore.php" parsley-validate>
                <input type="hidden" name="a" value="1"/>

                <div class="form-item">
                    <label class="label">Email address</label>
                    <input name="email" type="email" parsley-type="email" require_onced placeholder="Email" class="input-text"/>
                </div>

                <div class="form-item">
                    <label class="label">Captcha</label>
                    <input class="field" type="text" require_onced name="captcha" class="forms" style="width:92%;"/>
                    <img alt="" src='turing_img.php' style="border: 1px solid #999999; margin-top: 20px; margin-left: 54px;"/>
                </div>

                <div class="form-submit-block submit-button forgot">
                    <input class="" type="submit" value="Restore Your Password"/>
                </div>
            </form>
        </div>
    </div>
    <!-- finish here   -->

    </div>
    <footer>
        <div class="footer-wrapper">
            <div class="left-footer">
                <div class="footer-links">
                    <ul>
                        <li><a href="index.php">HOME</a>|</li>
                        <li><a href="index.php#myDiv">FEATURE TOUR</a>|</li>
                        <?php if ($_SESSION['user_id'] > 0) { ?>
                            <li><a href="affiliation.php">AFFILIATION</a>|</li>
                        <?php } ?>
                        <li><a href="http://linkauthority.zendesk.com/" target="_blank">SUPPORT</a></li>
                    </ul>
                </div>
                <div class="copyright">Copyright <?php echo date(Y); ?> Linkauthority.com</div>
            </div>
            <div class="right-footer">
                <div class="email-footer"><img alt="" src="common/images_nw/mail-icon.png">Mail: support@linkauthority.com</div>
                <div class="privacy-footer">
                    <ul>
                        <li><a target="_blank" href="privacy_policy.php">Privacy Policy</a>|</li>
                        <li><a target="_blank" href="terms_and_conditions.php">Terms and Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <script type="text/javascript">
        adroll_adv_id = "BRPDHME6GZHBVNLJE3TZR2";
        adroll_pix_id = "6LV2SGL2ZBGXVG25AD5UNI";
        (function () {
            var oldonload = window.onload;
            window.onload = function () {
                __adroll_loaded = true;
                var scr = document.createElement("script");
                var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
                scr.setAttribute('async', 'true');
                scr.type = "text/javascript";
                scr.src = host + "/j/roundtrip.js";
                ((document.getElementsByTagName('head') || [null])[0] ||
                document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
                if (oldonload) {
                    oldonload()
                }
            };
        }());
    </script>
    <!--footer ends-->
<?php
require_once('google_analytics_code.php');
?>
    </body>
    </html>
<?php
}

// header  for password recovery page_title

function ht_top_index_new_password($active_item = 1, $active_item2 = 0, $show_metatext = 0) {
header('Content-Type:text/html; charset=UTF-8');
global $menu_arr_public;
global $GVars;
global $q;

if ($active_item > 0)
    $menu_arr_public[$active_item]['active'] = 1;

$page_width = 950;
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="icon" type="image/png" href="common/images_nw/fav-icon.png"/>
    <title><?php
        $top_word = $active_item;
        if ($active_item2 > 0)
            $top_word = $active_item2;

        get_page_title($top_word);
        ?></title>
    <?php
    if ($show_metatext == 1) {
        ?>
        <meta name="description" content="LinkAuthority is a one of a kind service giving your website the trust and value it needs with high quality one way links to help your site gain the exposure it needs in the search engine rankings."/>
    <?php
    }
    ?>
    <!-- <style type="text/css" media="all">@import url("common/css/style.css"); </style>   -->
    <link rel="stylesheet" type="text/css" href="common/newtheme_css/style.css"/>
    <link rel="stylesheet" type="text/css" href="common/newtheme_css/min.css"/>
    <link rel="stylesheet" type="text/css" href="common/newtheme_css/animations.css"/>
    <link rel="stylesheet" type="text/css" href="common/newtheme_css/jquery.gritter.css"/>
    <link rel="stylesheet" type="text/css" href="common/newtheme_css/bootstrap-parsley.css"/>

    <?php if ($active_item == 1) { ?>
        <link rel="canonical" href="http://www.linkauthority.com/"/>
    <?php } ?>
    <!--[if IE 6]>
    <link href="common/css/ie.css" rel="stylesheet" type="text/css"/>
    <![endif]-->
    <!--[if IE 6]>
    <script type="text/javascript" src="common/js/png1.js"></script><![endif]-->
    <!--[if IE]>
    <script type="text/javascript" src="common/js/ieh5fix.js"></script><![endif]-->
    <script type="text/javascript" src="common/newtheme_js/jquery-1.10.2.js"></script>
    <script type="text/javascript" src="common/newtheme_js/min_3.JS"></script>
    <script type="text/javascript" src="common/newtheme_js/jquery.gritter.min.js"></script>
    <script type="text/javascript" src="common/newtheme_js/parsley.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#form_password_restore").parsley('validate');
            // on hover case 
            $(".resource-menu").hover(
                function () {
                    $(".sub-menu-res").css({"display": "block"});
                }
            );
            // removing hover effects
            $('body').click(function () {
                $(".sub-menu-res").css({"display": "none"});
            });

            $('body').on('scroll', function (e) {
                $(".sub-menu-res").css({"display": "none"});
            });

        });

        function message(msg) {

            $.gritter.add({
                title: 'Link Authority',
                text: msg,
                image: 'common/images_nw/la_logo.png',
                sticky: false,
            });
        }
    </script>
</head>
<body>
<div class='main-wrapper'>
    <div class="modal-overlay"></div>
    <header class="active">
        <section class="header-wrapper">
            <div class="left-header">
                <a href="http://www.linkauthority.com/"><img class="logo-icon-main" src="http://i.imgur.com/8Igf0Uy.png" alt=""> </a>
            </div>

            <nav class="nav-wrap">
                <ul>
                    <li class="our-range-scroll"><a href="index.php#myDiv">HOW IT WORKS</a></li>
                    <li class="who-use-scroll"><a href="index.php#myDiv2">WHO USES IT</a></li>
                    <li class="features-scroll"><a href="index.php#myDiv3">FEATURES</a></li>
                    <li class="resource-menu"><a href="#">RESOURCES</a>

                        <div class="sub-menu-res">
                            <ul>
                                <li>
                                    <h1>Case Studies</h1>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/wgBY9gW.jpg"/></div>
                                        <div class="right-case-std"><a target="_blank" href="http://linkauthority.com/forum/showthread.php?tid=10&pid=10#pid10">How to rank clickbank niche within 7 weeks</a>
                                            <span>November 15th, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/FbG6vbq.png"/></div>
                                        <div class="right-case-std"><a href="javascript:void(0)">Coming soon - case study will be live before below mentioned date</a>
                                            <span>December 15th, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/FbG6vbq.png"/></div>
                                        <div class="right-case-std"><a href="javascript:void(0)">Coming soon - case study will be live before below mentioned date</a>
                                            <span>December 27th, 2014</span>
                                        </div>
                                    </div>

                                    <div class="view-res-btn"><a href="javascript:void(0)">View all</a></div>

                                </li>

                                <li>
                                    <h1>Ebooks</h1>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/kBAq5XM.png"/></div>
                                        <div class="right-case-std"><a class="" href="javascript:void(0)">The definitive guide to multi-tier link building</a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/yXe2HKr.png"/></div>
                                        <div class="right-case-std"><a class="" href="javascript:void(0)">Ultimate anchor text guide – never get hit by PENGUIN again!</a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/2049Zu2.png"></div>
                                        <div class="right-case-std"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">The right way to build a powerful private blog network</a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>
                                    <div class="view-res-btn"><a class="trigger-modal" data-modal-id=".modal-loginpdf" href="javascript:void(0)">View all</a></div>
                                </li>

                                <li><h1>Forum</h1>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/NfGoh9I.png"></div>
                                        <div class="right-case-std"><a target="_blank" href="http://www.linkauthority.com/forum/showthread.php?tid=26&pid=28#pid28

">Start here with Linkauthority.com</a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/JcICUWk.png"></div>
                                        <div class="right-case-std"><a target="_blank" href="http://linkauthority.com/forum/forumdisplay.php?fid=9"> Main discussions - SEO offsite and onsite </a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>

                                    <div class="case-std-con">
                                        <div class="left-case-std"><img alt="" src="http://i.imgur.com/niOsANt.png"></div>
                                        <div class="right-case-std"><a target="_blank" href="http://linkauthority.com/forum/forumdisplay.php?fid=13">Live experiments - learn more about the evolving SEO trend from live experiments</a>
                                            <span>November 22nd, 2014</span>
                                        </div>
                                    </div>

                                    <div class="view-res-btn"><a target="_blank" href="http://linkauthority.com/forum/index.php">View all</a></div>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>
            <?php
            if ($_SESSION['user_id'] == 0) {
                ?>
                <div class="right-login-sec">
                    <div class="top-right-button"><a class="button orange filled small clickable trigger-modal" data-modal-id=".modal-signup" id="free-trial-button">TRY IT FOR FREE</a></div>
                    <div class="login-btn"><a class="button green hollow small clickable trigger-modal" data-modal-id=".modal-login" id="login-button">log-in</a></div>

                </div>
            <?php } ?>
        </section>
    </header>
    <?php
    }
    // for password recovery page
    function load_password_recovery_page($email1, $secret1)
    {
        ?>
        <div class="clr"></div>
        <section class="recover-prv">
            <div class="recover-wrapper"><h1>
                    Please enter and repeat your new password (min. 8 symbols)
                </h1>

                <div class="content-second">
                    <Form method='post' id='form_password_restore' action='password_restore.php' parsley-validate>
                        <input type='hidden' name=a value=password_recovery>
                        <input type='hidden' name='email' value='<?php echo $email1;?>'>
                        <input type='hidden' name='secret' value='<?php echo $secret1;?>'>

                        <div class="form-first-trial recoverField">
                            <label>Enter New Password:</label>
                            <input type='password' name='password1' class='forms' id='password1' require_onced data-parsley-minlength="8">
                        </div>
                        <div class="form-first-trial recoverField">
                            <label>Repeat New Password:</label> <input type='password' name='password2' id='password2' class='forms' require_onced data-parsley-equalto="#password1"></div>
                        <div class="start-trial-btn">
                            <input type='submit' value='Save New Password' class='forms'>
                        </div>
                    </form>
                </div>
            </div>
        </section>


    <?php
    }

    function page_title($title, $space = 1) {
	
	if($_SESSION['user_admin'] == 2)
	{
		$user_id=$_SESSION['user_email'];
		$user_id=strstr($user_id, '@', true);
		$user_id=str_replace("."," ",$user_id)." 's ";
		
	}
	else
	{
	$user_id="";
	}
	
    ?>
		<div class="red-info">
		<div class="label"> 
			<h1 class="header_h1"><?php echo $user_id; ?><?= $title; ?></h1>
			<?php
			if ($title == 'Projects' || $title == 'My sites' || $title == 'Posts') {
				?>
				<a href="#" class="hitme btn btn-primary"> 
				
				<?php
					if ($title == 'Projects')
						echo "Add New Project";
					if ($title == 'My sites')
						echo "Add New site";
					if ($title == 'Posts')
						echo "Add New Post";
					?>
				</a>
			<?php
			}
			?>
		</div>
		</div>
        <?php
		}

        function info_message_display()
        {
            //$_SESSION['info_message'] = "AAA"; $_SESSION['info_message_error'] = 0;
            if (isset($_SESSION['info_message']) && $_SESSION['info_message'] != '') {
                //echo "<br>";
                if ($_SESSION['info_message_error'] == 1) {
                    echo "<div class='alert alert-danger sitealert'>" . $_SESSION['info_message'] . "</div>";
                    //$txt = "<div class='infomsg'>xxx".$_SESSION['info_message']."</div>";
                    //$tbbg = "#FF0000";
                } else {
                    //$txt = "<div class='infomsg'>ccc".$_SESSION['info_message']."</div>";
                    echo "<div class='alert alert-success sitealert'>" . $_SESSION['info_message'] . "</div>";
                    $tbbg = "#55FF55";
                }
                $_SESSION['info_message'] = '';
                $_SESSION['info_message_error'] == 0;
            }
        }

        // new function added On 14-11-2014
        function info_message_display_new()
        {

            if (isset($_SESSION['info_message']) && $_SESSION['info_message'] != '') {

                if ($_SESSION['info_message_error'] == 1) {

                    ?>
                    <script>message("<?php echo $_SESSION['info_message']; ?>");</script>
                <?php

                } else {

                ?>
                    <script>message("<?php echo $_SESSION['info_message']; ?>");</script>
                <?php

                }
                $_SESSION['info_message'] = '';
                $_SESSION['info_message_error'] == 0;
            }
        }

        function log_action($user_id, $action_text)
        {
            global $GVars;
            $ip = $_SERVER['REMOTE_ADDR'];
            $action_text = substr($action_text, 0, 2048);
            mysql__("insert into log_users values ('','$user_id','" . $GVars['timenow'] . "','" . addslashes($action_text) . "','$ip')");
        }

        function log_article($article_id, $user_id, $action_text)
        {
            global $GVars;
            $ip = $_SERVER['REMOTE_ADDR'];
            $action_text = substr($action_text, 0, 4096);
            mysql__("insert into log_article values ('','$article_id','$user_id','" . addslashes($action_text) . "','$ip','" . $GVars['timenow'] . "')");
        }

        function load_my_projects()
        {
            global $GVars;
            $_SESSION['projects'] = array();
            $_SESSION['total_articles_in_publishing_queue'] = 0;
            $_SESSION['total_articles_published'] = 0;

            //quick fix for projects info
            $rs = mysql__("select * from projects where user_id='" . $_SESSION['user_id'] . "' and status>0");
            while ($rw = mysql_fetch_array($rs)) {
                //update_project_article_count($rw['id']);
                update_full_project_stats($rw['id']);
            }

            $n = 0;
			
			
            $rs = mysql__("select * from projects where user_id='" . $_SESSION['user_id'] . "' and status>0 order by status desc, title asc");
            while ($rw = mysql_fetch_array($rs)) {
                $_SESSION['projects'][$n]['id'] = $rw['id'];
                $_SESSION['projects'][$n]['id_hash'] = $rw['id_hash'];
                $_SESSION['projects'][$n]['title'] = $rw['title'];
                $_SESSION['projects'][$n]['url'] = $rw['url'];
                $_SESSION['projects'][$n]['status'] = $rw['status'];
                $_SESSION['projects'][$n]['n_articles'] = $rw['n_articles'];
                $_SESSION['projects'][$n]['n_articles_published'] = $rw['n_articles_published'];
                $_SESSION['projects'][$n]['n_articles_in_publishing_queue'] = $rw['n_articles_in_publishing_queue'];
                $_SESSION['projects'][$n]['n_articles_problematic'] = $rw['n_articles_problematic'];

                if ($_SESSION['projects'][$n]['id_hash'] == '') {
                    $_SESSION['projects'][$n]['id_hash'] = md5($GVars['secret_string'] . $_SESSION['projects'][$n]['id']);
                    $rs2 = mysql__("update projects set id_hash='" . $_SESSION['projects'][$n]['id_hash'] . "' where id=" . $_SESSION['projects'][$n]['id']);
                }

                $_SESSION['total_articles_in_publishing_queue'] += $rw['n_articles_in_publishing_queue'];
                $_SESSION['total_articles_published'] += $rw['n_articles_published'];

                $n++;
            }

            mysql__("update users set cnt_artc_waiting='" . $_SESSION['total_articles_in_publishing_queue'] . "' where id='" . $_SESSION['user_id'] . "'");
        }

        function get_my_project_session_id_by_hash($pid)
        { // ir return -1, project is not mine...
            $myproject = -1;
            for ($i = 0; $i < sizeof($_SESSION['projects']); $i++) {
                if ($_SESSION['projects'][$i]['id_hash'] == $pid) {
                    $myproject = $i;
                }
            }
            return $myproject;
        }

        function article_ownership_by_hash($aid)
        {
            $ownership = FALSE;
            if (strlen($aid) == 32) {
                $rs = mysql__("select * from articles where id_hash='$aid'");
                while ($rw = mysql_fetch_array($rs)) {
                    if ($rw['user_id'] == $_SESSION['user_id'])
                        $ownership = TRUE;
                    if ($rw['status'] > 4) // can't edit approved or published articles, view only
                        $ownership = FALSE;
                }
            }
            return $ownership;
        }

        function get_article_info_by_hash($aid)
        {
            $ret_arr = array();
            if (strlen($aid) == 32) {
                $rs = mysql__("select * from articles where id_hash='$aid' union select * from articles_published where id_hash='$aid'");
                while ($rw = mysql_fetch_array($rs)) {
                    $ret_arr['id'] = $rw['id'];
                    $ret_arr['user_id'] = $rw['user_id'];
                    $ret_arr['project_id'] = $rw['project_id'];
                    $ret_arr['status'] = $rw['status'];
                    $ret_arr['title'] = $rw['title'];
                    $ret_arr['text'] = $rw['text'];
                    $ret_arr['keyword1'] = $rw['keyword1'];
                    $ret_arr['keyword2'] = $rw['keyword2'];
                    $ret_arr['keyword3'] = $rw['keyword3'];
                    $ret_arr['url1'] = $rw['url1'];
                    $ret_arr['url2'] = $rw['url2'];
                    $ret_arr['url3'] = $rw['url3'];
                    $ret_arr['admin_comment'] = $rw['admin_comment'];
                    $ret_arr['user_signed_post'] = $rw['user_signed_post'];
                }
            }

            return $ret_arr;
        }

        function check_article_components_for_errors($f_title, $f_text, $f_tags, $f_cat, $f_keyword1, $f_keyword2, $f_keyword3, $f_link1, $f_link2, $f_link3)
        {
            global $static_article_categories;

            $ret_arr = array();

            $ret_arr['errors'] = 0;
            $ret_arr['error_str'] = '';
            $ret_arr['publishable'] = 1;

            $check_title = check_article_title($f_title);
            if ($check_title['publishable'] == 0)
                $ret_arr['publishable'] = 0;
            if ($check_title['errors'] > 0)
                $ret_arr['errors'] += $check_title['errors'];
            if ($check_title['error_str'] != '')
                $ret_arr['error_str'] .= "" . $check_title['error_str'] . "";

            //echo "After title check: ".$ret_arr['errors']." - ";

            $check_text = check_article_text($f_text);
            if ($check_text['publishable'] == 0)
                $ret_arr['publishable'] = 0;
            if ($check_text['errors'] > 0)
                $ret_arr['errors'] += $check_text['errors'];
            if ($check_text['error_str'] != '')
                $ret_arr['error_str'] .= "" . $check_text['error_str'] . "";

            //echo "After text check: ".$ret_arr['errors']." - ";
            //xxxxxxxxxx

            $check_tags = check_article_tags($f_tags);
            //print_r($check_tags);

            if ($check_tags['publishable'] == 0)
                $ret_arr['publishable'] = 0;
            if ($check_tags['errors'] > 0)
                $ret_arr['errors'] += $check_tags['errors'];
            if ($check_tags['error_str'] != '')
                $ret_arr['error_str'] .= "" . $check_tags['error_str'] . "";

            //echo "After tags check: ".$ret_arr['errors']." - ";

            $combined_text_for_spun_check = $f_title . $f_text . $f_tags . $f_keyword1 . $f_keyword2 . $f_keyword3;
            if (check_for_spun_content($combined_text_for_spun_check)) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot;  Sorry, we do not accept spun content. Please refrain from using these characters: { } [ ] | ~ <br>";
            }

            if (($f_cat > 0) and ($f_cat <= 43) and (is_numeric($f_cat))) {

            } else {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Category is not selected.<br>";
            }

            $bw_f_title = has_bad_words($f_title);
            $bw_f_text = has_bad_words($f_text);
            $bw_f_tags = has_bad_words($f_tags);
            $bw_f_keywords = has_bad_words($f_keyword1 . " " . $f_keyword2 . " " . $f_keyword3);

            //print_r($bw_f_keywords);

            if ($bw_f_title['n_bad_words'] > 0) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Title has bad words (" . trim($bw_f_title['bad_words']) . ").<br>";
            }
            if ($bw_f_text['n_bad_words'] > 0) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Text has bad words (" . trim($bw_f_text['bad_words']) . ").<br>";
            }
            if ($bw_f_tags['n_bad_words'] > 0) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Tags has bad words (" . trim($bw_f_tags['bad_words']) . ").<br>";
            }
            if ($bw_f_keywords['n_bad_words'] > 0) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Keywords has bad words (" . trim($bw_f_keywords['bad_words']) . ").<br>";
            }

            if (($f_keyword1 == '') and ($f_keyword2 == '') and ($f_keyword3 == '')) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Keywords missing. Please enter at least one.<br>";
            }

            if (strlen($f_link1) < 11)
                $f_link1 = '';
            if (strlen($f_link2) < 11)
                $f_link2 = '';
            if (strlen($f_link3) < 11)
                $f_link3 = '';

            if (($f_link1 == '') and ($f_link2 == '') and ($f_link3 == '')) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "URL's missing. Please enter at least one.<br>";
            }

            $n_link_pos_found = 0;
            $pos11 = stripos($f_text, '%LINK1%');
            if (($pos11 >= 0) and (is_numeric($pos11)))
                $n_link_pos_found++;
            $pos21 = stripos($f_text, '%LINK2%');
            if (($pos21 >= 0) and (is_numeric($pos21)))
                $n_link_pos_found++;
            //    $pos31 = stripos($f_text, '%LINK3%'); if (($pos31>=0) and (is_numeric($pos31))) $n_link_pos_found++;

            $pos12 = strripos($f_text, '%LINK1%');
            $pos22 = strripos($f_text, '%LINK2%');
            //    $pos32 = strripos($f_text, '%LINK3%');

            if (($pos11 != $pos12) or ($pos21 != $pos22)) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "You are not allowed to multiply links %LINK1% or %LINK2%<br>";
            }

            //echo $check_text['nwords']." ".$n_link_pos_found; die();

            if (($check_text['nwords'] < 300) and ($n_link_pos_found > 1)) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; " . "Links %LINK2% will not be published.<br>";
            }

            if ($n_link_pos_found == 0) {
                $ret_arr['publishable'] = 0;
                $ret_arr['errors']++;
                $ret_arr['error_str'] .= "&middot; Please add %LINK1% to the post text. Add %LINK2% if your article is longer than 300 words.<br>";
            }

            if (($ret_arr['errors'] == 0) and ($ret_arr['publishable'] == 1))
                $ret_arr['error_str'] = "Article is ready for publishing";

            //print_r($ret_arr); die();

            return $ret_arr;
        }

        function magic_menu($menu_arr)
        {
            ?>
            <div class="navigation">
                <div class="m-menu">
                    <div class="lastlistcover">
                    </div>
                    <ul class="menu">
                        <?php
                        $n_letters = 0;
                        for ($i = 1; $i <= sizeof($menu_arr); $i++) {
                            if (!isset($menu_arr[$i]))
                                continue;
                            $n_letters += strlen($menu_arr[$i]['text']);
                        }
                        foreach ($menu_arr as $i => $value) {
                            $this_bgcolor = '';
                            if ($menu_arr[$i]['active'] == 1) {
                                //$this_bgcolor = " background='i/menua.png' ";
                            }
                            echo "<li><a href=\"" . $menu_arr[$i]['link'] . "\">" . $menu_arr[$i]['text'] . "</a></li>";
                            //echo "<a href='".$menu_arr[$i]['link']."' class=$classs>".$menu_arr[$i]['text']."</a>";
                        }
                        ?>
                    </ul>
                    <!--menu-->
                </div>
            </div>
            <!--navigation -->
        <?php
        }

        function magic_menu_index($menu_width, $menu_arr)
        {
            ?>
            <section id="menu">
                <ul>
                    <li class="first">
                    </li>
                    <?php
                    $n_letters = 0;
                    for ($i = 1; $i <= sizeof($menu_arr); $i++)
                        $n_letters += strlen($menu_arr[$i]['text']);

                    foreach ($menu_arr as $i => $value) {
                        $this_pressed = $menu_arr[$i]['text'];
                        if ($menu_arr[$i]['active'] == 1) {
                            //$this_pressed .= ":active";
                        }

                        echo "<li class=\"$this_pressed\"><a href=\"" . $menu_arr[$i]['link'] . "\"></a></li>";
                    }
                    ?>
                    <li class="last">
                    </li>
                </ul>
            </section>
        <?php
        }

        function magic_tabs($menu_width, $menu_arr, $param_string)
        {
            $n_letters = 0;
            for ($i = 1; $i <= sizeof($menu_arr); $i++)
                $n_letters += strlen($menu_arr[$i]['text']);
            foreach ($menu_arr as $i => $value) {
                $class = '';
                if ($menu_arr[$i]['active'] == 1) {
                    $class = "defaulttab selected";
                }
                echo "<li>";
                echo "<a href='" . $param_string . $menu_arr[$i]['link'] . "' class='" . $class . "'>" . $menu_arr[$i]['text'] . "</a>";
                echo "</li>";
            }
        }

        function get_site_info_by_hash($id_hash)
        {
            $ret_arr = array();
            $rs = mysql__("select * from sites where id_hash='$id_hash'");
            while ($rw = mysql_fetch_array($rs)) {
                $ret_arr['id'] = $rw['id'];
                $ret_arr['user_id'] = $rw['user_id'];
                $ret_arr['url'] = $rw['url'];
                $ret_arr['pagerank'] = $rw['pagerank'];
                $ret_arr['login'] = $rw['login'];
                $ret_arr['password'] = $rw['password'];
                $ret_arr['status'] = $rw['status'];
                $ret_arr['status2'] = $rw['status2'];
                $ret_arr['site_approved'] = $rw['site_approved'];
                $ret_arr['approval_comment'] = $rw['approval_comment'];
                $ret_arr['lvl'] = $rw['lvl'];
                $ret_arr['f_cat'] = $rw['f_cat'];
            }
            return $ret_arr;
        }

        function count_user_quota($user_id = 0, $hash = "")
        {
            global $GVars;

            if ($user_id == 0)
                $user_id = $_SESSION['user_id'];
            if ($user_id == 0 && strlen($hash) > 0) {
                $users = mysql__("select id from users where id_hash=='$hash'");
                $user = mysql_fetch_array($users);
                $user_id = $user['id'];
            }

            $this_user_quota_sites = 0;
            $this_user_quota_purchased = 0;
            $this_user_quota_sites_aff = 0;
            $this_user_quota_admin = 0;
            $this_user_quota_use_once = 0;
            $n_quota_used_today = 0;
            $this_user_quota = 0; // total

            $this_user_n_sites_added = 0;
            $monthly_subscription_usd = 0;

            // 4) quota from admin, atkelta i virsu is selectu sekos
            $rs = mysql__("select * from users where id='$user_id'");
            while ($rw = mysql_fetch_array($rs)) {
                if ($GVars['timenow'] < $rw['quota_admin_till_time'])
                    $this_user_quota_admin = $rw['quota_admin'];

                if ($rw['quota_use_once'] > 0)
                    $this_user_quota_use_once = $rw['quota_use_once'];

                $n_quota_used_today = $rw['n_quota_used_today'];
                $quota_purchased_before_calc = $rw['quota_purchased'];
            }

            // 1) Quota from sites
            $rs = mysql__("select * from sites where user_id='" . $user_id . "'");
            while ($rw = mysql_fetch_array($rs)) {
                if (($rw['pagerank'] >= 1) and ($rw['status'] == 5) and ($rw['site_approved'] == 1)) {
                    $this_user_quota_sites += $_SESSION['global_vars']['daily_free_post_limit_pr' . $rw['pagerank']];
                    $this_user_n_sites_added++;
                }
            }

            // 2) Quota from money
            $timelimit = date('Y-m-d H:i:s', strtotime('-30 days'));
            $timelimit_grace = date('Y-m-d H:i:s', strtotime('-' . (30 + $_SESSION['global_vars']['paypal_grace_period_days']) . ' days'));
            $active_subs = array();

            // getting subscription signups
            $rs = mysql__("select * from payments where user_id='" . $user_id . "' and txn_type='subscr_signup' order by id desc");
            while ($rw = mysql_fetch_array($rs)) {
                $active_subs[$rw['subscr_id']]['subscr_signup'] = $rw['cr_time'];
                $active_subs[$rw['subscr_id']]['subscr_cancel'] = '';

                // looking for last payment
                $rs2 = mysql__("select * from payments where user_id='" . $user_id . "' and subscr_id='" . $rw['subscr_id'] . "' and txn_type='subscr_payment'  order by cr_time desc limit 0,1");
                while ($rw2 = mysql_fetch_array($rs2)) {
                    if ($rw2['cr_time'] > $timelimit) {
                        $active_subs[$rw['subscr_id']]['quota'] = $rw2['quota'];

                        $this_user_quota_purchased += $rw2['quota'];
                    } else {
                        $active_subs[$rw2['subscr_id']]['quota'] = $rw2['quota'];

                        if ($rw2['cr_time'] > $timelimit_grace)
                            $this_user_quota_purchased += $rw2['quota'];
                    }
                    $active_subs[$rw['subscr_id']]['payment_time'] = $rw2['cr_time'];
                    $active_subs[$rw['subscr_id']]['subscr_id'] = $rw2['subscr_id'];
                    $active_subs[$rw['subscr_id']]['payment_active_till_time'] = add_seconds($rw2['cr_time'], 2592000);  // 30 days in seconds
                    //echo "[".$rw2['cr_time']."]";

                    $active_subs[$rw['subscr_id']]['payment_money'] = sprintf("%01.2f", $rw2['money']);
                    $active_subs[$rw['subscr_id']]['payment_money_real'] = $rw2['money'];
                }

                // looking for subscription cancelation
                $rs2 = mysql__("select * from payments where user_id='" . $user_id . "' and subscr_id='" . $rw['subscr_id'] . "' and txn_type='subscr_cancel'");
                while ($rw2 = mysql_fetch_array($rs2)) {
                    $active_subs[$rw['subscr_id']]['subscr_cancel'] = $rw2['cr_time'];
                }

                if ($active_subs[$rw['subscr_id']]['subscr_cancel'] == '' && $active_subs[$rw['subscr_id']]['payment_time'] > $timelimit_grace) {
                    $monthly_subscription_usd += $active_subs[$rw['subscr_id']]['payment_money_real'];
                    $active_subs[$rw['subscr_id']]['status'] = "Active";
                } else {
                    $active_subs[$rw['subscr_id']]['status'] = "Cancelled";
                }
            }


            // 3) Quota from affiliation
            $rs = mysql__("select sum(cnt_sites_ok) as csites from users where referred_by='$user_id'");
            while ($rw = mysql_fetch_array($rs)) {
                if ($rw['csites'] > 0)
                    $this_user_quota_sites_aff = $rw['csites'];
            }


            $sql = mysql__("SELECT count(*) as o FROM `payments` WHERE cr_time>DATE_sub(now() ,INTERVAL 30 DAY)  AND money>149 and quota=2 and user_id='$user_id' and 
            (payment_status='completed' OR payment_status='approved' OR payment_status='pending' ) ORDER BY `payments`.`cr_time` DESC ");


            $array_rsul = mysql_fetch_array($sql);
            $this_user_quota = 0;
            if ($array_rsul['o'] > 0) {
                $this_user_quota += 2;
                $monthly_subscription_usd;
                //$monthly_subscription_usd+=150;
                $sql = mysql__("SELECT ADDDATE(cr_time, INTERVAL 31 DAY) as d FROM `payments` WHERE cr_time>DATE_sub(now() ,INTERVAL 30 DAY)  AND money>149 and quota=2 and user_id='$user_id' and 
            (payment_status='completed' OR payment_status='approved' OR payment_status='pending'  ) ");
                $array_rsul = mysql_fetch_array($sql);
                $date_up = $array_rsul['d'];
                $sql = mysql__("UPDATE `users` SET `upgrade_time` = '$date_up' , upgrade=1 WHERE `users`.`id` ='$user_id';");
            }
            // FINAL
            $this_user_quota += $this_user_quota_sites + $this_user_quota_purchased + $this_user_quota_sites_aff + $this_user_quota_admin + $this_user_quota_use_once;
            mysql__("update users set   quota_sites = '$this_user_quota_sites',
            quota_purchased = '$this_user_quota_purchased',
            quota_sites_affiliate = '$this_user_quota_sites_aff',
            quota='$this_user_quota',
            monthly_subscription_usd='$monthly_subscription_usd',
            upd_time='" . $GVars['timenow'] . "',
            cnt_sites_ok='$this_user_n_sites_added'
            where id='$user_id'");


            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['user_quota'] = $this_user_quota;
                $_SESSION['user_quota_sites'] = $this_user_quota_sites;
                $_SESSION['user_quota_purchased'] = $this_user_quota_purchased;
                $_SESSION['user_quota_sites_aff'] = $this_user_quota_sites_aff;
                $_SESSION['user_quota_admin'] = $this_user_quota_admin;
                $_SESSION['n_quota_used_today'] = $n_quota_used_today;
                $_SESSION['user_n_sites_added'] = $this_user_n_sites_added;
                $_SESSION['active_subs'] = $active_subs;
            }
        }

        function mail_sendgrid($subject, $content, $to, $from = "dontreply@linkauthority.com", $from_name = "Link Authority", $category = "not_defined")
        {
		
			
			
            if ($_SERVER['DOCUMENT_ROOT'] == "")
                $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
				
            require_once $_SERVER['DOCUMENT_ROOT'] . '/MailModel.php';
            $MailModel = new MailModel(0);
            $MailModel->set_to($to);  
            $MailModel->set_from($from);
            $MailModel->set_from_name($from_name);
            $MailModel->set_subject($subject);
            $MailModel->set_body($content);   
            $MailModel->buildIt();
            return $MailModel->send();


//    $url = 'https://api.sendgrid.com/';
//    $user = 'Ishanguptaa';
//    $pass = 'Cognoscenti2195@';
//
//    $json_string = array(
//        'to' => array(
//            $to
//        ),
//        'category' => $category
//    );
//
//    $params = array(
//        'api_user' => $user,
//        'api_key' => $pass,
//        'x-smtpapi' => json_encode($json_string),
//        'to' => $to,
//        'subject' => $subject,
//        'html' => "<html><head>
//                <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /></head><body>" . $content . "</body>"
//        . "</html>",
//        'fromname' => 'Link Authority',
//        'from' => $from
//    );
//    $request = $url . 'api/mail.send.json';
//// Generate curl request
//    $session = curl_init($request);
//// Tell curl to use HTTP POST
//    curl_setopt($session, CURLOPT_POST, true);
//// Tell curl that this is the body of the POST
//    curl_setopt($session, CURLOPT_POSTFIELDS, $params);
//// Tell curl not to return headers, but do return the response
//    curl_setopt($session, CURLOPT_HEADER, false);
//    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
//
//// obtain response
//    $response = json_decode(curl_exec($session), 1);
//    curl_close($session);
//
//    if ($response['message'] == "success") {
//        return true;
//    } else {
//        return false;
//    }
        }

        function zmail($to, $from, $subject, $text, $simple_txt = "")
        {
            global $mail_headers;
            global $GVars;


//        $mail_headers .= "From: $from" . "\r\n";
            $text = "<html><head><title>" . $subject . "</title></head><body>" . $text . "</body></html>";
            //$text =  $text; 
            //$mail_request = mail_sendgrid($subject, $text, $to);
            $mail_request = mail_sendgrid_1($subject, $text, $to); 
            

//        $mail_request = @mail($to, $subject, $text, $mail_headers);
            //print_r($mail_request); echo "x"; die();

            if ($mail_request) {

            } else {
                //mysql_die_action("LinkAuthority MAIL SMTP Error! \n To: $to / Subject: $subject / Text: $text");
            }
        }
		
		function mail_sendgrid_1($subject, $text, $to) 
		{
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			 if ($_SERVER['DOCUMENT_ROOT'] == "")
                $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__); 
				
            require_once $_SERVER['DOCUMENT_ROOT'] . '/php-mailjet-v3-simple.class.php';
			
			$mj = new Mailjet();
			$params = array(
				"method" => "POST",
				"from" => "dontreply@linkauthority.com",
				"to" => $to,  
				"subject" => $subject,
				"html" => $text,  
			);
			
			$result = $mj->sendEmail($params);

			if ($mj->_response_code == 200) 
			   $resp=200;
			else
			   $resp=0;

			return $resp; 
			
		}  

		
        function send_mass_mail($admin_preview = 0)
        {
            global $GVars;
            $nEmailsPerMinute = 100;

            $rs = mysql__("select * from mass_email");
            while ($rw = mysql_fetch_array($rs)) {
                $esubject = stripslashes($rw['esubject']);
                $etext = nl2br(stripslashes($rw['etext']));
                $eon = $rw['eon'];
                $efrom = $rw['efrom']; // from time
                $esendto = $rw['esendto'];

                $esubject = str_replace('&quot;', '"', $esubject);
                $etext = str_replace('&quot;', '"', $etext);

                /*
              esendto:
              1 = everyone
              2 = free users
              3 = paying members
             */
            }
            // for removing extra br in newsletter
            //	$etext=str_replace("<br />"," ",$etext); 

            if ($admin_preview == 1) {
                $rs = mysql__("select * from users where admin=1");
                while ($rw = mysql_fetch_array($rs)) {
                    // added by Deep to add usernam to every newsletter
                    $etext_final = str_replace("%username%", preg_replace("/@(.*?)$/", " ", $rw['email']), $etext);

                    $this_mail_text = $etext_final . "<br><br><a href='http://www.linkauthority.com/unsubscribe_email.php?email=" . urlencode($rw['email']) . "&eh=" . urlencode(md5($GVars['secret_string'] . $rw['email'])) . "&m=2'>Click here to unsubscribe from LinkAuthority Newsletter</a>";
                    zmail($rw['email'], $GVars['site_public_email'], $esubject, $this_mail_text);
                }
                //die();
            } else {
                if ($eon == 1) {
                    $send_to_emails = '';
                    if ($esendto == 1)
                        $sql2 = " where get_mass_email=1 and mass_email_sent=0 order by id desc limit ";
                    if ($esendto == 2)
                        $sql2 = " where get_mass_email=1 and monthly_subscription_usd=0 and mass_email_sent=0 order by id desc limit ";
                    if ($esendto == 3)
                        $sql2 = " where get_mass_email=1 and monthly_subscription_usd>0 and mass_email_sent=0 order by id desc limit  ";

                    $rs = mysql__("select * from users " . $sql2 . " 0,$nEmailsPerMinute ");
                    while ($rw = mysql_fetch_array($rs)) {
                        //$send_to_emails .= $rw['email']." ";
                        // added by Deep to add usernam to every newsletter
                        $etext_final = str_replace("%username%", preg_replace("/@(.*?)$/", " ", $rw['email']), $etext);

                        $this_mail_text = $etext_final . "<br><br><a href='http://www.linkauthority.com/unsubscribe_email.php?email=" . urlencode($rw['email']) . "&eh=" . urlencode(md5($GVars['secret_string'] . $rw['email'])) . "&m=2'>Click here to unsubscribe from LinkAuthority Newsletter</a>";

                        zmail($rw['email'], $GVars['site_public_email'], $esubject, $this_mail_text);
                    }

                    //zmail($GVars['admin_email'], $GVars['site_public_email'], 'MassEmailControl', $send_to_emails);
                    $rs = mysql__("update users set mass_email_sent=1 " . $sql2 . " $nEmailsPerMinute");
                }
            }
        }

        function render_final_text($f_text, $f_keyword1, $f_keyword2, $f_keyword3, $f_link1, $f_link2, $f_link3, $with_link = true)
        {
            $text_stripped = strip_tags($f_text);
            $words = explode(" ", $text_stripped);
            $link1 = ($with_link) ? "<a href='$f_link1'>$f_keyword1</a>" : "$f_keyword1";
            $link2 = ($with_link) ? "<a href='$f_link2'>$f_keyword2</a>" : "$f_keyword2";
            $link3 = ($with_link) ? "$f_keyword3" : "$f_keyword3";
            $f_text = str_ireplace("%LINK1%", $link1, $f_text);

            if (sizeof($words) > 300) {
                $f_text = str_ireplace("%LINK2%", $link2, $f_text);
                $f_text = str_ireplace("%LINK3%", $link3, $f_text);
            } else {
                $f_text = str_ireplace("%LINK2%", "$f_keyword2", $f_text);
                $f_text = str_ireplace("%LINK3%", "$f_keyword3", $f_text);
            }

            return nl2br($f_text);
        }

        function posts_info_text()
        {
            if ($_SESSION['total_articles_in_publishing_queue'] > 0) { // if posts to publish > 0
                ?>
                <div class="alert alert-warning" style="font-size:14px;float: left;width: 100%;margin-top:20px;margin-bottom:0;">
				<div class="infobox" style="padding-top: 0;">   
                   
                    <div class="infobox_mid">
                        <?php
                        if ($_SESSION['user_quota'] > 0) {
                            ?> You currently have
                            <?= $_SESSION['total_articles_in_publishing_queue']; ?>  posts which are waiting to be published, with your daily quota of
                            <?= $_SESSION['user_quota']; ?>  these should all be published within
                            <?= ceil($_SESSION['total_articles_in_publishing_queue'] / $_SESSION['user_quota']); ?>  days.
                            <br>
                        <?php
                        } else {
                            ?>You currently have
                            <?= $_SESSION['total_articles_in_publishing_queue']; ?> posts which are waiting to be published, with your daily quota of 0.
                            <br>
                        <?php
                        }
                        ?>To increase this rate you will either need to
                        <a href=sites.php>add more sites</a>,
                        <a href=affiliation.php>refer users who add sites</a> or
                        <a href=purchase.php>purchase more quota</a>.
                    </div>
                    <div class="infobox_bottom">
                    </div>
                </div>
				</div>
            <?php
            }
        }

        function projects_info_text()
        {
            ?>
            <div class="infobox">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid"> Here is where you can view your projects - You can create projects for a website and then add an unlimited amount of posts per project.
                    <br> Once you have completed a project you can choose to delete it or archive it.
                </div>
                <div class="infobox_bottom">
                </div>
            </div>
        <?php
        }

        function site_info_text()
        {
            ?>
            <div class="red-info alert alert-warning" style="font-size:14px;margin-bottom:0;">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">Here is where you can add sites into the system.
                    There are two benefits of adding sites - Firstly you get to receive unique content on them and secondly you increase your daily submission quota.
                    <ul>
					<li><b>*</b> Please note we post content from all niches.</li> 
					<li><b>*</b> Submitted sites should have:PA>10 and DA>10</li>
					</ul>
				</div>
                <div class="infobox_bottom">
                </div>
            </div>
        <?php
        }

        function site_info_text2()
        {
            ?>
			<div class="red-info">
            <div class="infoboxgr">
                <div class="infoboxgr_top">
                </div>
                <div class="infoboxgr_mid">

                    <table cellpadding=10 cellspacing=0 border=0 bgcolor=#ffffff>
                        <strong>Site Statuses</strong>
                        <tr>
                            <td><strong>New Site</strong> - New site that has just been added to the system and is waiting for check. It usually happens ASAP.
                                <br><strong>Problematic Site</strong> - A problematic site can be a few different things and the below could appear in the problematic tab (That means the site you have added into the system has errors on it, we will continue to check this site for 10 days):
                                <br><strong>Can't login</strong> - This means we cannot login this is usually because of incorrect login information supplied
                                <br><strong>Can't publish</strong> - This means we cannot publish to the site for one of many reasons. There will be an icon next to a Can't Publish site <img src="http://www.linkauthority.com/images/status_1.png" alt="problematic site"/> which you can click on to see the reason. This will only be clickable if we have gone to publish to your site and it has sent us back an error response.
                                <br><strong>Dead Site</strong> - This means the site you have added has been down for over 10 days and we will no longer continue to check its status, you will need to fix the issues and edit the site.
                                <br><strong>Rejected</strong> - This means that the site has had a manual review and been rejected. You can see the reason for rejection by clicking on the <img src="http://www.linkauthority.com/images/status_1.png" alt="rejected site"/>
                                <br></td>
                        </tr>
                    </table>

                </div>
                <div class="infoboxgr_bottom">
                </div>
            </div>
			</div>

            <br>
        <?php
        }

        function posts_info_text2()
        {
            ?>
			<div class="red-info">
            <div class="infobox">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">

                    <table cellpadding=10 cellspacing=0 border=0 bgcolor=#ffffff>
                        <tr>
                            <td>Here is where you can view all of your submitted posts. Posts will show various stats and the following status:
                                <br><b>Draft</b> - This means your post is ready to go and just needs you to hit the publish button;
                                <br><b>Waiting for Approval</b> - This means you are not on the trust list and all of your articles will undergo a manual review until we deem you to be a good author;
                                <br><b>Approved</b> - This means the post is ready to be published by the system;
                                <br><b>Approved (No Quota)</b> - The post is ready to be published, but you have no quota;
                                <br><b>Published</b> - This means the post has successfully been published;
                                <br><b>Problematic</b> - This means there are various issues with the post you submitted - You can click on the post to view the problems;
                                <br><b>Rejected</b> - This means the article has been reviewed by admin and rejected with a reason - The post will need to be fixed and resubmitted.
                                <br></td>
                        </tr>
                    </table>

                </div>
                <div class="infobox_bottom">
                </div>
            </div>
			</div>

            <br>
        <?php
        }

        function purchase_quota_info_text()
        {
            ?>
			
            <div class="">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">This is where you can top up your daily quota limits.
                    <br>Just add the amount of daily quota you need and you will then be taken off to Paypal or 2Checkout to make the payment and subscription.
                    <br> If you ever need to cancel this you can unsubscribe from your Paypal or 2Checkout account.
                </div>
                <div class="infobox_bottom">
                </div>
            </div>
			
        <?php
        }

        // for thanking message
        function purchase_quota_info_text_request()
        {
            ?>
            <div class="infobox">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid"><strong>Thank you for Your Participation!.</strong>
                    <br>Quota will be credited to your account within 24hrs
                    <br>If you have additional questions or issues, please don't hesitate to request help online on our <a href='http://linkauthority.zendesk.com/' target='_blank'>support</a></div>
                <div class="infobox_bottom">
                </div>
            </div>
        <?php
        }

        function purchase_upgrade_info_text()
        {
            ?>
            <div class="red-info">
            <div class="infobox" style="padding: 0;width: 100%;">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">You can upgrade your LA account to premium status by using the checkout button below. There are three main benefits of having a premium LA account.
                    <br> <br>- <strong>Access to an all new network!</strong>
                    <br>
                    Access to our premium network, as well as your articles getting syndicated to the normal network, premium accounts will have their content syndicated to our higher level private network, which has less posts, higher average PR, good mozrank and is growing at a steady monthly pace.
                    <br> <!--<br>  - <strong>2 relevant posts, Authorship Outreach</strong> -->
                    <!--<br> -->                                                                                                                                 <!-- We will contact site owners manually and write and place content on third party sites, these sites are NOT part of any network, have real visitors and readers and carry a very high level of weight and a report will be provided. (After upgrading please submit a ticket detailing require_oncements such as your URL, Anchor Text and any other information) -->

                    <!--<br> -->   <br> - <strong>2 Bonus Daily Quota</strong>
                    <br> For upgrading your account to premium you will get an extra 2 daily quota as a bonus.
                </div>
                <div class="infobox_bottom">
                </div>
            </div>
            </div>
            </div>
        <?php
        }

        function purchase_upgrade_info_text_done()
        {
            ?>
            <div class="red-info">
            <div class="infobox">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">
                    Your account is upgraded until the <?php echo $_SESSION['upgrade_time'] ?> where it will auto renew.
                    <br/>
                    <br/>Your content will now be syndicated to both the normal and premium network.
                    <br/>
                    <br/>For your authorship outreach posts please kindly join <a href="http://guestblogged.com">GuestBlogged.com</a> and then submit a ticket to the outreach department informing them of your GuestBlogged username, so we can add your credits for you to order the posts.
                </div>
                <div class="infobox_bottom">
                </div>
            </div>
            </div>
        <?php
        }

        function purchase_scrill_quota_info_text()
        {
            ?>
            <div class="infobox">
                <div class="infobox_top">
                </div>
                <div class="infobox_mid">This is where you can top up your daily quota limits.
                    <br>Just add the amount of daily quota you need and you will then be taken off to Paypal or 2Checkout to make the payment and subscription.
                    <br> If you ever need to cancel this you can unsubscribe from your Paypal or 2Checkout account.
                </div>
                <div class="infobox_bottom">
                </div>
            </div>
        <?php
        }

        function edit_post_info_text()
        {
            ?>
                       
                <div class="projects-num projects-num-alert"><strong>IMPORTANT TIP :</strong> PREVIEW THE POSTS BEFORE PUBLISHING THEM TO AVOID THE REJECTIONS BASED ON FORMATTING PROBLEMS
                </div>
                <!--infobox_mid -->                
           
        <?php
        }

        function get_user_info_from_id($id = 0, $id_hash = '', $like = false)
        {
            $ret_arr = array();
            $sql = '';

            if ($id != 0) {
                $sql = "select * from users where id='$id'";
            }
            if ($id_hash != '') {
                if ($like) {
                    $sql = "select * from users where id_hash like '%$id_hash%'";
                } else {
                    $sql = "select * from users where id_hash='$id_hash'";
                }

            }


            if ($sql != '') {
                $rs = mysql__($sql);
                while ($rw = mysql_fetch_array($rs)) {
                    $ret_arr['id'] = $rw['id'];
                    $ret_arr['id_hash'] = $rw['id_hash'];
                    $ret_arr['username'] = $rw['username'];
                    $ret_arr['email'] = $rw['email'];
                    $ret_arr['email_paypal'] = $rw['email_paypal'];
                    $ret_arr['rejected_n_times'] = $rw['rejected_n_times'];
                    $ret_arr['cnt_artc_published'] = $rw['cnt_artc_published'];
                    $ret_arr['trusted'] = $rw['trusted'];
                }
            }
            return $ret_arr;
        }

        function show_article_errors($articleinfo, $article_errors)
        {
            global $article_statuses;

            //echo "<pre>"; print_r($articleinfo); echo "<br>"; print_r($article_errors);

            if (($article_errors['errors'] > 0) or ($articleinfo['admin_comment'] != '')) {
                if ($article_errors['errors'] > 0) {
                    echo "<div class='alert alert-danger' style='width: 890px;margin: auto;margin-bottom: 20px;margin-top:20px;'>" . $article_errors['error_str'] . "</div>";
                } else {
                    if (($articleinfo['status'] == 4) or ($articleinfo['status'] == 6)) {
                        echo "<div class='alert alert-danger' style='width: 890px;margin: auto;margin-bottom: 20px;margin-top:20px;'>Admin comment: " . stripslashes($articleinfo['admin_comment']) . "</div>";
                    } else {
                        if ($articleinfo['status'] >= 7) {

                        } else {
                            ?>
                            <div class="red-info">
							<div class="infobox">
                                <div class="infobox_top">
                                </div>
                                <div class="infobox_mid">
                                    <?php
                                    echo $article_statuses[$articleinfo['status']];
                                    ?>
                                </div>
                                <!--infobox_mid -->
                                <div class="infobox_bottom">
                                </div>
                            </div>
							</div>
                        <?php
                        }
                    }
                }
            }
            ?>
        <?php
        }

        function has_bad_words($mystring)
        {
            $n_bad_words = 0;
            $bad_words_string = '';
            $bad_words = explode(",", $_SESSION['global_vars']['forbidden_words']);
            foreach ($bad_words as &$value) {
                $pos = strpos($mystring, trim(strtolower($value)));
                if ($pos == '') {

                } else {
                    $n_bad_words++;
                    $bad_words_string .= $value . ", ";
                }
            }
            $ret_arr = array();
            $ret_arr['n_bad_words'] = $n_bad_words;
            $ret_arr['bad_words'] = substr($bad_words_string, 0, -2);

            return $ret_arr;
        }

        function has_bad_sites($mystring)
        {
            $n_bad_sites = 0;
            $bad_sites_string = '';
            $bad_sites = explode(",", $_SESSION['global_vars']['forbidden_sites']);
            foreach ($bad_sites as &$value) {
                $value = trim(strtolower($value));
                if ($value != '')
                    $pos = strpos($mystring, $value);

                if ($pos == '') {

                } else {
                    $n_bad_sites++;
                    $bad_sites_string .= $value . ", ";
                }
            }
            $ret_arr = array();
            $ret_arr['n_bad_sites'] = $n_bad_sites;
            $ret_arr['bad_sites'] = substr($bad_sites_string, 0, -2);

            return $ret_arr;
        }

        function get_site_info_for_publishing($user_id, $cron_id, $article_id)
        {
		
			/* $rs = mysql__("select * from articles where id='$article_id'");
            while ($ramit_rw = mysql_fetch_array($rs)) 
			{
				$art_cat=$ramit_rw['publish_category']; 
			}   */
			
            global $GVars;

            $user_dont_publish_on_my_sites = 0;
            $rs = mysql__("select * from users where id='$user_id'");
            while ($rw = mysql_fetch_array($rs)) {
                $user_dont_publish_on_my_sites = $rw['dont_publish_on_my_sites'];
                $now = time(); // or your date as well

                $your_date = strtotime($rw['upgrade_time']);
                $datediff = -$now + $your_date + 3 * 60 * 60;
                if (floor($datediff / (60 * 60 * 24)) > 0) {
                    $upgrade = 1;
                }
            }


            $resulttr = mysql__("SELECT distinct(site_id)  FROM `log_publish` WHERE `article_id` IN (SELECT id  FROM `articles_published` WHERE `user_id` = $user_id) ORDER BY `cr_time` DESC LIMIT 0,100");
            $rows = array();
            while ($row = mysql_fetch_array($resulttr)) {
                $rows[] = $row['site_id'];
            }
            $condition_lim = "";
            if (count($rows) > 0) {
                $condition_lim = " and id NOT IN (" . implode(",", $rows) . ") ";
            }
 

            //here to add the condition to show if we use the premium website or not.
            $con = "and lvl=0 ";

            if ($upgrade == 1) { 
                $con = "";
            } 

            if ($user_dont_publish_on_my_sites == 0)
                $sql = "select * from sites where (status=5  $condition_lim   $con      and n_articles_can_publish_today>0 and user_blocked=0 and site_approved=1) order by  last_publish_time asc limit  0,1";  
            else
                $sql = "select * from sites where (status=5   $condition_lim  $con        and n_articles_can_publish_today>0 and user_blocked=0 and user_id<>$user_id and site_approved=1) order by  last_publish_time asc limit  0,1"; 

            $ret_arr = array();
            $rs = mysql__($sql);
            while ($rw = mysql_fetch_array($rs)) {
                $ret_arr['site_id'] = $rw['id'];
                $ret_arr['site_url'] = $rw['url'];
                $ret_arr['site_login'] = $rw['login'];
                $ret_arr['site_password'] = $rw['password'];
                $ret_arr['site_pagerank'] = $rw['pagerank'];
                $ret_arr['site_user_id'] = $rw['user_id'];
            }

            zlog__("Get site for publishing (SQL: $sql), result: " . print_r($ret_arr, TRUE));

            if ($ret_arr['site_id'] > 0) {
                mysql__("update sites set last_publish_time='" . $GVars['timenow'] . "' where id='" . $ret_arr['site_id'] . "'");
            } else {
                if (date('i') == 1) { // send info once per hour
                    zmail($GVars['admin_email'], 'SITE PUBLISH LIMIT', 'No more sites for publihing today, system needs more sites...');
                }
                die();
            }

            $sql2 = "insert into log_publish values('','" . date("Y-m-d H:i:s") . "', '$cron_id', '$article_id', '" . $ret_arr['site_id'] . "','0','','Set time to " . $GVars['timenow'] . "')";
            $res2 = mysql_query($sql2);
            if (!$res2)
                mysql_die_action($sql2 . " \n" . mysql_error());

            return $ret_arr; 
        }

        function correct_xmlrpc_url($url)
        {
            if ($url[strlen($url) - 1] == '/')
                $url .= 'xmlrpc.php';
            else
                $url .= '/xmlrpc.php';

            return $url;
        }

        /*
                                                                                                                                                                                                          function update_project_article_count($project_id)
                                                                                                                                                                                                          {
                                                                                                                                                                                                          // all articles, except deleted
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles where project_id=$project_id and status>0");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_all_articles = $rw['cid'];
                                                                                                                                                                                                          }
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles_published where project_id=$project_id and status>0");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_all_articles += $rw['cid'];
                                                                                                                                                                                                          }

                                                                                                                                                                                                          // published articles
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles where project_id=$project_id and status=9");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_published_articles = $rw['cid'];
                                                                                                                                                                                                          }
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles_published where project_id=$project_id and status=9");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_published_articles += $rw['cid'];
                                                                                                                                                                                                          }

                                                                                                                                                                                                          // in publishing queue articles
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles where project_id=$project_id and (status=7 or status=8 or status=9) ");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_in_queue_articles = $rw['cid'];
                                                                                                                                                                                                          }

                                                                                                                                                                                                          // problematic articles
                                                                                                                                                                                                          $rs = mysql__("select count(id) as cid from articles where project_id=$project_id and (status=2 or status=3 or status=6) ");
                                                                                                                                                                                                          while($rw = mysql_fetch_array($rs))
                                                                                                                                                                                                          {
                                                                                                                                                                                                          $this_problematic_articles = $rw['cid'];
                                                                                                                                                                                                          }

                                                                                                                                                                                                          $sql = "update projects set n_articles='$this_all_articles',
                                                                                                                                                                                                          n_articles_published='$this_published_articles',
                                                                                                                                                                                                          n_articles_in_publishing_queue='$this_in_queue_articles',
                                                                                                                                                                                                          n_articles_problematic='$this_problematic_articles'
                                                                                                                                                                                                          where id=$project_id";
                                                                                                                                                                                                          //echo $sql;
                                                                                                                                                                                                          $rs = mysql__($sql);


                                                                                                                                                                                                          $ret_arr = array();
                                                                                                                                                                                                          $ret_arr['all_a'] = $this_all_articles;
                                                                                                                                                                                                          $ret_arr['publ_a'] = $this_published_articles;
                                                                                                                                                                                                          $ret_arr['queue_a'] = $this_in_queue_articles;

                                                                                                                                                                                                          return $ret_arr;
                                                                                                                                                                                                          }
                                                                                                                                                                                                         */

        function check_quota_vs_price($quota, $price)
        {
            $rs = mysql__("select * from global_vars");
            while ($rw = mysql_fetch_array($rs)) {
                $dsc_q[1] = $rw['dsc_q1']; // 20
                $dsc_q[2] = $rw['dsc_q2']; // 40
                $dsc_q[3] = $rw['dsc_q3']; // 60
                $dsc_q[4] = $rw['dsc_q4']; // 80
                $dsc_q[5] = $rw['dsc_q5']; // 100

                $dsc_p[1] = $rw['dsc_p1']; // 5
                $dsc_p[2] = $rw['dsc_p2']; // 10
                $dsc_p[3] = $rw['dsc_p3']; // 20
                $dsc_p[4] = $rw['dsc_p4']; // 30
                $dsc_p[5] = $rw['dsc_p5']; // 35

                $singleprice = $rw['single_quota_cost'];
            }

            //echo "singleprice:$singleprice<br>";

            $normalprice = $singleprice * $quota;
            $discountpercent = $dsc_p[5];

            //echo "normalprice:$normalprice<br>";

            if ($normalprice < $dsc_q[5])
                $discountpercent = $dsc_p[5];
            if ($normalprice < $dsc_q[4])
                $discountpercent = $dsc_p[4];
            if ($normalprice < $dsc_q[3])
                $discountpercent = $dsc_p[3];
            if ($normalprice < $dsc_q[2])
                $discountpercent = $dsc_p[2];
            if ($normalprice < $dsc_q[1])
                $discountpercent = $dsc_p[1];

            $discount = ($normalprice / 100) * $discountpercent;
            $discount = round($discount, 2);
            $finalprice = $normalprice - $discount;

            //echo "finalprice:$finalprice price:$price <br>";

            if (($finalprice == $price) or ($finalprice < $price)) { // if pays more, its not bad ;)
                return $quota;
            } else {
                $min_quota = floor($price / $singleprice);
                //echo $min_quota."<br>";

                $real_quota = 0;
                for ($i = $min_quota; $i < ($min_quota * 2); $i++) {
                    $normalprice = $singleprice * $i;
                    $discountpercent = $dsc_p[5];

                    if ($normalprice < $dsc_q[5])
                        $discountpercent = $dsc_p[5];
                    if ($normalprice < $dsc_q[4])
                        $discountpercent = $dsc_p[4];
                    if ($normalprice < $dsc_q[3])
                        $discountpercent = $dsc_p[3];
                    if ($normalprice < $dsc_q[2])
                        $discountpercent = $dsc_p[2];
                    if ($normalprice < $dsc_q[1])
                        $discountpercent = $dsc_p[1];

                    $discount = ($normalprice / 100) * $discountpercent;
                    $discount = round($discount, 2);
                    $finalprice = $normalprice - $discount;

                    if ($finalprice < $price) {
                        $real_quota = $i;
                    } else {
                        return $real_quota;
                    }
                }

                return $real_quota;
            }
        }

        function mk_time_add_seconds($timefrom, $secplus)
        {
            $timestamp = time($timefrom);
            $newtime = $timestamp + $secplus;
            $newtime = date("Y-m-d H:i:s", $newtime);

            //echo "[ timefrom:$timefrom/ timestamp:$timestamp / newtime:$newtime ]";

            return $newtime;
        }

        function add_seconds($date1, $sec_plus, $round_time_to_minute = 0)
        {
            // jei round time, tai jokia kelione netrunka maziau nei 1 minute
            if ($round_time_to_minute == 1) {
                //echoz($sec_plus);
                if ($sec_plus < 60)
                    $sec_plus = 61;
            }
            $isec1 = substr($date1, 17, 2);
            $imin1 = substr($date1, 14, 2);
            $ihour1 = substr($date1, 11, 2);
            $iday1 = substr($date1, 8, 2);
            $imonth1 = substr($date1, 5, 2);
            $iyear1 = substr($date1, 0, 4);
            $new_date = date("Y-m-d H:i:s", mktime($ihour1, $imin1, ($isec1 + $sec_plus), $imonth1, $iday1, $iyear1));
            //2010-12-12 12:12:12
            if ($round_time_to_minute == 1) {
                $new_date = substr($new_date, 0, -2);
                $new_date .= "00";
            }
            return $new_date;
        }

        function close_period($period_id, $p_start, $p_end, $close_type)
        {
            if ($close_type == 'transient') {
                mysql__("truncate table affiliate_log_transient");
            }

            $vat_percent = 0;
            $rs = mysql__("select * from global_vars");
            while ($rw = mysql_fetch_array($rs)) {
                $vat_percent = $rw['vat_percent'];
            }

            $sql = "select payments.money as pmoney,
                                                                                                                                                                                                        users.email as uemail,
                                                                                                                                                                                                        users.id as uid,
                                                                                                                                                                                                        users.country_id,
                                                                                                                                                                                                        users.vat_nr,
                                                                                                                                                                                                        users.parent_percent as uppercent,
                                                                                                                                                                                                        users.referred_by as ref_by
                                                                                                                                                                                                        from payments, users
                                                                                                                                                                                                        where payments.user_id=users.id
                                                                                                                                                                                                        and users.referred_by>0
                                                                                                                                                                                                        and payments.txn_type = 'subscr_payment'
                                                                                                                                                                                                        and (payments.cr_time > '$p_start' and payments.cr_time < '$p_end') ";
            $rs = mysql__($sql);
            while ($rw = mysql_fetch_array($rs)) {
                if ($close_type == 'monthly')
                    $table_to_write = 'affiliate_log';
                if ($close_type == 'transient')
                    $table_to_write = 'affiliate_log_transient';

                if ($rw['pmoney'] > 0) {
                    $this_money = $rw['pmoney'];
                    $deduct_vat = test_vat_charge($rw['country_id'], $rw['vat_nr']);
                    if ($deduct_vat == 1) {
                        $one_money_percent = (100 + $vat_percent);
                        $one_money_percent = ($this_money / $one_money_percent);
                        $this_money = 100 * $one_money_percent;
                    }
                    $usd_aff = ($this_money / 100) * $rw['uppercent'];
                    $usd_aff = round($usd_aff, 2);
                    $this_money = round($this_money, 2);
                    mysql__("insert into $table_to_write values ('','$period_id',
                                                                                                                                                                                                        '" . $rw['ref_by'] . "',
                                                                                                                                                                                                        '" . $rw['uid'] . "',
                                                                                                                                                                                                        '" . $rw['uemail'] . "',
                                                                                                                                                                                                        '" . $this_money . "',
                                                                                                                                                                                                        '$usd_aff')");
                }
            }
        }

        function hide_email($email)
        {
            $new_email = '';

            for ($i = 0; $i < strlen($email); $i++)
                $new_email .= '*';
            if (strlen($email) < 15) {
                $new_email[0] = $email[0];
                $new_email[strlen($email) - 1] = $email[strlen($email) - 1];
            } else {
                $new_email[0] = $email[0];
                $new_email[1] = $email[1];
                $new_email[2] = $email[2];
                $new_email[strlen($email) - 3] = $email[strlen($email) - 3];
                $new_email[strlen($email) - 2] = $email[strlen($email) - 2];
                $new_email[strlen($email) - 1] = $email[strlen($email) - 1];
            }

            return $new_email;
        }

        /**
         *
         * @global type $GVars
         * @param type $f_url
         * @param type $f_login
         * @param type $f_password
         * @param type $prenium
         * @return int
         */
       function insert_new_site($f_url, $f_login, $f_password,$f_cat, $prenium = 0)
        {
            global $GVars;

            $ins_report = array();
            $ins_report['try_login_after_reg'] = 0;

            $badsites = 0;
            $try_login_after_reg = 0;
            $badsites = has_bad_sites($f_url);

            $f_url = fixBlogUrl($f_url);
            if (($f_url != '') and ($f_login != '') and ($f_password != '') and ($badsites['n_bad_sites'] == 0)) {
                $same_site_found = 0;
                $rs = mysql__("select * from sites where url like '%$f_url%'");
                while ($rw = mysql_fetch_array($rs)) {
                    $same_site_found = 1;
                }

                $url_no_www = str_replace('www.', '', $f_url);
                $rs = mysql__("select * from sites where url like '%$url_no_www%'");
                while ($rw = mysql_fetch_array($rs)) {
                    $same_site_found = 1;
                }

                $url_with_www = str_replace('://', '://www.', $f_url);
                $rs = mysql__("select * from sites where url like '%$url_with_www%'");
                while ($rw = mysql_fetch_array($rs)) {
                    $same_site_found = 1;
                }

                /*
                                                                                                                                                                                                                  if ($same_site_found == 0)
                                                                                                                                                                                                                  {
                                                                                                                                                                                                                  $hostname = parse_url($f_url);
                                                                                                                                                                                                                  $hostname = $hostname['host'];
                                                                                                                                                                                                                  }
                                                                                                                                                                                                                 */
                if ($same_site_found == 0) {
                    $site_approved = $_SESSION['user_trusted']; 
					
					
                     mysql__("insert into sites values ('','','" . $_SESSION['user_id'] . "','$f_url','','$f_login', '$f_password','1', '0','" . $GVars['timenow'] . "','2000-01-01 00:00:00','" . $GVars['timenow'] . "','" . $GVars['timenow'] . "','0','" . $_SESSION['global_vars']['post_limit_per_website'] . "','0','0','$prenium','',$prenium,'','','$f_cat')");   
					
					
					
                    log_action($_SESSION['user_id'], "New Site " . $f_url); 
                    
					$id = 0;
                    $rs = mysql__("select * from sites where url='$f_url'");
                    while ($rw = mysql_fetch_array($rs)) {
                        $id = $rw['id'];
                        $id_hash = md5($GVars['secret_string'] . $id);
                    }
                    if ($id > 0) {
                        mysql__("update sites set id_hash='$id_hash' where id='$id'");
                        $ins_report['id'] = $id;
                        $try_login_after_reg = 1;

                        $ins_report['info_message_error'] = 1;
                        $ins_report['info_message'] = "Site $f_url saved.";
                    }
                } else {
                    $ins_report['info_message_error'] = 1;
                    $ins_report['info_message'] = "Sorry, site $f_url already exists...";
                }
            } else {
                $ins_report['info_message_error'] = 1;
                $ins_report['info_message'] = '';
                if ($f_cat == 0)
                    $ins_report['info_message'] .= '&middot; Please select category<br>';  
				if ($f_url == '')
                    $ins_report['info_message'] .= '&middot; You have to enter URL.<br>';
                if ($f_login == '')
                    $ins_report['info_message'] .= '&middot; You have to enter site login.<br>';
                if ($f_password == '')
                    $ins_report['info_message'] .= '&middot; You have to enter site password.<br>';
                if ($badsites['n_bad_sites'] > 0)
                    $ins_report['info_message'] .= "&middot; URL contains bad address (" . $badsites['bad_sites'] . ").<br>";
            }
            /*
                                                                                                                                                                                                              if ($try_login_after_reg == 1)
                                                                                                                                                                                                              {
                                                                                                                                                                                                              $ins_report['login_rett'] = try_to_login_to_site($f_url, $f_login, $f_password, 0, $id);
                                                                                                                                                                                                              }
                                                                                                                                                                                                             */
            $ins_report['try_login_after_reg'] = $try_login_after_reg;

            //print_r($ins_report); die();
            return $ins_report;
        }

        function try_to_login_to_site($f_url, $f_login, $f_password, $f_pagerank, $f_id)
        {
            global $GVars, $UserAgentArray;
            global $static_article_categories;

            $f_url = fixBlogUrl($f_url);

            $login_rett = array();

            //log_action($_SESSION['user_id'], "try_to_login_to_site site $f_url");

            $new_site_status = 1;


            //Test
            $f_urlxmlrpc = correct_xmlrpc_url($f_url);
            $cats = array();
            $params = array(0, $f_login, $f_password);
            $request = xmlrpc_encode_request('metaWeblog.getCategories', $params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_URL, $f_urlxmlrpc);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            $result = curl_exec($ch);
            curl_close($ch);
            $proxity = (!$result) ? true : false;

            if (wpLoginCheck($f_url, $f_login, $f_password)) {
                if ($f_pagerank == 0) {
                    $parsed_url = parse_url($f_url);
                    //-------------------------------------------------
                    $pr_page_ranker = new GooglePageRankChecker();
                    $rank = $pr_page_ranker->getRank($parsed_url['host']);
                    //--------------------------------------------------
                    // just for testing 
                    //  $rank = GooglePageRankChecker::getRank($parsed_url['host']);
                    //$rank = 3; // comment this line after debug
                } else
                    $rank = $f_pagerank;

                $sql_add = '';

                if ($rank >= 1) {
                    $new_site_status = 5;

                    $login_rett['info_message_error'] = 0;
                    $login_rett['info_message'] = "Website $f_url is OK!";
                } else {
                    $new_site_status = 2;
                    $login_rett['info_message_error'] = 1;
                    $login_rett['info_message'] = "Website $f_url has low pagerank.";
                }

                $sql_add = " status='$new_site_status', pagerank='$rank', last_pr_check_time='" . $GVars['timenow'] . "', last_publish_time='2000-01-01 00:00:00', ";
                mysql__("update sites set $sql_add  url='$f_url'  where id='$f_id'");


                if ($_SESSION['user_id'] > 0)
                    count_user_quota();
            } else {
                $login_rett['info_message_error'] = 1;
                $login_rett['info_message'] = "Can't login to website $f_url";

                mysql__("update sites set status=1, status2=1 where id='$f_id'");
            }


            if ($new_site_status == 5) {
                $f_urlxmlrpc = correct_xmlrpc_url($f_url);

                //Get categories
                $cats = array();
                $params = array(0, $f_login, $f_password);
                $request = xmlrpc_encode_request('metaWeblog.getCategories', $params);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_URL, $f_urlxmlrpc);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if ($proxity) {
                    $ch = setupProxy($ch);
                }
                $result = curl_exec($ch);
                curl_close($ch);
                preg_match_all('/\<member\>\<name\>categoryName\<\/name\>\<value\>\<string\>(\w+)\<\/string\>\<\/value\>\<\/member\>/', $result, $matches, PREG_SET_ORDER);
                for ($i = 0; $i < count($matches); $i++) {
                    array_push($cats, $matches[$i][1]);
                }

                $test_cat_name = '';
                $i = 0;
                while ($i < sizeof($static_article_categories)) {
                    $test_cat_name = $static_article_categories[$i];
                    for ($i2 = 0; $i2 < sizeof($cats); $i2++) {
                        if ($cats[$i2] == $test_cat_name)
                            $test_cat_name = '';
                    }
                    $i++;
                    if ($test_cat_name != '')
                        $i = 100;
                }

                if ($test_cat_name == '') {
                    $test_cat_name = "testCat" . mt_rand(11111, 99999);
                }

                // try to create test category
                $n_cr_cat_errors = 0;
                $cat = $test_cat_name;

                zlog__("Function try_to_login_to_site tried to insert test category [ $cat ] to website [ $f_urlxmlrpc ] \n\n");

                $newcat = array('name' => $cat, 'slug' => strtolower($cat), 'description' => $cat);

                $params = array(0, $f_login, $f_password, $newcat);
                $request = xmlrpc_encode_request('wp.newCategory', $params);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_URL, $f_urlxmlrpc);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if ($proxity) {
                    $ch = setupProxy($ch);
                }
                $result = curl_exec($ch);
                curl_close($ch);

                //echo strlen($result); echo " $result "; die();

                if (strpos($result, 'do not have the right to') > 0)
                    $n_cr_cat_errors++;

                if ($n_cr_cat_errors == 0) {
                    // delete test category
                    $params = array(0, $f_login, $f_password, $result);
                    $request = xmlrpc_encode_request('wp.deleteCategory', $params);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                    curl_setopt($ch, CURLOPT_URL, $f_urlxmlrpc);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    if ($proxity) {
                        $ch = setupProxy($ch);
                    }
                    $result = curl_exec($ch);
                    curl_close($ch);
                } else {
                    $login_rett['info_message_error'] = 1;
                    $login_rett['info_message'] = "Account <b>$f_login</b> to the site <b>$f_url</b> has no editor or admin rights for publishing.";
                    mysql__("update sites set status=1, status2=3 where id='$f_id'");
                }
            }


            //mysql__("delete from sites where id>0"); // for test purposes
            //print_r($login_rett);
            //echo "<br> $f_url, $f_login, $f_password, $f_pagerank, $f_id";
            //die();
            return $login_rett;
        }

        function draw_admin_menu()
        {
            global $a;

            if ($_SESSION['user_admin'] == 1) {
                $amarr[1]['l'] = 'admin.php?a=0';
                $amarr[1]['a'] = '0';
                $amarr[1]['i'] = 'dash';
                $amarr[2]['l'] = 'admin.php?a=1';
                $amarr[2]['a'] = '1';
                $amarr[2]['i'] = 'posts';
                $amarr[3]['l'] = 'admin.php?a=6';
                $amarr[3]['a'] = '6';
                $amarr[3]['i'] = 'sites';
                $amarr[4]['l'] = 'admin.php?a=3';
                $amarr[4]['a'] = '3';
                $amarr[4]['i'] = 'users';
                $amarr[5]['l'] = 'admin.php?a=4';
                $amarr[5]['a'] = '4';
                $amarr[5]['i'] = 'members';
                $amarr[6]['l'] = 'admin.php?a=5';
                $amarr[6]['a'] = '5';
                $amarr[6]['i'] = 'settings';
                $amarr[7]['l'] = 'admin.php?a=7';
                $amarr[7]['a'] = '7';
                $amarr[7]['i'] = 'mail';
                $amarr[8]['l'] = 'admin.php?a=8';
                $amarr[8]['a'] = '8';
                $amarr[8]['i'] = 'paypal';
                $amarr[9]['l'] = 'admin.php?a=9';
                $amarr[9]['a'] = '9';
                $amarr[9]['i'] = 'affiliate';
                $amarr[10]['l'] = 'admin.php?a=100';
                $amarr[10]['a'] = '100';
                $amarr[10]['i'] = 'vat';
                $amarr[11]['l'] = 'admin.php?a=2';
                $amarr[11]['a'] = '2';
                $amarr[11]['i'] = 'server';

                // added by Deep on 28-1-2015
                //$amarr[12]['l'] = 'admin.php?a=22';
                $amarr[12]['l'] = 'new-content.php';
                $amarr[12]['a'] = '22';
                $amarr[12]['i'] = 'content purchase';
                // end here 
            } else {
                for ($i = 1; $i <= 11; $i++) {
                    $amarr[$i]['l'] = '';
                }

                $amarr[1]['l'] = 'admin.php?a=0';
                $amarr[1]['a'] = '0';
                $amarr[1]['i'] = 'dash';
                $amarr[2]['l'] = 'admin.php?a=1';
                $amarr[2]['a'] = '1';
                $amarr[2]['i'] = 'posts';
                $amarr[4]['l'] = 'admin.php?a=3';
                $amarr[4]['a'] = '3';
                $amarr[4]['i'] = 'users';
                //        $amarr[5]['l']='admin.php?a=4';    $amarr[5]['a']='4';       $amarr[5]['i']='members';
            }
            ?>
            <!--<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>-->

        <div style="margin:20px 0 0 0;" class="">
		<div class="">
		<div class="panel panel-default mb20 panel-hovered analytics">
		<div class="panel-body">    
        <?php
        for ($i = 1; $i <= 12; $i++) {
            if ($amarr[$i]['l'] != '') {

                if ($a == $amarr[$i]['a'])
                    echo "<div class=adminmenu2><a href='" . $amarr[$i]['l'] . "'><img src='images/am_" . $amarr[$i]['i'] . "_on.png' border=0></a></div>";
                else
                    echo "<div class=adminmenu2><a href='" . $amarr[$i]['l'] . "'><img src='images/am_" . $amarr[$i]['i'] . "_off.png' class='img-swap' border=0></a></div>";
            }
        }
        ?>
		</div>
		</div>
		</div>
		</div>
            <div class=newp></div>

            <script>
                jQuery(function () {
                    $(".img-swap").hover(
                        function () {
                            this.src = this.src.replace("_off", "_on");
                        },
                        function () {
                            this.src = this.src.replace("_on", "_off");
                        });
                });
            </script>

        <?php
        }

        function microtime_float2()
        {
            list($usec, $sec) = explode(" ", microtime());
            $ret1 = ((float)$usec + (float)$sec);
            $ret1 = str_replace('.', '', $ret1);
            return $ret1;
        }

        function count_reverse_vat($money, $vat)
        {
            $total_percent = 100 + $vat;
            $one_percent = ($money / $total_percent);

            $ret_arr = array();

            $ret_arr['money'] = round($one_percent * 100, 2);
            $ret_arr['vat'] = $money - $ret_arr['money'];

            return $ret_arr;
        }

        function update_full_project_stats($project_id)
        {
            $p_published = 0;
            $p_pending = 0;
            $p_problematic = 0;
            $p_total = 0;

            $rs = mysql__("select count(id) as cid from articles_published where project_id='$project_id'");
            while ($rw = mysql_fetch_array($rs)) {
                $p_published = $rw['cid'];
            }

            $p_total += $p_published;

            $sql_status_arr = array();
            for ($i = 0; $i < 10; $i++)
                $sql_status_arr[$i] = 0;

            $sql = "select status, count(id) as cid from articles where project_id='$project_id' and status>0 group by status";
            //echo $sql."<br>";

            $rs = mysql__($sql);
            while ($rw = mysql_fetch_array($rs)) {
                $sql_status_arr[$rw['status']] = $rw['cid'];
            }

            //echo "<pre>"; print_r($sql_status_arr);

            for ($i = 0; $i < 10; $i++) {
                if ($sql_status_arr[$i] > 0) {
                    if ($i == 2) {
                        $p_problematic += $sql_status_arr[$i];
                    }
                    if ($i == 4) {
                        $p_problematic += $sql_status_arr[$i];
                    }
                    if ($i == 6) {
                        $p_problematic += $sql_status_arr[$i];
                    }

                    if ($i == 7) {
                        $p_pending += $sql_status_arr[$i];
                    }
                    if ($i == 8) {
                        $p_pending += $sql_status_arr[$i];
                    }
                    if ($i == 9) {
                        $p_pending += $sql_status_arr[$i];
                    }
                }
                $p_total += $sql_status_arr[$i];
            }

            $sql = "    update projects set
                                                                                                                                                                                                        n_articles='$p_total',
                                                                                                                                                                                                        n_articles_published='$p_published',
                                                                                                                                                                                                        n_articles_in_publishing_queue='$p_pending',
                                                                                                                                                                                                        n_articles_problematic='$p_problematic'
                                                                                                                                                                                                        where id='$project_id'";
            //echo $sql."<br>";
            mysql__($sql);

            $ret_arr = array();

            $ret_arr['n_articles_in_publishing_queue'] = $p_pending;

            return $ret_arr;
        }

        function objectsIntoArray($arrObjData, $arrSkipIndices = array())
        {
            $arrData = array();

            // if input is object, convert into array
            if (is_object($arrObjData)) {
                $arrObjData = get_object_vars($arrObjData);
            }

            if (is_array($arrObjData)) {
                foreach ($arrObjData as $index => $value) {
                    if (is_object($value) || is_array($value)) {
                        $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
                    }
                    if (in_array($index, $arrSkipIndices)) {
                        continue;
                    }
                    $arrData[$index] = $value;
                }
            }
            return $arrData;
        }

        function setupProxy($curl)
        {
            global $proxy;
            if (isset($proxy) && is_array($proxy)) {
                $address = $proxy[array_rand($proxy)];
                //        $address = "216.67.236.26:9050";
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_PROXY, $address);
                //        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                //        curl_setopt($curl, CURLOPT_USERPWD, "digitalconnectedmedia.com:LgkLwzAd");
                //        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            }
            return $curl;
        }

        function XmlRpcGetPermaLink($id, $login, $password, $rpcurl, $proxity = false)
        {
            //$rpcurl = correct_xmlrpc_url("http://www.stutter-ed.com/");
            global $UserAgentArray;

            $params = array($id, $login, $password);
            $request = xmlrpc_encode_request('metaWeblog.getPost', $params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_URL, $rpcurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if ($proxity) {
                $ch = setupProxy($ch);
            }
            $result = curl_exec($ch);
            curl_close($ch);
            if (!$result && !$proxity) {
                return XmlRpcGetPermaLink($id, $login, $password, $rpcurl, true);
            }
            //echo "<pre>";

            $xmlObj = simplexml_load_string($result);
            $arrXml = objectsIntoArray($xmlObj);
            //print_r($arrXml);
            $arrXml2 = $arrXml['params']['param']['value']['struct']['member'];

            for ($i = 0; $i < sizeof($arrXml2); $i++) {
                if ($arrXml2[$i]['name'] == 'permaLink') {
                    return ($arrXml2[$i]['value']['string']);
                }
            }
        }
		
		function XmlRpcGetPermaLink_1($id, $login, $password, $rpcurl, $proxity = false)
        {
            //$rpcurl = correct_xmlrpc_url("http://www.stutter-ed.com/");
            global $UserAgentArray;

            $params = array($id, $login, $password);
            $request = xmlrpc_encode_request('metaWeblog.editPost', $params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgentArray[mt_rand(0, 15)]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_URL, $rpcurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if ($proxity) {
                $ch = setupProxy($ch);
            }
            $result = curl_exec($ch);
            curl_close($ch);
            if (!$result && !$proxity) {
                return XmlRpcGetPermaLink($id, $login, $password, $rpcurl, true);
            }
            //echo "<pre>";

            $xmlObj = simplexml_load_string($result);
            $arrXml = objectsIntoArray($xmlObj);
            //print_r($arrXml);
            $arrXml2 = $arrXml['params']['param']['value']['struct']['member']; 

            for ($i = 0; $i < sizeof($arrXml2); $i++) {
                if ($arrXml2[$i]['name'] == 'permaLink') {
                    return ($arrXml2[$i]['value']['string']);
                }
            }
        }

        function test_vat_charge($user_country_id, $user_vat_nr)
        {
            global $vat_array;

            $do_charge_vat = 0;

            if (in_array($user_country_id, $vat_array)) {
                if ($user_country_id == 44)
                    $do_charge_vat = 1;
                else {
                    if ($user_vat_nr == '')
                        $do_charge_vat = 1;
                }
            }

            return $do_charge_vat;
        }

        function zcleantext($text)
        {
            $text = str_replace('â€œ', '"', $text);
            $text = str_replace('â€', '"', $text);
            //$text = str_replace("â€˜", "'", $text);
            $text = str_replace('Â£', 'GBPGBPGBPGBPGBPGBP', $text);

            $text = preg_replace('/[^(\x20-\x7F)]*/', '', $text);

            $text = str_replace('GBPGBPGBPGBPGBPGBP', 'Â£', $text);

            for ($i = 0; $i < 10; $i++)
                $text = str_replace('  ', ' ', $text);


            return $text;
        }

        function get_32_words_from_top_and_bottom_for_google($text)
        {
            $n_words = 32;
            $pieces = explode(' ', $text);

            $text1 = '';
            $text2 = '';
            for ($i = 0; $i < $n_words; $i++) {
                $text1 .= $pieces[$i] . " ";
                $text2 .= $pieces[sizeof($pieces) - ($n_words - $i)] . " ";
            }

            $ret_arr[0] = trim($text1);
            $ret_arr[1] = trim($text2);

            return $ret_arr;
        }

        function get_random_words_for_google($text, $n_words = 32)
        {
            $pieces = explode(' ', $text);

            $text1 = '';
            $text2 = '';
            $start = rand($n_words + 1, sizeof($pieces) - $n_words);
            for ($i = 0; $i < $n_words; $i++) {
                $text1 .= $pieces[$start + $i] . " ";
            }

            return $text1;
        }

        function count_suspicious_symbols_in_text($text)
        {
            $n_susp_symbols = 0;
            $n_susp_symbols += substr_count($text, '&#1');
            return $n_susp_symbols;
        }

        function isValidURL($url)
        {
            return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
        }

        function storeUserIp($user_id, $ip)
        {

            //conver ip address to int
            $ip = ip2long($ip);

            //search that user with that ip
            $rs = mysql__("select * from users_ip where user_id='$user_id' and ip='$ip'");

            if ($rw = mysql_fetch_array($rs)) {
                //update ip counter and latest date
                mysql__("update users_ip set count=count+1, updated_at=now() where id='{$rw['id']}'");
            } else {
                //insert new row
                mysql__("insert into users_ip values ('','$user_id','$ip',1,now())");
            }
        }

        function unsubscribe2co($subscription_id)
        {
            $username = getGlobalVar('main_2co_api_login', 'digitalconnectedapi');
            $password = getGlobalVar('main_2co_api_password', 'GbOCysgjUmnhH1JCLhjv');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.2checkout.com/api/sales/detail_sale?sale_id=' . $subscription_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $result = curl_exec($ch);
            curl_close($ch);
            $xmlObj = simplexml_load_string($result);
            $json = json_encode($xmlObj);
            $arr = json_decode($json);
            $arr = (array)$arr->sale->invoices->lineitems->billing;
            $id = $arr['lineitem_id'];
            if (!$id)
                return false;
            $ch = curl_init();
            $params = array(
                "lineitem_id" => $id,
            );
            curl_setopt($ch, CURLOPT_URL, 'https://www.2checkout.com/api/sales/stop_lineitem_recurring');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $result = curl_exec($ch);
            curl_close($ch);
            $xmlObj = simplexml_load_string($result);
            $json = json_encode($xmlObj);
            $arr = (array)json_decode($json);
            if ($arr['response_code'] == "OK")
                return true;
            return false;
        }

        function unsubscribeSkrill($subscription_id)
        {
            global $GVars;
            $password = md5(getGlobalVar('main_skrill_api_password', 'carr0tmb1'));
            $email = getGlobalVar('main_scrill_email', 'payments@digitalconnectedmedia.com');
            $ch = curl_init();
            $url = "https://www.moneybookers.com/app/query.pl?action=cancel_rec&email=$email&password=$password&trn_id=$subscription_id";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $result = curl_exec($ch);
            curl_close($ch);
            mail($GVars['admin_email'], 'ScrillUnsubscribeLog', $url . "\n\n" . $result);
            return true;
            return false;
        }

        function getProjectTemplate($pid, $user_id)
        {
            $rs = mysql__("select * from projects where id_hash='$pid' AND user_id='$user_id'");
            $data = array();
            while ($rw = mysql_fetch_array($rs)) {
                $data['publish_tags'] = stripslashes(str_replace('"', '&quot;', $rw['publish_tags']));
                $data['publish_category'] = $rw['publish_category'];
                $data['publish_from_time'] = $rw['publish_from_time'];
                $data['publish_from'] = $rw['publish_from'];
                $data['url1'] = stripslashes(str_replace('"', '&quot;', $rw['url1']));
                $data['keyword1'] = stripslashes(str_replace('"', '&quot;', $rw['keyword1']));
                $data['url2'] = stripslashes(str_replace('"', '&quot;', $rw['url2']));
                $data['keyword2'] = stripslashes(str_replace('"', '&quot;', $rw['keyword2']));
                if (($data['publish_from_time'] == '') or ($data['publish_from_time'] == '0000-00-00 00:00:00'))
                    $data['publish_from_time'] = date("Y-m-d", strtotime("+1 day"));

                $data['publish_from_time'] = substr($data['publish_from_time'], 0, 10);
            }
            return $data;
        }

        function setProjectTemplate($pid, $user_id, $data)
        {
            $set = '';
            $set .= ($data['publish_tags']) ? "publish_tags='" . $data['publish_tags'] . "'," : '';
            $set .= ($data['publish_category']) ? "publish_category='" . $data['publish_category'] . "'," : '';
            $set .= ($data['url1']) ? "url1='" . $data['url1'] . "'," : '';
            $set .= ($data['url2']) ? "url2='" . $data['url2'] . "'," : '';
            $set .= ($data['keyword1']) ? "keyword1='" . $data['keyword1'] . "'," : '';
            $set .= ($data['keyword2']) ? "keyword2='" . $data['keyword2'] . "'," : '';

            $sql = "update projects set $set
                                                                                                                                                                                                        publish_from='" . $data['publish_from'] . "',
                                                                                                                                                                                                        publish_from_time='" . $data['publish_from_time'] . "'
                                                                                                                                                                                                        where id_hash='$pid' AND user_id='$user_id'";
            mysql__($sql);
            return 1;
        }

        $_SESSION['pageviews_counter']++;

        if ($_SESSION['pageviews_counter'] > 1000) {
            $_SESSION['pageviews_counter'] = 0;
            zmail($GVars['admin_email'], $GVars['site_public_email'], 'Pageviews counter > 1000', "<pre>" . print_r($_SESSION, TRUE) . "<br>" . print_r($_SERVER, TRUE));
        }
		
		
		function get_page_rank($url)
		{
			$url='http://tools.alastair.pro/pagerank/?url='.$url.'&format=plain';

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);

			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		}
		
        ?>
