<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();

if ($CURUSER["uploadpos"] == 'no')
	stderr($lang_upload['std_sorry'], $lang_upload['std_unauthorized_to_upload'],false);

if ($enableoffer == 'yes') {
  $offerres = sql_query("SELECT id, name, descr, category FROM offers WHERE userid = ? AND allowed = 'allowed' AND ISNULL(torrent_id) ORDER BY name ASC", [$CURUSER['id']]);
  $has_allowed_offer = $offerres->rowCount();
}
else $has_allowed_offer = 0;
$uploadfreely = user_can_upload("torrents");
$allowtorrents = ($has_allowed_offer || $uploadfreely);
$allowspecial = user_can_upload("music");

if (!$allowtorrents && !$allowspecial)
	stderr($lang_upload['std_sorry'],$lang_upload['std_please_offer'],false);
$allowtwosec = ($allowtorrents && $allowspecial);

$brsectiontype = $browsecatmode;
$spsectiontype = $specialcatmode;
$showsource = (($allowtorrents && get_searchbox_value($brsectiontype, 'showsource')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showsource'))); //whether show sources or not
$showmedium = (($allowtorrents && get_searchbox_value($brsectiontype, 'showmedium')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showmedium'))); //whether show media or not
$showcodec = (($allowtorrents && get_searchbox_value($brsectiontype, 'showcodec')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showcodec'))); //whether show codecs or not
$showstandard = (($allowtorrents && get_searchbox_value($brsectiontype, 'showstandard')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showstandard'))); //whether show standards or not
$showprocessing = (($allowtorrents && get_searchbox_value($brsectiontype, 'showprocessing')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showprocessing'))); //whether show processings or not
$showteam = (($allowtorrents && get_searchbox_value($brsectiontype, 'showteam')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showteam'))); //whether show teams or not
$showaudiocodec = (($allowtorrents && get_searchbox_value($brsectiontype, 'showaudiocodec')) || ($allowspecial && get_searchbox_value($spsectiontype, 'showaudiocodec'))); //whether show languages or not

stdhead($lang_upload['head_upload']);
?>
<form id="compose" enctype="multipart/form-data" action="takeupload.php" method="post" name="upload">
  <div class="edit-hint minor-list-vertical"><ul>
    <li><?php echo $lang_upload['text_red_star_required']; ?></li>
    <li><?php echo $lang_upload['text_tracker_url'] ?>: <strong><?php echo get_protocol_prefix() . $announce_urls[0]?></strong></li>
    <?php
      if(!is_writable($torrent_dir)) {
	print("<li><b>ATTENTION</b>: Torrent directory isn't writable. Please contact the administrator about this problem!</li>");
      }
      if(!$max_torrent_size) {
	print("<li><b>ATTENTION</b>: Max. Torrent Size not set. Please contact the administrator about this problem!</li>");
      }
    ?>
  </ul></div>
  <dl class="table">
    <?php
	dl_item($lang_upload['row_torrent_file'], "<input type=\"file\" class=\"file\" id=\"torrent\" name=\"file\" data-url=\"torrentupload.php\" required=\"required\" />\n", 1, 'required');
	dl_item('下载链接', '<input type="url" name="dl-url" class="colspan" placeholder="可填网盘链接、在线视频地址等" />', true);
	# 若填了此项可以不上传种子"><br/>文件大小: <input type="number" min="0" name="filesize" placeholder="若无上传种子，建议填写" style="width: 200px" /> MiB', true);
	if ($altname_main == 'yes'){
	  dl_item($lang_upload['row_torrent_name'], "<b>".$lang_upload['text_english_title']."</b>&nbsp;<input type=\"text\" style=\"width: 250px;\" name=\"name\" />&nbsp;&nbsp;&nbsp;
	  <b>".$lang_upload['text_chinese_title']."</b>&nbsp;<input type=\"text\" style=\"width: 250px\" name=\"cnname\" ><br /><span class=\"medium\">".$lang_upload['text_titles_note']."</span>", 1);
	}
	else
	  dl_item($lang_upload['row_torrent_name'], "<input type=\"text\" class=\"colspan\" id=\"name\" name=\"name\" required=\"required\" /><div class=\"medium\">".$lang_upload['text_torrent_name_note']."</div>", 1, 'required');
	if ($smalldescription_main == 'yes')
	  dl_item($lang_upload['row_small_description'], "<input type=\"text\" class=\"colspan\" name=\"small_descr\" /><div class=\"medium\">".$lang_upload['text_small_description_note']."</div>", 1);
	
	get_external_tr('', true);
	if ($enablenfo_main=='yes')
	  dl_item($lang_upload['row_nfo_file'], "<input type=\"file\" class=\"file\" name=\"nfo\" /><br /><font class=\"medium\">".$lang_upload['text_only_viewed_by'].get_user_class_name($viewnfo_class,false,true,true).$lang_upload['text_or_above']."</font>", 1);
	print('<dt class="required">' . $lang_upload['row_description'] . '</dt><dd>');
	textbbcode("upload","descr","",false);
	print("</dd>\n");

	if ($allowtorrents){
	  $disablespecial = " onchange=\"disableother('browsecat','specialcat')\"";
	  $s = "<select name=\"type\" id=\"browsecat\" ".($allowtwosec ? $disablespecial : "").">\n<option value=\"0\">".$lang_upload['select_choose_one']."</option>\n";
	  $cats = genrelist($browsecatmode);
	  foreach ($cats as $row)
	    $s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
	  $s .= "</select>\n";
	}
	else $s = "";
	if ($allowspecial){
	  $disablebrowse = " onchange=\"disableother('specialcat','browsecat')\"";
	  $s2 = "<select name=\"type\" id=\"specialcat\" ".$disablebrowse.">\n<option value=\"0\">".$lang_upload['select_choose_one']."</option>\n";
	  $cats2 = genrelist($specialcatmode);
	  foreach ($cats2 as $row)
	    $s2 .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
	  $s2 .= "</select>\n";
	}
	else $s2 = "";
	dl_item($lang_upload['row_type'], ($allowtwosec ? $lang_upload['text_to_browse_section'] : "").$s.($allowtwosec ? $lang_upload['text_to_special_section'] : "").$s2.($allowtwosec ? $lang_upload['text_type_note'] : ""), 1, 'required');

	echo '<dt>分类</dt>';
	echo '<dd class="minor-list" id="tcategories"><ul></ul></dd>';

	if ($showsource || $showmedium || $showcodec || $showaudiocodec || $showstandard || $showprocessing){
	  if ($showsource){
	    $source_select = torrent_selection($lang_upload['text_source'],"source_sel","sources");
	  }
	  else $source_select = "";

	  if ($showmedium){
	    $medium_select = torrent_selection($lang_upload['text_medium'],"medium_sel","media");
	  }
	  else $medium_select = "";

	  if ($showcodec){
	    $codec_select = torrent_selection($lang_upload['text_codec'],"codec_sel","codecs");
	  }
	  else $codec_select = "";

	  if ($showaudiocodec){
	    $audiocodec_select = torrent_selection($lang_upload['text_audio_codec'],"audiocodec_sel","audiocodecs");
	  }
	  else $audiocodec_select = "";

	  if ($showstandard){
	    $standard_select = torrent_selection($lang_upload['text_standard'],"standard_sel","standards");
	  }
	  else $standard_select = "";

	  if ($showprocessing){
	    $processing_select = torrent_selection($lang_upload['text_processing'],"processing_sel","processings");
	  }
	  else $processing_select = "";
	  
	  dl_item($lang_upload['row_quality'], $source_select . $medium_select. $codec_select . $audiocodec_select. $standard_select . $processing_select, 1 );
	}

	if ($showteam){
	  if ($showteam){
	    $team_select = torrent_selection($lang_upload['text_team'],"team_sel","teams");
	  }
	  else $showteam = "";

	  dl_item($lang_upload['row_content'],$team_select,1);
	}

	//==== offer dropdown for offer mod  from code by S4NE

	if ($has_allowed_offer > 0) {
	  $offer = "<select name=\"offer\" id=\"offer\">";
	  if ($uploadfreely || $allowspecial) {
	    $offer .= "<option value=\"0\">".$lang_upload['select_choose_one']."</option>";
	  }
	  $js = [];
	  while($offerrow = _mysql_fetch_array($offerres)) {
	    $offer .= "<option value=\"" . $offerrow["id"] . '">' . htmlspecialchars($offerrow["name"]) . "</option>";
	    $js[$offerrow["id"]] = $offerrow;
	  }
	  $offer .= "</select>";
	  $dt = $lang_upload['row_your_offer'];
	  if (!$uploadfreely && !$allowspecial) {
	    $class = 'required';
	  }
	  else {
	    $class = '';
	  }
	  dl_item($dt, $offer.$lang_upload['text_please_select_offer'] , 1, $class);

	  echo '<script type="text/javascript">hb.offers=' . json_encode($js) . '</script>';
	}
	//===end

	if(get_user_class()>=$beanonymous_class) {
	  dl_item($lang_upload['row_show_uploader'], "<input type=\"checkbox\" name=\"uplver\" value=\"yes\" />".$lang_upload['checkbox_hide_uploader_note'], 1);
	}
    ?>
    <dd class="toolbox"><b><?php echo $lang_upload['text_read_rules']?></b> <input id="qr" type="submit" class="btn" value="<?php echo $lang_upload['submit_upload']?>" /></dd>
  </dl>
</form>
<?php
	stdfoot();
