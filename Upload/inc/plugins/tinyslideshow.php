<?php
/** 
 * Tinyslideshow
 *
 * Version: 2.1
 *
 * Author: MrBrechreiz & Vintagedaddyo
 */

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_config_menu", "tinyslideshow_admin_nav");
$plugins->add_hook("admin_config_permissions", "tinyslideshow_admin_permissions");
$plugins->add_hook("admin_config_action_handler", "tinyslideshow_action_handler");
$plugins->add_hook("admin_load", "tinyslideshow_admin");
$plugins->add_hook("global_start", "tinyslideshow_run");

function tinyslideshow_info()
{
   global $lang;

    $lang->load("tinyslideshow");
    
    $lang->tinyslideshow_desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->tinyslideshow_desc;

    return Array(
        'name' => $lang->tinyslideshow_name,
        'description' => $lang->tinyslideshow_desc,
        'website' => $lang->tinyslideshow_web,
        'author' => $lang->tinyslideshow_auth,
        'authorsite' => $lang->tinyslideshow_authsite,
        'version' => $lang->tinyslideshow_ver,
        'compatibility' => $lang->tinyslideshow_compat
    );
}

function tinyslideshow_install()
{
	global $db, $lang;
		
	$lang->load("tinyslideshow", false, true);
	
	if(!$db->table_exists("tinyslideshow"))
	{
		$db->write_query("
			CREATE TABLE ".TABLE_PREFIX."tinyslideshow (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `active` int(11) NOT NULL,
			  `name` varchar(225) NOT NULL,
			  `link` varchar(225) NOT NULL,
			  `clicks` int(11) NOT NULL default '0',
			  `image` varchar(225) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM ;
		");
	}
	
	$template1 = array(
		"title"		=> "tinyslideshow",
		"template"	=> $db->escape_string("		
<div id=\"wrapper-slider\">
  <div id=\"slideleft\" onclick=\"slideshow.move(-1)\"><img src=\"images/left.png\" alt=\"\" title=\"{\$lang->back}\" /></div>
    <div id=\"slideright\" onclick=\"slideshow.move(1)\"><img src=\"images/right.png\" alt=\"\" title=\"{\$lang->forward}\" /></div>
	<div id=\"container-slider\">		
			<div id=\"slider\">
			<ul>
				{\$list_tinyslideshow}
            </ul>
			</div>
	</div>
</div>
<br />
<script type=\"text/javascript\">
var slideshow=new TINY.slider.slide('slideshow' ,{
	id:'slider',
	auto:3, /*time in seconds*/
	resume:false,
	vertical:false,
	activeclass:'current',
	position:0,
	rewind:false,
	elastic:true,
	left:'slideleft',
	right:'slideright'
});
</script>			

"),
		"sid"		=> "-1",
		"tid" => "0",
	);

	$db->insert_query("templates", $template1);
	
	$template2 = array(
		"title"		=> "list_tinyslideshow",
		"template"	=> $db->escape_string("<li><a href=\"{\$mybb->settings['bburl']}/index.php?action=visit&amp;id={\$tinyslideshow['id']}&amp;key={\$mybb->post_code}\" target=\"_blank\"><img src=\"{\$mybb->settings['uploadspath']}/tinyslideshow/{\$tinyslideshow['image']}\" alt=\"{\$tinyslideshow['name']}\" title=\"{\$tinyslideshow['name']}\" width=\"518\" height=\"215\" style=\"-moz-border-radius: 16px;-webkit-border-radius: 16px;-khtml-border-radius: 16px;border-radius: 16px;\"  /></a></li>"),
		"sid"		=> "-1",
		"tid" => "0",
	);

	$db->insert_query("templates", $template2);
	
	$template3 = array(
		"title"		=> "no_tinyslideshow",
		"template"	=> $db->escape_string("{\$lang->no_tinyslideshow}"),
		"sid"		=> "-1",
		"tid" => "0",
	);
	
		$db->insert_query("templates", $template3);
	
	
	$tinyslideshow = array(
		"name" => "tinyslideshow",
		"title" => $lang->setting_0_title,
		"description" => $lang->setting_0_description,
		"disporder" => "403",
		"isdefault" => "0",
	);
	$group['gid'] = $db->insert_query("settinggroups", $tinyslideshow);
	$gid = $db->insert_id();
	
	$tinyslideshow[]= array(
		"name"			=> "tinyslideshow_active",
		"title" => $lang->setting_1_title,
		"description" => $lang->setting_1_description,
		"optionscode"	=> "yesno",
		"value"			=> '0',
		"disporder"		=> '1',
		"gid"			=> $gid,
	);
	
	$tinyslideshow[]= array(
		"name"			=> "tinyslideshow_dimensions",
		"title" => $lang->setting_2_title,
		"description" => $lang->setting_2_description,
		"optionscode"	=> "text",
		"value"			=> '1900x1200',
		"disporder"		=> '2',
		"gid"			=> $gid,
	);
	
	$tinyslideshow[]= array(
		"name"			=> "tinyslideshow_groups_ignore",
		"title" => $lang->setting_3_title,
		"description" => $lang->setting_3_description,
		"optionscode"	=> "text",
		"value"			=> '',
		"disporder"		=> '3',
		"gid"			=> $gid,
	);
	
	$tinyslideshow[]= array(
		"name"			=> "tinyslideshow_header",
		"title" => $lang->setting_4_title,
		"description" => $lang->setting_4_description,
		"optionscode"	=> "radio
1=Header
0=Footer",
		"value"			=> '',
		"disporder"		=> '4',
		"gid"			=> $gid,
	);
	
	foreach ($tinyslideshow as $a)
	{
		$db->insert_query("settings", $a);
	}
	
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("index", '#{\$boardstats}#', "{\$boardstats}\n{\$tinyslideshow}");
	find_replace_templatesets("index", '#{\$header}#', "{\$header}\n{\$tinyslideshow_header}");
	find_replace_templatesets('index', '#{\$headerinclude}#', "{\$headerinclude}\n<link rel=\"stylesheet\" type=\"text/css\" href=\"tinyslideshow/style.css\" />
<script type=\"text/javascript\" src=\"tinyslideshow/script.js\"></script>");
	
	change_admin_permission('config', 'tinyslideshow');
	
	rebuild_settings();
}

function tinyslideshow_deactivate()
{
	change_admin_permission('config', 'tinyslideshow', -1);
}

function tinyslideshow_is_installed()
{
	global $db;
	
	return $db->table_exists("tinyslideshow");
}

function tinyslideshow_uninstall()
{
	global $db;
	
	if($db->table_exists("tinyslideshow"))
	{
		$db->drop_table("tinyslideshow");
	}
	
	$db->delete_query('templates', 'title IN (\'tinyslideshow\',\'list_tinyslideshow\',\'no_tinyslideshow\')');
	
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets("index", '#{\$tinyslideshow}(\r?)\n#', "", 0);
	find_replace_templatesets("index", '#{\$tinyslideshow_header}(\r?)\n#', "", 0);
	find_replace_templatesets("index", '#<link rel="stylesheet" type="text/css" href="tinyslideshow/style.css" />
<script type="text/javascript" src="tinyslideshow/script.js"></script>(\r?)\n#', "", 0);
	
	
	$db->delete_query("settings","name IN ('tinyslideshow_active')");
	$db->delete_query("settinggroups","name='tinyslideshow'");
	
	change_admin_permission('config', 'tinyslideshow', -1);
	
	rebuild_settings();
}

function tinyslideshow_run()
{
	global $mybb, $db, $templates, $lang, $tinyslideshow, $tinyslideshow_header;
	
	$lang->load("tinyslideshow");
	
	if($mybb->settings['tinyslideshow_active'] && (!check_groups($mybb->settings['tinyslideshow_groups_ignore'])))
	{
		if($mybb->input['action'] == "visit")
		{
			$query = $db->simple_select("tinyslideshow", "*", "id='".intval($mybb->input['id'])."'");
			$tinyslideshow = $db->fetch_array($query);
				
			if(!$tinyslideshow['id'])
			{
				error($lang->invalid_tinyslideshow);
			}
			
			verify_post_check($mybb->input['key']);

			$db->query("UPDATE ".TABLE_PREFIX."tinyslideshow SET clicks = clicks +1 WHERE id='".intval($mybb->input['id'])."'");
			
			header("Location: ".$tinyslideshow['link']."");
		}
		
		## Please use a variant here, how the picture order should be for the output. 
		## What is not needed, please comment out with double slash.
		## Standard is random
		//$query = $db->simple_select("tinyslideshow", "*", "active='1'", array("order_by" => "id")); // Output order according to the pictures ID
		$query = $db->simple_select("tinyslideshow", "*", "active='1'", array("order_by" => "RAND();"));  // Output sequence by a random image

		if($db->num_rows($query) > 0)
		{
			while($tinyslideshow = $db->fetch_array($query))
			{
				list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
				eval("\$list_tinyslideshow .= \"".$templates->get("list_tinyslideshow")."\";");
			}
		}
		else
		{
			eval("\$list_tinyslideshow = \"".$templates->get("no_tinyslideshow")."\";");
		}
		
		if($mybb->settings['tinyslideshow_header'] == 1)
		{
			eval("\$tinyslideshow_header = \"".$templates->get("tinyslideshow")."\";");
		}
		else
		{
			eval("\$tinyslideshow = \"".$templates->get("tinyslideshow")."\";");
		}
	}
}

function tinyslideshow_action_handler(&$action)
{
	$action['tinyslideshow'] = array('active' => 'tinyslideshow', 'file' => '');
}

function tinyslideshow_admin_nav(&$sub_menu)
{
	global $mybb, $lang;

	$lang->load("tinyslideshow", false, true);
		
	end($sub_menu);
	$key = (key($sub_menu))+10;

	if(!$key)
	{
		$key = '100';
	}
	
	$sub_menu[$key] = array('id' => 'tinyslideshow', 'title' => 'Tinyslideshow', 'link' => "index.php?module=config-tinyslideshow");
}

function tinyslideshow_admin_permissions(&$admin_permissions)
{
  	global $db, $mybb, $lang;
		
	$lang->load("tinyslideshow", false, true);
		
	$admin_permissions['tinyslideshow'] = ''.$lang->canwe.'';
}

function tinyslideshow_admin()
{
	global $mybb, $db, $page, $lang;
	
	$lang->load("tinyslideshow", false, true);
	
	if($page->active_action != "tinyslideshow")
	{
		return;
	}
	
	$page->add_breadcrumb_item($lang->tinyslideshow);
	
	$sub_tabs['manage'] = array(
		'title' => $lang->manage_tab,
		'link' => "index.php?module=config-tinyslideshow",
		'description' => $lang->manage_desc
	);

	$sub_tabs['add'] = array(
		'title' => $lang->add_tab,
		'link' => "index.php?module=config-tinyslideshow&amp;action=add",
		'description' => $lang->add_desc
	);
	
	if($mybb->input['action'] == "edit")
	{
		$sub_tabs['edit'] = array(
			'title' => $lang->edit_tab,
			'link' => "index.php?module=config-tinyslideshow",
			'description' => $lang->edit_desc
		);		
	}

	if($mybb->input['action'] == "delete")
	{
		$query = $db->simple_select("tinyslideshow", "*", "id='".intval($mybb->input['id'])."'");
		$tinyslideshow = $db->fetch_array($query);

		if(!$tinyslideshow['id'])
		{
			flash_message($lang->error_invalid_partner, 'error');
			admin_redirect("index.php?module=config-tinyslideshow");
		}
		
		if($mybb->input['no'])
		{
			admin_redirect("index.php?module=config-tinyslideshow");
		}
		
		if($mybb->request_method == "post")
		{
			$tinyslideshowimg = $tinyslideshow['image'];
			unlink(MYBB_ROOT.$mybb->settings['uploadspath'].'/tinyslideshow/'.$tinyslideshowimg);
			
			$db->delete_query("tinyslideshow", "id='{$tinyslideshow['id']}'");
			
			flash_message($lang->success_tinyslideshow_deleted, 'success');
			admin_redirect("index.php?module=config-tinyslideshow");
		}
		else
		{
			$page->output_confirm_action("index.php?module=config-tinyslideshow&amp;action=delete&id={$tinyslideshow['id']}", $lang->tinyslideshow_deletion_confirmation);
		}
		
		$page->output_footer();
	}
	
	if($mybb->input['action'] == "edit")
	{
		$page->output_header($lang->tinyslideshow);
		$page->output_nav_tabs($sub_tabs, 'edit');
		
		$query = $db->simple_select("tinyslideshow", "*", "id='".intval($mybb->input['id'])."'");
		$tinyslideshow = $db->fetch_array($query);

		if(!$tinyslideshow['id'])
		{
			flash_message($lang->error_invalid_tinyslideshow, 'error');
			admin_redirect("index.php?module=config-tinyslideshow");
		}
		
		if($mybb->request_method == "post")
		{
			list($width, $height) = @getimagesize($_FILES['image_upload']['tmp_name']);
			list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
			switch(strtolower($_FILES['image_upload']['type']))
			{
				case "image/gif":
				case "image/jpeg":
				case "image/x-jpg":
				case "image/x-jpeg":
				case "image/pjpeg":
				case "image/jpg":
				case "image/png":
				case "image/x-png":
					$img_type = 1;
				break;
				default:
					$img_type = 0;
				
			}
			if(!$mybb->input['name'])
			{
				$errors[] = $lang->error_invalid_name;
			}
			if(!preg_match("/^(https?:\/\/+[\w\-]+\.[\w\-]+)/i", $mybb->input['url']))
			{
				$errors[] = $lang->error_invalid_url;
			}
			if($_FILES['image_upload']['name'])
			{
				if(!$_FILES['image_upload'])
				{
					$errors[] = $lang->error_invalid_upload;						
				}
				if($img_type == 0)
				{
					$errors[] = $lang->error_invalid_file_type;
				}	
				if($width > $maxwidth || $height > $maxheight)
				{
					$errors[] = $lang->error_image_too_large = $lang->sprintf($lang->error_image_too_large, $maxwidth, $maxheight);
				}
			}
			if(!$errors)
			{
				if($_FILES['image_upload']['name'])
				{
					$filename = $_FILES['image_upload']['name'];
					$file_basename = substr($filename, 0, strripos($filename, '.'));
					$file_ext = substr($filename, strripos($filename, '.'));
					$filesize = $_FILES['image_upload']['size'];
					$allowed_file_types = array('.png','.jpg','.bmp','.gif');
					
					list($width, $height, $type) = @getimagesize($_FILES['image_upload']['tmp_name']);
					list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
					
					// delete old image
					$old_tinyslideshowimg = $tinyslideshow['image'];
					unlink(MYBB_ROOT.$mybb->settings['uploadspath'].'/tinyslideshow/'.$old_tinyslideshowimg);
					
					// upload new image
					$newfilename = random_str(12).$file_ext;
					@move_uploaded_file($_FILES['image_upload']['tmp_name'], MYBB_ROOT.$mybb->settings['uploadspath'].'/tinyslideshow/'.$newfilename);
					
					$update = array(
						"name" => $db->escape_string($mybb->input['name']),
						"link" => $mybb->input['url'],
						"image" => $newfilename,
					);
					$db->update_query("tinyslideshow", $update, "id={$mybb->input['id']}");
			
					flash_message($lang->success_tinyslideshow_edited, 'success');
					admin_redirect("index.php?module=config-tinyslideshow");
				}
				else
				{
					$update = array(
						"name" => $db->escape_string($mybb->input['name']),
						"link" => $mybb->input['url'],
					);
					$db->update_query("tinyslideshow", $update, "id={$tinyslideshow['id']}");
				
					flash_message($lang->success_tinyslideshow_edited, 'success');
					admin_redirect("index.php?module=config-tinyslideshow");
				}
			}
		}
		
		if($errors)
		{
			$page->output_inline_error($errors);
		}
		
		$form = new Form("index.php?module=config-tinyslideshow&amp;action=edit&amp;id={$tinyslideshow['id']}", "post", "", 1);
		
		list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
		
		$form_container = new FormContainer($lang->edit_tinyslideshow_info);
		$form_container->output_row($lang->current_image, "", "<img src=\".".$mybb->settings['uploadspath']."/tinyslideshow/".htmlspecialchars_uni($tinyslideshow['image'])."\" width=\"600\" height=\"auto\" alt=\"#\" />", array('width' => 1));

		$form_container->output_row($lang->name." <em>*</em>", "", $form->generate_text_box('name', $tinyslideshow['name'], $mybb->input['name'], array('id' => 'name')), 'name');
		$form_container->output_row($lang->url." <em>*</em>", $lang->use_http, $form->generate_text_box('url', $tinyslideshow['link'], $mybb->input['url'], array('id' => 'url')), 'url');
		$form_container->output_row($lang->upload_image." <em>*</em>", $lang->sprintf($lang->image_desc, $maxwidth, $maxheight), $form->generate_file_upload_box('image_upload', array('id' => 'image_upload')), 'image_upload');

		$form_container->end();
		$buttons[] = $form->generate_submit_button($lang->button_edit);
		$form->output_submit_wrapper($buttons);

		$form->end();
		$page->output_footer();
	}
	
	if($mybb->input['action'] == "approve")
	{
		global $db;
		
		$array = array(
			"active" => 1
		);
		$db->update_query("tinyslideshow", $array, "id={$mybb->input['id']}");
		flash_message($lang->tinyslideshow_approved, 'success');
		admin_redirect("index.php?module=config-tinyslideshow");
	}
	
	if($mybb->input['action'] == "unapprove")
	{
		global $db;
		
		$array = array(
			"active" => 0
		);
		$db->update_query("tinyslideshow", $array, "id={$mybb->input['id']}");
		flash_message($lang->tinyslideshow_unapproved, 'success');
		admin_redirect("index.php?module=config-tinyslideshow");
	}
	
	if($mybb->input['action'] == "add")
	{
		$page->output_header($lang->tinyslideshow);
		$page->output_nav_tabs($sub_tabs, 'add');

		if($mybb->request_method == "post")
		{
			list($width, $height) = @getimagesize($_FILES['image_upload']['tmp_name']);
			list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
			switch(strtolower($_FILES['image_upload']['type']))
			{
				case "image/gif":
				case "image/jpeg":
				case "image/x-jpg":
				case "image/x-jpeg":
				case "image/pjpeg":
				case "image/jpg":
				case "image/png":
				case "image/x-png":
					$img_type = 1;
				break;
				default:
					$img_type = 0;
				
			}
			if(!$_FILES['image_upload'])
			{
				$errors[] = $lang->error_invalid_upload;						
			}
			if(!$mybb->input['name'])
			{
				$errors[] = $lang->error_invalid_name;
			}
			if(!preg_match("/^(https?:\/\/+[\w\-]+\.[\w\-]+)/i", $mybb->input['url']))
			{
				$errors[] = $lang->error_invalid_url;
			}
			if($img_type == 0)
			{
				$errors[] = $lang->error_invalid_file_type;
			}	
			if($width > $maxwidth || $height > $maxheight)
			{
				$errors[] = $lang->error_image_too_large = $lang->sprintf($lang->error_image_too_large, $maxwidth, $maxheight);
			}
			elseif(!$errors)
			{
				$filename = $_FILES['image_upload']['name'];
				$file_ext = substr($filename, strripos($filename, '.'));
				$filesize = $_FILES['image_upload']['size'];
				
				$process_upload = random_str(12).$file_ext;
				@move_uploaded_file($_FILES['image_upload']['tmp_name'], MYBB_ROOT.$mybb->settings['uploadspath'].'/tinyslideshow/'.$process_upload);

				$insert = array(
					"active" => 1,
					"name" => $db->escape_string($mybb->input['name']),
					"link" => $mybb->input['url'],
					"image" => $process_upload
				);
				$db->insert_query("tinyslideshow", $insert);
		
				flash_message($lang->success_tinyslideshow_added, 'success');
				admin_redirect("index.php?module=config-tinyslideshow");				
			}
		}
		
		if($errors)
		{
			$page->output_inline_error($errors);
		}
		
		list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
		
		$form = new Form("index.php?module=config-tinyslideshow&amp;action=add", "post", "", 1);
		
		$form_container = new FormContainer($lang->add_tinyslideshow_info);
		$form_container->output_row($lang->name." <em>*</em>", $lang->name_desc, $form->generate_text_box('name', $mybb->input['name'], array('id' => 'name')), 'name');
		$form_container->output_row($lang->url." <em>*</em>", $lang->use_http, $form->generate_text_box('url', $mybb->input['url'], array('id' => 'url')), 'url');
		$form_container->output_row($lang->upload_image." <em>*</em>", $lang->sprintf($lang->image_desc, $maxwidth, $maxheight), $form->generate_file_upload_box('image_upload', array('id' => 'image_upload')), 'image_upload');

		$form_container->end();
		$buttons[] = $form->generate_submit_button($lang->button_add);
		$form->output_submit_wrapper($buttons);

		$form->end();
		
		$page->output_footer();
	}
	
	if(!$mybb->input['action'])
	{
		$page->output_header($lang->tinyslideshow);
		$page->output_nav_tabs($sub_tabs, 'manage');
		
		$form = new Form("index.php?module=tools/pms&amp;action=delete", "post");
		
		$table = new Table;
		$table->construct_header("", array("colspan" => 1, "width" => "1%", "class" => "align_center"));
		$table->construct_header($lang->name, array("colspan" => 1));
		$table->construct_header($lang->preview, array("colspan" => 1, "width" => "13%", "class" => "align_center"));
		$table->construct_header($lang->clicks, array("colspan" => 1, "width" => "5%", "class" => "align_center"));
		$table->construct_header($lang->actions, array("colspan" => 1, "width" => "5%", "class" => "align_center"));
		
		$query = $db->simple_select("tinyslideshow", "*", "", array("order_by" => "id"));
		
		while($tinyslideshow = $db->fetch_array($query))
		{
			if($tinyslideshow['active'] == 1)
			{
				$active = "<img src=\"".$mybb->settings['bburl']."/images/minion.png\" title=\"enabled\" />";
			}
			else
			{
				$active = "<img src=\"".$mybb->settings['bburl']."/images/minioff.png\" title=\"disabled\" />";
			}
			
			list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['tinyslideshow_dimensions']));
			
			$table->construct_cell($active, array("class" => "align_center"));
			$table->construct_cell("<a href=\"".$tinyslideshow['link']."\" target=\"_blank\">".$tinyslideshow['name']."</a>");
			$table->construct_cell("<img src=\".".$mybb->settings['uploadspath']."/tinyslideshow/".$tinyslideshow['image']."\" width=\"100%\" height=\"auto\" alt=\"\" />", array("class" => "align_center"));
			$table->construct_cell($tinyslideshow['clicks'], array("class" => "align_center"));
			
			$popup = new PopupMenu("tinyslideshow_{$tinyslideshow['id']}", $lang->actions);
			$popup->add_item($lang->edit_tinyslideshow, "index.php?module=config-tinyslideshow&amp;action=edit&amp;id={$tinyslideshow['id']}");
			$popup->add_item($lang->delete_tinyslideshow, "index.php?module=config-tinyslideshow&amp;action=delete&amp;id={$tinyslideshow['id']}&amp;my_post_key={$mybb->post_code}", "return AdminCP.deleteConfirmation(this, '{$lang->tinyslideshow_deletion_confirmation}')");
			if($tinyslideshow['active'] == 1)
			{
				$popup->add_item($lang->unapprove_tinyslideshow, "index.php?module=config-tinyslideshow&amp;action=unapprove&amp;id={$tinyslideshow['id']}");
			}
			else
			{
				$popup->add_item($lang->approve_tinyslideshow, "index.php?module=config-tinyslideshow&amp;action=approve&amp;id={$tinyslideshow['id']}");
			}
			$table->construct_cell($popup->fetch());
			$table->construct_row();
		}
		
		if($table->num_rows() == 0)
		{
			$table->construct_cell($lang->no_tinyslideshow_found, array("colspan" => "6"));
			$table->construct_row();
			$table->output($lang->manage);
		}
		else
		{
			$table->output($lang->manage);
		}
		
		$form->end();
		
		$page->output_footer();
	}
	exit;
}

function check_groups($groups_check)
{
    global $mybb;
    
    if($groups_check == '')
    {
        return false;
    }
    
    $groups = explode(',', $groups_check);
    $add_groups = explode(',', $mybb->user['additionalgroups']);
    
    if(!in_array($mybb->user['usergroup'], $groups))
    {
        if($add_groups)
        {
            if(count(array_intersect($add_groups, $groups)) == 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    else
    {
        return true;
    }
} 
?>