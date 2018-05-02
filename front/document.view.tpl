<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=99" />
	<link rel="shortcut icon" href="##favIconUrl##" />
	<title>##title##</title>
	
</head>
<body style="margin: 0; padding: 0; overflow:hidden;">

<form target="WebApplicationFrame" action="##urlsrc##" id="canvas_form" method="post">
  <input name="access_token" value="##access_token##" type="hidden" />
</form>
<iframe id="WebApplicationFrame" name="WebApplicationFrame" src="" width="100%" height="100%" style="position: relative; top: 0px; left: 0px; right: 0px; bottom: 0px" frameborder="0" marginwidth="0" marginheight="0"></iframe>
<script type="text/javascript">
  document.getElementById("canvas_form").submit()
</script>
<script type="text/javascript">
    window.onresize = ResizeWacFrame;
    document.body.onload = ResizeWacFrame;

    function ResizeWacFrame()
    {
        var wacFrame = document.getElementById('WebApplicationFrame');
        if (wacFrame)
        {
            var iframeHeight = GetWindowHeight() - wacFrame.offsetTop;
            if (iframeHeight > 0)
            {
                wacFrame.style.height = iframeHeight + 'px';
            }
            else
            {
                wacFrame.style.height = '0px';
            }
        }
    }

    function GetWindowHeight()
    {
        if (typeof(window.innerHeight) == 'number' &&
            !(document.documentMode && document.documentMode >= 10) &&
            navigator.userAgent.indexOf('iPad') == -1)
        {
            // Do not use in IE10 or iPad, as they report incorrect value when the on-screen keyboard is up
            // We should probably just always use the 'else if' block, but minimizing risk at this point in the cycle.
            return window.innerHeight;
        }
        else if (typeof(document.documentElement.clientHeight) == 'number')
        {
            // IE8, IE10 compatible, and iPad
            return document.documentElement.clientHeight;
        }

        return(0);
    }

    // resize the WAC iframe as soon as we can
    ResizeWacFrame();
</script>

</body>
</html>
