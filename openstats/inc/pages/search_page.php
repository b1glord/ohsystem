<?php

if (!isset($website) ) { header('HTTP/1.1 404 Not Found'); die; }

	
    $sth = $db->prepare("SELECT * FROM ".OSDB_ALIASES." ORDER BY alias_id ASC");
	$result = $sth->execute();
	$GameAliases = array();
	$DefaultGameType = 1;
	$currentYear  = date("Y", time() );
    $currentMonth = date("n", time() );
	$c = 0;
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	 $GameAliases[$c]["alias_id"] = $row["alias_id"];
	 $GameAliases[$c]["alias_name"] = $row["alias_name"];
	 
	 if ( isset($_GET["game_type"]) AND $_GET["game_type"] == $row["alias_id"] )
	 $GameAliases[$c]["selected"] = 'selected="selected"'; else $GameAliases[$c]["selected"] = '';
	 
	 if ( !isset($_GET["game_type"]) AND $row["default_alias"] == 1) {
	 $GameAliases[$c]["selected"] = 'selected="selected"';
	 $DefaultGameType = $row["alias_id"];
	 }
	 
	 $c++;
	}

      $s = safeEscape( $_GET["search"]);
	  $sth = $db->prepare("SELECT COUNT(*) FROM ".OSDB_STATS." WHERE (player) LIKE ?  LIMIT 1");
	  
	  $sth->bindValue(1, "%".strtolower($s)."%", PDO::PARAM_STR);
	  //$sth->bindValue(2, $DefaultGameType, PDO::PARAM_INT);
	  $result = $sth->execute();
	  $r = $sth->fetch(PDO::FETCH_NUM);
	  $numrows = $r[0];
	  $result_per_page = $TopPlayersPerPage;
	  $draw_pagination = 0;
	  include('inc/pagination.php');
	  $draw_pagination = 1;
	  
	  
	  $sth = $db->prepare("SELECT MAX(id) as id, pid, player, score, games, wins, losses, draw, kills, deaths, assists, creeps, denies, neutrals, towers, rax, banned, ip, alias_id
	  FROM ".OSDB_STATS." WHERE (player) LIKE ? 
	  GROUP BY player
	  ORDER BY id DESC, score DESC
	  LIMIT $offset, $rowsperpage");
	  
	  $sth->bindValue(1, "%".strtolower($s)."%", PDO::PARAM_STR);
	  //$sth->bindValue(2, $DefaultGameType, PDO::PARAM_INT);
	  $result = $sth->execute();
	  
	$c=0;
    $SearchData = array();
	if ( file_exists("inc/geoip/geoip.inc") ) {
	include("inc/geoip/geoip.inc");
	$GeoIPDatabase = geoip_open("inc/geoip/GeoIP.dat", GEOIP_STANDARD);
	$GeoIP = 1;
	}
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	if ( isset($GeoIP) AND $GeoIP == 1) {
	$SearchData[$c]["letter"]   = geoip_country_code_by_addr($GeoIPDatabase, $row["ip"]);
	$SearchData[$c]["country"]  = geoip_country_name_by_addr($GeoIPDatabase, $row["ip"]);
	}
	if ($GeoIP == 1 AND empty($SearchData[$c]["letter"]) ) { 
	$SearchData[$c]["letter"] = "blank";
	$SearchData[$c]["country"]  = "Reserved";
	}
	$SearchData[$c]["id"]        = (int)($row["pid"]);
	$SearchData[$c]["alias_id"]  = (($row["alias_id"])-1);
	$SearchData[$c]["player"]  = ($row["player"]);
	$SearchData[$c]["score"]  = number_format($row["score"],0);
	$SearchData[$c]["games"]  = number_format($row["games"],0);
	$SearchData[$c]["wins"]  = number_format($row["wins"],0);
	$SearchData[$c]["losses"]  = number_format($row["losses"],0);
	$SearchData[$c]["draw"]  = number_format($row["draw"],0);
	$SearchData[$c]["kills"]  = number_format($row["kills"],0);
	$SearchData[$c]["deaths"]  = number_format($row["deaths"],0);
	$SearchData[$c]["assists"]  = number_format($row["assists"],0);
	$SearchData[$c]["creeps"]  = number_format($row["creeps"],0);
	$SearchData[$c]["denies"]  = number_format($row["denies"],0);
	$SearchData[$c]["neutrals"]  = number_format($row["neutrals"],0);
	$SearchData[$c]["towers"]  = ($row["towers"]);
	$SearchData[$c]["rax"]  = ($row["rax"]);
	$SearchData[$c]["banned"]  = ($row["banned"]);
	$SearchData[$c]["ip"]  = ($row["ip"]);
	
	$c++;
	}
	if ( isset($GeoIP) AND $GeoIP == 1) geoip_close($GeoIPDatabase);
	
?>