<?php
function curl($url, $params = false) {
        usleep(333333);

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Language: ru,uk;q=0.8,en;q=0.6']);
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.137 YaBrowser/17.4.1.910 Yowser/2.5 Safari/537.36");
        if($params) { 
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params)); 
        }
        $response = curl_exec($ch); 
        curl_close($ch); 

        return $response;
    }

function sendMessage($text, $userId) {
        global $token, $v_api;

         $send = curl('https://api.vk.com/method/messages.send', array(
            'message' => $text, 
            'user_id' => $userId, 
            'access_token' => $token, 
            'v' => $v_api
        ));

    }


function checkAll($text, $menu)
{
    foreach ($menu as $value) {
        if (parseMessage($text, $value->keywords)) {
            return $value->index;
        }
    }
    return false;
}
/*
 * Check string for only 1 number
 */
function checkIsNumeric($message)
{
    if (is_numeric($message)) {
        return intval($message);
    } else {
        return false;
    }
}
/*
 * Function to generate text menu
 */
function generateMenu($invitation, $menu)
{
    $result = $invitation;
    foreach ($menu as $value) {
        $result .= strval($value->index) . " - " . $value->menu_text . ";<br>";
    }
    return $result;
}


function markAsReaded($token, $message_id)
{
    $request_params = array(
        'message_ids' => $message_id,
        'access_token' => $token,
        'v' => '5.6'
    );
    $get_params = http_build_query($request_params);
    file_get_contents('https://api.vk.com/method/messages.markAsRead?' . $get_params);
}

function parseMessage($message, $keywords)
{
    $keys = explode(" ", $keywords);
    $values = explode(" ", $message);
    return !empty(array_intersect($keys, $values));
}



//////////////////////////


 function uploadPhoto() {
     global $token, $v_api ,$group_id;

        $data = curl("https://api.vk.com/method/photos.getMessagesUploadServer", array(
            'access_token' => $token, 
        ));

        if($data) {
            $getUrl = json_decode($data, true);
            $url = $getUrl['response']['upload_url'];
            $name = '/var/www/bot.website/html/out.png';
            $post_fields = array( 'photo' => curl_file_create( $name ) );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            $upload = curl_exec( $ch );
            curl_close( $ch );

            if($upload) {
                $upload = json_decode($upload, true);

                $data = curl("https://api.vk.com/method/photos.saveMessagesPhoto", array(
                    'hash' => $upload['hash'],
                    'photo' => $upload['photo'],
                    'server' => $upload['server'],
                    'access_token' => $token, 
                    'v' => $v_api
                ));

                if($data) {
                    $data = json_decode($data, true);

                    return 'photo'.$data['response'][0]['owner_id'].'_'.$data['response'][0]['id'];
                }
            } 
        }
    }


    function getPrewText($text, $maxwords=4, $maxchar=50) {
        $words=split(' ',$text);
        $text='';
        foreach ($words as $word) {
            if (mb_strlen($text.' '.$word)<$maxchar) {
                $text.=" \n ".$word;
            }
            else {
                $text.='';
                break;
            }
        }
        return $text;
    }

    function drawImage($text, $fileName, $x, $y, $angle, $fontName, $fontSize, $fontColor){
        $draw = new ImagickDraw(); 
        $bg = new Imagick(BASEPATH.'signa/'.$fileName);
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);
        $draw->setFont(BASEPATH."/fonts/".$fontName);
        $draw->setFontSize($fontSize);
        $draw->setFillColor($fontColor);
        $bg->annotateImage($draw, $x, $y, $angle, $text);
        $bg->setImageFormat("png");
        $bg->writeImage('out.png');
    }