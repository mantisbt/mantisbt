Just a personal freemind flash browser v.0.99

// CHANGES v1.0b

offsetX and offsetY didn't worked with numbers.
new conf attribute cssFile:/css/freemindbrowser.css
new conf attribute baseImagePath: will be added to all the images src's.
	ej: baseImagePath=/images/  src="house.gif" --> /images/house.gif

// CHANGES v1.0a

new button "fit".
Now mouse wheel scale map.

// CHANGES v99

minor modifications plus:

some new atributes:

defaultToolTipWordWrap: max width for tooltips.
offsetX: for the center of the mindmap // admit also "left" and "right"
offsetY: for the center of the mindmap // admit also "top" and "bottom"
scaleTooltips: has been requested, default is true
toolTipsBgColor: bgcolor for tooltips ej;"0xaaeeaa"
now you can use "flashfreemind.css" for tooltips stile:
	p {
	font-family: Arial;
	color: #664400;
	}

max_alpha_buttons: for dynamic view of buttons
min_alpha_buttons: for dynamic view of buttons

buttonsPos: "top" or "bottom"


/////CHANGES v98

added sad icon :(
modiffied some other for better looking
somo suport for freemind 9, for richcontent. It doesn't work very well.

some new options on the html configuration.

	//for hiding the upper options
	// default="false"
		fo.addVariable("justMap","true");

	//for hiding shape of the main node
	// default="false"
		fo.addVariable("mainNodeShape","none");


////V97////

It does only load jpg's images (limitation of flash7 and olders), in this versión, if you have flash8
will load png and gif.

 it will improve with time.
 
 For the easy of development flashout (http://www.potapenko.com/flashout/) have
 been used with Eclipse.

USE:
 - insert in any browser page like in the example.

 CONFIGURATION:
	All this variables can be added in the script. None of then if needed, they all
	have default values.

	//Where to open a link: 
	//default="_self"
		fo.addVariable("openUrl", "_self");

	// for changing the WordWrap size
		fo.addVariable("defaultWordWrap","300"); //default 600

	// for changing to old elipseNode edges
		fo.addVariable("noElipseMode","anyValue");

	// IF we want to initiate de freemind with al the nodes collapset from this level
	// =default "-1" that means, do nothing
		fo.addVariable("startCollapsedToLevel","1");

	// Initial mindmap to load
	// default="index.mm"
		fo.addVariable("initLoadFile", "index.mm");

	// To create de main node with a diferent shape.	// default="elipse "
		fo.addVariable("mainNodeShape", "rectangle");

	//set max width of a text node
	// default="600"
		fo.addVariable("defaultWordWrap", "600");

	//width of  snapshots
	// default="600"
		fo.addVariable("ShotsWidth", "600");

	//generate snapshots for all the mindmaps reachable from throught the main mindmap
	// default="false"
		fo.addVariable("genAllShots", "true");

	//for every mindmap loaded start the visualization with all the nodes unfolded
	// default="false"
		fo.addVariable("unfoldAll", "true");


 
CONFIGURATION OLD MODE:
	 		
	For iexplorer
	 <param name="FlashVars" value="initLoadFile=index.mm"/>
	For others
	 <embed FlashVars="initLoadFile=index.mm" 