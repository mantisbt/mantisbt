<?php
header('Content-Type: text/html; charset=utf-8');
?>
<html>
<head>
<title>PHPUTF8 Tests</title>
</head>
<body>
<ul>
<li>
    <a href="runtests.php">RUN ALL TESTS</a>
    <a href="runtests.php?engine=mbstring">[mbstring]</a>
    <a href="runtests.php?engine=native">[native]</a>
</li>
</ul>
<ul>
<?php
    $path = dirname(__FILE__).'/cases';
    if ( $d = opendir($path) ) {
        while (($file = readdir($d)) !== false) {
            if ( is_file($path.'/'.$file) ) {
                $farray = explode('.',$file);
                if ( $farray[1] == 'test' ) {
?>
<li>
    <a href="./cases/<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></a>
    <a href="./cases/<?php echo htmlspecialchars($file); ?>?engine=mbstring">[mbstring]</a>
    <a href="./cases/<?php echo htmlspecialchars($file); ?>?engine=native">[native]</a>
    </li>
<?php
                }
            }
        }
        closedir($d);
    }
?>
</ul>
</body>
</html>