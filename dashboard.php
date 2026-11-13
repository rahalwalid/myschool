<?php require 'inc.php'; need_login(); $u=user();
if($u['role']==='teacher'){ header('Location: teacher.php'); exit; }
if($u['role']==='admin'){ header('Location: admin.php'); exit; }
$types=['lesson'=>['lessons','📚 الدروس'],'exam'=>['exams','📝 الامتحانات'],'video'=>['videos','🎬 الفيديوهات']];
$stats=[];
foreach($types as $k=>[$tb,$lbl]){
  $t=$pdo->prepare("SELECT COUNT(*) FROM $tb WHERE level=?"); $t->execute([$u['level']]); $total=(int)$t->fetchColumn();
  $d=$pdo->prepare("SELECT COUNT(*) FROM views v JOIN $tb x ON x.id=v.item_id WHERE v.user_id=? AND v.item_type=? AND x.level=?"); $d->execute([$u['id'],$k,$u['level']]); $done=(int)$d->fetchColumn();
  $stats[]=[$lbl,$done,$total,$total?round($done*100/$total):0];
}
$recent=$pdo->prepare("SELECT v.item_type,v.hits,v.last_at,COALESCE(l.title,e.title,x.title) title FROM views v
 LEFT JOIN lessons l ON v.item_type='lesson' AND l.id=v.item_id
 LEFT JOIN exams e ON v.item_type IN('exam','correction') AND e.id=v.item_id
 LEFT JOIN videos x ON v.item_type='video' AND x.id=v.item_id
 WHERE v.user_id=? ORDER BY v.last_at DESC LIMIT 10");
$recent->execute([$u['id']]); $recent=$recent->fetchAll();
$names=['lesson'=>'درس','exam'=>'امتحان','correction'=>'إصلاح','video'=>'فيديو'];
head('لوحتي'); ?>
<h1>👋 أهلاً <?=e($u['name'])?></h1><p><span class="tag"><?=e($u['level'])?></span> <a class="btn" href="chat.php">💬 تواصل مع الأساتذة</a></p>
<div class="grid"><?php foreach($stats as [$lbl,$done,$total,$pct]): ?>
<article class="card"><h3><?=$lbl?></h3><p><?=$done?> من <?=$total?> (<?=$pct?>%)</p>
<div class="bar"><span style="width:<?=$pct?>%"></span></div></article><?php endforeach; ?></div>
<h2>آخر نشاطاتك</h2><section class="card"><table>
<?php foreach($recent as $r): ?><tr><td><span class="tag"><?=$names[$r['item_type']]?></span></td><td><?=e($r['title']?:'(محذوف)')?></td><td><?=$r['hits']?>×</td><td><?=e($r['last_at'])?></td></tr>
<?php endforeach; if(!$recent) echo '<tr><td>لم تبدأ بعد، ابدأ بأول درس! 📚</td></tr>'; ?></table></section>
<?php foot(); ?>
