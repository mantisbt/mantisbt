<?php

# Declare supported plugin events
event_declare_many( array(

	##### Events specific to plugins #####

	# Called when all plugins have been initialized
	'EVENT_PLUGIN_INIT' 		=> EVENT_TYPE_EXECUTE,

	##### Events for processing data #####

	# Called to process generic strings for output
	'EVENT_TEXT_GENERAL'		=> EVENT_TYPE_CHAIN,

	# Called to process strings with linkable content
	'EVENT_TEXT_LINKS'			=> EVENT_TYPE_CHAIN,

	# Called to process RSS output
	'EVENT_TEXT_RSS'			=> EVENT_TYPE_CHAIN,
	
	##### Events for layout additions #####

	# Called just before ending the <head> tag
	'EVENT_PAGE_HEAD' 			=> EVENT_TYPE_OUTPUT,

	# Called after the page logo has been included
	'EVENT_PAGE_TOP' 			=> EVENT_TYPE_OUTPUT,

	# Called before the page footer
	'EVENT_PAGE_BOTTOM'			=> EVENT_TYPE_OUTPUT,

	# Called just before ending the <body> tag
	'EVENT_PAGE_END'			=> EVENT_TYPE_OUTPUT,

) );

