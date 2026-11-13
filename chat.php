<?php require 'inc.php'; need_login(); $me=user(); $staff=$me['role']!=='student';
$w=(int)($_GET['with']??0); $p=$w?chat_partner($w):null;
$roles=$staff?"u.role='student'":"u.role IN('teacher','admin')";
$s=$pdo->prepare("SELECT u.id,u.name,u.level,u.subject,u.role,
 (SELECT COUNT(*) FROM messages m WHERE m.sender_id=u.id AND m.receiver_id=? AND m.is_read=0) unread,
 (SELECT MAX(m.id) FROM messages m WHERE (m.sender_id=u.id AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=u.id)) last_id
 FROM users u WHERE $roles AND u.id<>? ORDER BY last_id IS NULL, last_id DESC, u.name");
$s->execute([$me['id'],$me['id'],$me['id'],$me['id']]); $contacts=$s->fetchAll();
head('الدردشة'); ?>
<h1>💬 الدردشة</h1>
<div class="chat-wrap <?=$p?'has-chat':''?>">
<aside class="card contacts"><?php foreach($contacts as $c): ?>
<a class="contact <?=$c['id']==$w?'on':''?>" href="chat.php?with=<?=$c['id']?>"><span><?=e($c['name'])?><small><?=e($staff?$c['level']:($c['role']==='admin'?'الإدارة':$c['subject']))?></small></span>
<?php if($c['unread']) echo '<b class="badge">'.$c['unread'].'</b>'; ?></a>
<?php endforeach; if(!$contacts) echo '<p>لا توجد جهات اتصال.</p>'; ?></aside>
<section class="card panel">
<?php if($p): ?>
<div class="chead"><a class="back" href="chat.php">←</a> <b><?=e($p['name'])?></b></div>
<div id="msgs" class="msgs" data-with="<?=$p['id']?>" data-me="<?=$me['id']?>" data-t="<?=e($_SESSION['t']??'')?>"></div>
<?=csrf()?>
<form id="send" class="sendbar"><input name="body" maxlength="1000" placeholder="اكتب رسالتك..." autocomplete="off" autofocus value="<?=e(isset($_GET['about'])?'بخصوص: '.mb_substr($_GET['about'],0,100).' — ':'')?>"><label class="attach" title="إرفاق صورة أو PDF">📎<input type="file" id="file" accept="image/*,.pdf" hidden></label><span id="fname"></span><button>إرسال</button></form>
<script>document.getElementById('msgs').dataset.t=document.querySelector('input[name=t]').value</script>
<script src="chat.js"></script>
<?php else: ?><p class="hint">اختر محادثة من القائمة لبدء الدردشة.</p><?php endif; ?>
</section></div>
<?php foot(); ?>
