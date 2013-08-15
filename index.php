<?
error_reporting(1);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=400">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <!--this nifty little snippet positions the window perfectly for the 3DS screens.
        also check out: that viewport up on line 7, and #topscreen #bottomscreen CSS.-->
    <script>
    window.setInterval(function() {
      window.scrollTo(50,270);
    }, 50);
    </script>
    <title>imgur 3ds upload</title>
    <style>
      body{margin:0px;  font-family: sans-serif; background: black; color: white;}
      h1 {font-size: 14px; text-align: center; display: block; font-weight: normal; padding: 20px 10px;} 
      a {color: white;}
      #topscreen{width:500px;height:270px;overflow:hidden; background: url('./sprites.gif') -1px -301px; color: white;}
      #bottomscreen{width:400px;height:270px;overflow:hidden;margin:0 0 0 50px; position: relative;}
      .bottomnormal, .bottomsuccess{background: url('./sprites.gif') -1px -572px;}
      .bottomsuccess .status {color: #85BF25;}
      .btn {background: url('./sprites.gif') 0 -132px; width: 126px; height: 82px; display: inline-block; margin-right: 5px;}
      .previewimg {margin: 7px 0 0 8px;}
      .wait {width: 110px; height: 66px; background: url('./sprites.gif') 0 -66px; background-repeat: none; margin: 8px 0 0 7px;}
      .select {width: 110px; height: 66px; background: url('./sprites.gif') 0 0; background-repeat: none; margin: 7px 0 0 7px;}
      .file_container {width: 365px; height: 102px; margin: 16px 0 0 20px; overflow-x: auto; overflow-y: hidden;}
      .upload { width: 400px; height: 50px;}
      .disabled {background: url('./sprites.gif') -127px -200px;}
      .single { background: url('./sprites.gif') -127px 0px;}
      .multiple { background: url('./sprites.gif') -127px -50px;}
      .description {margin: 5px 0 17px 20px; width: 349px; height: 16px; padding: 7px; background: rgb(24,24,23); border: 1px solid #444442; border-radius: 4px; color: #888;}
      .files {margin-left: -1000px;}
      .viewlabel {font-size: 12px; margin-top: 10px; display: block;}
      .imglink {margin-top: 15px; display: block; font-size: 24px;}
      .imglink a {color: white;}
      .pleasewait {background: url('./sprites.gif') -127px -100px !important;}
      .uploadagain {background: url('./sprites.gif') -127px -150px !important;}
      .status {margin: 24px 0 0 22px; font-weight: bold;}
      .loginopts {margin: 20px; text-align: center; font-size: 36px; line-height: 60px;}
      .loginopts a { text-decoration: none;}
      #sbimg, #sbgal, #sbadd {display: inline-block; height: 50px; width: 132px; background:url('./sprites.gif'); background-position-y: -250px;}
      #sbimg {background-position-x: -128px; margin-right: 2px;}
      #sbgal {background-position-x: -262px; margin-right: 2px;}
      #sbadd {background-position-x: -396px;}
    </style>
</head>
<?
$client_id = 'REDACTED';
$client_secret = 'REDACTED';
//ok, lets check out the cookie to see if we've been here before. if not, we're gonna give the user the login screen all the way at the bottom of the file.
if ($_COOKIE['username']) {
    if ($_COOKIE['username'] == 'Anonymous') {
        $hs = 'Anonymous Upload <a href="https://api.imgur.com/oauth2/authorize?response_type=token&client_id=ec3d6a194cc8683" style="font-weight: normal; display: block; float: right; margin-right: 25px;">Login</a>';
        //we're starting an album here because an anonymous user needs to attach images to galleries on upload, and we need to upload immediately to get the preview image
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/album/');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer 6605cbb09c4b9ddad2e4e7f851abbba0580ce3f6'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'title' => ''));
        $reply = curl_exec($ch);
        curl_close($ch);
        $reply = json_decode($reply);
        $galleryid = $reply->data->id;
        $galleryhash = $reply->data->deletehash;
    } else {
        $hs = 'Uploading as: '.$_COOKIE['username'].' <a href="login.php?logout=1" style="font-weight: normal; display: block; float: right; margin-right: 25px;">Logout</a>';
        //we've got an actual user, so instead of creating a gallery we're refreshing the access token. not completely sure if this is 100% working...
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/oauth2/token/');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'refresh_token' => $_COOKIE['refreshtoken'],
                                                    'client_id' => $client_id,
                                                    'client_secret' => $client_secret,
                                                    'grant_type' => 'refresh_token'));
        $reply = curl_exec($ch);
        curl_close($ch);
        //echo $reply;
        $reply = json_decode($reply);
        setcookie('accesstoken', $reply->access_token, time()+3600);
        setcookie('refreshtoken', $reply->refresh_token, time()+3600);
        setcookie('username', $reply->account_username, time()+3600);
    }
?>
<body>
    <div id="topscreen"></div>
    <div id="bottomscreen" class="bottomnormal">
        <div class="status" id="status"><?=$hs?></div>
        <div class="file_container" id="file_container">
            <div style="height: 82px; display: inline-block; white-space: nowrap;">
                <div class="btn ubtn" ubtn="1" id="btn1">
                    <div id="status1" class="select"></div>
                </div>
                <!--the first of two hidden forms.  this one is where we're attaching our images for upload.
                    the other one is right below this and covers the final upload process. for anon users, we already
                    have an album created so we're including that, because anon uploads can only be attached
                    to an album at upload.-->
                <form method="post" enctype="multipart/form-data" id="form1" action="upload.php" target="upload">
                    <input type="file" class="files" name="upload1" id="file1" />
                    <input type="hidden" name="incrval" id="incrval" value="1">
                    <input type="hidden" name="galleryhash" id="galleryhash" value="<?=$galleryhash?>">
                </form>        
            </div>
        </div>
        <!--our second form - this one is doing a couple things. as we upload images, the imgjson and updatejson fields
        will change to keep track of what we're uploading for later. in addition, this form contains the title field that
        you see near the bottom of the page. basically, this will have all the data we need to finalize our album when we're 
        done uploading.-->
        <form method="post" enctype="multipart/form-data" id="masterform" action="upload.php" target="upload">
            <? 
                if ($_COOKIE['username'] && $_COOKIE['username']!='Anonymous'){
                    echo '<select id="gallerylist" name="gallerylist" style="position: absolute; top: 1px; visibility:hidden;">';
                    echo '<option value="">&nbsp;</option>';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/account/'.$_COOKIE['username'].'/albums');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    //curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
                    $reply = curl_exec($ch);
                    //echo $reply;
                    $reply = json_decode($reply);
                    foreach ($reply->data as $r){
                        if (!$r->title){$r->title = ' Untitled';}
                        echo "<option value='".$r->id."'>".$r->title." (".$r->id.")</option>";
                    }
                    echo '</select>';
                }
            ?>
            <input class="description" type="text" name="description" id="description" placeholder="Enter title (optional)">
            <input type="hidden" name="imgjson" id="imgjson" value='{"description":"","images":{}}'>
            <input type="hidden" name="uploadtype" id="uploadtype" value=''>
            <input type="hidden" name="updatejson" id="updatejson" value='{"images":{}}'>
            <input type="hidden" name="galleryval" id="galleryval" value='0'>
            <input type="hidden" name="galleryid" id="galleryid" value="<?=$galleryid?>">
            <input type="hidden" name="username" id="username" value="<?=$_COOKIE['username']?>">
            <input type="hidden" name="galleryhash" id="galleryhash" value="<?=$galleryhash?>">
        </form>
        <div class="upload disabled" id="finishbtn"></div>
    </div>
<script>
//this variable is keeping track of how many files we've uploaded, and what field we're on.
var incr = 1;
var oldthis = {};
//this function gets triggered when the file input field changes.
function input_change() {     
        if ( jQuery(this).parent().find('input[type="file"]:empty').length <= 1 ) {
            //replace upload with the waiting button so the user doesnt interrupt the upload process.
            $('#btn'+incr).removeClass('ubtn');       
            $('#status' + (incr)).removeClass('select').addClass('wait');
            incr++;
            if (incr>3 && $('#username').val()!='Anonymous' && $('#username').val()!=null){
                $('#finishchoices').html('');
                $('#finishchoices').attr("id","finishbtn-wait");
                $('#finishbtn-wait').addClass('upload pleasewait');
            } else {
                $('#finishbtn').addClass('pleasewait');
                $('#finishbtn').attr("id","finishbtn-wait");
            }
            //the actual form submission           
            $("#form1").submit();
            $("#incrval").val(incr);  
            if (incr == 2) {
                $("#finishbtn-wait").removeClass('disabled').addClass('single');
            }
            if (incr > 2) {
                $("#finishbtn-wait").removeClass('single').addClass('multiple');
            }
        }
    //}
}
//this gets called at the end of upload.php and adds a new upload button to the UI.
function addbutton() {
    $('#btn' + (incr-1)).after('<div class="btn ubtn" ubtn="' + incr + '" id="btn' + incr + '" ><div id="status' + incr + '" class="select"></div></div>');
}
$(document).ready(function(){
    //a bunch of custom triggers...
    //triggered when the file changes - checks that its not blank, and not the same.
    jQuery(document).on('change','input[type="file"]',function(){
        if (this.value != '' && this.value != oldthis.value) {
            oldthis.files = this.value;
            input_change(this);
        }
    });
    //select image button - triggers the 3DS image select dialog.
    $('.file_container').on('click', '.ubtn', function(){
        var filenum = $(this).attr('ubtn');
        $('#file1').click();
    });
    //image upload button - submit the 2nd form for final output.
    //we use the galleryval value to specify what kind of upload
    //we're making
    $('#bottomscreen').on('click', '.single', function() {
        images = jQuery.parseJSON($('#imgjson').val());
        galleryid = $('#galleryid').val();
        numimages = Object.keys(images.images).length;
        $("#galleryval").val('imgs');
        $("#masterform").submit();
    });
    //album upload button
    $('#bottomscreen').on('click', '.multiple', function() {
        $("#galleryval").val('anon-create');
        images = jQuery.parseJSON($('#imgjson').val());
        galleryid = $('#galleryid').val();
        numimages = Object.keys(images.images).length;
        $("#galleryval").val('create');
        $("#masterform").submit();
    });
    //upload again at final screen - reloads page
    $('#bottomscreen').on('click', '.uploadagain', function() {
        window.location.reload();
    });
    //logged in user - individual image upload
    $('#bottomscreen').on('click', '#sbimg', function() {
        $("#galleryval").val('imgs');
        $("#masterform").submit();
    });
    //logged in user - create new gallery
    $('#bottomscreen').on('click', '#sbgal', function() {
        $("#galleryval").val('create');
        $("#masterform").submit(); 
    });
    //logged in user - add to album. this one has another
    //nifty js snippet i found that triggers the select menu
    //dropdown. On a desktop browser, this bugs out because
    //i have visibility set to hidden, but on the 3DS it 
    //brings up the selection dialog perfectly!
    $('#bottomscreen').on('click', '#sbadd', function() {
        var element = $("#gallerylist")[0], worked = false;
        if (document.createEvent) { // all browsers
            var e = document.createEvent("MouseEvents");
            e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            worked = element.dispatchEvent(e);
        } else if (element.fireEvent) { // ie
            worked = element.fireEvent("onmousedown");
        }
        if (!worked) { // unknown browser / error
            alert("It didn't worked in your browser.");
        }
    });
    //triggered when an album is actually chosen from
    //the dropdown.
    jQuery(document).on('change','select[name="gallerylist"]',function(){
        $("#galleryval").val('add');
        $("#masterform").submit(); 
    });
});
</script>
<!--our hidden iframe - this is where our upload.php responses go, that trigger
    actions in the parent document.-->
<iframe src="upload.php" height="200" width="200" name="upload" style="display: none;"></iframe>
<?
} else {
?>
<!--this is what we show the user if they don't have a login cookie set.-->
<body>
    <div id="topscreen"></div>
    <div id="bottomscreen" class="bottomnormal">
        <div class="status">Please select:</div>
        <div class="loginopts">
          <a href="https://api.imgur.com/oauth2/authorize?response_type=token&client_id=ec3d6a194cc8683">login to imgur</a>
        <br>
        <a href="login.php?anon=1">anonymous upload</a>
        </div>
    </div>
<?
} 
?>
</body>
</html>