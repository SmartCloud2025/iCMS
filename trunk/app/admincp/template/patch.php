<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2003 2013-07-22 07:27:56Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>

<div class="iCMS-container">
  <div class="well iCMS-well iCMS-patch">
    <div id="log"></div>
    <?php if($_GET['do']=="update"){?>
    <div class="form-actions"> <a class="btn btn-large" href="<?php echo APP_URI; ?>&do=install"><i class="fa fa-wrench"></i> 开始升级</a> </div>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
var log = "<?php echo $this->msg; ?>";
var n = 0;
var timer = 0;
log = log.split('<iCMS>');
setIntervals();
function GoPlay(){
	if (n > log.length-1) {
		n=-1;
		clearIntervals();
	}
	if (n > -1) {
		postcheck(n);
		n++;
	}
}
function postcheck(n){
	log[n]=log[n].replace('#','<br />');
	document.getElementById('log').innerHTML += log[n] + '<br /><a name="last"></a>';
	document.getElementById('log').scrollTop = document.getElementById('log').scrollHeight;
}
function setIntervals(){
	timer = setInterval('GoPlay()',100);
}
function clearIntervals(){
	clearInterval(timer);
	finish();
}
</script>
<?php iACP::foot();?>
