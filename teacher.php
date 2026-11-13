<?php require 'inc.php'; need_staff(); $u=user(); $me=(int)$u['id'];
$cnt=[]; foreach(['lessons'=>'📚 دروسي','exams'=>'📝 امتحاناتي','videos'=>'🎬 فيديوهاتي'] as $tb=>$lbl){
  $s=$pdo->prepare("SELECT COUNT(*) FROM $tb WHERE teacher_id=?"); $s->execute([$me]); $cnt[$lbl]=(int)$s->fetchColumn(); }
$J="FROM views v LEFT JOIN lessons l ON v.item_type='lesson' AND l.id=v.item_id
 LEFT JOIN exams e ON v.item_type IN('exam','correction') AND e.id=v.item_id
 LEFT JOIN videos x ON v.item_type='video' AND x.id=v.item_id
 WHERE (l.teacher_id=? OR e.teacher_id=? OR x.teacher_id=?)";
$s=$pdo->prepare("SELECT COUNT(DISTINCT v.user_id) st,COALESCE(SUM(v.hits),0) h $J"); $s->execute([$me,$me,$me]); $tot=$s->fetch();
$s=$pdo->prepare("SELECT v.item_type,COALESCE(l.title,e.title,x.title) title,SUM(v.hits) h,COUNT(*) st $J GROUP BY v.item_type,v.item_id,title ORDER BY h DESC LIMIT 5");
$s->execute([$me,$me,$me]); $top=$s->fetchAll();
$s=$pdo->prepare("SELECT m.body,m.created_at,m.is_read,u.id,u.name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.receiver_id=? ORDER BY m.id DESC LIMIT 5");
$s->execute([$me]); $msgs=$s->fetchAll();
$names=['lesson'=>'درس','exam'=>'امتحان','correction'=>'إصلاح','video'=>'فيديو'];
head('نافذة الأستاذ'); ?>
<h1>🧑‍🏫 نافذة الأستاذ: <?=e($u['name'])?></h1>
<p><a class="btn" href="admin.php">⬆️ رفع محتوى جديد</a> <a class="btn alt" href="chat.php">💬 الرسائل <?php if(unread()) echo '('.unread().')'; ?></a></p>
<div class="grid">
<?php foreach($cnt as $l=>$n): ?><article class="card"><h3><?=$l?></h3><p class="big"><?=$n?></p></article><?php endforeach; ?>
<article class="card"><h3>👥 تلاميذ تفاعلوا</h3><p class="big"><?=$tot['st']?></p></article>
<article class="card"><h3>👁️ إجمالي المشاهدات</h3><p class="big"><?=$tot['h']?></p></article></div>
<h2>🔥 الأكثر مشاهدة</h2><section class="card"><table>
<?php foreach($top as $r): ?><tr><td><span class="tag"><?=$names[$r['item_type']]?></span></td><td><?=e($r['title'])?></td><td><?=$r['h']?> مشاهدة</td><td><?=$r['st']?> تلميذ</td></tr>
<?php endforeach; if(!$top) echo '<tr><td>لا توجد مشاهدات بعد.</td></tr>'; ?></table></section>
<h2>💬 آخر الرسائل</h2><section class="card"><table>
<?php foreach($msgs as $m): ?><tr><td><a href="chat.php?with=<?=$m['id']?>"><?=e($m['name'])?></a> <?php if(!$m['is_read']) echo '<b class="badge">جديد</b>'; ?></td><td><?=e($m['body']!==''?mb_strimwidth($m['body'],0,60,'…'):'📎 مرفق')?></td><td><?=e($m['created_at'])?></td></tr>
<?php endforeach; if(!$msgs) echo '<tr><td>لا توجد رسائل.</td></tr>'; ?></table></section>
<?php foot(); ?>
