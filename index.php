<?php
require_once('./logic.php');
    if (!isset($_REQUEST)) { 
        return; 
    } 

    $v_api = '5.65';
    define('BASEPATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
    $data = json_decode(file_get_contents('php://input')); 
    $json = json_decode(file_get_contents('signa.json'), true);
    
    $token = $json['token'];
    $confirmation_token = $json['confirmation_token'];
    $group_join = $json['group_join'];
    $group_leave = $json['group_leave'];
    $gismember = $json['gismember'];

    $config = json_decode(file_get_contents('config.cfg'));
$menu = $config->menu;
$menu2 = $json['signa'];
    $group_id = $data->group_id;
    $message_id = $data->object->id;
    $user_id = $data->object->user_id;
    $body = $data->object->body; 

    switch ($data->type) { 
        //Если это уведомление для подтверждения адреса сервера... 
        case 'confirmation': 
            //...отправляем строку для подтверждения адреса 
            echo $confirmation_token; 
            break; 

        //Если это уведомление о новом сообщении
        case 'message_new': 
            
            // Получаем данные о пользователе
            $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v={$v_api}")); 
            $user_name = $user_info->response[0]->first_name;
            $last_name = $user_info->response[0]->last_name;

            // Проверка на подписку
            $isMember = json_decode(curl("https://api.vk.com/method/groups.isMember", array(
                "group_id" => $group_id, 
                "user_id" => $user_id,
                "extended" => "0",
                'access_token' => $token, 
                'v' => $v_api
            )), true);

	 $user_id = $data->object->user_id;
        $message_text = $data->object->body;
        markAsReaded($token, $data->object->id);
        $in = checkIsNumeric($message_text);
        $out = "";
        if(preg_match("/0/i", $isMember['response'])) {
                sendMessage($gismember, $user_id);
            } else {
                if (is_int($in)) {
                       if($in <= count($menu2)) {
    foreach($json['signa'] as $s) {
                        if($in == $s['id']) {
                            drawImage($user_name. "\n". $last_name, $s['img'], $s['x'], $s['y'], $s['angle'], $s['font'], $s['font_size'], $s['font_color']);
                        
                            $photo = uploadPhoto();

                            $data = curl('https://api.vk.com/method/messages.send', array( 
                                'message' => 'Твоя сигна готова, гондон.'. $s['img']. $s['x']. $s['y']. $s['angle']. $s['font']. $s['font_size']. $s['id'], 
                                'attachment' => $photo,
                                'user_id' => $user_id, 
                                'access_token' => $token, 
                                'v' => $v_api
                            ));

                            if(file_exists('out.png')) {
                                @unlink('out.png');
                            }

                        }}



                            
                       } else {
                           $out = generateMenu($config->menu_invitation, $menu);
                           sendMessage($out, $user_id);
                       }
                } else {
                    $idx = checkAll($message_text, $menu);
                         if (is_int($idx)) {
                            $out = $menu[$idx]->answer;
                            sendMessage($out, $user_id);
                         } else {
                           $out = generateMenu($config->menu_invitation, $menu);
                           sendMessage($out, $user_id);
                         }
        }
       
    }
        echo('ok');
        break;
  
        case 'group_join':
            sendMessage($group_join, $user_id);
            echo('ok');
            break;
        case 'group_leave':
            sendMessage($group_leave, $user_id);
            echo('ok');
            break;
    }

 
?>
