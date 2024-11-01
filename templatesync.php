<?php
/*
Plugin Name: Template Sync
Plugin URI: http://wordpress.org/extend/plugins/templatesync/
Description: Copy the HTML from your WordPress theme to another application's template library.
Author: Shannon Whitley
Version: 1.00
Author URI: http://voiceoftech.com/swhitley/
*/

function tsync_template_update()
{
	$tsync_include_dir = get_option('tsync_include_dir');
	$tsync_header_file = get_option('tsync_header_file');
	$tsync_footer_file = get_option('tsync_footer_file');
	$tsync_smarty = get_option('tsync_smarty');

	//Retrieve the home page
	$response = wp_remote_get(get_bloginfo( 'url' ));

	if( !is_wp_error( $response ) )
	{
		//Remove Excluded Code
		$html = $response[body];
		
		
		//Exclude sections.
		$html = preg_replace("/<!--tpl.exclude-->(.*?)<!--\/tpl.exclude-->/", '', $html);
	
	
		//Include files.
		preg_match_all("/<!--(.*?)\.inc-->/", $html, $tsyncinclude);

		if(count($tsyncinclude) > 1)
		{
			$tsyncinclude = $tsyncinclude[1];
			foreach($tsyncinclude as $include)
			{
				$includeFile = $tsync_include_dir.$include.".inc";
				
				if(file_exists($includeFile))
				{
					$fh = fopen($includeFile, 'r') or wp_die("Can't open ".$includeFile.".  Please make sure read permissions are enabled.");
					$data = fread($fh, filesize($includeFile)); 
					fclose($fh);
					$html = str_replace('<!--'.$include.'.inc-->', $data, $html);
				}
			}
		}
	
		//Write the Header
		preg_match("/^(.*?)<!--header.end-->/s", $html, $header);

		if(count($header) > 0)
		{
			$headerFile = $tsync_header_file;
			$fh = fopen($headerFile, 'w') or wp_die("Can't open ".$headerFile.".  Please make sure write permissions are enabled.");
   		    $header = $header[0];
			if($tsync_smarty !== 'N')
			{
				$header = tsync_smarty_literal($header);
			}
			fwrite($fh, $header);
			fclose($fh);
		}

		//Write the Footer
		preg_match("/<!--footer.begin-->(.*?)<\/html>/s", $html, $footer);
		
		if(count($footer) > 0)
		{
			$footerFile = $tsync_footer_file;
			$fh = fopen($footerFile, 'w') or wp_die("Can't open ".footerFile.".  Please make sure write permissions are enabled.");
			$footer = $footer[0];
			if($tsync_smarty !== 'N')
			{
				$footer = tsync_smarty_literal($footer);
			}
			fwrite($fh, $footer);
			fclose($fh);
		}

	}
    
}

//Turn script and style tags into smarty tag literals.
function tsync_smarty_literal($html)
{
	$html = str_replace('</script>','</script>{/literal}',str_replace('<script','{literal}<script',$html));
	$html = str_replace('</style>','</style>{/literal}',str_replace('<style','{literal}<style',$html));
	return $html;
}


add_action('admin_menu', 'tsync_menu');

function tsync_menu() {

  add_options_page('Template Sync Settings', 'Template Sync', 'manage_options', 'tsync_settings_menu', 'tsync_plugin_options');

}




//* S E T T I N G S

function tsync_plugin_options() {

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}


	$tsync_include_dir = get_option('tsync_include_dir');
	$tsync_header_file = get_option('tsync_header_file');
	$tsync_footer_file = get_option('tsync_footer_file');
	$tsync_smarty = get_option('tsync_smarty');
	if(empty($tsync_smarty))
	{
		$tsync_smarty = 'Y';
	}

	if(isset($_POST['tsyncbtnUpdate']))
	{
		$tsync_include_dir = $_POST['tsync_include_dir'];
		$tsync_header_file = $_POST['tsync_header_file'];
		$tsync_footer_file = $_POST['tsync_footer_file'];
		$tsync_smarty = $_POST['tsync_smarty'] == 'Y' ? 'Y' : 'N';
		update_option('tsync_include_dir', $tsync_include_dir);
		update_option('tsync_header_file', $tsync_header_file);
		update_option('tsync_footer_file', $tsync_footer_file);
		update_option('tsync_smarty', $tsync_smarty);
		tsync_template_update();
	}

	$tsync_smarty = $tsync_smarty == 'Y' ? 'checked="checked"' : '';

?>
<div class="wrap">
<h1>Template Sync Settings</h1>
<?php if(isset($_POST['tsyncbtnUpdate'])):?>
     <div id="message" class="updated fade"><p><?php _e('Template edited successfully.') ?></p></div>
<?php endif; ?>
<form method="post">
<p>Add the following hints to your WordPress theme files.  The hints will be used to update another application's template files.  Click <strong>Sync Template</strong> whenever your WordPress header or footer change.</p>
<p>
<table cellpadding="5">
<tr>
<td width="200">&lt;!--header.end--&gt;</td><td>The end of your WordPress header code.</td></tr>
<tr>
<td>&lt;!--footer.begin--&gt;</td><td>The beginning of your WordPress footer code.</td></tr>
</table>
</p>
<p>Include file contents from another template directory.</p>
<p>
<table cellpadding="5">
<tr>
<td width="200">&lt;!--{unique id}.inc--></td><td>The contents of <?php echo $tsync_include_dir; ?>{unique id}.inc will be added to the code at the specified location.</td></tr>
</table>
</p>
<p>Exclude WordPress code from your templates.</p>
<p>
<table cellpadding="5">
<tr>
<td width="200">&lt;!--tpl.exclude--&gt;</td><td>The beginning of the text to exclude.</td></tr>
<tr>
<td>&lt;!--/tpl.exclude--&gt;</td><td>The end of the text to exclude.</td></tr>
</table>
</p>
<table cellpadding="5">
<tr><td>Include files from this directory:</td><td><input id="tsync_include_dir" name="tsync_include_dir" type="text" value="<?php echo $tsync_include_dir; ?>" size="100" /></td></tr>
<tr><td>Write the header to this file (include path):</td><td><input id="tsync_header_file" name="tsync_header_file" type="text" value="<?php echo $tsync_header_file; ?>" size="100"/></td></tr>
<tr><td>Write the footer to this file (include path):</td><td><input id="tsync_footer_file" name="tsync_footer_file" type="text" value="<?php echo $tsync_footer_file; ?>" size="100"/></td></tr>
<tr><td>Use <a href="http://www.smarty.net/" target="_blank">Smarty</a> template literals?</td><td><input id="tsync_smarty" name="tsync_smarty" type="checkbox" value="Y" <?php echo $tsync_smarty; ?> /></td></tr>
<tr><td>&nbsp;</td><td></td></tr>
<tr><td><input id="tsyncbtnUpdate" name="tsyncbtnUpdate" type='submit' value="Sync Template" class="button-primary"/></td><td></td></tr>
</table>
</form>
</div>
<?php
}
?>