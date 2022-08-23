<?php
error_reporting(0);
set_time_limit(0);
ob_start();
if(!file_exists("iTelegram.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/iTelegram/main/iTelegram.phar', 'iTelegram.php');
}
require_once('iTelegram.php');
use iTelegram\Bot;
$channel		= "";
$admin			= "671062879";
$api			= "https://api.ineo-team.ir"; # don't change it.
define('API_KEY', "5281541980:AAEWZkVWFLAKyu_oy4ksb96thu3MA44TWfs");
function safe($input){
	$array = ['$', ';', '"', "'", '<', '>'];
	return str_replace($array, null, $input);
}
function timedate(){
	global $api;
	$time = json_decode(file_get_contents($api."/timezone.php?action=time&zone=fa"));
	$date = json_decode(file_get_contents($api."/timezone.php?action=date&zone=fa"));
	return ['time' => $time->result->time, 'date' => $date->result->date];
}
function AddUser($chat_id){
	if(!is_dir("data")){ mkdir("data"); }
	if(!is_dir("data/".$chat_id)){ mkdir("data/".$chat_id); }
	copy("redirector.php", "data/index.php");
	copy("redirector.php", "data/".$chat_id."/index.php");
	$users = file_get_contents("data/userslist.txt");
	if(!in_array($chat_id, explode("\n", $users))){
		$users .= $chat_id."\n";
		file_put_contents("data/userslist.txt", $users);
	}
}
$bot		= new Bot();
$bot->Authentification(API_KEY);
$update		= $bot->getUpdate();
$text		= safe($bot->Text());
$chat_id	= $bot->UserId();
$username	= $bot->Username();
$firstname	= safe($bot->Firstname());
$message_id	= $bot->MessageId();
$chatID		= $bot->InlineUserId();
$messageID	= $bot->InlineMessageId();
$data		= $update['callback_query']['data'];
$callbackId = $update['callback_query']['id'];
$getStep	= file_get_contents("data/".$chat_id."/step.txt");
$cancelBtn	= json_encode(['inline_keyboard' => [[['text' => "• Cancel •", 'callback_data' => "cancel"]]]]);
$backBtn	= json_encode(['inline_keyboard' => [[['text' => "Bᴀᴄᴋ ᴛᴏ Pᴀɴᴇʟ", 'callback_data' => "adminlogin"]]]]);
if(isset($chat_id) && $bot->getChatType() != "private"){ exit; }
AddUser($chat_id);
$commands	= json_encode([
['command' => base64_decode("c3RhcnQ="), 'description' => base64_decode("2LTYsdmI2Lkg2Ygg2LHYp9mHINin2YbYr9in2LLbjCDZhdis2K/YryDYsdio2KfYqg==")],
['command' => base64_decode("Y3JlYXRvcg=="), 'description' => base64_decode("2LfYsdin2K3bjCDZiCDYqtmI2LPYudmHINix2KjYp9iq")]
]);
$bot->TelegramAPI("setMyCommands", ['commands' => $commands]);
$sign = "";
if(isset($chat_id) && in_array($chat_id, explode("\n", file_get_contents("data/blockeduserslist.txt")))){
	$message = "[ ❗️ ] You are banned\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null); exit;
}
if($text == "/start" && $chat_id != $admin){
	file_put_contents("data/$chat_id/name.txt", $firstname);
	file_put_contents("data/$chat_id/step.txt", "none");
	$message = "• welcome <b><a href='tg://user?id=".$chat_id."'>".$firstname."</a></b> !
send Your Message\n$sign";
    $r = $bot->sendMessage($chat_id, $message, "HTML", true);
	###################################################################################################
}elseif($text == "/start" && $chat_id == $admin){
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$message = "Hey <a href='tg://user?id=".$admin."'></a> !
\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "• Panel •", 'callback_data' => "adminlogin"]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif($data == "cancel"){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "Canceled successfully !";
	$bot->AnswerCallBack($callbackId, $message, true);
	$bot->deleteMessage($chatID, $messageID);
	###################################################################################################
}elseif($data == "cl"){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "Closed successfully !";
	$bot->AnswerCallBack($callbackId, $message, true);
	$bot->deleteMessage($chatID, $messageID);
	###################################################################################################
}elseif(strpos($data, "r2_usr:") !== false && strpos($data, "&msgId:") !== false && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "sendAnswerTo_data:".base64_encode($data));
	preg_match('#r2_usr:(.*?)&msgId:(.*)#su', $data, $output);
	$name = file_get_contents("data/".$output[1]."/name.txt");
	$message = "You are replying to <a href='tg://user?id=".$output[1]."'>$name</a> [ <code>".$output[1]."</code> ] !";
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, $cancelBtn); $data = null;
	###################################################################################################
}elseif($chat_id == $admin && strpos($getStep, "sendAnswerTo_data:") !== false){
	file_put_contents("data/$chat_id/step.txt", "none");
	$getStep = base64_decode(str_replace('sendAnswerTo_data:', null, $getStep));
	preg_match('#r2_usr:(.*?)&msgId:(.*)#su', $getStep, $output);
	$name = file_get_contents("data/".$output[1]."/name.txt");
	$type = $bot->InputMessageType();
	$text = $update['message']['text'] ?? $update['message']['caption'];
	$caption = safe($text);
	$timedate = timedate();
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	]]);
	if($type == "text"){
		$bot->sendMessage($output[1], $caption, "HTML", true, $output[2], $button);
	}elseif($type == "document"){
		$bot->sendDocument($output[1], $update['message']['document']['file_id'], $caption, null, "HTML", null, $output[2], $button);
	}elseif($type == "audio"){
		$bot->sendAudio($output[1], $update['message']['audio']['file_id'], $caption, null, null, null, null, "HTML", null, $output[2], $button);
	}elseif($type == "voice"){
		$bot->sendVoice($output[1], $update['message']['voice']['file_id'], $caption, null, "HTML", null, $output[2], $button);
	}elseif($type == "video"){
		$bot->sendVideo($output[1], $update['message']['video']['file_id'], $caption, "HTML", null, $output[2], $button);
	}elseif($type == "photo"){
		$count = count($update['message']['photo']) - 1;
		$bot->sendPhoto($output[1], $update['message']['photo'][$count]['file_id'], $caption, "HTML", null, $output[2], $button);
	}elseif($type == "sticker"){
		$bot->sendSticker($output[1], $update['message']['sticker']['file_id'], null, $output[2], $button);
	}
	$message = "Your reply sent to<a href='tg://user?id=".$output[1]."'>$name</a> [ <code>".$output[1]."</code> ] !";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	[['text' => "• Unblock •", 'callback_data' => "unblockthisuser_".$output[1]], ['text' => "• Block •", 'callback_data' => "blockt_hisuser_".$output[1]]],
	[['text' => "• Send another Message •", 'callback_data' => $getStep]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif($data == "adminlogin" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "welcome to admin Panel !";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "• Unblock •", 'callback_data' => "ubu"], ['text' => "• Block •", 'callback_data' => "bu"]],
	[['text' => "• Send to All •", 'callback_data' => "s2a"], ['text' => "• Forward to All •", 'callback_data' => "f2a"]],
	[['text' => "• Close Panel •", 'callback_data' => "cl"], ['text' => "• Bot Status •", 'callback_data' => "ac"]],
	]]);
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $button);
	###################################################################################################
}elseif(in_array($data, ['ubu', 'bu']) && $chatID == $admin){
	if($data == "ubu"){
		$method = "unblock";
		$methodFA = "آنبلاک";
	}else{
		$method = "block";
		$methodFA = "بلاک";
	}
	file_put_contents("data/$chatID/step.txt", "getId4_$method");
	$message = "send UserID to $methodFA ..\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif(isset($text) && strpos($getStep, "getId4_") !== false){
	file_put_contents("data/$chat_id/step.txt", "none");
	$method = str_replace("getId4_", null, $getStep);
	$id = safe($text);
	$users = file_get_contents("data/userslist.txt");
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(!in_array($id, explode("\n", $users))){
		$message = "User Not found !\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn);
		exit;
	}
	if($method == "block"){
		if(in_array($id, explode("\n", $blocked))){
			$message = "This user [ <code>$id</code> ] was already blocked !\n$sign";
		}else{
			$blocked .= $id."\n";
			file_put_contents("data/blockeduserslist.txt", $blocked);
			$message = "Your account has been blocked !\n$sign";
			$bot->sendMessage($id, $message, "HTML", true, null, null);
			$message = "This user [ <code>$id</code> ] has been blocked successfully.\n$sign";
		}
	}else{
		if(in_array($id, explode("\n", $blocked))){
			$blocked = str_replace($id."\n", null, $blocked);
			file_put_contents("data/blockeduserslist.txt", $blocked);
			$message = "Your account has been unblocked !\n$sign";
			$bot->sendMessage($id, $message, "HTML", true, null, null);
			$message = "This user [ <code>$id</code> ] has been unblocked successfully.\n$sign";
		}else{
			$message = "This user [ <code>$id</code> ] was already free.\n$sign";
		}
	}
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn);
	###################################################################################################
}elseif($data == "f2a" && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "getForward");
	$message = "send A message to forward to all ..\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif($getStep == "getForward" && $chat_id == $admin){
	file_put_contents("data/$chat_id/step.txt", "none");
	$message = "Please wait ..";
	$msgId = $bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null)->result->message_id;
	$users = fopen("data/userslist.txt", 'r');
	while(!feof($users)){
		$user = fgets($users);
		$bot->forwardMessage($user, $chat_id, $message_id);
	}
	$bot->deleteMessage($chat_id, $msgId);
	$message = "Forwarded successfully.\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, null, $backBtn);
	###################################################################################################
}elseif($data == "s2a" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "getMessage");
	$message = "send A message to send to all ..\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif(isset($text) && $getStep == "getMessage" && $chat_id == $admin){
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$text = safe($text);
	$message = "Please wait ..";
	$msgId = $bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null)->result->message_id;
	$users = fopen("data/userslist.txt", 'r');
	$message = "#all\n\n<code>$text</code>\n$sign";
	while(!feof($users)){
		$user = fgets($users);
		$bot->sendMessage($user, $message, "HTML", true, null, null);
	}
	$bot->deleteMessage($chat_id, $msgId);
	$message = "Sent successfully.\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, null, $backBtn);
	###################################################################################################
}elseif($data == "ac" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$users = count(explode("\n", file_get_contents("data/userslist.txt"))) - 1;
	$blocked = count(explode("\n", file_get_contents("data/blockeduserslist.txt"))) - 1;
	$timedate = timedate();
	$message = "• <b>Recent status :</b> <code>".$timedate['time']." - ".$timedate['date']."</code>
•<b>Server ping :</b> <code>".sys_getloadavg()[2]."ms</code>
•<b>PHP version :</b> <code>".phpversion()."</code>
•<b>Base version :</b> <code>".$bot->version()."</code>
•<b>Memory Usage :</b> <code>".number_format(memory_get_usage(true))." KB</code>
•<b>Users :</b> <code>$users</code> !
• <b>Blocked :</b> <code>$blocked</code> !
$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $backBtn);
	###################################################################################################
}elseif(strpos($data, "show_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("show_", null, $data);
	$name = file_get_contents("data/$id/name.txt");
	$message = "This message is from <a href='tg://user?id=$id'>$name</a> [ <code>$id</code> ]\n$sign";
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
	###################################################################################################
}elseif(strpos($data, "unblockthisuser_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("unblockthisuser_", null, $data);
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(in_array($id, explode("\n", $blocked))){
		$blocked = str_replace($id."\n", null, $blocked);
		file_put_contents("data/blockeduserslist.txt", $blocked);
		$message = "Your account has been unblocked !\n$sign";
		$bot->sendMessage($id, $message, "HTML", true, null, null);
		$message = "unblocked successfully";
	}else{
		$message = "This user was already free !";
	}
	$bot->AnswerCallBack($callbackId, $message, true);
	###################################################################################################
}elseif(strpos($data, "blockt_hisuser_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("blockt_hisuser_", null, $data);
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(!in_array($id, explode("\n", $blocked))){
		$blocked .= $id."\n";
		file_put_contents("data/blockeduserslist.txt", $blocked);
		$message = "Your account has been blocked !\n$sign";
		$bot->sendMessage($id, $message, "HTML", true, null, null);
		$message = "blocked successfully";
	}else{
		$message = "This user was already block !";
	}
	$bot->AnswerCallBack($callbackId, $message, true);
	###################################################################################################
}else{
	if($chat_id == $admin){
		$message = "You cant send message to your self !\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn); exit;
	}
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$timedate = timedate();
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	[['text' => "• Unblock •", 'callback_data' => "unblockthisuser_".$chat_id], ['text' => "• Block •", 'callback_data' => "blockt_hisuser_".$chat_id]],
	[['text' => "• Reply •", 'callback_data' => "r2_usr:".$chat_id."&msgId:".$message_id], ['text' => "• User •", 'callback_data' => "show_".$chat_id]],
	]]);
	$button2 = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	]]);
	if($bot->InputMessageType() == "document"){
		$bot->sendDocument($admin, $update['message']['document']['file_id'], safe($update['message']['caption']), null, "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "audio"){
		
	}elseif($bot->InputMessageType() == "voice"){
		$bot->sendVoice($admin, $update['message']['voice']['file_id'], safe($update['message']['caption']), null, "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "video"){
		$bot->sendVideo($admin, $update['message']['video']['file_id'], safe($update['message']['caption']), "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "photo"){
		$count = count($update['message']['photo']) - 1;
		$photo = $update['message']['photo'][$count]['file_id'];
		$bot->sendPhoto($admin, $photo, safe($update['message']['caption']), "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "text"){
		$text = safe($text);
		$message = $text;
		$bot->sendMessage($admin, $message, "HTML", true, null, $button);
	}else{
		$message = "File not supported !\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
		exit;
	}
	$bot->sendMessage($chat_id, "Sent successfully. I'll reply to you soon ..\n$sign", "HTML", true, $message_id);
	###################################################################################################
}
unlink("error_log");
?>
