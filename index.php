<?php

ini_set("log_errors", 1);
ini_set("error_log", "error.log");

$original_url = "http://gitlab/";
$destination = "../gitlab-mock/";

// Identify what method we're using (e.g. GET, POST etc.)
$request_method = $_SERVER['REQUEST_METHOD'];

$get_params = $_GET;
$params = [];
if($request_method != "GET") {
  if(empty($_POST)) {
    $params = file_get_contents("php://input");
  } else {
    $params = $_POST;
  }
}

$request_url = $original_url . "/" . $_REQUEST["path"] . "/";
$request_url .= '?' . http_build_query($get_params);

@mkdir($destination . "/" . $_REQUEST["path"], 0777, true);

$ch = curl_init($request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // And we want the response as a string
curl_setopt($ch, CURLOPT_HEADER, true);       // With headers too.

if($request_method == 'HEAD') {
  $headers = get_headers($request_url);
  foreach ($headers as $key => $response_header) {
    header($response_header);
  }
  $path = pathinfo($request_url);
  if(empty($path["extension"])) {
    $request_url .= "/index.php";
    $path = pathinfo($request_url);
  }
  if($path["basename"] == "index.php") {
    // And write a small PHP script that base64_decodes the results. This is extremely simplified, but hey, it works.
    @file_put_contents($api_path . $_REQUEST["path"] . "/" . $path["basename"], urldecode("%3C%3Fphp+%0D%0A++echo+base64_decode%28%22" . base64_encode($response_content) . "%22%29%3B%0D%0A%3F%3E"));
  } else {
    @file_put_contents($api_path . $_REQUEST["path"] . "/" . $path["basename"], $response_content);
  }
  exit();
} else {
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
}

$response = curl_exec($ch);

// Split up our content and headers
list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);

// And pass the headers on to whoever is requesting this script
$response_headers = preg_split('/(\r\n){1}/', $response_headers);

foreach ($response_headers as $key => $response_header) {
    if(strpos($response_header, "Transfer-Encoding: chunked") === false && strpos($response_header, "gzip") === false) {
      header($response_header);
    } else {
      error_log("Skipping gzip and chunked encoding");
    }
    // Rewrite the `Location` header, so clients will also use the proxy for redirects.
    if (preg_match('/^Location:/', $response_header)) {
        list($header, $value) = preg_split('/: /', $response_header, 2);
        $response_header = 'Location: http://localhost/mockery/';
    }
}

// Now output the contents
echo($response_content);
$path = pathinfo($request_url);
if(empty($path["extension"])) {
  $request_url .= "/index.php";
  $path = pathinfo($request_url);
}
if($path["basename"] == "index.php") {
  error_log("No filename for " . $request_url . ", saving as index.php");
  // And write a small PHP script that base64_decodes the results. This is extremely simplified, but hey, it works.
  file_put_contents($destination . $_REQUEST["path"] . "/" . $path["basename"], urldecode("%3C%3Fphp+%0D%0A++echo+base64_decode%28%22" . base64_encode($response_content) . "%22%29%3B%0D%0A%3F%3E"));
} else {
  error_log("No filename found for " . $request_url . ", saving as " . $path["basename"]);
  file_put_contents($destination . $_REQUEST["path"] . "/" . $path["basename"], $response_content);
}
