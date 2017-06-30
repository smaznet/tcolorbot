<?php
/**
 * Created by PhpStorm.
 * User: smaznet
 * Date: 6/30/17
 * Time: 11:43 PM
 */
$token="403784710:AAHZ2viBVMpN0X1yZKESu4FIeo-FHkfEQLs";
$toChat=-1001108766702;
$update=json_decode(file_get_contents("php://input"));
require_once "core/telegramhelper.php";
require_once "core/InlineKeyBoardMarkUp.php";
require_once "core/InlineKeyBoardItem.php";
$Tl=new telegramhelper($token);
function HexTo255($hexval){
    $spilted=str_split(substr($hexval,1,6),2);
    $out=[];
    foreach ($spilted as $hex){
        $out[]=hexdec($hex);
    }
    return $out;
}
function getRandomColor(){
    $str="abcdef0123456789";
    $out='';
    while (strlen($out)<6){
        $out.=substr($str,rand(0,15),1);
    }
    return "#$out";
}
function buildImage($hex){
    $img=imagecreate(512,512);
    $C=HexTo255($hex);
    imagecolorallocate($img,$C[0],$C[1],$C[2]);

    ob_start();

    imagejpeg($img);
    $stringdata = ob_get_contents();
    ob_end_clean();
    imagedestroy($img);

    return $stringdata;

}
if (isset($update->message)){
$Tl->senMessage(['text'=>'خب خوش اومدی

الان کسی گفت بیا پی وی آخه حالا که اومدی ببین خیلی راحته فقط کافیه تو هرچتی که هستی تایپ کنی
@TcolorBot #424242

به جای #424242 کد هر رنگی میتونی بزنی','chat_id'=>$update->message->chat->id,'reply_markup'=>json_encode(InlineKeyBoardMarkUp::build(false,[
    [
        InlineKeyBoardItem::build("بزن بریم",null,null,getRandomColor())
    ]
]))]);
}elseif (isset($update->inline_query)){
    $query=$update->inline_query->query;
    $INID = $update->inline_query->id;
    $query=strtolower($query);
    if (empty($query)){

        $results=[];
        for($i=0;$i<5;$i++){
            $query=getRandomColor();
            $res=  $Tl->sendMediaByContent('Photo',buildImage($query),['chat_id'=>$toChat]);
            $results[]= ['type'=>'photo',
                'id'=>'IdResult_'.$query,
                'photo_file_id'=>end($res['result']['photo'])['file_id'],
                'caption'=>$query
            ];
        }
        $Tl->makeHTTPRequest('answerInlineQuery',['inline_query_id'=>$INID,'cache_time'=>120000,'results'=>json_encode($results)]);
        exit;
    }
    if (preg_match("/^#[a-f0-9]{6}$/",$query)){
      $res=  $Tl->sendMediaByContent('Photo',buildImage($query),['chat_id'=>$toChat]);
        $Tl->makeHTTPRequest('answerInlineQuery',['inline_query_id'=>$INID,'results'=>json_encode([
            ['type'=>'photo',
                'id'=>'IdResult_'.$query,
                'photo_file_id'=>end($res['result']['photo'])['file_id'],
                'caption'=>$query
            ]
        ])]);
    }else{
        $Tl->makeHTTPRequest('answerInlineQuery',['inline_query_id'=>$INID,'results'=>json_encode([
            ['type'=>'article',
                'id'=>'IdWrong',
                'title'=>'ورودی شما اشتباه است',
                'description'=>'مثال : #ff00ff',
                'input_message_content'=>['message_text'=>"ورودی شما اشتباه است\nمثال : #ff00ff"]
                ]
        ])]);
    }
}
