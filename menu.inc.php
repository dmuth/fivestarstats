<?php
/**
* This file contains our menu hooks.
* 
* @author Douglas Muth <http://www.dmuth.org/>
*
*/


/**
* Our permissions function.
* For now, we'll leave this empty.
*/
function fivestarstats_perm() {
	$retval = array();
	return($retval);
}


/**
* Create our menu.
*/
function fivestarstats_menu() {

	$retval = array();

	//
	// Our main menu item.
	//
	$retval["admin/settings/fivestarstats"] = array(
		"title" => "Fivestar Stats",
		"page callback" => "fivestarstats_main",
		"page arguments" => array(3, 4, 5, 6),
		"access arguments" => array("administer site configuration"),
		"type" => MENU_NORMAL_ITEM,
		);

	//
	// View voting history of a specific IP
	//
	// (At some point I should remove this item, and make 
	// fivestarstats_main() do routing. That would be easier.)
	//
	$retval["admin/settings/fivestarstats/ip/%"] = array(
		"title" => "IP Voting Activity",
		"page callback" => "fivestarstats_ip",
		"page arguments" => array(4),
		"access arguments" => array("administer site configuration"),
		"type" => MENU_CALLBACK,
		);

	//
	// View all votes cast by a specific IP.
	//
	// (At some point I should remove this item, and make 
	// fivestarstats_main() do routing. That would be easier.)
	//
	$retval["admin/settings/fivestarstats/ip/%/votes/%"] = array(
		"title" => "All votes cast by an IP",
		"page callback" => "fivestarstats_ip_votes",
		"page arguments" => array(4, 6),
		"access arguments" => array("administer site configuration"),
		"type" => MENU_CALLBACK,
		);

	//
	// View votes cast on a specific user.
	//
	// (At some point I should remove this item, and make 
	// fivestarstats_main() do routing. That would be easier.)
	//
	$retval["admin/settings/fivestarstats/uid/%"] = array(
		"title" => "Votes cast on user",
		"page callback" => "fivestarstats_uid",
		"page arguments" => array(4),
		"access arguments" => array("administer site configuration"),
		"type" => MENU_CALLBACK,
		);

	$retval["admin/settings/fivestarstats/uid/%/votes/%"] = array(
		"title" => "Votes cast on user",
		"page callback" => "fivestarstats_uid_received_votes_detail",
		"page arguments" => array(4, 6),
		"access arguments" => array("administer site configuration"),
		"type" => MENU_CALLBACK,
		);

	return($retval);

} // End of fivestarstats_menu()



