<head>
	<title>guest@sekrim</title>
	<link rel="stylesheet" href="style.css" type="text/css"/>
    <link href="css/jquery.terminal.css" rel="stylesheet"/>
	
	<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
    <script src="js/jquery.mousewheel-min.js"></script>
    <script src="js/jquery.terminal-0.7.12.js"></script>
    <script src="js/code.js"></script>
    
</head>
<script>
var files = new Array();
<?php
    if ($handle = opendir('./files')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
			?>files.push("<?php echo $entry ?>");<?php
            }
        }
        closedir($handle);
   }
?>
var html = '';
<?php $html = file_get_contents('http://sekrim.com/feed'); 
$items = substr_count($html,"<content:encoded>"); ?>
html = <?php echo json_encode($html); ?>;
var items = [];
var splittedHTML = html.split("content:encoded>");
var position = 0;
for (i = 0; i < splittedHTML.length; i++) {
	if (i % 2 == 1) {
		items.push(splittedHTML[i]);
		position++;
	}
	/*if (lines[i].contains("<content:encoded>")) {
		var start = lines[i].indexOf("<content:encoded>") + 17;
	}
	if (lines[i],contains("</content:encoded>")) {
		var end = lines[i].indexOf("</content:encoded>");
		items[position] = html.substring(start,end);;
		position++;
	}*/
	
}
for (i = 0; i < items.length; i++) {
	items[i] = items[i].replace(/<\/?[^>]+(>|$)/g, "");
	items[i] = items[i].replace("]]>","");
	items[i] = items[i].replace("&nbsp","");
} 
</script>
<script>jQuery(function($, undefined) {
    $('#term').terminal(function(command, term) {
		var words = command.split(" ");
		func = words[0];
		var option = '';
		var parameter = '';
		for (i = 1; i < words.length; i++) {
			if (i % 2 == 1) {
				option = words[i];
				if (i + 1 < words.length) {
					parameter = words[i+1];
				}
			}
		}
		func = func.toUpperCase();
		switch (func)
		{
			case "HELP": 
				term.echo("    admin");
				term.echo("    cat");
				term.echo("    cd");
				term.echo("    display");
				term.echo("    hello");
				term.echo("    ls");
				term.echo("    mail");
				break;
			case "HELLO":
				option = option.toUpperCase();
				if (option !== '') {	
					switch (option)
					{
						case "-NAME":
							term.echo("Hello, " + parameter + "!");
							break;
						default:
							term.echo("Hello: Usage: hello [-name]");
							break;
					}
				} else {
					term.echo("Hello!");
				}
				break;
			case "ADMIN":
				option = option.toUpperCase();
				if (option !== '') {
					switch (option) {
						case "NEW":
							parameter = parameter.toUpperCase();
							switch (parameter) {
								case "POST":
									goToURL("http://sekrim.com/wp-admin/post-new.php");
									break;
								case "PAGE":
									goToURL("http://sekrim.com/wp-admin/post-new.php?post_type=page");
									break;
								default:
									term.echo("Admin: Usage: admin new [post] [page]");
									break;
							}
							break;
						case "ANALYTICS":
							goToURL("http://www.google.com/analytics/web/?hl=en#report/visitors-overview/a38643243w67343847p69298455/");
							break;
						default:
							term.echo("Admin: Usage: admin [new] [analytics]");
							break;
					}	
				} else {
					term.echo("Admin: Usage: admin [new] [analytics]");
				}
				break;
			case "MAIL":
				if (option !== '') {
					sendMail(option);
				} else {
					term.echo("Mail: Usage: mail [message]");
				}
				break;
			case "CAT":
				found = false;
				foundIndex = -1;
				for (i = 0; i < files.length; i++) {
					if (option == files[i]) {
						found = true;
						foundIndex = i;
					}
				}
				if (found) {
					var rawFile = new XMLHttpRequest();
					rawFile.open("GET", "files/" + option, true);
					rawFile.onreadystatechange = function ()
					{
						if(rawFile.readyState === 4)
						{
							if(rawFile.status === 200 || rawFile.status == 0)
							{
								var allText = rawFile.responseText;
								term.echo(allText);
							}
						}
					}
					rawFile.send(null);
				} else {
					term.echo("File not found");
				}
				break;
			case "LS":
				for (i = 0; i < files.length; i++) {
					term.echo("    " + files[i]);
				}
				term.echo("    about/");
				term.echo("    blog/");
				term.echo("    github/");
				term.echo("    linkedin/");
				term.echo("    resume/");
				term.echo("    twitter/");
				 break;
			case "CD": 
				if (option !== '') {
					option = option.toUpperCase();
					switch (option) {
						case "ABOUT":
							goToURL("http://sekrim.com/about");
							break;
						case "BLOG":
							goToURL("http://sekrim.com/blog");
							break;
						case "GITHUB":
							goToURL("http://git.sekrim.com");
							break;
						case "LINKEDIN":
							goToURL("http://www.linkedin.com/in/sekrim");
							break;
						case "RESUME":
							goToURL("http://sekrim.com/resume");
							break;
						case "TWITTER":
							goToURL("http://twitter.sekrim.com");
							break;
						default:
							term.echo("cd: undefined: No such file or directory");
							break;
					}	
				} else {
					term.echo("cd: undefined: No such file or directory");
				}
				break;
			case "DISPLAY":
				if (option !== '') {
					option = option.toUpperCase();
					switch (option) {
						case 'BLOG':
							if (parameter !== '') {
								term.echo(items[parameter - 1]);
							} else {
								term.echo(items[0]);
							}
							break;
						default: 
							term.echo("Display: Usage: display [blog]");
							break;
					}
				} else {
					term.echo("Display: Usage: display [blog]");
				}
			case "":
				break;
			default:
				try {
					var result = window.eval(command);
					if (result !== undefined) {
						term.echo(new String(result));
					}
				} catch(e) {
					term.error(new String(e));
				}
				break;
		} 
    }, {
        greetings: 'guest@sekrim:/$ cat welcome.txt\nWelcome to the UniXrim console.\nUse "ls", "cat", and "cd" to navigate the filesystem.',
        name: 'js_demo',
        prompt: 'guest@sekrim:/$ '});
});
function sendMail(message) {
    var link = "mailto:sean@sekrim.com"
             /* + "?cc=myCCaddress@example.com" */
             + "?subject=" + escape("Sekrim Terminal Message")
             + "&body=" + escape(message)
    ;

    window.location.href = link;
}
function goToURL(url) {
	 window.location = url;
}
</script>
<div id="term" class="terminal" style="height: 100%;"></div>
