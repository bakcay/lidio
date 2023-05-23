<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name lidio
 * 12.05.2023 01:11
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../../../init.php';
global $CONFIG;
$systemurl      = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
$paymentSuccess = $_REQUEST['Result'] == 'Success';
$invoiceid      = $_REQUEST['OrderId'];
if ($paymentSuccess) {
    $redirectPage = $systemurl . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true";
} else {
    $redirectPage = $systemurl . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true";
}
?>
<html>
<head>
    <title><?php echo $CONFIG['CompanyName'];?> - LidIO</title>
</head>
<body>
<div class="sk-cube-grid">
    <div class="sk-cube sk-cube1"></div>
    <div class="sk-cube sk-cube2"></div>
    <div class="sk-cube sk-cube3"></div>
    <div class="sk-cube sk-cube4"></div>
    <div class="sk-cube sk-cube5"></div>
    <div class="sk-cube sk-cube6"></div>
    <div class="sk-cube sk-cube7"></div>
    <div class="sk-cube sk-cube8"></div>
    <div class="sk-cube sk-cube9"></div>
</div>
<div style="min-width: 350px; font-family: sans-serif;font-size: 20px;padding-left: 58px;">
   <br>
    Yönlendiriliyorsunuz...<br>
    <?php
    if($paymentSuccess!=true){
        echo "İşleminiz Onaylanamadı. Tekrar denemek için faturanıza yönlendiriliyorsunuz.";
    }else{
        echo "İşleminiz başarılıdır. Faturanıza yönlendiriliyorsunuz.";
    }
    ?>
    <br>
    <br>
    <form name="frmResultPage" method="post" action="<?php echo $redirectPage; ?>" target="_parent">
        <input type="submit" value="Eğer 10 Saniye içersinde yönlendirilmezseniz lütfen buraya tıklayın">
    </form>
    <!-- Developer Bünyamin AKÇAY -->
</div>
<style>
    .sk-cube{background-color:#333!important}.sk-cube-grid{width:40px;height:40px;margin:21px 24px 100px 100px;float:left}.sk-cube-grid .sk-cube{width:33%;height:33%;background-color:#333;float:left;-webkit-animation:sk-cubeGridScaleDelay 1.3s infinite ease-in-out;animation:sk-cubeGridScaleDelay 1.3s infinite ease-in-out}.sk-cube-grid .sk-cube1{-webkit-animation-delay:.2s;animation-delay:.2s}.sk-cube-grid .sk-cube2{-webkit-animation-delay:.3s;animation-delay:.3s}.sk-cube-grid .sk-cube3{-webkit-animation-delay:.4s;animation-delay:.4s}.sk-cube-grid .sk-cube4{-webkit-animation-delay:.1s;animation-delay:.1s}.sk-cube-grid .sk-cube5{-webkit-animation-delay:.2s;animation-delay:.2s}.sk-cube-grid .sk-cube6{-webkit-animation-delay:.3s;animation-delay:.3s}.sk-cube-grid .sk-cube7{-webkit-animation-delay:0s;animation-delay:0s}.sk-cube-grid .sk-cube8{-webkit-animation-delay:.1s;animation-delay:.1s}.sk-cube-grid .sk-cube9{-webkit-animation-delay:.2s;animation-delay:.2s}@-webkit-keyframes sk-cubeGridScaleDelay{0%,100%,70%{-webkit-transform:scale3D(1,1,1);transform:scale3D(1,1,1)}35%{-webkit-transform:scale3D(0,0,1);transform:scale3D(0,0,1)}}@keyframes sk-cubeGridScaleDelay{0%,100%,70%{-webkit-transform:scale3D(1,1,1);transform:scale3D(1,1,1)}35%{-webkit-transform:scale3D(0,0,1);transform:scale3D(0,0,1)}}
</style>
<script type="text/javascript">
    window.setTimeout(function () {
        document.frmResultPage.submit();
    }, 10000)
</script>
</body>
</html>
