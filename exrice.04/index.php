<?php
session_start();
date_default_timezone_set('Africa/Algiers'); // لضبط الوقت


$presenceFile = "presence.txt";
if(isset($_POST['presence_submit'])){
    $name = trim($_POST['presence_name']);
    if($name!==""){
        $line = "$name | ".date("Y-m-d")." | ".date("H:i")."\n";
        file_put_contents($presenceFile, $line, FILE_APPEND);
    }
}
$presenceLines = file_exists($presenceFile) ? file($presenceFile) : [];


$journalDir = "journal";
if(!is_dir($journalDir)) mkdir($journalDir);
$journalFile = $journalDir."/".date("Y-m-d").".txt";
if(isset($_POST['journal_submit'])){
    $note = trim($_POST['journal_text']);
    if($note!==""){
        file_put_contents($journalFile, "[".date("H:i")."] ".$note."\n", FILE_APPEND);
    }
}


$todoFile = "tasks.txt";
if(!file_exists($todoFile)) file_put_contents($todoFile,"");
if(isset($_POST['todo_add'])){
    $task = trim($_POST['todo_text']);
    if($task!=="") file_put_contents($todoFile, $task."\n", FILE_APPEND);
}
if(isset($_POST['todo_remove'])){
    $idx = intval($_POST['todo_index']);
    $all = file($todoFile, FILE_IGNORE_NEW_LINES);
    if(isset($all[$idx])){
        unset($all[$idx]);
        file_put_contents($todoFile, implode("\n",$all).(count($all)? "\n" : ""));
    }
}
$tasks = file($todoFile, FILE_IGNORE_NEW_LINES);


$calcResult = "";
if(isset($_POST['calc_submit'])){
    $expr = $_POST['calc_expr'];
    $expr = str_replace(["×","÷"], ["*","/"], $expr);
    
    if(preg_match('/[^0-9+\-*/()., sincosqrtabs]/i',$expr)){
        $calcResult = "خطأ: رموز غير مسموح بها";
    } else {
        try{ $calcResult = eval("return $expr;"); } catch(Exception $e){ $calcResult="خطأ في التعبير"; }
    }
}


if(!isset($_SESSION['secret'])){
    $_SESSION['secret'] = rand(1,100);
    $_SESSION['attempts'] = 0;
}
$guessMessage = "";
if(isset($_POST['guess_submit'])){
    $g = intval($_POST['guess_number']);
    $_SESSION['attempts']++;
    if($g < $_SESSION['secret']) $guessMessage = "أكبر من $g";
    elseif($g > $_SESSION['secret']) $guessMessage = "أصغر من $g";
    else{
        $guessMessage = "مبروك! الرقم: ".$_SESSION['secret']." بعد ".$_SESSION['attempts']." محاولة";
        unset($_SESSION['secret']); unset($_SESSION['attempts']);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>كل التمارين في صفحة واحدة</title>
<style>
body{font-family:sans-serif; background:#f5f5f5; color:#222; padding:20px;}
section{background:#fff; padding:16px; border-radius:8px; margin-bottom:20px;}
input,textarea,button{padding:6px; margin-top:6px; width:100%; box-sizing:border-box;}
button{cursor:pointer; background:#2563eb; color:#fff; border:none; border-radius:4px;}
h2{margin-top:0;}
</style>
</head>
<body>

<h1>كل التمارين الخمسة في صفحة واحدة</h1>


<section>
<h2>1) حضور</h2>
<form method="post">
    الاسم: <input type="text" name="presence_name" required>
    <button name="presence_submit">تسجيل</button>
</form>
<h3>قائمة الحضور</h3>
<?php foreach($presenceLines as $l) echo $l."<br>"; ?>
</section>


<section>
<h2>2) يوميات</h2>
<form method="post">
    <textarea name="journal_text" rows="4" placeholder="اكتب ملاحظتك هنا..."></textarea>
    <button name="journal_submit">حفظ اليوميات</button>
</form>
<h3>مذكرة اليوم</h3>
<?php if(file_exists($journalFile)) foreach(file($journalFile) as $n) echo $n."<br>"; ?>
</section>


<section>
<h2>3) قائمة المهام</h2>
<form method="post">
    <input type="text" name="todo_text" placeholder="أضف مهمة جديدة">
    <button name="todo_add">إضافة</button>
</form>
<h3>المهام الحالية</h3>
<?php foreach($tasks as $i=>$t): ?>
    <?= htmlspecialchars($t) ?>
    <form method="post" style="display:inline">
        <input type="hidden" name="todo_index" value="<?= $i ?>">
        <button name="todo_remove" style="background:#ef4444;">حذف</button>
    </form>
    <br>
<?php endforeach; ?>
</section>


<section>
<h2>4) آلة حاسبة</h2>
<form method="post">
    <input type="text" name="calc_expr" placeholder="مثال: sqrt(9)+5">
    <button name="calc_submit">احسب</button>
</form>
<?php if($calcResult!=="") echo "<p>النتيجة: $calcResult</p>"; ?>
</section>


<section>
<h2>5) خمن الرقم</h2>
<form method="post">
    <input type="number" name="guess_number" min="1" max="100" placeholder="أدخل رقم من 1 إلى 100">
    <button name="guess_submit">جرب</button>
</form>
<?php if($guessMessage!=="") echo "<p>$guessMessage</p>"; ?>
</section>

</body>
</html>

