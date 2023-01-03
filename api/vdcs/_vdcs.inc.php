<?php require_once __DIR__ . '/../_api.php';
$requestVdcsModelType = RequestVdcsModelType::None;
if(isset($request_model_type))//if(isset($post) && is_array($post) && array_key_exists("mode", $post))
{
    //$request_model_type = strtoupper($post["mode"]);
    $requestVdcsModelType = RequestVdcsModelType::tryFrom($request_model_type)??RequestVdcsModelType::Latest;
}

$jno = 16185;
if(isset($post) && is_array($post) && array_key_exists("jno", $post))
{
    $jno = $post["jno"];
}
if(!$jno) exit;