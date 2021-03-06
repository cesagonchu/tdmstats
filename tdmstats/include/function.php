<?php
/**
 * ****************************************************************************
 *  - TDMStats By TDM   - TEAM DEV MODULE FOR XOOPS
 *  - GNU Licence Copyright (c)  (http://www.)
 *
 * La licence GNU GPL, garanti � l'utilisateur les droits suivants
 *
 * 1. La libert� d'ex�cuter le logiciel, pour n'importe quel usage,
 * 2. La libert� de l' �tudier et de l'adapter � ses besoins,
 * 3. La libert� de redistribuer des copies,
 * 4. La libert� d'am�liorer et de rendre publiques les modifications afin
 * que l'ensemble de la communaut� en b�n�ficie.
 *
 * @copyright       	(http://www.tdmxoops.net)
 * @license        	http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		TDM ; TEAM DEV MODULE 
 *
 * ****************************************************************************
 */

include_once ('getresult.php');

// count the information that show on summary
function CountDays() {
	global $xoopsDB, $xoopsModule, $xoopsConfig;

    $date =formatTimeStamp(time(), 'Y-m-d');
	$week = formatTimeStamp(time(), 'W');
	$mth  = date('m');
	$year = formatTimeStamp(time(), 'Y');

	$days = getResult("select count(distinct date) as count from ".$xoopsDB->prefix("TDMStats_daycount")."");
    $result['days'] = $days[0]['count'];

	$total_visits = getResult("select * from ".$xoopsDB->prefix("TDMStats_count")."");
	$result['total'] = $total_visits[0]['count'];

	if ($result['total'] > 0) {
		$result['ava_day'] = $result['total'] / $result['days'];
		$result['ava_hour'] = $result['ava_day'] / 24;
		$result['ava_week'] = $result['ava_day'] * 7;
		$result['ava_mth'] = $result['ava_day'] * 30;
	} else {
		$result['ava_day'] = 0;
		$result['ava_hour'] = 0;
		$result['ava_week'] = 0;
		$result['ava_mth'] = 0;
		$result['total'] = 0;
	}

	$max_date = getResult("select * from ".$xoopsDB->prefix("TDMStats_daycount")." order by daycount desc limit 1");

	if ($max_date) {
		$result['max_date'] = strtotime($max_date[0]['date']);
		$result['max_daycount'] = $max_date[0]['daycount'];
	} else {
		$result['max_date'] = "------";
		$result['max_daycount'] = 0;
	}

	$today = getResult("select daycount from ".$xoopsDB->prefix("TDMStats_daycount")." where date='$date'");
	$this_week = getResult("select count from ".$xoopsDB->prefix("TDMStats_week_count")." where week='$week' and year='$year'");
	$this_mth = getResult("select count from ".$xoopsDB->prefix("TDMStats_mth")." where mth='$mth' and year='$year'");

	if ($today) {
		$result['today'] = $today[0]['daycount'];
	} else {
		$result['today'] = 0;
	}

	if ($this_week) {
		$result['this_week'] = $this_week[0]['count'];
	} else {
		$result['this_week'] = 0;
	}

	if ($this_mth) {
		$result['this_mth'] = $this_mth[0]['count'];
	} else {
		$result['this_mth'] = 0;
	}

	$max_week = getResult("select * from ".$xoopsDB->prefix("TDMStats_week_count")." order by count desc limit 1");
	$max_mth = getResult("select * from ".$xoopsDB->prefix("TDMStats_mth")." order by count desc limit 1");

	if ($max_week) {
		$result['max_week_w'] = $max_week[0]['week'];
		$result['max_week_y'] = $max_week[0]['year'];
		$result['max_weekcount'] = $max_week[0]['count'];
	} else {
		$result['max_week_w'] = false;
		$result['max_week_y'] = false;
		$result['max_week'] = "------";
		$result['max_weekcount'] = 0;
	}

	if ($max_mth) {
		$result['max_mth_m'] = $max_mth[0]['mth'];
		$result['max_mth_y'] = $max_mth[0]['year'];
		$result['max_mthcount'] = $max_mth[0]['count'];
	} else {
		$result['max_mth_m'] = false;
		$result['max_mth_y'] = false;
		$result['max_mth'] = "------";
		$result['max_mthcount'] = 0;
	}

/**
 * @changelog
 * v1.02 Total pages served can be displayed in counter.
 */
	$totalpages = getResult("select sum(count) as total from ".$xoopsDB->prefix("TDMStats_page")."");
    $result['totalpages'] = $totalpages[0]['total'];

///////

	if (isset($result)) {
		return $result;
	}
}

function CountAvg($item) {
	global $xoopsDB;

	if ($item == "hour") {
		$hour = getResult("select * from ".$xoopsDB->prefix("TDMStats_hour")." order by hour");
		$hour_sum = getResult("select sum(count) as sum from ".$xoopsDB->prefix("TDMStats_hour")."");

		for ($i=0; $i<sizeof($hour); $i++){
			$result[$i] = $hour[$i]['count'] / $hour_sum[0]['sum'];
			$result[$i] = sprintf("%.0f", $result[$i]);
		}

		$result['sum'] = $hour_sum[0]['sum'];
	}

	return $result;
}

// this function used by print day, week and month
function PrintStats($sum, $max, $visit, $period, $d_bar=380) {

	$total_bar = $d_bar + 10;
	for($i=0; $i<$period; $i++){
		if ($max > 0){
			$percent = $visit[$i]['count'] / $sum * 100;
			$percent = sprintf("%.2f", $percent);
			$bar = $visit[$i]['count'] / $max * $d_bar;
			$bar = sprintf("%.0f", $bar);
			$bg_bar = $total_bar - $bar;
		} else {
			$percent = 0;
			$bar = 0;
			$bg_bar = $total_bar;
		}

		if ($i % 2){
			$bg_color = "class='even'";
	    } else {
		    $bg_color = "class='odd'";
	    }

		$result[$i]['percent']  = $percent;
		$result[$i]['bar']      = $bar;
		$result[$i]['bg_bar']   = $bg_bar;
		$result[$i]['bg_color'] = $bg_color;
	}

	if (isset($result)) {
		return $result;
	}
}

function istats_PrettySize($size)
{
    $mb = 1024 * 1024;
    if ($size > $mb)
    {
        $mysize = sprintf ("%01.2f", $size / $mb) .' '.  _AM_ISTATS_MB;
    }elseif ($size >= 1024)
    {
        $mysize = sprintf ("%01.2f", $size / 1024) .' '.  _AM_ISTATS_KB;
    }
    else
    {
		$mysize = $size . " oc";
    }
    return $mysize;
}

/**
 * admin menu
 */
 function Adminmenu ($currentoption = 0, $breadcrumb = '') {      
		
	/* Nice buttons styles */
	echo "
    	<style type='text/css'>
    	#buttontop { float:left; width:100%; background: #e7e7e7; font-size:93%; line-height:normal; border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black; margin: 0; }
    	#buttonbar { float:left; width:100%; background: #e7e7e7 url('" . XOOPS_URL . "/modules/TDMStats/images/deco/bg.png') repeat-x left bottom; font-size:93%; line-height:normal; border-left: 1px solid black; border-right: 1px solid black; margin-bottom: 12px; }
    	#buttonbar ul { margin:0; margin-top: 15px; padding:10px 10px 0; list-style:none; }
		#buttonbar li { display:inline; margin:0; padding:0; }
		#buttonbar a { float:left; background:url('" . XOOPS_URL . "/modules/TDMStats/images/decos/left_both.png') no-repeat left top; margin:0; padding:0 0 0 9px; border-bottom:1px solid #000; text-decoration:none; }
		#buttonbar a span { float:left; display:block; background:url('" . XOOPS_URL . "/modules/TDMStats/images/decos/right_both.png') no-repeat right top; padding:5px 15px 4px 6px; font-weight:bold; color:#765; }
		/* Commented Backslash Hack hides rule from IE5-Mac \*/
		#buttonbar a span {float:none;}
		/* End IE5-Mac hack */
		#buttonbar a:hover span { color:#333; }
		#buttonbar #current a { background-position:0 -150px; border-width:0; }
		#buttonbar #current a span { background-position:100% -150px; padding-bottom:5px; color:#333; }
		#buttonbar a:hover { background-position:0% -150px; }
		#buttonbar a:hover span { background-position:100% -150px; }
		</style>
    ";
	
	global $xoopsModule, $xoopsConfig;
	$myts = &MyTextSanitizer::getInstance();
	
	$tblColors = Array();
	$tblColors[0] = $tblColors[1] = $tblColors[2] = $tblColors[3] = $tblColors[4] = $tblColors[5] = $tblColors[6] = $tblColors[7] = $tblColors[8] = '';
	$tblColors[$currentoption] = 'current';
	if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php')) {
		include_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php';
	} else {
		include_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/english/modinfo.php';
	}
	
	echo "<div id='buttontop'>";
	echo "<table style=\"width: 100%; padding: 0; \" cellspacing=\"0\"><tr>";
	//echo "<td style=\"width: 45%; font-size: 10px; text-align: left; color: #2F5376; padding: 0 6px; line-height: 18px;\"><a class=\"nobutton\" href=\"../../system/admin.php?fct=preferences&amp;op=showmod&amp;mod=" . $xoopsModule->getVar('mid') . "\">" . _AM_SF_OPTS . "</a> | <a href=\"import.php\">" . _AM_SF_IMPORT . "</a> | <a href=\"../index.php\">" . _AM_SF_GOMOD . "</a> | <a href=\"../help/index.html\" target=\"_blank\">" . _AM_SF_HELP . "</a> | <a href=\"about.php\">" . _AM_SF_ABOUT . "</a></td>";
	echo "<td style='font-size: 10px; text-align: left; color: #2F5376; padding: 0 6px; line-height: 18px;'>
	<a href='" . XOOPS_URL . "/modules/".$xoopsModule->getVar('dirname')."/index.php'>".$xoopsModule->getVar('dirname')."</a>
	</td>";
	echo "<td style='font-size: 10px; text-align: right; color: #2F5376; padding: 0 6px; line-height: 18px;'><b>" . $myts->displayTarea($xoopsModule->name()) . "  </b> ".$breadcrumb." </td>";
	echo "</tr></table>";
	echo "</div>";
	
	echo "<div id='buttonbar'>";
	echo "<ul>";
    echo "<li id='" . $tblColors[0] . "'><a href=\"" . XOOPS_URL . "/modules/".$xoopsModule->getVar('dirname')."/admin/index.php\"><span>"._AM_ISTATS_INDEXDESC."</span></a></li>";
	echo "<li id='" . $tblColors[1] . "'><a href=\"" . XOOPS_URL . "/modules/".$xoopsModule->getVar('dirname')."/admin/plug.php\"><span>"._AM_ISTATS_PLUG."</span></a></li>";
	echo "<li id='" . $tblColors[2] . "'><a href=\"" . XOOPS_URL . "/modules/".$xoopsModule->getVar('dirname')."/admin/about.php\"><span>"._AM_ISTATS_ABOUT."</span></a></li>";
	echo "<li id='" . $tblColors[3] . "'><a href=\"" . XOOPS_URL . "/modules/".$xoopsModule->getVar('dirname')."/admin/permissions.php\"><span>" ._AM_ISTATS_PERM. "</span></a></li>";
	echo "<li id='" . $tblColors[4] . "'><a href='../../system/admin.php?fct=preferences&amp;op=showmod&amp;mod=".$xoopsModule ->getVar('mid')."'><span>" ._AM_ISTATS_PERM. "</span></a></li>";
	echo "</ul></div>&nbsp;";
}
?>
