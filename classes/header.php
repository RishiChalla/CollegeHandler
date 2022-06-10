<?php

/**
 * Stores the details required to make a page header
 */
class Header {
	/** The title of the page */
	public $title;

	/** The sub title of the page */
	public $subTitle;

	/** Which sidebar item is active */
	public $active;

	/** Whether or not to include the top search bar */
	public $search;

	/** If search is true, action link */
	public $searchAction;

	/** Whether or not to use the container fluid */
	public $containerFluid = true;

	public $additionalNav = "";
}

?>