<?php require 'inc.php'; header('Content-Type: application/json; charset=utf-8');
if(!user()){ http_response_code(401); exit('{}'); }
$me=(int)user()['id']; $w=(int)($_REQUEST['with']??0);
if(!chat_partner($w)){ http_response_code(403); exit('{"error":"forbidden"}'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf(); $b=trim($_POST['body']??'');
  $f=null; $fn=null;
  if(!empty($_FILES['file']) && $_FILES['file']['error']!==UPLOAD_ERR_NO_FILE){
    $fx=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    $ok=$_FILES['file']['error']===UPLOAD_ERR_OK && in_array($fx,['jpg','jpeg','png','gif','webp','pdf']);
    if($ok && $fx!=='pdf') $ok=@getimagesize($_FILES['file']['tmp_name'])!==false;
    $f=$ok ? upload('file','chat',['jpg','jpeg','png','gif','webp','pdf'],$fx==='pdf'?10:5) : null;
    if(!$f){ http_response_code(400); exit('{"error":"file"}'); }
    $fn=mb_substr(preg_replace('/[\r\n\/\\\\]/','',$_FILES['file']['name']),0,100);
  }
  if(($b===''&&!$f)||mb_strlen($b)>1000){ http_response_code(400); exit('{"error":"invalid"}'); }
  $pdo->prepare('INSERT INTO messages(sender_id,receiver_id,body,file,file_name) VALUES(?,?,?,?,?)')->execute([$me,$w,$b,$f,$fn]);
  echo json_encode(['id'=>$pdo->lastInsertId()]); exit;
}
$after=(int)($_GET['after']??0);
$q=$after ? 'SELECT id,sender_id,body,file,file_name,created_at FROM messages WHERE id>? AND ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) ORDER BY id LIMIT 100'
          : 'SELECT * FROM (SELECT id,sender_id,body,file,file_name,created_at FROM messages WHERE id>? AND ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) ORDER BY id DESC LIMIT 50) t ORDER BY id';
$s=$pdo->prepare($q); $s->execute([$after,$me,$w,$w,$me]);
$pdo->prepare('UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0')->execute([$w,$me]);
$ms=$s->fetchAll();
foreach($ms as &$m){ $m['kind']=$m['file']?(strtolower(pathinfo($m['file'],PATHINFO_EXTENSION))==='pdf'?'pdf':'image'):null; unset($m['file']); }
echo json_encode(['messages'=>$ms]);
