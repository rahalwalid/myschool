<?php require 'inc.php'; need_staff(); $adm=user()['role']==='admin'; $me=(int)user()['id'];
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf(); $a=$_POST['action']??'';
  $t=trim($_POST['title']??''); $sub=trim($_POST['subject']??''); $l=$_POST['level']??'';
  if(in_array($a,['lesson','exam','video']) && ($t===''||$sub===''||!in_array($l,$LEVELS))) $msg='أكمل جميع الحقول.';
  elseif($a==='lesson'){
    $f=upload('file','lessons',['pdf'],20);
    if($f){ $pdo->prepare('INSERT INTO lessons(title,subject,level,file,teacher_id) VALUES(?,?,?,?,?)')->execute([$t,$sub,$l,$f,$me]); $msg='تمت إضافة الدرس ✔'; } else $msg='ارفع ملف PDF (حتى 20MB).';
  } elseif($a==='exam'){
    $f=upload('file','exams',['pdf'],20); $c=upload('correction','exams',['pdf'],20);
    if($f){ $pdo->prepare('INSERT INTO exams(title,subject,level,file,correction,teacher_id) VALUES(?,?,?,?,?,?)')->execute([$t,$sub,$l,$f,$c,$me]); $msg='تمت إضافة الامتحان ✔'; } else $msg='ارفع ملف الامتحان PDF.';
  } elseif($a==='video'){
    $u=trim($_POST['url']??''); $f=upload('video','videos',['mp4','webm'],200);
    if($f||$u){ $pdo->prepare('INSERT INTO videos(title,subject,level,url,video_file,teacher_id) VALUES(?,?,?,?,?,?)')->execute([$t,$sub,$l,$u?:null,$f,$me]); $msg='تمت إضافة الفيديو ✔'; } else $msg='ضع رابط يوتيوب أو ارفع ملف فيديو.';
  } elseif($a==='teacher' && $adm){
    $m=trim($_POST['email']); $p=$_POST['password'];
    if($t===''||!filter_var($m,FILTER_VALIDATE_EMAIL)||strlen($p)<6) $msg='تحقق من بيانات الأستاذ.';
    else{ $c=$pdo->prepare('SELECT id FROM users WHERE email=?'); $c->execute([$m]);
      if($c->fetch()) $msg='البريد مسجل مسبقًا.';
      else{ $pdo->prepare("INSERT INTO users(name,email,password,level,role,subject) VALUES(?,?,?,'-','teacher',?)")->execute([$t,$m,password_hash($p,PASSWORD_DEFAULT),$sub]); $msg='تم إنشاء حساب الأستاذ ✔'; } }
  } elseif($a==='delete'){
    $cols=['lessons'=>['file'],'exams'=>['file','correction'],'videos'=>['video_file']]; $tb=$_POST['table']??'';
    if(isset($cols[$tb])){
      $s=$pdo->prepare("SELECT * FROM $tb WHERE id=?"); $s->execute([(int)$_POST['id']]); $r=$s->fetch(); if($r && !$adm && $r['teacher_id']!=$me) $r=null;
      if($r){ foreach($cols[$tb] as $c) if($r[$c] && is_file(__DIR__.'/'.$r[$c])) unlink(__DIR__.'/'.$r[$c]);
        $ty=['lessons'=>"'lesson'",'exams'=>"'exam','correction'",'videos'=>"'video'"][$tb];
        $pdo->prepare("DELETE FROM views WHERE item_id=? AND item_type IN($ty)")->execute([(int)$_POST['id']]);
        $pdo->prepare("DELETE FROM $tb WHERE id=?")->execute([(int)$_POST['id']]); $msg='تم الحذف.'; }
    }
  }
}
$opts=''; foreach($LEVELS as $x) $opts.='<option>'.e($x).'</option>';
$students=$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
head('لوحة التحكم'); ?>
<h1><?=$adm?'⚙️ لوحة تحكم المدير':'⬆️ رفع المحتوى'?></h1>
<?php if($msg): ?><p class="alert"><?=e($msg)?></p><?php endif; ?>
<p class="tag">عدد التلاميذ المسجلين: <?=$students?></p>
<div class="tabs"><button class="tab active" data-t="a1">درس</button><button class="tab" data-t="a2">امتحان</button><button class="tab" data-t="a3">فيديو</button><button class="tab" data-t="a4">إدارة المحتوى</button><?php if($adm): ?><button class="tab" data-t="a5">📊 المتابعة</button><button class="tab" data-t="a6">👨‍🏫 الأساتذة</button><?php endif; ?></div>

<form method="post" enctype="multipart/form-data" class="card pane" id="a1"><?=csrf()?><input type="hidden" name="action" value="lesson">
<input name="title" placeholder="عنوان الدرس" required><input name="subject" placeholder="المادة" required>
<select name="level" required><option value="">المستوى</option><?=$opts?></select>
<label>ملف الدرس (PDF)<input type="file" name="file" accept=".pdf" required></label><button>رفع الدرس</button></form>

<form method="post" enctype="multipart/form-data" class="card pane" id="a2" hidden><?=csrf()?><input type="hidden" name="action" value="exam">
<input name="title" placeholder="عنوان الامتحان" required><input name="subject" placeholder="المادة" required>
<select name="level" required><option value="">المستوى</option><?=$opts?></select>
<label>ملف الامتحان (PDF)<input type="file" name="file" accept=".pdf" required></label>
<label>ملف الإصلاح (PDF)<input type="file" name="correction" accept=".pdf"></label><button>رفع الامتحان</button></form>

<form method="post" enctype="multipart/form-data" class="card pane" id="a3" hidden><?=csrf()?><input type="hidden" name="action" value="video">
<input name="title" placeholder="عنوان الفيديو" required><input name="subject" placeholder="المادة" required>
<select name="level" required><option value="">المستوى</option><?=$opts?></select>
<input name="url" placeholder="رابط يوتيوب (اختياري)"><label>أو ارفع فيديو (mp4/webm)<input type="file" name="video" accept=".mp4,.webm"></label><button>إضافة الفيديو</button></form>

<div class="card pane" id="a4" hidden>
<?php foreach(['lessons'=>'الدروس','exams'=>'الامتحانات','videos'=>'الفيديوهات'] as $tb=>$lbl): ?>
<h3><?=$lbl?></h3><table><?php foreach($pdo->query("SELECT id,title,level FROM $tb ".($adm?"":"WHERE teacher_id=$me")." ORDER BY id DESC") as $r): ?>
<tr><td><?=e($r['title'])?></td><td><?=e($r['level'])?></td><td>
<form method="post" onsubmit="return confirm('حذف نهائي؟')"><?=csrf()?><input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="<?=$tb?>"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="danger">حذف</button></form></td></tr>
<?php endforeach; ?></table><?php endforeach; ?></div>
<?php if($adm): ?><div class="card pane" id="a5" hidden><h3>متابعة التلاميذ</h3><div style="overflow-x:auto"><table>
<tr><th>التلميذ</th><th>المستوى</th><th>عناصر مشاهدة</th><th>إجمالي المشاهدات</th><th>آخر نشاط</th></tr>
<?php foreach($pdo->query("SELECT u.name,u.level,COUNT(v.item_id) items,COALESCE(SUM(v.hits),0) total,MAX(v.last_at) last FROM users u LEFT JOIN views v ON v.user_id=u.id WHERE u.role='student' GROUP BY u.id ORDER BY total DESC") as $r): ?>
<tr><td><?=e($r['name'])?></td><td><?=e($r['level'])?></td><td><?=$r['items']?></td><td><?=$r['total']?></td><td><?=e($r['last']?:'—')?></td></tr>
<?php endforeach; ?></table></div></div>
<div class="card pane" id="a6" hidden><form method="post"><?=csrf()?><input type="hidden" name="action" value="teacher">
<input name="title" placeholder="اسم الأستاذ" required><input name="subject" placeholder="المادة" required>
<input type="email" name="email" placeholder="البريد الإلكتروني" required><input type="password" name="password" placeholder="كلمة المرور (6+)" minlength="6" required><button>إنشاء حساب أستاذ</button></form>
<table><?php foreach($pdo->query("SELECT name,email,subject FROM users WHERE role='teacher'") as $r): ?><tr><td><?=e($r['name'])?></td><td><?=e($r['subject'])?></td><td><?=e($r['email'])?></td></tr><?php endforeach; ?></table></div>
<?php endif; ?>
<?php foot(); ?>
