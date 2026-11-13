<?php require 'inc.php'; need_login();
$t=$_GET['t']??''; $id=(int)($_GET['id']??0);
$map=['lesson'=>['lessons','file'],'exam'=>['exams','file'],'correction'=>['exams','correction']];
if(!isset($map[$t])) exit('غير موجود');
[$tb,$col]=$map[$t];
$s=$pdo->prepare("SELECT $col f FROM $tb WHERE id=?"); $s->execute([$id]); $f=$s->fetchColumn();
$p=__DIR__.'/'.$f;
if(!$f||!is_file($p)){ http_response_code(404); exit('الملف غير موجود'); }
log_view($t,$id);
header('Content-Type: application/pdf');
header('Content-Disposition: '.(isset($_GET['dl'])?'attachment':'inline').'; filename="'.$t.'-'.$id.'.pdf"');
header('Content-Length: '.filesize($p));
readfile($p);
