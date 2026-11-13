<?php require 'inc.php'; need_login();
$lv=$_GET['level']??''; $q=$lv?'WHERE x.level=?':'';
$s=$pdo->prepare("SELECT x.*,u.name tname FROM exams x LEFT JOIN users u ON u.id=x.teacher_id $q ORDER BY x.id DESC"); $s->execute($lv?[$lv]:[]); $rows=$s->fetchAll(); $seen=seen('exam');
head('الامتحانات'); ?>
<h1>📝 الامتحانات التقييمية</h1>
<form class="filter" method="get"><select name="level" onchange="this.form.submit()"><option value="">كل المستويات</option>
<?php foreach($LEVELS as $l) echo '<option '.($l===$lv?'selected':'').'>'.e($l).'</option>'; ?></select></form>
<div class="grid"><?php foreach($rows as $r): ?>
<article class="card"><h3><?=e($r['title'])?></h3><?php if(in_array($r['id'],$seen)) echo '<span class="tag done">✓ تمت المشاهدة</span>'; ?><?php if($r['tname']) echo '<p class="by">👨‍🏫 '.e($r['tname']).'</p>'; ?><p><span class="tag"><?=e($r['subject'])?></span> <span class="tag"><?=e($r['level'])?></span></p>
<a class="btn" href="open.php?t=exam&id=<?=$r['id']?>" target="_blank">الامتحان</a>
<?php if($r['correction']): ?><a class="btn ok" href="open.php?t=correction&id=<?=$r['id']?>" target="_blank">الإصلاح ✔</a><?php endif; ?><?php if($r['teacher_id'] && user()['role']==='student'): ?> <a class="btn ask" href="chat.php?with=<?=$r['teacher_id']?>&about=<?=urlencode($r['title'])?>">💬 اسأل الأستاذ</a><?php endif; ?></article>
<?php endforeach; if(!$rows) echo '<p>لا توجد امتحانات بعد.</p>'; ?></div>
<?php foot(); ?>
