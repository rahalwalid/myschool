<?php require 'inc.php'; need_login();
$s=$pdo->prepare('SELECT file,file_name FROM messages WHERE id=? AND (sender_id=? OR receiver_id=?)');
$s->execute([(int)($_GET['id']??0),user()['id'],user()['id']]); $m=$s->fetch();
$p=$m?__DIR__.'/'.$m['file']:'';
if(!$m||!$m['file']||!is_file($p)){ http_response_code(404); exit('غير موجود'); }
$x=strtolower(pathinfo($p,PATHINFO_EXTENSION));
$mime=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','pdf'=>'application/pdf'][$x]??'application/octet-stream';
header('Content-Type: '.$mime); header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, max-age=3600');
header('Content-Disposition: '.(isset($_GET['dl'])?'attachment':'inline')."; filename*=UTF-8''".rawurlencode($m['file_name']?:basename($p)));
header('Content-Length: '.filesize($p)); readfile($p);
