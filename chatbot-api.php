<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';
header('Content-Type: application/json');
if(!isLoggedIn()){echo json_encode(['reply'=>'Please log in to use the chatbot.']);exit;}
$userMessage=clean($_POST['message']??''); if(!$userMessage){echo json_encode(['reply'=>'Please type a message.']);exit;}
if(aiRateLimitExceeded()){echo json_encode(['reply'=>'AI limit reached (10/hour). Please try again later.']);exit;}
$trainingData=@file_get_contents(__DIR__.'/data/travel-knowledge.txt')?:'';
$prompt="You are Wandi, the WanderWise AI travel assistant for India and international travel.\nYou are friendly, helpful, and give practical travel advice.\nKeep answers concise (under 150 words unless asked for detail).\n\nUse this knowledge base as your PRIMARY reference:\n--- KNOWLEDGE BASE START ---\n{$trainingData}\n--- KNOWLEDGE BASE END ---\n\nUser question: {$userMessage}";
$reply=callGemini($prompt); echo json_encode(['reply'=>$reply]);
?>
