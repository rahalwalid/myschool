<?php require 'inc.php';
if(user()){ header('Location: dashboard.php'); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  if($_POST['do']==='register'){
    $n=trim($_POST['name']); $m=trim($_POST['email']); $p=$_POST['password']; $l=$_POST['level'];
    if($n===''||!filter_var($m,FILTER_VALIDATE_EMAIL)||strlen($p)<6||!in_array($l,$LEVELS)) $err='تحقق من البيانات (كلمة المرور 6 أحرف على الأقل).';
    else{
      $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$m]);
      if($s->fetch()) $err='هذا البريد مسجل مسبقًا.';
      else{
        $role = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()==0 ? 'admin':'student'; // أول حساب = مدير
        $pdo->prepare('INSERT INTO users(name,email,password,level,role) VALUES(?,?,?,?,?)')->execute([$n,$m,password_hash($p,PASSWORD_DEFAULT),$l,$role]);
        $s=$pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$m]);
        session_regenerate_id(true); $_SESSION['user']=$s->fetch(); header('Location: dashboard.php'); exit;
      }
    }
  } else {
    $s=$pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([trim($_POST['email'])]); $u=$s->fetch();
    if($u && password_verify($_POST['password'],$u['password'])){ session_regenerate_id(true); $_SESSION['user']=$u; header('Location: dashboard.php'); exit; }
    $err='البريد أو كلمة المرور غير صحيحة.';
  }
}
head('الدخول'); ?>
<section class="card auth">
<h1>مرحبًا بك في My School</h1>
<?php if($err): ?><p class="alert"><?=e($err)?></p><?php endif; ?>
<div class="tabs"><button class="tab active" data-t="login">تسجيل الدخول</button><button class="tab" data-t="register">تلميذ جديد</button></div>
<form method="post" class="pane" id="login"><?=csrf()?><input type="hidden" name="do" value="login">
<input type="email" name="email" placeholder="البريد الإلكتروني" required>
<input type="password" name="password" placeholder="كلمة المرور" required><button>دخول</button></form>
<form method="post" class="pane" id="register" hidden><?=csrf()?><input type="hidden" name="do" value="register">
<input name="name" placeholder="الاسم الكامل" required>
<input type="email" name="email" placeholder="البريد الإلكتروني" required>
<input type="password" name="password" placeholder="كلمة المرور (6 أحرف+)" minlength="6" required>
<select name="level" required><option value="">المستوى الدراسي</option><?php foreach($LEVELS as $l) echo '<option>'.e($l).'</option>'; ?></select>
<button>إنشاء حساب</button></form>
</section>
<?php foot(); ?>
