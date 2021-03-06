<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/html; charset=utf-8");
function maketable($res, $mode = 'seeding')
{
	global $lang_getusertorrentlistajax,$CURUSER,$smalldescription_main;
	global $id;

	$can_storing = permissionAuth("storing",$CURUSER['usergroups'],$CURUSER['class']);
	
	switch ($mode)
	{
		case 'uploaded': {
		$showsize = true;
		$showsenum = true;
		$showlenum = true;
		$showuploaded = true;
		$showdownloaded = false;
		$showratio = false;
		$showsetime = true;
		$showletime = false;
		$showcotime = false;
		$showanonymous = true;
		$showstoringtime = false;
#		$columncount = 8;
		$showaction = false;
		break;
		}
		case 'seeding': {
		$showsize = true;
		$showsenum = true;
		$showlenum = true;
		$showuploaded = true;
		$showdownloaded = true;
		$showratio = true;
		$showsetime = false;
		$showletime = false;
		$showcotime = false;
		$showanonymous = false;
		$showstoringtime = false;
#		$columncount = 8;
		$showaction = ($can_storing && $CURUSER['id'] == $id);
		break;
		}
		case 'leeching': {
		$showsize = true;
		$showsenum = true;
		$showlenum = true;
		$showuploaded = true;
		$showdownloaded = true;
		$showratio = true;
		$showsetime = false;
		$showletime = false;
		$showcotime = false;
		$showanonymous = false;
		$showstoringtime = false;
#		$columncount = 8;
		$showaction = false;
		break;
		}
		case 'completed': {
		$showsize = false;
		$showsenum = false;
		$showlenum = false;
		$showuploaded = true;
		$showdownloaded = false;
		$showratio = false;
		$showsetime = true;
		$showletime = true;
		$showcotime = true;
		$showanonymous = false;
		$showstoringtime = false;
#		$columncount = 8;
		$showaction = false;
		break;
		}
		case 'incomplete': {
		$showsize = false;
		$showsenum = false;
		$showlenum = false;
		$showuploaded = true;
		$showdownloaded = true;
		$showratio = true;
		$showsetime = false;
		$showletime = true;
		$showcotime = false;
		$showanonymous = false;
		$showstoringtime = false;
#		$columncount = 7;
		$showaction = false;
		break;
		}
		
		case 'storing': {
		$showsize = true;
		$showsenum = true;
		$showlenum = false;
		$showuploaded = false;
		$showdownloaded = false;
		$showratio = false;
		$showsetime = false;
		$showletime = false;
		$showcotime = false;
		$showanonymous = false;
		$showstoringtime = true;
#		$columncount = 5;
		$showaction = false;
		break;
		}
		default: break;
	}
	$ret = "<table class=\"torrents no-vertical-line\" cellpadding=\"5\"><thead><tr><th class=\"unsortable\">".$lang_getusertorrentlistajax['col_type']."</th><th>".$lang_getusertorrentlistajax['col_name']."</th>".
	  ($showsize ? "<th><img class=\"size\" src=\"pic/trans.gif\" alt=\"size\" title=\"".$lang_getusertorrentlistajax['title_size']."\" /></th>" : "").
	  ($showsenum ? "<th><img class=\"seeders\" src=\"pic/trans.gif\" alt=\"seeders\" title=\"".$lang_getusertorrentlistajax['title_seeders']."\" /></th>" : "").
	  ($showlenum ? "<th><img class=\"leechers\" src=\"pic/trans.gif\" alt=\"leechers\" title=\"".$lang_getusertorrentlistajax['title_leechers']."\" /></th>" : "").
	  ($showuploaded ? "<th>".$lang_getusertorrentlistajax['col_uploaded']."</th>" : "") .
	  ($showdownloaded ? "<th>".$lang_getusertorrentlistajax['col_downloaded']."</th>" : "").
	  ($showratio ? "<th>".$lang_getusertorrentlistajax['col_ratio']."</th>" : "").
	  ($showsetime ? "<th>".$lang_getusertorrentlistajax['col_se_time']."</th>" : "").
	  ($showletime ? "<th>".$lang_getusertorrentlistajax['col_le_time']."</th>" : "").
	  ($showcotime ? "<th>".$lang_getusertorrentlistajax['col_time_completed']."</th>" : "").
	  ($showanonymous ? "<th>".$lang_getusertorrentlistajax['col_anonymous']."</th>" : "").
	  ($showstoringtime ? "<th>".$lang_getusertorrentlistajax['col_time_storing']."</th>" : "").
	  ($showaction ? '<th>动作</th>' : '').
	  "</tr></thead><tbody>\n";

	$torrents = $res->fetchAll();
	if ($id == $CURUSER['id']) {
	  $progress = torrenttable_progress(array_map(function($r) {
		return $r['torrent'];
	      }, $torrents));
	}
	else {
	  $progress = [];
	}
	
	foreach ($torrents as $arr) {
		$catimage = htmlspecialchars($arr["image"]);
		$catname = htmlspecialchars($arr["catname"]);

		$sphighlight = get_torrent_bg_color($arr['sp_state']);
		$sp_torrent = get_torrent_promotion_append($arr['sp_state']);

		//torrent name
		$dispname = $nametitle = htmlspecialchars($arr["torrentname"]);
		$count_dispname=mb_strlen($dispname,"UTF-8");
		$max_lenght_of_torrent_name = 70;
		if($count_dispname > $max_lenght_of_torrent_name)
			$dispname=mb_substr($dispname, 0, $max_lenght_of_torrent_name,"UTF-8") . "..";
		if ($smalldescription_main == 'yes'){
			//small description
			$dissmall_descr = htmlspecialchars(trim($arr["small_descr"]));
			$count_dissmall_descr=mb_strlen($dissmall_descr,"UTF-8");
			$max_lenght_of_small_descr=80; // maximum length
			if($count_dissmall_descr > $max_lenght_of_small_descr)
			{
				$dissmall_descr=mb_substr($dissmall_descr, 0, $max_lenght_of_small_descr,"UTF-8") . "..";
			}
		}
		else $dissmall_descr == "";
		$ret .= "<tr" .  $sphighlight  . "><td class=\"category-icon\">".return_category_image($arr['category'], "torrents.php?allsec=1&amp;")."</td>\n"; 
		/* "<td width=\"100%\" align=\"left\"><a href=\"".htmlspecialchars("details.php?id=".$arr['torrent']."&hit=1")."\" title=\"".$nametitle."\"><b>" . $dispname . "</b></a>". $sp_torrent .($dissmall_descr == "" ? "" : "<br />" . $dissmall_descr) . "</td>"; */
		ob_start();
		$row = ['name' => $arr['torrentname'],
			'id' => $arr['torrent'],
			'small_descr' => $arr['small_descr'],
			'sp_state' => $arr['sp_state'],
			'owner' => $arr['owner'],
			'size' => $arr['size'],
			'pos_state' => $arr['pos_state'],
			'info_hash' => $arr['info_hash'],
			'storing' => $arr['storing']];
		torrent_td($row, $progress);
		$ret .= ob_get_clean();
		//size
		if ($showsize)
			$ret .= "<td>". mksize_compact($arr['size'])."</td>";
		//number of seeders
		if ($showsenum)
			$ret .= "<td>".$arr['seeders']."</td>";
		//number of leechers
		if ($showlenum)
			$ret .= "<td>".$arr['leechers']."</td>";
		//uploaded amount
		if ($showuploaded){
			$uploaded = mksize_compact($arr["uploaded"]);
			$ret .= "<td>".$uploaded."</td>";
		}
		//downloaded amount
		if ($showdownloaded){
			$downloaded = mksize_compact($arr["downloaded"]);
			$ret .= "<td>".$downloaded."</td>";
		}
		//ratio
		if ($showratio){
			if ($arr['downloaded'] > 0)
			{
				$ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
				$ratio = "<font color=\"" . get_ratio_color($ratio) . "\">".$ratio."</font>";
			}
			elseif ($arr['uploaded'] > 0) $ratio = "Inf.";
			else $ratio = "---";
			$ret .= "<td>".$ratio."</td>";
		}
		if ($showsetime){
			$ret .= "<td>".mkprettytime($arr['seedtime'])."</td>";
		}
		if ($showletime){
			$ret .= "<td>".mkprettytime($arr['leechtime'])."</td>";
		}
		if ($showcotime)
			$ret .= "<td>"."". str_replace("&nbsp;", "<br />", gettime($arr['completedat'],false)). "</td>";
		if ($showanonymous)
			$ret .= "<td>".$arr['anonymous']."</td>";
		if($showstoringtime)
			$ret .= "<td>".mkprettytime($arr['out_seedtime']-$arr['in_seedtime'])."</td>";

		if ($showaction) {
		  $ret .= '<td>';
		  if ($can_storing && $arr['storing']) {
		    $storingKeeperList = storing_keeper_list($arr['torrent']);
		    if (in_array($CURUSER['id'], $storingKeeperList)) {
		      $ret .= '<a href="storing.php?action=checkout&torrentid=' . $arr['torrent'] . '&userid=' . $CURUSER['id'] .'" target="_blank">结束认领</a>';	
		    }
		    else {
		      $ret .= '<a href="storing.php?action=checkin&torrentid=' . $arr['torrent'] . '&userid=' . $CURUSER['id'] . '" target="_blank">认领</a>';
		    }
		  }
		  $ret .= '</td>';
		}
		$ret .="</tr>\n";
		
	}
	$ret .= "</tbody></table>\n";
	return $ret;
}

$id = 0+$_GET['userid'];
$type = $_GET['type'];
if (!in_array($type,array('uploaded','seeding','leeching','completed','incomplete','storing')))
die;
if(get_user_class() < $torrenthistory_class && $id != $CURUSER["id"])
permissiondenied();

switch ($type) {
case 'uploaded': {
  $res = sql_query("SELECT torrents.id AS torrent, torrents.name as torrentname, small_descr, seeders, leechers, anonymous, categories.name AS catname, category, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, size, snatched.seedtime, snatched.uploaded FROM torrents LEFT JOIN snatched ON torrents.id = snatched.torrentid LEFT JOIN categories ON torrents.category = categories.id WHERE torrents.owner=$id AND snatched.userid = $id " . (($CURUSER["id"] != $id) && (get_user_class() < $viewanonymous_class) ? " AND anonymous = 'no'":"") ." ORDER BY torrents.added DESC") or sqlerr(__FILE__, __LINE__);
		$count = _mysql_num_rows($res);
		if ($count > 0)
		{
			$torrentlist = maketable($res, 'uploaded');
		}
		break;
	}

	// Current Seeding
	case 'seeding':
	{
		$res = sql_query("SELECT torrent,added,snatched.uploaded,snatched.downloaded,torrents.name as torrentname, torrents.small_descr, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, torrents.storing, categories.name as catname,size,category,seeders,leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN snatched ON torrents.id = snatched.torrentid WHERE peers.userid=$id AND snatched.userid = $id AND peers.seeder='yes' ORDER BY torrents.added DESC") or sqlerr();
		$count = _mysql_num_rows($res);
		if ($count > 0){
			$torrentlist = maketable($res, 'seeding');
		}
		break;
	}

	// Current Leeching
	case 'leeching':
	{
		$res = sql_query("SELECT torrent,snatched.uploaded,snatched.downloaded,torrents.name as torrentname, torrents.small_descr, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, torrents.storing, categories.name as catname,size,category,seeders,leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN snatched ON torrents.id = snatched.torrentid WHERE peers.userid=$id AND snatched.userid = $id AND peers.seeder='no' ORDER BY torrents.added DESC") or sqlerr();
		$count = _mysql_num_rows($res);
		if ($count > 0){
			$torrentlist = maketable($res, 'leeching');
		}
		break;
	}

	// Completed torrents
	case 'completed':
	{
		$res = sql_query("SELECT torrents.id AS torrent, torrents.name AS torrentname, small_descr, categories.name AS catname, category, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, torrents.storing, size, snatched.uploaded, snatched.seedtime, snatched.leechtime, snatched.completedat FROM torrents LEFT JOIN snatched ON torrents.id = snatched.torrentid LEFT JOIN categories on torrents.category = categories.id WHERE snatched.finished='yes' AND torrents.owner != $id AND userid=$id ORDER BY snatched.completedat DESC") or sqlerr();
		$count = _mysql_num_rows($res);
		if ($count > 0)
		{
			$torrentlist = maketable($res, 'completed');
		}
		break;
	}

	// Incomplete torrents
	case 'incomplete':
	{
		$res = sql_query("SELECT torrents.id AS torrent, torrents.name AS torrentname, small_descr, categories.name AS catname, category, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, torrents.storing, size, snatched.uploaded, snatched.downloaded, snatched.leechtime FROM torrents LEFT JOIN snatched ON torrents.id = snatched.torrentid LEFT JOIN categories on torrents.category = categories.id WHERE snatched.finished='no' AND userid=$id AND torrents.owner != $id ORDER BY snatched.startdat DESC") or sqlerr();
		$count = _mysql_num_rows($res);
		if ($count > 0)
		{
			$torrentlist = maketable($res, 'incomplete');
		}
		break;
	}
	
	case 'storing':
	{

		$res = sql_query("SELECT torrents.id AS torrent, torrents.name AS torrentname, small_descr, categories.name AS catname, category, torrents.sp_state, torrents.pos_state, torrents.owner, torrents.info_hash, torrents.storing, size, seeders, storing_records.in_seedtime, storing_records.out_seedtime FROM torrents JOIN storing_records ON torrents.id = storing_records.torrent_id LEFT JOIN categories on torrents.category = categories.id WHERE checkout = 0 AND storing_records.keeper_id = $id ORDER BY storing_records.torrent_id DESC") or sqlerr();
	
		$count = _mysql_num_rows($res);
		if ($count > 0)
		{
			$torrentlist = maketable($res, 'storing');
		}
		break;
	}
	
	default: 
	{
		$count = 0;
		$torrentlist = "";
		break;
	}
}

if ($count)
echo "<b>".$count."</b>".$lang_getusertorrentlistajax['text_record'].add_s($count)."<br />".$torrentlist;
else
echo $lang_getusertorrentlistajax['text_no_record'];

