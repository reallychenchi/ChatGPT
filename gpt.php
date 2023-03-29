<?php

//require 'vendor/autoload.php';
//use GuzzleHttp\Client;


// 最大提交聊天记录数，内容太多会消耗更多tokens
// The maximum number of submitted chat records, too much content will consume more tokens
$limit = 2;

//$chatGpt = new ChatGpt($apiKey, $limit);

// 设置HTTP代理（选填） Set HTTP proxy (optional)
//\Onekb\ChatGpt\Di::set('proxy', 'http://127.0.0.1:1087');
$history = [];

header("Content-Type:text/html;charset=utf-8");
// 自定义聊天记录 Custom chat history
//$chatGpt->history = [
//    [
//        'role' => 'user',
//        'content' => '你好',
//    ],
//    [
//        'role' => 'assistant',
//        'content' => '你好',
//    ],
//];

$act = 'question';
$input = $_POST['question'];
if ($act == 'question') {
    try {
        $result = ask($input);
        $text = $result['choices'][0]['message']['content'];
	    $ret = 0;
    } catch (\Exception $e) {
        $text = '可能是因为网络原因或速率限制，请求中断，你可以再问一次。'; // It may be due to network reasons or rate limiting, the request is interrupted, you can ask again.
	    $ret = 1;
	    echo $e;
    }
    //array_push($history, $input => $text);
    $history = $history + [$input => $text];
    foreach(json_decode($_POST['history'], true) as $i => $t) {
        $history = $history + [$i => $t];
    };
    $value = json_encode($history);
    echo <<<END
<html>
<head>
    <meta charset="utf-8">
    <title>欢迎使用狗屁通</title>
</head>
<body>
<style type="text/css">
		body {
			font-size: 20px;
			margin: 0;
			padding: 0;
			background-color: #f2f2f2;
		}
.form  {
			display: flex;
			flex-direction: row;
			align-items: center;
}
.input {
			flex-grow: 1;
			padding: 10px;
			border: none;
			border-radius: 5px;
			box-shadow: 0 2px 5px rgba(0,0,0,0.3);
			background-color: #fff;
			outline: none;
}
.submit{
			font-size: 20px;
			padding: 10px 20px;
			border: none;
			border-radius: 5px;
			box-shadow: 0 2px 5px rgba(0,0,0,0.3);
			background-color: #4CAF50;
			color: #fff;
			cursor: pointer;
			outline: none;
}
</style>

<form action="gpt.php" method="POST" class="form">
<input type="text" name="question" class="input" placeholder="请输出你想对ChatGpt提问的内容">
<input type="hidden" name="history" value='$value'>
<input type="submit" value="提问" class="submit">
</form>
END;
foreach($history as $i =>$t) {
  echo "问：".$i;
  echo "<br>";
  echo "答：".$t;
  echo "<br>";
}
echo <<<END
</body>
</html>
END;
} else {
    echo <<<END
<form action="gpt.php" method="POST">
<form>
问题：
<input type="text" name="question">
<input type="hidden" name="history">
<input type="submit" value="提问"> </form>
<br>
}
<br>
END;
}

    /**
     * @param string $content
     *
     * @return array
     */
    function ask($content)
    {
        $history = array(
            'role' => 'user',
            'content' => $content,
        );

        $data = gpt35Curl($history);
        return $data;
    }


    function gpt35Curl(array $message, $json=true) {
       $url = "https://api.openai.com/v1/chat/completions";
       $headers = array(
           'Content-Type: application/json',
           'Authorization: Bearer '.'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',
           //'Authorization: Bearer '.'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',
           //'Authorization: Bearer '.'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',
       );

       $data = array(
           'model' => 'gpt-3.5-turbo',
           'messages' => array(
                $message
           ),
           'temperature' => 0.7
       );

       $ch = curl_init($url);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

       $result = curl_exec($ch);
       curl_close($ch);
       $ret = json_decode($result, true);
       return $ret;
    }
?>
