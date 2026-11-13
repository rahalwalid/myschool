<?php require 'inc.php'; need_login();
$s=$pdo->prepare('SELECT x.*,u.name tname FROM videos x LEFT JOIN users u ON u.id=x.teacher_id WHERE x.id=?'); $s->execute([(int)($_GET['id']??0)]); $v=$s->fetch();
if(!$v){ http_response_code(404); exit('الفيديو غير موجود'); }
log_view('video',$v['id']);
head($v['title']); ?>
<a class="btn alt" href="videos.php">← الفيديوهات</a>
<section class="card" style="margin-top:12px">
<?php if($v['video_file']): ?><video controls autoplay src="<?=e($v['video_file'])?>"></video>
<?php elseif($v['url'] && yt($v['url'])): ?><iframe src="<?=e(yt($v['url']))?>" allowfullscreen></iframe><?php endif; ?>
<h2><?=e($v['title'])?></h2><?php if($v['tname']) echo '<p class="by">👨‍🏫 '.e($v['tname']).'</p>'; ?><span class="tag"><?=e($v['subject'])?></span> <span class="tag"><?=e($v['level'])?></span>
<?php if($v['teacher_id'] && user()['role']==='student'): ?><p><a class="btn ask" href="chat.php?with=<?=$v['teacher_id']?>&about=<?=urlencode($v['title'])?>">💬 اسأل الأستاذ</a></p><?php endif; ?></section>
<?php foot(); ?>
