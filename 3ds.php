<?php session_start(); ?>
<HTML>
<BODY OnLoad="OnLoadEvent();">
<FORM name="downloadForm" action="<?php echo $_SESSION['acsurl'];?>" method="POST">
<NOSCRIPT>
<BR><BR><CENTER>
<H2>3-Dセキュア認証を続けます。<BR>
ボタンをクリックしてください。</h2>
<INPUT type="submit" value="OK">
</CENTER>
</NOSCRIPT>
<INPUT type="hidden" name="PaReq" value="<?php echo $_SESSION['PaReq'];?>">
<INPUT type="hidden" name="TermUrl" value="<?php echo $_SESSION['TermUrl'];?>">
<INPUT type="hidden" name="MD" value="<?php echo $_SESSION['MD'];?>">
</FORM>
<SCRIPT language="Javascript"><!--
function OnLoadEvent(){
document.downloadForm.submit();
}
//--></SCRIPT>
</BODY>
</HTML>