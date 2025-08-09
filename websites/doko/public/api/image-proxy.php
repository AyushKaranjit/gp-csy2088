<?php
// Simple image proxy to mitigate CORB and unify caching
// Usage: /api/image-proxy.php?url=<encoded_external_url>
// Security: allow only whitelisted hosts (Unsplash & source.unsplash.com) and only image content types.

require_once __DIR__ . '/../../template/config.php';

if(!IMAGE_PROXY_ENABLED){ http_response_code(404); echo 'Proxy disabled'; exit; }

$url = isset($_GET['url']) ? trim($_GET['url']) : '';
if($url === ''){ http_response_code(400); echo 'Missing url'; exit; }

// Decode and sanitize
$url = filter_var($url, FILTER_SANITIZE_URL);

// Allow only https
if(stripos($url,'https://') !== 0){ http_response_code(400); echo 'Only https allowed'; exit; }

$parsed = parse_url($url);
$host = $parsed['host'] ?? '';
$allowedHosts = ['images.unsplash.com','source.unsplash.com'];
if(!in_array($host,$allowedHosts,true)){
  http_response_code(403); echo 'Host not allowed'; exit; }

// Basic cache headers (30 min)
header('Cache-Control: public, max-age=1800');
// ETag simplistic (hash of URL)
$etag = 'W/"'.substr(sha1($url),0,16).'"';
if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag){ http_response_code(304); exit; }

// Fetch remote (no curl extension assumption; attempt with file_get_contents)
$ctx = stream_context_create(['http'=>['timeout'=>6,'follow_location'=>1,'header'=>"User-Agent: DOKO-ImageProxy/1.0\r\nAccept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8\r\n"]]);
$imgData = @file_get_contents($url,false,$ctx);
if($imgData === false){ http_response_code(502); echo 'Upstream fetch failed'; exit; }

// Determine Content-Type from headers
$ctype = 'image/jpeg';
if(isset($http_response_header)){
  foreach($http_response_header as $h){
    if(stripos($h,'Content-Type:')===0){ $ctype = trim(substr($h,13)); break; }
  }
}
if(stripos($ctype,'image/')!==0){ http_response_code(415); echo 'Not an image'; exit; }

header('Content-Type: '.$ctype);
header('ETag: '.$etag);
// Small fingerprint header
header('X-Image-Proxy: 1');
// Pass length if available
header('Content-Length: '.strlen($imgData));

echo $imgData;
