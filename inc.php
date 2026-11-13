<?php
session_start();
// ===== إعدادات قاعدة البيانات (عدّلها حسب استضافتك) =====
$pdo = new PDO('mysql:host=localhost;dbname=myschoool;charset=utf8mb4','root','',[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

$LEVELS = ['باكالوريا العلوم التجريبية','باكالوريا الرياضيات','باكالوريا الآداب','باكالوريا العلوم التقنية','باكالوريا الإقتصاد و التصرف','باكالوريا الرياضة','التكوين في : اللغة الأنجليزية','اللغة الألمانية','اللغة الإيطالية'];

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function user(){ return $_SESSION['user'] ?? null; }
function need_login(){ if(!user()){ header('Location: index.php'); exit; } }
function need_admin(){ need_login(); if(user()['role']!=='admin'){ http_response_code(403); exit('غير مسموح'); } }
function csrf(){ if(empty($_SESSION['t'])) $_SESSION['t']=bin2hex(random_bytes(16)); return '<input type="hidden" name="t" value="'.$_SESSION['t'].'">'; }
function check_csrf(){ if(!hash_equals($_SESSION['t']??'', $_POST['t']??'')) exit('طلب غير صالح'); }

function upload($field,$sub,$exts,$mb){
  if(empty($_FILES[$field]) || $_FILES[$field]['error']!==UPLOAD_ERR_OK) return null;
  $x = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
  if(!in_array($x,$exts) || $_FILES[$field]['size']>$mb*1048576) return null;
  $dir = __DIR__."/uploads/$sub"; if(!is_dir($dir)) mkdir($dir,0755,true);
  $name = bin2hex(random_bytes(8)).'.'.$x;
  return move_uploaded_file($_FILES[$field]['tmp_name'],"$dir/$name") ? "uploads/$sub/$name" : null;
}

function head($title){ $u=user(); ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | My School</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"></head><body>
<header class="top"><a class="logo" href="index.php">🎓 My School</a>
<?php if($u): ?><nav>
<a href="dashboard.php">👤 لوحتي</a><a href="lessons.php">📚 الدروس</a><a href="exams.php">📝 الامتحانات</a><a href="videos.php">🎬 الفيديوهات</a>
<?php if($u['role']==='teacher'): ?><a href="teacher.php">🧑‍🏫 نافذة الأستاذ</a><a href="admin.php">⬆️ رفع المحتوى</a><?php endif; ?>
<?php if($u['role']==='admin'): ?><a href="admin.php">⚙️ التحكم</a><?php endif; ?>
<a href="chat.php">💬 الدردشة<?php if($n=unread()) echo ' <b class="badge">'.$n.'</b>'; ?></a>
<a href="logout.php">خروج (<?=e($u['name'])?>)</a></nav><?php endif; ?></header><main>
<?php }
function foot(){ echo '</main><footer>© '.date('Y').' My School</footer><script src="script.js"></script></body></html>'; }

function log_view($type,$id){ global $pdo; $u=user(); if(!$u||$u['role']!=='student') return;
  $pdo->prepare('INSERT INTO views(user_id,item_type,item_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE hits=hits+1,last_at=NOW()')->execute([$u['id'],$type,$id]); }
function seen($type){ global $pdo; $s=$pdo->prepare('SELECT item_id FROM views WHERE user_id=? AND item_type=?'); $s->execute([user()['id'],$type]); return $s->fetchAll(PDO::FETCH_COLUMN); }
function yt($u){ return preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([\w-]{11})~',$u,$m) ? 'https://www.youtube.com/embed/'.$m[1] : null; }

function need_staff(){ need_login(); if(!in_array(user()['role'],['teacher','admin'])){ http_response_code(403); exit('غير مسموح'); } }
function unread(){ static $n=null; global $pdo; if(!user()) return 0;
  if($n===null){ $s=$pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0'); $s->execute([user()['id']]); $n=(int)$s->fetchColumn(); } return $n; }
// المحادثة مسموحة فقط بين تلميذ وأستاذ/مدير
function chat_partner($id){ global $pdo; $me=user(); $s=$pdo->prepare('SELECT id,name,role,subject,level FROM users WHERE id=?'); $s->execute([(int)$id]); $o=$s->fetch();
  if(!$o) return null; $a=in_array($me['role'],['teacher','admin']); $b=in_array($o['role'],['teacher','admin']); return $a!==$b ? $o : null; }
