<html>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type"text/javascript">
<?
error_reporting(1);
if ($_REQUEST['logout']) {
//logout user
?>
setCookie('accesstoken', '', 30);
setCookie('refreshtoken', '', 30);
setCookie('username', '', 30);
<?
} else if ($_GET['anon']) {
//login anon
?>
setCookie('accesstoken', 'anon', 30);
setCookie('refreshtoken', 'anon', 30);
setCookie('username', 'Anonymous', 30);
<?
} else {
//imgur response
?>
var params = {}, queryString = location.hash.substring(1),
    regex = /([^&=]+)=([^&]*)/g, m;
while (m = regex.exec(queryString)) {
  params[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
}
setCookie('accesstoken', params['access_token'], 30);
setCookie('refreshtoken', params['refresh_token'], 30);
setCookie('username', params['account_username'], 30);
<?
}
?>
window.location = "http://www.webrender.net/imgur/";
function setCookie(c_name,value,exdays)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}
</script>