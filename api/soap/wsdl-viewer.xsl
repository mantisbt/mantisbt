<?xml version="1.0" encoding="utf-8"?>

<!--
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
-->


<!--
* ====================================================================
* wsdl-viewer.xsl
* Version: 3.1.02-mantis.1
*
* URL: http://tomi.vanek.sk/xml/wsdl-viewer.xsl
*
* Author: tomi vanek
* Inspiration: Uche Ogbui - WSDL processing with XSLT
* 		http://www-106.ibm.com/developerworks/library/ws-trans/index.html
* ====================================================================
-->


<!--
* ====================================================================
* Description:
* 		wsdl-viewer.xsl is a lightweight XSLT 1.0 transformation with minimal
* 		usage of any hacks that extend the possibilities of the transformation
* 		over the XSLT 1.0 constraints but eventually would harm the engine independance.
*
* 		The transformation has to run even in the browser offered XSLT engines
* 		(tested in IE 6 and Firefox) and in ANT "batch" processing.
* ====================================================================
* How to add the HTML look to a WSDL:
* 		<?xml version="1.0" encoding="utf-8"?>
* 		<?xml-stylesheet type="text/xsl" href="wsdl-viewer.xsl"?>
* 		<wsdl:definitions ...>
* 		    ... Here is the service declaration
* 		</wsdl:definitions>
*
* 		The web-browsers (in Windows) are not able by default automatically recognize
* 		the ".wsdl" file type (suffix). For the type recognition the WSDL file has
* 		to be renamed by adding the suffix ".xml" - i.e. "myservice.wsdl.xml".
* ====================================================================
* Constraints:
* 	1. Processing of imported files
* 		1.1 Only 1 imported WSDL and 1 imported XSD is processed
* 			(well, maybe with a smarter recursive strategy this restriction could be overcome)
* 		1.2 No recursive including is supported (i.e. includes in included XSD are ignored)
* 	2. Namespace support
* 		2.1 Namespaces are not taken in account by processing (references with NS)
* 	3. Source code
* 		3.1 Only the source code allready processed by the XML parser is rendered - implications:
* 			== no access to the XML head line (<?xml version="1.0" encoding="utf-8"?>)
* 			== "expanded" CDATA blocks (parser processes the CDATA,
* 				XSLT does not have access to the original code)
* 			== no control over the code page
* 			== processing of special characters
* 			== namespace nodes are not rendered (just the namespace aliases)
* ====================================================================
* Possible improvements:
* 	* Functional requirements
* 		+ SOAP 1.2 binding (http://schemas.xmlsoap.org/wsdl/soap12/WSDL11SOAP12.pdf)
* 		+ WSDL 2.0 (http://www.w3.org/TR/2006/CR-wsdl20-primer-20060327/)
* 		+ Recognition of WSDL patterns (interface, binding, service instance, ...)
* 		- Creating an xsd-viewer.xsl for XML-Schema file viewing
* 			(extracting the functionality from wsdl-viewer into separate XSLT)
* 		- Check the full support of the WSDL and XSD going through the standards
* 		- Real-world WSDL testing
* 		- XSLT 2.0 (http://www-128.ibm.com/developerworks/library/x-xslt20pt5.html) ???
* 		? Adding more derived information
* 			* to be defined, what non-trivial information can we read out from the WSDL
* 	* XSLT
* 		+ Modularization
* 			- Is it meaningful?
* 			- Maybe more distribution alternatives (modular, fat monolithic, thin performance monolithic)?
* 			- Distribution build automatization
* 		+ Dynamic page: JavaSript
* 		+ Performance
* 		- Better code comments / documentation
* 		- SOAP client form - for testing the web service (AJAX based)
* 		- New XSD parser - clean-up the algorithm
* 		- Complete (recursive, multiple) include support
* 		? Namespace-aware version (no string processing hacks ;-)
* 			* I think, because of the goal to support as many engines as possible,
* 				this requirement is unrealistic. Maybe when XSLT 2.0 will be supported
* 				in a huge majority of platforms, we can rethink this point....
* 				(problems with different functionality of namespace-uri XPath function by different engines)
* 	* Development architecture
* 		- Setup of the development infrastructure
* 		- Unit testing
* 		? Collaboration platform
* 	* Documentation, web
* 		- Better user guide
* 		? Forum, Wiki
* ====================================================================
-->


<!--
* ====================================================================
* History:
* 	2005-04-15 - Initial implementation
* 	2005-09-12 - Removed xsl:key to be able to use the James Clark's XT engine on W3C web-site
* 	2006-10-06 - Removed the Oliver Becker's method of conditional selection
* 				of a value in a single expression (in Xalan/XSLTC this hack does not work!)
* 	2005-10-07 - Duplicated operations
* 	2006-12-08 - Import element support
* 	2006-12-14 - Displays all fault elements (not just the first one)
* 	2006-12-28 - W3C replaced silently the James Clark's XT engine with Michael Kay's closed-source Saxon!
* 				wsdl-viewer.xsl will no longer support XT engine
* 	2007-02-28 - Stack-overflow bug (if the XSD element @name and @type are identic)
* 	2007-03-08 - 3.0.00 - New parsing, new layout
* 	2007-03-28 - 3.0.01 - Fix: New anti-recursion defense (no error message by recursion
* 						because of dirty solution of namespace processing)
* 						- Added: variables at the top to turn on/off certain details
* 	2007-03-29 - 3.0.02 - Layout clean-up for IE
* 	2007-03-29 - 3.0.03 - Fix: Anti-recursion algorithm
* 	2007-03-30 - 3.0.04 - Added: source code rendering of imported WSDL and XSD
* 	2007-04-15 - 3.0.05 - Fix: Recursive calls in element type rendering
* 						- Fix: Rendering of messages (did not render the message types of complex types)
* 						- Fix: Links in src. by arrays
* 						- Fix: $binding-info
* 	2007-04-15 - 3.0.06 - Added: Extended rendering control ENABLE-xxx parameters
* 						- Changed: Anti-recursion algorithm has recursion-depth parameter
* 	2007-07-19 - 3.0.07 - Fix: Rendering of array type in detail
* 	2007-08-01 - 3.0.08 - Fix: xsl:template name="render-type"
* 						  Fix: typo - "Impotred WSDL" should be "Impotred WSDL"
* 	2007-08-16 - 3.0.09 - Fix: xsl:template name="render-type" - anti recursion
* 	2007-12-05 - 3.1.00 - Modularized
* 	2007-12-23 - 3.1.01 - Terminating message by WS without interface or service definition was removed
* 						  (seems to be a correct state)
* 	2008-08-20 - 3.1.02 - Woden-214: Anti-recursion bypassed in xsd:choice element
* 	2015-02-02 - 3.1.02-mantis.1 - removed unnecessary wsdl-viewer.js <script> block
* ====================================================================
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:ws="http://schemas.xmlsoap.org/wsdl/"
                xmlns:ws2="http://www.w3.org/ns/wsdl"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
                xmlns:local="http://tomi.vanek.sk/xml/wsdl-viewer"
                version="1.0"
                exclude-result-prefixes="ws ws2 xsd soap local">

   <xsl:output method="xml" version="1.0" encoding="utf-8" indent="no"
               omit-xml-declaration="no"
               media-type="text/html"
               doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
               doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

   <xsl:strip-space elements="*"/>

   <xsl:param name="wsdl-viewer.version">3.1.01</xsl:param>



   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-global.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:param name="ENABLE-SERVICE-PARAGRAPH" select="true()"/>
   <xsl:param name="ENABLE-OPERATIONS-PARAGRAPH" select="true()"/>
   <xsl:param name="ENABLE-SRC-CODE-PARAGRAPH" select="true()"/>
   <xsl:param name="ENABLE-ABOUT-PARAGRAPH" select="true()"/>
   <xsl:param name="ENABLE-OPERATIONS-TYPE" select="true()"/>
   <xsl:param name="ENABLE-LINK" select="true()"/>
   <xsl:param name="ENABLE-INOUTFAULT" select="true()"/>
   <xsl:param name="ENABLE-STYLEOPTYPEPATH" select="true()"/>
   <xsl:param name="ENABLE-DESCRIPTION" select="true()"/>
   <xsl:param name="ENABLE-PORTTYPE-NAME" select="true()"/>
   <xsl:param name="ENABLE-ANTIRECURSION-PROTECTION" select="true()"/>
   <xsl:param name="ANTIRECURSION-DEPTH">3</xsl:param>
   <xsl:variable name="GENERATED-BY">Generated by wsdl-viewer.xsl</xsl:variable>
   <xsl:variable name="PORT-TYPE-TEXT">Port type</xsl:variable>
   <xsl:variable name="IFACE-TEXT">Interface</xsl:variable>
   <xsl:variable name="SOURCE-CODE-TEXT">Source code</xsl:variable>
   <xsl:variable name="RECURSIVE"> ... is recursive</xsl:variable>
   <xsl:variable name="SRC-PREFIX">src.</xsl:variable>
   <xsl:variable name="SRC-FILE-PREFIX">src.file.</xsl:variable>
   <xsl:variable name="OPERATIONS-PREFIX">op.</xsl:variable>
   <xsl:variable name="PORT-PREFIX">port.</xsl:variable>
   <xsl:variable name="IFACE-PREFIX">iface.</xsl:variable>
   <xsl:variable name="global.wsdl-name"
                 select="/*/*[(local-name() = 'import' or local-name() = 'include') and @location][1]/@location"/>
   <xsl:variable name="consolidated-wsdl" select="/* | document($global.wsdl-name)/*"/>
   <xsl:variable name="global.xsd-name"
                 select="($consolidated-wsdl/*[local-name() = 'types']//xsd:import[@schemaLocation] | $consolidated-wsdl/*[local-name() = 'types']//xsd:include[@schemaLocation])[1]/@schemaLocation"/>
   <xsl:variable name="consolidated-xsd"
                 select="(document($global.xsd-name)/xsd:schema/xsd:*|/*/*[local-name() = 'types']/xsd:schema/xsd:*)[local-name() = 'complexType' or local-name() = 'element' or local-name() = 'simpleType']"/>
   <xsl:variable name="global.service-name"
                 select="concat($consolidated-wsdl/ws:service/@name, $consolidated-wsdl/ws2:service/@name)"/>
   <xsl:variable name="global.binding-name"
                 select="concat($consolidated-wsdl/ws:binding/@name, $consolidated-wsdl/ws2:binding/@name)"/>
   <xsl:variable name="html-title">
	     <xsl:apply-templates select="/*" mode="html-title.render"/>
   </xsl:variable>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-global.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->



<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-css.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:variable name="css">

/**
	wsdl-viewer.css
*/

/**
=========================================
	Body
=========================================
*/
html {
	background-color: teal;
}

body {
	margin: 0;
	padding: 0;
	height: auto;
	color: white;
	background-color: teal;
	font: normal 80%/120% Arial, Helvetica, sans-serif;
}

#outer_box {
	padding: 3px 3px 3px 194px;
}

#inner_box {
	width: auto;
	background-color: white;
	color: black;
	border: 1px solid navy;
}

/**
=========================================
	Fixed box with links
=========================================
*/
#outer_links { 
	position: fixed;
	left: 0px;
	top: 0px;
	margin: 3px;
	padding: 1px;
	z-index: 200; 
	width: 180px;
	height: auto;
	background-color: gainsboro;
	padding-top: 2px;
	border: 1px solid navy;
}

* html #outer_links /* Override above rule for IE */ 
{ 
	position: absolute; 
	width: 188px;
	top: expression(offsetParent.scrollTop + 0); 
} 

#links {
	margin: 1px;
	padding: 3px;
	background-color: white;
	height: 350px;
	overflow: auto;
	border: 1px solid navy;
}

#links ul {
	left: -999em;
	list-style: none;
	margin: 0;
	padding: 0;
	z-index: 100;
}

#links li {
	margin: 0;
	padding: 2px 4px;
	width: auto;
	z-index: 100;
}

#links ul li {
	margin: 0;
	padding: 2px 4px;
	width: auto;
	z-index: 100;
}

#links a {
	display: block;
	padding: 0 2px;
	color: blue;
	width: auto;
	border: 1px solid white;
	text-decoration: none;
	white-space: nowrap;
}

#links a:hover {
	color: white;
	background-color: gray;
	border: 1px solid gray;
} 


/**
=========================================
	Navigation tabs
=========================================
*/

#outer_nav {
	background-color: yellow;
	padding: 0;
	margin: 0;
}

#nav {
	height: 100%;
	width: auto;
	margin: 0;
	padding: 0;
	background-color: gainsboro;
	border-top: 1px solid gray;
	border-bottom: 3px solid gray;
	z-index: 100;
	font: bold 90%/120% Arial, Helvetica, sans-serif;
	letter-spacing: 2px;
} 

#nav ul { 
	background-color: gainsboro;
	height: auto;
	width: auto;
	list-style: none;
	margin: 0;
	padding: 0;
	z-index: 100;

	border: 1px solid silver; 
	border-top-color: black; 
	border-width: 1px 0 9px; 
} 

#nav li { 
	display: inline; 
	padding: 0;
	margin: 0;
} 

#nav a { 
	position: relative;
	top: 3px;
	float:left; 
	width:auto; 
	padding: 8px 10px 6px 10px;
	margin: 3px 3px 0;
	border: 1px solid gray; 
	border-width: 2px 2px 3px 2px;

	color: black; 
	background-color: silver; 
	text-decoration:none; 
	text-transform: uppercase;
}

#nav a:hover { 
	margin-top: 1px;
	padding-top: 9px;
	padding-bottom: 7px;
	color: blue;
	background-color: gainsboro;
} 

#nav a.current:link,
#nav a.current:visited,
#nav a.current:hover {
	background: white; 
	color: black; 
	text-shadow:none; 
	margin-top: 0;
	padding-top: 11px;
	padding-bottom: 9px;
	border-bottom-width: 0;
	border-color: red; 
}

#nav a:active { 
	background-color: silver; 
	color: white;
} 



/**
=========================================
	Content
=========================================
*/
#header {
	margin: 0;
	padding: .5em 4em;
	color: white;
	background-color: red;
	border: 1px solid darkred;
}

#content {
	margin: 0;
	padding: 0 2em .5em;
}

#footer {
	clear: both;
	margin: 0;
	padding: .5em 2em;
	color: gray;
	background-color: gainsboro;
	font-size: 80%;
	border-top: 1px dotted gray;
	text-align: right
}

.single_column {
	padding: 10px 10px 10px 10px;
	/*margin: 0px 33% 0px 0px; */
	margin: 3px 0;
}

#flexi_column {
	padding: 10px 10px 10px 10px;
	/*margin: 0px 33% 0px 0px; */
	margin: 0px 212px 0px 0px;
}

#fix_column {
	float: right;
	padding: 10px 10px 10px 10px;
	margin: 0px;
	width: 205px;
	/*width: 30%; */
	voice-family: "\"}\"";
	voice-family:inherit;
	/* width: 30%; */
	width: 205px;
}
html&gt;body #rightColumn {
	width: 205px; /* ie5win fudge ends */
} /* Opera5.02 shows a 2px gap between. N6.01Win sometimes does.
	Depends on amount of fill and window size and wind direction. */

/**
=========================================
	Label / value
=========================================
*/

.page {
	border-bottom: 3px dotted navy;
	margin: 0;
	padding: 10px 0 20px 0;
}

.value, .label {
	margin: 0;
	padding: 0;
}

.label {
	float: left;
	width: 140px;
	text-align: right;
	font-weight: bold;
	padding-bottom: .5em;
	margin-right: 0;
	color: darkblue;
}

.value {
	margin-left: 147px;
	color: darkblue;
	padding-bottom: .5em;
}

strong, strong a {
	color: darkblue;
	font-weight: bold;
	letter-spacing: 1px;
	margin-left: 2px;
}


/**
=========================================
	Links
=========================================
*/

a.local:link,
a.local:visited {
	color: blue; 
	margin-left: 10px;
	border-bottom: 1px dotted blue;
	text-decoration: none;
	font-style: italic;
}

a.local:hover {
	background-color: gainsboro; 
	color: darkblue;
	padding-bottom: 1px;
	border-bottom: 1px solid darkblue;
}

a.target:link,
a.target:visited,
a.target:hover
{
	text-decoration: none;
	background-color: transparent;
	border-bottom-type: none;
}

/**
=========================================
	Box, Shadow
=========================================
*/

.box {
	padding: 6px;
	color: black;
	background-color: gainsboro;
	border: 1px solid gray;
}

.shadow {
	background: silver;
	position: relative;
	top: 5px;
	left: 4px;
}

.shadow div {
	position: relative;
	top: -5px;
	left: -4px;
}

/**
=========================================
	Floatcontainer
=========================================
*/

.spacer
{
	display: block;
	height: 0;
	font-size: 0;
	line-height: 0;
	margin: 0;
	padding: 0;
	border-style: none;
	clear: both; 
	visibility:hidden;
}

.floatcontainer:after {
	content: ".";
	display: block;
	height: 0;
	font-size:0; 
	clear: both;
	visibility:hidden;
}
.floatcontainer{
	display: inline-table;
} /* Mark Hadley's fix for IE Mac */ /* Hides from IE Mac \*/ * 
html .floatcontainer {
	height: 1%;
}
.floatcontainer{
	display:block;
} /* End Hack 
*/ 

/**
=========================================
	Source code
=========================================
*/

.indent {
	margin: 2px 0 2px 20px;
}

.xml-element, .xml-proc, .xml-comment {
	margin: 2px 0;
	padding: 2px 0 2px 0;
}

.xml-element {
	word-spacing: 3px;
	color: red;
	font-weight: bold;
	font-style:normal;
	border-left: 1px dotted silver;
}

.xml-element div {
	margin: 2px 0 2px 40px;
}

.xml-att {
	color: blue;
	font-weight: bold;
}

.xml-att-val {
	color: blue;
	font-weight: normal;
}

.xml-proc {
	color: darkred;
	font-weight: normal;
	font-style: italic;
}

.xml-comment {
	color: green;
	font-weight: normal;
	font-style: italic;
}

.xml-text {
	color: green;
	font-weight: normal;
	font-style: normal;
}


/**
=========================================
	Heading
=========================================
*/
h1, h2, h3 {
	margin: 10px 10px 2px;
	font-family: Georgia, Times New Roman, Times, Serif;
	font-weight: normal;
	}

h1 {
	font-weight: bold;
	letter-spacing: 3px;
	font-size: 220%;
	line-height: 100%;
}

h2 {
	font-weight: bold;
	font-size: 175%;
	line-height: 200%;
}

h3 {
	font-size: 150%;
	line-height: 150%;
	font-style: italic;
}

/**
=========================================
	Content formatting
=========================================
*/
.port {
	margin-bottom: 10px;
	padding-bottom: 10px;
	border-bottom: 1px dashed gray;
}

.operation {
	margin-bottom: 20px;
	padding-bottom: 10px;
	border-bottom: 1px dashed gray;
}


/* --------------------------------------------------------
	Printing
*/

/*
@media print
{
	#outer_links, #outer_nav { 
		display: none;
	}
*/

	#outer_box {
		padding: 3px;
	}
/* END print media definition
}
*/

</xsl:variable>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-css.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->




<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-util.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:template match="@*" mode="qname.normalized">
	     <xsl:variable name="local" select="substring-after(., ':')"/>
	     <xsl:choose>
		       <xsl:when test="$local">
            <xsl:value-of select="$local"/>
         </xsl:when>
		       <xsl:otherwise>
            <xsl:value-of select="."/>
         </xsl:otherwise>
	     </xsl:choose>
   </xsl:template>
   <xsl:template match="ws:definitions | ws2:description" mode="html-title.render">
	     <xsl:choose>
		       <xsl:when test="$global.service-name">
            <xsl:value-of select="concat('Web Service: ', $global.service-name)"/>
         </xsl:when>
		       <xsl:when test="$global.binding-name">
            <xsl:value-of select="concat('WS Binding: ', $global.binding-name)"/>
         </xsl:when>
		       <xsl:when test="ws2:interface/@name">
            <xsl:value-of select="concat('WS Interface: ', ws2:interface/@name)"/>
         </xsl:when>
		       <xsl:otherwise>Web Service Fragment</xsl:otherwise>
         <!--		<xsl:otherwise><xsl:message terminate="yes">Syntax error in element <xsl:call-template name="src.syntax-error.path"/></xsl:message>
		</xsl:otherwise>
-->
	</xsl:choose>
   </xsl:template>
   <xsl:template name="src.syntax-error">
	     <xsl:message terminate="yes">Syntax error by WSDL source rendering in element <xsl:call-template name="src.syntax-error.path"/>
      </xsl:message>
   </xsl:template>
   <xsl:template name="src.syntax-error.path">
	     <xsl:for-each select="parent::*">
         <xsl:call-template name="src.syntax-error.path"/>
      </xsl:for-each>
	     <xsl:value-of select="concat('/', name(), '[', position(), ']')"/>
   </xsl:template>
   <xsl:template match="*[local-name(.) = 'documentation']" mode="documentation.render">
	     <xsl:if test="$ENABLE-DESCRIPTION and string-length(.) &gt; 0">
		       <div class="label">Description:</div>
		       <div class="value">
            <xsl:value-of select="." disable-output-escaping="yes"/>
         </div>
	     </xsl:if>
   </xsl:template>
   <xsl:template name="render.source-code-link">
	     <xsl:if test="$ENABLE-SRC-CODE-PARAGRAPH and $ENABLE-LINK">
		       <a class="local" href="{concat('#', $SRC-PREFIX, generate-id(.))}">
            <xsl:value-of select="$SOURCE-CODE-TEXT"/>
         </a>
	     </xsl:if>
   </xsl:template>
   <xsl:template name="about.detail">
      <xsl:param name="version"/>
      <div>
	This page has been generated by <big>wsdl-viewer.xsl</big>, version <xsl:value-of select="$version"/>
         <br/>
	Author: <a href="http://tomi.vanek.sk/">tomi vanek</a>
         <br/>
	Download at <a href="http://tomi.vanek.sk/xml/wsdl-viewer.xsl">http://tomi.vanek.sk/xml/wsdl-viewer.xsl</a>.<br/>
	        <br/>
	The transformation was inspired by the article<br/>
	Uche Ogbuji: <a href="http://www-106.ibm.com/developerworks/library/ws-trans/index.html">WSDL processing with XSLT</a>
         <br/>
      </div>
   </xsl:template>
   <xsl:template name="processor-info.render">
      <xsl:text>
</xsl:text>
      <xsl:text>This document was generated by </xsl:text>
      <a href="{system-property('xsl:vendor-url')}">
         <xsl:value-of select="system-property('xsl:vendor')"/>
      </a>
      <xsl:text> XSLT engine.
</xsl:text>

      <xsl:text>The engine processed the WSDL in XSLT </xsl:text>
      <xsl:value-of select="format-number(system-property('xsl:version'), '#.0')"/>
      <xsl:text> compliant mode.
</xsl:text>

   </xsl:template>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-util.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->



<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-service.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:template match="ws:service|ws2:service" mode="service-start">
	     <div class="indent">
		       <div class="label">Target Namespace:</div>
		       <div class="value">
            <xsl:value-of select="$consolidated-wsdl/@targetNamespace"/>
         </div>
		       <xsl:apply-templates select="*[local-name(.) = 'documentation']" mode="documentation.render"/>
		       <xsl:apply-templates select="ws:port|ws2:endpoint" mode="service"/>
	     </div>
   </xsl:template>
   <xsl:template match="ws2:endpoint" mode="service">
	     <xsl:variable name="binding-name">
		       <xsl:apply-templates select="@binding" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:variable name="binding" select="$consolidated-wsdl/ws2:binding[@name = $binding-name]"/>

	     <xsl:variable name="binding-type" select="$binding/@type"/>
	     <xsl:variable name="binding-protocol" select="$binding/@*[local-name() = 'protocol']"/>
	     <xsl:variable name="protocol">
		       <xsl:choose>
			         <xsl:when test="starts-with($binding-type, 'http://schemas.xmlsoap.org/wsdl/soap')">SOAP 1.1</xsl:when>
			         <xsl:when test="starts-with($binding-type, 'http://www.w3.org/2005/08/wsdl/soap')">SOAP 1.2</xsl:when>
			         <xsl:when test="starts-with($binding-type, 'http://schemas.xmlsoap.org/wsdl/mime')">MIME</xsl:when>
			         <xsl:when test="starts-with($binding-type, 'http://schemas.xmlsoap.org/wsdl/http')">HTTP</xsl:when>
			         <xsl:otherwise>Unknown</xsl:otherwise>
		       </xsl:choose>

		       <!-- TODO: Add all bindings to transport protocols -->
		<xsl:choose>
			         <xsl:when test="starts-with($binding-protocol, 'http://www.w3.org/2003/05/soap/bindings/HTTP')"> over HTTP</xsl:when>
			         <xsl:otherwise/>
		       </xsl:choose>
	     </xsl:variable>

	     <div class="label">Location:</div>
	     <div class="value">
         <xsl:value-of select="@address"/>
      </div>

	     <div class="label">Protocol:</div>
	     <div class="value">
         <xsl:value-of select="$protocol"/>
      </div>

	     <xsl:apply-templates select="$binding" mode="service"/>

	     <xsl:variable name="iface-name">
		       <xsl:apply-templates select="../@interface" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws2:interface[@name = $iface-name]" mode="service"/>

   </xsl:template>
   <xsl:template match="ws2:interface" mode="service">
	     <h3>Interface <b>
            <xsl:value-of select="@name"/>
         </b>
         <xsl:if test="$ENABLE-LINK">
            <xsl:text> </xsl:text>
            <small>
               <xsl:if test="$ENABLE-OPERATIONS-PARAGRAPH">
                  <a class="local" href="#{concat($PORT-PREFIX, generate-id(.))}"> 
                     <xsl:value-of select="$PORT-TYPE-TEXT"/>
                  </a>
               </xsl:if> 
               <xsl:call-template name="render.source-code-link"/>
            </small>
         </xsl:if>
      </h3>

	     <xsl:variable name="base-iface-name">
		       <xsl:apply-templates select="@extends" mode="qname.normalized"/>
	     </xsl:variable>

	     <xsl:if test="$base-iface-name">
		       <div class="label">Extends: </div>
		       <div class="value">
            <xsl:value-of select="$base-iface-name"/>
         </div>
	     </xsl:if>

	     <xsl:variable name="base-iface"
                    select="$consolidated-wsdl/ws2:interface[@name = $base-iface-name]"/>

	     <div class="label">Operations:</div>
	     <div class="value">
         <xsl:text>
</xsl:text>
		       <ol style="line-height: 180%;">
			         <xsl:apply-templates select="$base-iface/ws2:operation | ws2:operation" mode="service">
				           <xsl:sort select="@name"/>
			         </xsl:apply-templates>
		       </ol>
	     </div>
   </xsl:template>
   <xsl:template match="ws:port" mode="service">
	     <xsl:variable name="binding-name">
		       <xsl:apply-templates select="@binding" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:variable name="binding" select="$consolidated-wsdl/ws:binding[@name = $binding-name]"/>

	     <xsl:variable name="binding-uri"
                    select="namespace-uri( $binding/*[local-name() = 'binding'] )"/>
	     <xsl:variable name="protocol">
		       <xsl:choose>
			         <xsl:when test="starts-with($binding-uri, 'http://schemas.xmlsoap.org/wsdl/soap')">SOAP</xsl:when>
			         <xsl:when test="starts-with($binding-uri, 'http://schemas.xmlsoap.org/wsdl/mime')">MIME</xsl:when>
			         <xsl:when test="starts-with($binding-uri, 'http://schemas.xmlsoap.org/wsdl/http')">HTTP</xsl:when>
			         <xsl:otherwise>unknown</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:variable name="port-type-name">
		       <xsl:apply-templates select="$binding/@type" mode="qname.normalized"/>
	     </xsl:variable>

	     <xsl:variable name="port-type"
                    select="$consolidated-wsdl/ws:portType[@name = $port-type-name]"/>


	     <h3>Port <b>
            <xsl:value-of select="@name"/>
         </b>
         <xsl:if test="$ENABLE-LINK">
            <xsl:text> </xsl:text>
            <small>
               <xsl:if test="$ENABLE-OPERATIONS-PARAGRAPH">
                  <a class="local" href="#{concat($PORT-PREFIX, generate-id($port-type))}"> 
                     <xsl:value-of select="$PORT-TYPE-TEXT"/>
                  </a>
               </xsl:if> 
               <xsl:call-template name="render.source-code-link"/>
            </small>
         </xsl:if>
      </h3>

	     <div class="label">Location:</div>
	     <div class="value">
         <xsl:value-of select="*[local-name() = 'address']/@location"/>
      </div>

	     <div class="label">Protocol:</div>
	     <div class="value">
         <xsl:value-of select="$protocol"/>
      </div>

	     <xsl:apply-templates select="$binding" mode="service"/>

	     <div class="label">Operations:</div>
	     <div class="value">
         <xsl:text>
</xsl:text>
		       <ol style="line-height: 180%;">
			         <xsl:apply-templates select="$consolidated-wsdl/ws:portType[@name = $port-type-name]/ws:operation"
                                 mode="service">
				           <xsl:sort select="@name"/>
			         </xsl:apply-templates>
		       </ol>
	     </div>
   </xsl:template>
   <xsl:template match="ws:operation|ws2:operation" mode="service">
	     <li>
         <big>
            <i>
               <xsl:value-of select="@name"/>
            </i>
         </big>
         <xsl:if test="$ENABLE-LINK">
		          <xsl:if test="$ENABLE-OPERATIONS-PARAGRAPH">
               <a class="local" href="{concat('#', $OPERATIONS-PREFIX, generate-id(.))}">Detail</a>
            </xsl:if> 
            <xsl:call-template name="render.source-code-link"/>
         </xsl:if>
	     </li>
   </xsl:template>
   <xsl:template match="ws:binding|ws2:binding" mode="service">
	     <xsl:variable name="real-binding" select="*[local-name() = 'binding']|self::ws2:*"/>

	     <xsl:if test="$real-binding/@style">
		       <div class="label">Default style:</div>
		       <div class="value">
            <xsl:value-of select="$real-binding/@style"/>
         </div>
	     </xsl:if>


	     <xsl:if test="$real-binding/@transport|$real-binding/*[local-name() = 'protocol']">
		       <xsl:variable name="protocol"
                       select="concat($real-binding/@transport, $real-binding/*[local-name() = 'protocol'])"/>
		       <div class="label">Transport protocol:</div>
		       <div class="value">
			         <xsl:choose>
				           <xsl:when test="$protocol = 'http://schemas.xmlsoap.org/soap/http'">SOAP over HTTP</xsl:when>
				           <xsl:otherwise>
                  <xsl:value-of select="$protocol"/>
               </xsl:otherwise>
			         </xsl:choose>
		       </div>
	     </xsl:if>

	     <xsl:if test="$real-binding/@verb">
		       <div class="label">Default method:</div>
		       <div class="value">
            <xsl:value-of select="$real-binding/@verb"/>
         </div>
	     </xsl:if>
   </xsl:template>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-service.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->



<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-operations.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:template match="ws2:interface" mode="operations">
	     <xsl:if test="$ENABLE-PORTTYPE-NAME">
	        <h3>
		          <a name="{concat($IFACE-PREFIX, generate-id(.))}">
               <xsl:value-of select="$IFACE-TEXT"/>
               <xsl:text>
</xsl:text>
               <b> 
                  <xsl:value-of select="@name"/> 
               </b>
            </a>
		          <xsl:call-template name="render.source-code-link"/>
	        </h3>
	     </xsl:if>

	     <ol>
		       <xsl:apply-templates select="ws2:operation" mode="operations">
			         <xsl:sort select="@name"/>
		       </xsl:apply-templates>
	     </ol>
   </xsl:template>
   <xsl:template match="ws2:operation" mode="operations">
	     <xsl:variable name="binding-info"
                    select="$consolidated-wsdl/ws2:binding[@interface = current()/../@name or substring-after(@interface, ':') = current()/../@name]/ws2:operation[@ref = current()/@name or substring-after(@ref, ':') = current()/@name]"/>
      <li>
         <xsl:if test="position() != last()">
            <xsl:attribute name="class">operation</xsl:attribute>
         </xsl:if>
         <big>
            <b>
               <a name="{concat($OPERATIONS-PREFIX, generate-id(.))}">
                  <xsl:value-of select="@name"/>
               </a>
            </b>
         </big>
	        <div class="value">
            <xsl:text>
</xsl:text>
            <xsl:call-template name="render.source-code-link"/>
         </div>
	        <xsl:apply-templates select="ws2:documentation" mode="documentation.render"/>

	        <xsl:if test="$ENABLE-STYLEOPTYPEPATH">
		<!-- TODO: add the operation attributes - according the WSDL 2.0 spec. -->
	</xsl:if>
	        <xsl:apply-templates select="ws2:input|ws2:output|../ws2:fault[@name = ws2:infault/@ref or @name = ws2:outfault/@ref]"
                              mode="operations.message">
		          <xsl:with-param name="binding-data" select="$binding-info"/>
	        </xsl:apply-templates>
      </li>
   </xsl:template>
   <xsl:template match="ws2:input|ws2:output|ws2:fault" mode="operations.message">
	     <xsl:param name="binding-data"/>
	     <xsl:if test="$ENABLE-INOUTFAULT">
		       <div class="label">
            <xsl:value-of select="concat(translate(substring(local-name(.), 1, 1), 'abcdefghijklmnoprstuvwxyz', 'ABCDEFGHIJKLMNOPRSTUVWXYZ'), substring(local-name(.), 2), ':')"/>
         </div>

		       <div class="value">
			         <xsl:variable name="type-name">
				           <xsl:apply-templates select="@element" mode="qname.normalized"/>
			         </xsl:variable>
	
			         <xsl:call-template name="render-type">
				           <xsl:with-param name="type-local-name" select="$type-name"/>
			         </xsl:call-template>

			         <xsl:call-template name="render.source-code-link"/>

			         <xsl:variable name="type-tree"
                          select="$consolidated-xsd[@name = $type-name and not(xsd:simpleType)][1]"/>
			         <xsl:apply-templates select="$type-tree" mode="operations.message.part"/>
		       </div>
	     </xsl:if>
   </xsl:template>
   <xsl:template match="ws:portType" mode="operations">
      <div>
         <xsl:if test="position() != last()">
            <xsl:attribute name="class">port</xsl:attribute>
         </xsl:if>
         <xsl:if test="$ENABLE-PORTTYPE-NAME">
            <h3>
	              <a name="{concat($PORT-PREFIX, generate-id(.))}">
                  <xsl:value-of select="$PORT-TYPE-TEXT"/>
                  <xsl:text>
</xsl:text>
                  <b> 
                     <xsl:value-of select="@name"/> 
                  </b>
               </a>
	              <xsl:call-template name="render.source-code-link"/>
            </h3>
         </xsl:if>
         <ol>
            <xsl:apply-templates select="ws:operation" mode="operations">
	              <xsl:sort select="@name"/>
            </xsl:apply-templates>
         </ol>
      </div>
   </xsl:template>
   <xsl:template match="ws:operation" mode="operations">
	     <xsl:variable name="binding-info"
                    select="$consolidated-wsdl/ws:binding[@type = current()/../@name or substring-after(@type, ':') = current()/../@name]/ws:operation[@name = current()/@name]"/>
      <li>
         <xsl:if test="position() != last()">
            <xsl:attribute name="class">operation</xsl:attribute>
         </xsl:if>
         <big>
            <b>
               <a name="{concat($OPERATIONS-PREFIX, generate-id(.))}">
                  <xsl:value-of select="@name"/>
               </a>
            </b>
         </big>
	        <div class="value">
            <xsl:text>
</xsl:text>
            <xsl:call-template name="render.source-code-link"/>
         </div>

	        <xsl:if test="$ENABLE-DESCRIPTION and string-length(ws:documentation) &gt; 0">
		          <div class="label">Description:</div>
		          <div class="value">
               <xsl:value-of select="ws:documentation" disable-output-escaping="yes"/>
            </div>
	        </xsl:if>

	        <xsl:if test="$ENABLE-STYLEOPTYPEPATH">
		          <xsl:variable name="binding-operation" select="$binding-info/*[local-name() = 'operation']"/>
		          <xsl:if test="$binding-operation/@style">
			            <div class="label">Style:</div>
			            <div class="value">
                  <xsl:value-of select="$binding-operation/@style"/>
               </div>
		          </xsl:if>
	
		          <div class="label">Operation type:</div>
		          <div class="value">
		             <xsl:choose>
			               <xsl:when test="$binding-info/ws:input[not(../ws:output)]">
                     <i>One-way.</i> The endpoint receives a message.</xsl:when>
			               <xsl:when test="$binding-info/ws:input[following-sibling::ws:output]">
                     <i>Request-response.</i> The endpoint receives a message, and sends a correlated message.</xsl:when>
			               <xsl:when test="$binding-info/ws:input[preceding-sibling::ws:output]">
                     <i>Solicit-response.</i> The endpoint sends a message, and receives a correlated message.</xsl:when>
			               <xsl:when test="$binding-info/ws:output[not(../ws:input)]">
                     <i>Notification.</i> The endpoint sends a message.</xsl:when>
			               <xsl:otherwise>unknown</xsl:otherwise>
		             </xsl:choose>
		          </div>
	
		          <xsl:if test="string-length($binding-operation/@soapAction) &gt; 0">
			            <div class="label">SOAP action:</div>
			            <div class="value">
                  <xsl:value-of select="$binding-operation/@soapAction"/>
               </div>
		          </xsl:if>
	
		          <xsl:if test="$binding-operation/@location">
			            <div class="label">HTTP path:</div>
			            <div class="value">
                  <xsl:value-of select="$binding-operation/@location"/>
               </div>
		          </xsl:if>
	        </xsl:if>
	        <xsl:apply-templates select="ws:input|ws:output|ws:fault" mode="operations.message">
		          <xsl:with-param name="binding-data" select="$binding-info"/>
	        </xsl:apply-templates>
      </li>
   </xsl:template>
   <xsl:template match="ws:input|ws:output|ws:fault" mode="operations.message">
	     <xsl:param name="binding-data"/>
	     <xsl:if test="$ENABLE-INOUTFAULT">
		       <div class="label">
            <xsl:value-of select="concat(translate(substring(local-name(.), 1, 1), 'abcdefghijklmnoprstuvwxyz', 'ABCDEFGHIJKLMNOPRSTUVWXYZ'), substring(local-name(.), 2), ':')"/>
         </div>
	
		       <xsl:variable name="msg-local-name" select="substring-after(@message, ':')"/>
		       <xsl:variable name="msg-name">
			         <xsl:choose>
				           <xsl:when test="$msg-local-name">
                  <xsl:value-of select="$msg-local-name"/>
               </xsl:when>
				           <xsl:otherwise>
                  <xsl:value-of select="@message"/>
               </xsl:otherwise>
			         </xsl:choose>
		       </xsl:variable>
	
		       <xsl:variable name="msg" select="$consolidated-wsdl/ws:message[@name = $msg-name]"/>
		       <xsl:choose>
			         <xsl:when test="$msg">
				           <xsl:apply-templates select="$msg" mode="operations.message">
					             <xsl:with-param name="binding-data"
                                  select="$binding-data/ws:*[local-name(.) = local-name(current())]/*"/>
				           </xsl:apply-templates>
			         </xsl:when>
			         <xsl:otherwise>
               <div class="value">
                  <i>none</i>
               </div>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:if>
   </xsl:template>
   <xsl:template match="ws:message" mode="operations.message">
	     <xsl:param name="binding-data"/>
	     <div class="value">
		       <xsl:value-of select="@name"/>
		       <xsl:if test="$binding-data">
			         <xsl:text> (</xsl:text>
			         <xsl:value-of select="name($binding-data)"/>
			         <xsl:variable name="use" select="$binding-data/@use"/>
			         <xsl:if test="$use">
               <xsl:text>, use = </xsl:text>
               <xsl:value-of select="$use"/>
            </xsl:if>
			         <xsl:variable name="part" select="$binding-data/@part"/>
			         <xsl:if test="$part">
               <xsl:text>, part = </xsl:text>
               <xsl:value-of select="$part"/>
            </xsl:if>
			         <xsl:text>)</xsl:text>
		       </xsl:if>
		       <xsl:call-template name="render.source-code-link"/>
	     </div>

	     <xsl:apply-templates select="ws:part" mode="operations.message"/>
   </xsl:template>
   <xsl:template match="ws:part" mode="operations.message">
	     <div class="value box" style="margin-bottom: 3px">
		       <xsl:choose>
			         <xsl:when test="string-length(@name) &gt; 0">
				           <b>
                  <xsl:value-of select="@name"/>
               </b>

				           <xsl:variable name="elem-or-type">
					             <xsl:choose>
						               <xsl:when test="@type">
                        <xsl:value-of select="@type"/>
                     </xsl:when>
						               <xsl:otherwise>
                        <xsl:value-of select="@element"/>
                     </xsl:otherwise>
					             </xsl:choose>
				           </xsl:variable>

				           <xsl:variable name="type-local-name" select="substring-after($elem-or-type, ':')"/>
				           <xsl:variable name="type-name">
					             <xsl:choose>
						               <xsl:when test="$type-local-name">
                        <xsl:value-of select="$type-local-name"/>
                     </xsl:when>
						               <xsl:when test="$elem-or-type">
                        <xsl:value-of select="$elem-or-type"/>
                     </xsl:when>
						               <xsl:otherwise>unknown</xsl:otherwise>
					             </xsl:choose>
				           </xsl:variable>

				           <xsl:call-template name="render-type">
					             <xsl:with-param name="type-local-name" select="$type-name"/>
				           </xsl:call-template>

				           <xsl:variable name="part-type"
                             select="$consolidated-xsd[@name = $type-name and not(xsd:simpleType)][1]"/>
				           <xsl:apply-templates select="$part-type" mode="operations.message.part"/>

			         </xsl:when>
			         <xsl:otherwise>
               <i>none</i>
            </xsl:otherwise>
		       </xsl:choose>
	     </div>
   </xsl:template>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-operations.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->



<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-xsd-tree.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:template match="xsd:simpleType" mode="operations.message.part"/>
   <xsl:template name="recursion.should.continue">
	     <xsl:param name="anti.recursion"/>
	     <xsl:param name="recursion.label"/>
	     <xsl:param name="recursion.count">1</xsl:param>
	     <xsl:variable name="has.recursion" select="contains($anti.recursion, $recursion.label)"/>
	     <xsl:variable name="anti.recursion.fragment"
                    select="substring-after($anti.recursion, $recursion.label)"/>
	     <xsl:choose>
		       <xsl:when test="$recursion.count &gt; $ANTIRECURSION-DEPTH"/>

		       <xsl:when test="not($ENABLE-ANTIRECURSION-PROTECTION) or string-length($anti.recursion) = 0 or not($has.recursion)">
			         <xsl:text>1</xsl:text>
		       </xsl:when>

		       <xsl:otherwise>
			         <xsl:call-template name="recursion.should.continue">
				           <xsl:with-param name="anti.recursion" select="$anti.recursion.fragment"/>
				           <xsl:with-param name="recursion.label" select="$recursion.label"/>
				           <xsl:with-param name="recursion.count" select="$recursion.count + 1"/>
			         </xsl:call-template>
		       </xsl:otherwise>
	     </xsl:choose>
   </xsl:template>
   <xsl:template match="xsd:complexType" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>

	     <xsl:variable name="recursion.label" select="concat('[', @name, ']')"/>
	     <xsl:variable name="recursion.test">
		       <xsl:call-template name="recursion.should.continue">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
			         <xsl:with-param name="recursion.label" select="$recursion.label"/>
		       </xsl:call-template>
	     </xsl:variable>

	     <xsl:choose>
		       <xsl:when test="string-length($recursion.test) != 0">
			         <xsl:apply-templates select="*" mode="operations.message.part">
				           <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
			         </xsl:apply-templates>
		       </xsl:when>
		       <xsl:otherwise>
			         <small style="color:blue">
				           <xsl:value-of select="$RECURSIVE"/>
			         </small>
		       </xsl:otherwise>
	     </xsl:choose>

   </xsl:template>
   <xsl:template match="xsd:complexContent" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>

	     <xsl:apply-templates select="*" mode="operations.message.part">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:complexType[descendant::xsd:attribute[ not(@*[local-name() = 'arrayType']) ]]"
                 mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="recursion.label" select="concat('[', @name, ']')"/>
	     <xsl:variable name="recursion.test">
		       <xsl:call-template name="recursion.should.continue">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
			         <xsl:with-param name="recursion.label" select="$recursion.label"/>
		       </xsl:call-template>
	     </xsl:variable>

	     <xsl:choose>
		       <xsl:when test="string-length($recursion.test) != 0">
			         <ul type="circle">
				           <xsl:apply-templates select="*" mode="operations.message.part">
					             <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
				           </xsl:apply-templates>
			         </ul>
		       </xsl:when>
		       <xsl:otherwise>
			         <small style="color:blue">
				           <xsl:value-of select="$RECURSIVE"/>
			         </small>
		       </xsl:otherwise>
	     </xsl:choose>
   </xsl:template>
   <xsl:template match="xsd:restriction | xsd:extension" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="type-local-name" select="substring-after(@base, ':')"/>
	     <xsl:variable name="type-name">
		       <xsl:choose>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="@base">
               <xsl:value-of select="@base"/>
            </xsl:when>
			         <xsl:otherwise>unknown type</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>
	     <xsl:variable name="base-type" select="$consolidated-xsd[@name = $type-name][1]"/>
	     <!-- xsl:if test="not($type/@abstract)">
		<xsl:apply-templates select="$type"/>
	</xsl:if -->
	<xsl:if test="$base-type != 'Array'">
		       <xsl:apply-templates select="$base-type" mode="operations.message.part">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
		       </xsl:apply-templates>
	     </xsl:if>
	     <xsl:apply-templates select="*" mode="operations.message.part">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:union" mode="operations.message.part">
	     <xsl:call-template name="process-union">
		       <xsl:with-param name="set" select="@memberTypes"/>
	     </xsl:call-template>
   </xsl:template>
   <xsl:template name="process-union">
	     <xsl:param name="set"/>
	     <xsl:if test="$set">
		       <xsl:variable name="item" select="substring-before($set, ' ')"/>
		       <xsl:variable name="the-rest" select="substring-after($set, ' ')"/>

		       <xsl:variable name="type-local-name" select="substring-after($item, ':')"/>
		       <xsl:variable name="type-name">
			         <xsl:choose>
				           <xsl:when test="$type-local-name">
                  <xsl:value-of select="$type-local-name"/>
               </xsl:when>
				           <xsl:otherwise>
                  <xsl:value-of select="$item"/>
               </xsl:otherwise>
			         </xsl:choose>
		       </xsl:variable>

		       <xsl:call-template name="render-type">
			         <xsl:with-param name="type-local-name" select="$type-name"/>
		       </xsl:call-template>

		       <xsl:call-template name="process-union">
			         <xsl:with-param name="set" select="$the-rest"/>
		       </xsl:call-template>
	     </xsl:if>
   </xsl:template>
   <xsl:template match="xsd:sequence" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <ul type="square">
		       <xsl:apply-templates select="*" mode="operations.message.part">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
		       </xsl:apply-templates>
	     </ul>
   </xsl:template>
   <xsl:template match="xsd:all|xsd:any|xsd:choice" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="list-type">
		       <xsl:choose>
			         <xsl:when test="self::xsd:all">disc</xsl:when>
			         <xsl:when test="self::xsd:any">circle</xsl:when>
			         <xsl:otherwise>square</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:element name="ul">
		       <xsl:attribute name="style">
			         <xsl:value-of select="concat('list-style-type:', $list-type)"/>
		       </xsl:attribute>
		       <xsl:apply-templates select="*" mode="operations.message.part">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
		       </xsl:apply-templates>
	     </xsl:element>
   </xsl:template>
   <xsl:template match="xsd:element[parent::xsd:schema]" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="recursion.label" select="concat('[', @name, ']')"/>
	     <xsl:variable name="recursion.test">
		       <xsl:call-template name="recursion.should.continue">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
			         <xsl:with-param name="recursion.label" select="$recursion.label"/>
		       </xsl:call-template>
	     </xsl:variable>

	     <xsl:choose>
		       <xsl:when test="string-length($recursion.test) != 0">
			         <xsl:variable name="type-name">
               <xsl:call-template name="xsd.element-type"/>
            </xsl:variable>
			         <xsl:variable name="elem-type"
                          select="$consolidated-xsd[generate-id() != generate-id(current()) and $type-name and @name=$type-name and contains(local-name(), 'Type')][1]"/>
	
			         <xsl:if test="$type-name != @name">
				           <xsl:apply-templates select="$elem-type" mode="operations.message.part">
					             <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
				           </xsl:apply-templates>
	
				           <xsl:if test="not($elem-type)">
					             <xsl:call-template name="render-type">
						               <xsl:with-param name="type-local-name" select="$type-name"/>
					             </xsl:call-template>
				           </xsl:if>
		
				           <xsl:apply-templates select="*" mode="operations.message.part">
					             <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
				           </xsl:apply-templates>
			         </xsl:if>
		       </xsl:when>
		       <xsl:otherwise>
			         <small style="color:blue">
				           <xsl:value-of select="$RECURSIVE"/>
			         </small>
		       </xsl:otherwise>
	     </xsl:choose>

   </xsl:template>
   <xsl:template match="xsd:element | xsd:attribute" mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
      <!--
	<xsl:variable name="recursion.label" select="concat('[', @name, ']')"/>
-->
	<li>
		       <xsl:variable name="local-ref" select="concat(@name, substring-after(@ref, ':'))"/>
		       <xsl:variable name="elem-name">
			         <xsl:choose>
				           <xsl:when test="@name">
                  <xsl:value-of select="@name"/>
               </xsl:when>
				           <xsl:when test="$local-ref">
                  <xsl:value-of select="$local-ref"/>
               </xsl:when>
				           <xsl:when test="@ref">
                  <xsl:value-of select="@ref"/>
               </xsl:when>
				           <xsl:otherwise>anonymous</xsl:otherwise>
			         </xsl:choose>
		       </xsl:variable>
		       <xsl:value-of select="$elem-name"/>

		       <xsl:variable name="type-name">
            <xsl:call-template name="xsd.element-type"/>
         </xsl:variable>

		       <xsl:call-template name="render-type">
			         <xsl:with-param name="type-local-name" select="$type-name"/>
		       </xsl:call-template>

		       <xsl:variable name="elem-type"
                       select="$consolidated-xsd[@name = $type-name and contains(local-name(), 'Type')][1]"/>
		       <xsl:apply-templates select="$elem-type | *" mode="operations.message.part">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
		       </xsl:apply-templates>
	     </li>
   </xsl:template>
   <xsl:template match="xsd:attribute[ @*[local-name() = 'arrayType'] ]"
                 mode="operations.message.part">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="array-local-name"
                    select="substring-after(@*[local-name() = 'arrayType'], ':')"/>
	     <xsl:variable name="type-local-name" select="substring-before($array-local-name, '[')"/>
	     <xsl:variable name="array-type" select="$consolidated-xsd[@name = $type-local-name][1]"/>

	     <xsl:variable name="recursion.label" select="concat('[', $type-local-name, ']')"/>
	     <xsl:variable name="recursion.test">
		       <xsl:call-template name="recursion.should.continue">
			         <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
			         <xsl:with-param name="recursion.label" select="$recursion.label"/>
		       </xsl:call-template>
	     </xsl:variable>

	     <xsl:choose>
		       <xsl:when test="string-length($recursion.test) != 0">
			         <xsl:apply-templates select="$array-type" mode="operations.message.part">
				           <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
			         </xsl:apply-templates>
		       </xsl:when>
		       <xsl:otherwise>
			         <small style="color:blue">
				           <xsl:value-of select="$RECURSIVE"/>
			         </small>
		       </xsl:otherwise>
	     </xsl:choose>
   </xsl:template>
   <xsl:template name="xsd.element-type">
	     <xsl:variable name="ref-or-type">
		       <xsl:choose>
			         <xsl:when test="@type">
               <xsl:value-of select="@type"/>
            </xsl:when>
			         <xsl:otherwise>
               <xsl:value-of select="@ref"/>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:variable name="type-local-name" select="substring-after($ref-or-type, ':')"/>
	     <xsl:variable name="type-name">
		       <xsl:choose>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="$ref-or-type">
               <xsl:value-of select="$ref-or-type"/>
            </xsl:when>
			         <xsl:otherwise>undefined</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>
	     <xsl:value-of select="$type-name"/>
   </xsl:template>
   <xsl:template match="xsd:documentation" mode="operations.message.part">
	     <div style="color:green">
         <xsl:value-of select="." disable-output-escaping="yes"/>
      </div>
   </xsl:template>
   <xsl:template name="render-type">
	     <xsl:param name="anti.recursion"/>
	     <xsl:param name="type-local-name"/>

	     <xsl:if test="$ENABLE-OPERATIONS-TYPE">
		       <xsl:variable name="properties">
			         <xsl:if test="self::xsd:element | self::xsd:attribute[parent::xsd:complexType]">
				           <xsl:variable name="min">
                  <xsl:if test="@minOccurs = '0'">optional</xsl:if>
               </xsl:variable>
				           <xsl:variable name="max">
                  <xsl:if test="@maxOccurs = 'unbounded'">unbounded</xsl:if>
               </xsl:variable>
				           <xsl:variable name="nillable">
                  <xsl:if test="@nillable">nillable</xsl:if>
               </xsl:variable>
	
				           <xsl:if test="(string-length($min) + string-length($max) + string-length($nillable) + string-length(@use)) &gt; 0">
					             <xsl:text> - </xsl:text>
					             <xsl:value-of select="$min"/>
					             <xsl:if test="string-length($min) and string-length($max)">
                     <xsl:text>, </xsl:text>
                  </xsl:if>
					             <xsl:value-of select="$max"/>
					             <xsl:if test="(string-length($min) + string-length($max)) &gt; 0 and string-length($nillable)">
                     <xsl:text>, </xsl:text>
                  </xsl:if>
					             <xsl:value-of select="$nillable"/>
					             <xsl:if test="(string-length($min) + string-length($max) + string-length($nillable)) &gt; 0 and string-length(@use)">
                     <xsl:text>, </xsl:text>
                  </xsl:if>
					             <xsl:value-of select="@use"/>
					             <xsl:text>; </xsl:text>
				           </xsl:if>
			         </xsl:if>
		       </xsl:variable>

		       <xsl:variable name="recursion.label" select="concat('[', $type-local-name, ']')"/>
		       <xsl:variable name="recursion.test">
			         <xsl:call-template name="recursion.should.continue">
				           <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
				           <xsl:with-param name="recursion.label" select="$recursion.label"/>
				           <xsl:with-param name="recursion.count" select="$ANTIRECURSION-DEPTH"/>
			         </xsl:call-template>
		       </xsl:variable>

		       <xsl:if test="string-length($recursion.test) != 0">
			         <small style="color:blue">
				           <xsl:value-of select="$properties"/>
				           <xsl:variable name="elem-type"
                             select="$consolidated-xsd[@name = $type-local-name and (not(contains(local-name(current()), 'element')) or contains(local-name(), 'Type'))][1]"/>
				           <xsl:if test="string-length($type-local-name) &gt; 0">
					             <xsl:call-template name="render-type.write-name">
						               <xsl:with-param name="type-local-name" select="$type-local-name"/>
					             </xsl:call-template>
				           </xsl:if>

				           <xsl:choose>
					             <xsl:when test="$elem-type">

						               <xsl:apply-templates select="$elem-type" mode="render-type">
							                 <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
						               </xsl:apply-templates>
					             </xsl:when>
					             <xsl:otherwise>

						               <xsl:apply-templates select="*" mode="render-type">
							                 <xsl:with-param name="anti.recursion" select="concat($anti.recursion, $recursion.label)"/>
						               </xsl:apply-templates>
					             </xsl:otherwise>
				           </xsl:choose>
			         </small>
		       </xsl:if>
	     </xsl:if>
   </xsl:template>
   <xsl:template name="render-type.write-name">
	     <xsl:param name="type-local-name"/>
	     <xsl:text> type </xsl:text>
	     <big>
         <i>
		          <xsl:choose>
			            <xsl:when test="$type-local-name">
                  <xsl:value-of select="$type-local-name"/>
               </xsl:when>
			            <xsl:otherwise>undefined</xsl:otherwise>
		          </xsl:choose>
	        </i>
      </big>
   </xsl:template>
   <xsl:template match="*" mode="render-type"/>
   <xsl:template match="xsd:element | xsd:complexType | xsd:simpleType | xsd:complexContent"
                 mode="render-type">
	     <xsl:param name="anti.recursion"/>
	     <xsl:apply-templates select="*" mode="render-type">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:restriction[ parent::xsd:simpleType ]" mode="render-type">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="type-local-name" select="substring-after(@base, ':')"/>
	     <xsl:variable name="type-name">
		       <xsl:choose>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="@base">
               <xsl:value-of select="@base"/>
            </xsl:when>
			         <xsl:otherwise>undefined</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:text> - </xsl:text>
	     <xsl:call-template name="render-type.write-name">
		       <xsl:with-param name="type-local-name" select="$type-local-name"/>
	     </xsl:call-template>
	     <xsl:text> with </xsl:text>
	     <xsl:value-of select="local-name()"/>
	     <xsl:apply-templates select="*" mode="render-type">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:simpleType/xsd:restriction/xsd:*[not(self::xsd:enumeration)]"
                 mode="render-type">
	     <xsl:text> </xsl:text>
	     <xsl:value-of select="local-name()"/>
	     <xsl:text>(</xsl:text>
	     <xsl:value-of select="@value"/>
	     <xsl:text>)</xsl:text>
   </xsl:template>
   <xsl:template match="xsd:restriction | xsd:extension" mode="render-type">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="type-local-name" select="substring-after(@base, ':')"/>
	     <xsl:variable name="type-name">
		       <xsl:choose>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="@base">
               <xsl:value-of select="@base"/>
            </xsl:when>
			         <xsl:otherwise>undefined</xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>
	     <xsl:variable name="base-type" select="$consolidated-xsd[@name = $type-name][1]"/>
	     <xsl:variable name="abstract">
         <xsl:if test="$base-type/@abstract">abstract </xsl:if>
      </xsl:variable>

	     <xsl:if test="not($type-name = 'Array')">
		       <xsl:value-of select="concat(' - ', local-name(), ' of ', $abstract)"/>
		       <xsl:call-template name="render-type.write-name">
			         <xsl:with-param name="type-local-name" select="$type-name"/>
		       </xsl:call-template>
	     </xsl:if>

	     <xsl:apply-templates select="$base-type | *" mode="render-type">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:attribute[ @*[local-name() = 'arrayType'] ]" mode="render-type">
	     <xsl:param name="anti.recursion"/>
	     <xsl:variable name="array-local-name"
                    select="substring-after(@*[local-name() = 'arrayType'], ':')"/>
	     <xsl:variable name="type-local-name" select="substring-before($array-local-name, '[')"/>
	     <xsl:variable name="array-type" select="$consolidated-xsd[@name = $type-local-name][1]"/>

	     <xsl:text> - array of </xsl:text>
	     <xsl:call-template name="render-type.write-name">
		       <xsl:with-param name="type-local-name" select="$type-local-name"/>
	     </xsl:call-template>

	     <xsl:apply-templates select="$array-type" mode="render-type">
		       <xsl:with-param name="anti.recursion" select="$anti.recursion"/>
	     </xsl:apply-templates>
   </xsl:template>
   <xsl:template match="xsd:enumeration" mode="render-type"/>
   <xsl:template match="xsd:enumeration[not(preceding-sibling::xsd:enumeration)]"
                 mode="render-type">
	     <xsl:text> - enum { </xsl:text>
	     <xsl:apply-templates select="self::* | following-sibling::xsd:enumeration" mode="render-type.enum"/>
	     <xsl:text> }</xsl:text>
   </xsl:template>
   <xsl:template match="xsd:enumeration" mode="render-type.enum">
	     <xsl:if test="preceding-sibling::xsd:enumeration">
		       <xsl:text>, </xsl:text>
	     </xsl:if>
	     <xsl:text disable-output-escaping="yes">'</xsl:text>
	     <xsl:value-of select="@value"/>
	     <xsl:text disable-output-escaping="yes">'</xsl:text>
   </xsl:template>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-xsd-tree.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->



<!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Begin of included transformation: wsdl-viewer-src.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->
<xsl:template match="@*" mode="src.import">
	     <xsl:param name="src.import.stack"/>
	     <xsl:variable name="recursion.label" select="concat('[', string(.), ']')"/>
	     <xsl:variable name="recursion.check" select="concat($src.import.stack, $recursion.label)"/>

	     <xsl:choose>
		       <xsl:when test="contains($src.import.stack, $recursion.label)">
			         <h2 style="red">
               <xsl:value-of select="concat('Cyclic include / import: ', $recursion.check)"/>
            </h2>
		       </xsl:when>
		       <xsl:otherwise>
			         <h2>
               <a name="{concat($SRC-FILE-PREFIX, generate-id(..))}">
			               <xsl:choose>
				                 <xsl:when test="parent::xsd:include">Included </xsl:when>
				                 <xsl:otherwise>Imported </xsl:otherwise>
			               </xsl:choose>

			               <xsl:choose>
				                 <xsl:when test="name() = 'location'">WSDL </xsl:when>
				                 <xsl:otherwise>Schema </xsl:otherwise>
			               </xsl:choose>
			               <i>
                     <xsl:value-of select="."/>
                  </i>
               </a>
            </h2>

			         <div class="box">
				           <xsl:apply-templates select="document(string(.))" mode="src"/>
			         </div>

			         <xsl:apply-templates select="document(string(.))/*/*[local-name() = 'import'][@location]/@location"
                                 mode="src.import">
				           <xsl:with-param name="src.import.stack" select="$recursion.check"/>
			         </xsl:apply-templates>
			         <xsl:apply-templates select="document(string(.))//xsd:import[@schemaLocation]/@schemaLocation"
                                 mode="src.import">
				           <xsl:with-param name="src.import.stack" select="$recursion.check"/>
			         </xsl:apply-templates>
		       </xsl:otherwise>
	     </xsl:choose>
   </xsl:template>
   <xsl:template match="*" mode="src">
	     <div class="xml-element">
		       <a name="{concat($SRC-PREFIX, generate-id(.))}">
			         <xsl:apply-templates select="." mode="src.link"/>
			         <xsl:apply-templates select="." mode="src.start-tag"/>
		       </a>
		       <xsl:apply-templates select="*|comment()|processing-instruction()|text()[string-length(normalize-space(.)) &gt; 0]"
                              mode="src"/>
		       <xsl:apply-templates select="." mode="src.end-tag"/>
	     </div>
   </xsl:template>
   <xsl:template match="*" mode="src.start-tag">
	     <xsl:call-template name="src.elem">
		       <xsl:with-param name="src.elem.end-slash"> /</xsl:with-param>
	     </xsl:call-template>
   </xsl:template>
   <xsl:template match="*[*|comment()|processing-instruction()|text()[string-length(normalize-space(.)) &gt; 0]]"
                 mode="src.start-tag">
	     <xsl:call-template name="src.elem"/>
   </xsl:template>
   <xsl:template match="*" mode="src.end-tag"/>
   <xsl:template match="*[*|comment()|processing-instruction()|text()[string-length(normalize-space(.)) &gt; 0]]"
                 mode="src.end-tag">
	     <xsl:call-template name="src.elem">
		       <xsl:with-param name="src.elem.start-slash">/</xsl:with-param>
	     </xsl:call-template>
   </xsl:template>
   <xsl:template match="*" mode="src.link-attribute">
      <xsl:if test="$ENABLE-LINK">
	        <xsl:attribute name="href">
            <xsl:value-of select="concat('#', $SRC-PREFIX, generate-id(.))"/>
         </xsl:attribute>
      </xsl:if>
   </xsl:template>
   <xsl:template match="*[local-name() = 'import' or local-name() = 'include'][@location or @schemaLocation]"
                 mode="src.link">
      <xsl:if test="$ENABLE-LINK">
	        <xsl:attribute name="href">
            <xsl:value-of select="concat('#', $SRC-FILE-PREFIX, generate-id(.))"/>
         </xsl:attribute>
      </xsl:if>
   </xsl:template>
   <xsl:template match="*" mode="src.link"/>
   <xsl:template match="ws2:service|ws2:binding" mode="src.link">
	     <xsl:variable name="iface-name">
		       <xsl:apply-templates select="@interface" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws2:interface[@name = $iface-name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws2:endpoint" mode="src.link">
	     <xsl:variable name="binding-name">
		       <xsl:apply-templates select="@binding" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws2:binding[@name = $binding-name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws2:binding/ws2:operation" mode="src.link">
	     <xsl:variable name="operation-name">
		       <xsl:apply-templates select="@ref" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws2:interface/ws2:operation[@name = $operation-name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws2:binding/ws2:fault|ws2:interface/ws2:operation/ws2:infault|ws2:interface/ws2:operation/ws2:outfault"
                 mode="src.link">
	     <xsl:variable name="operation-name">
		       <xsl:apply-templates select="@ref" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws2:interface/ws2:fault[@name = $operation-name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws2:interface/ws2:operation/ws2:input|ws2:interface/ws2:operation/ws2:output|ws2:interface/ws2:fault"
                 mode="src.link">
	     <xsl:variable name="elem-name">
		       <xsl:apply-templates select="@element" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-xsd[@name = $elem-name]" mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws:operation/ws:input[@message] | ws:operation/ws:output[@message] | ws:operation/ws:fault[@message] | soap:header[ancestor::ws:operation and @message]"
                 mode="src.link">
	     <xsl:apply-templates select="$consolidated-wsdl/ws:message[@name = substring-after( current()/@message, ':' )]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws:operation/ws:input[@message] | ws:operation/ws:output[@message] | ws:operation/ws:fault[@message] | soap:header[ancestor::ws:operation and @message]"
                 mode="src.link">
	     <xsl:apply-templates select="$consolidated-wsdl/ws:message[@name = substring-after( current()/@message, ':' )]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws:message/ws:part[@element or @type]" mode="src.link">
	     <xsl:variable name="elem-local-name" select="substring-after(@element, ':')"/>
	     <xsl:variable name="type-local-name" select="substring-after(@type, ':')"/>
	     <xsl:variable name="elem-name">
		       <xsl:choose>
			         <xsl:when test="$elem-local-name">
               <xsl:value-of select="$elem-local-name"/>
            </xsl:when>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="@element">
               <xsl:value-of select="@element"/>
            </xsl:when>
			         <xsl:when test="@type">
               <xsl:value-of select="@type"/>
            </xsl:when>
			         <xsl:otherwise>
               <xsl:call-template name="src.syntax-error"/>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:apply-templates select="$consolidated-xsd[@name = $elem-name]" mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws:service/ws:port[@binding]" mode="src.link">
	     <xsl:variable name="binding-name">
		       <xsl:apply-templates select="@binding" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws:binding[@name = $binding-name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="ws:operation[@name and parent::ws:binding/@type]" mode="src.link">
	     <xsl:variable name="type-name">
		       <xsl:apply-templates select="../@type" mode="qname.normalized"/>
	     </xsl:variable>
	     <xsl:apply-templates select="$consolidated-wsdl/ws:portType[@name = $type-name]/ws:operation[@name = current()/@name]"
                           mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template match="xsd:element[@ref or @type]" mode="src.link">
	     <xsl:variable name="ref-or-type">
		       <xsl:choose>
			         <xsl:when test="@type">
               <xsl:value-of select="@type"/>
            </xsl:when>
			         <xsl:otherwise>
               <xsl:value-of select="@ref"/>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:variable name="type-local-name" select="substring-after($ref-or-type, ':')"/>
	     <xsl:variable name="xsd-name">
		       <xsl:choose>
			         <xsl:when test="$type-local-name">
               <xsl:value-of select="$type-local-name"/>
            </xsl:when>
			         <xsl:when test="$ref-or-type">
               <xsl:value-of select="$ref-or-type"/>
            </xsl:when>
			         <xsl:otherwise/>
		       </xsl:choose>
	     </xsl:variable>

	     <xsl:if test="$xsd-name">
		       <xsl:variable name="msg"
                       select="$consolidated-xsd[@name = $xsd-name and contains(local-name(), 'Type')][1]"/>
		       <xsl:apply-templates select="$msg" mode="src.link-attribute"/>
	     </xsl:if>
   </xsl:template>
   <xsl:template match="xsd:attribute[contains(@ref, 'arrayType')]" mode="src.link">
	     <xsl:variable name="att-array-type"
                    select="substring-before(@*[local-name() = 'arrayType'], '[]')"/>
	     <xsl:variable name="xsd-local-name" select="substring-after($att-array-type, ':')"/>
	     <xsl:variable name="xsd-name">
		       <xsl:choose>
			         <xsl:when test="$xsd-local-name">
               <xsl:value-of select="$xsd-local-name"/>
            </xsl:when>
			         <xsl:otherwise>
               <xsl:value-of select="$att-array-type"/>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>
	     <xsl:if test="$xsd-name">
		       <xsl:variable name="msg" select="$consolidated-xsd[@name = $xsd-name][1]"/>
		       <xsl:apply-templates select="$msg" mode="src.link-attribute"/>
	     </xsl:if>
   </xsl:template>
   <xsl:template match="xsd:extension | xsd:restriction" mode="src.link">
	     <xsl:variable name="xsd-local-name" select="substring-after(@base, ':')"/>
	     <xsl:variable name="xsd-name">
		       <xsl:choose>
			         <xsl:when test="$xsd-local-name">
               <xsl:value-of select="$xsd-local-name"/>
            </xsl:when>
			         <xsl:otherwise>
               <xsl:value-of select="@type"/>
            </xsl:otherwise>
		       </xsl:choose>
	     </xsl:variable>
	     <xsl:variable name="msg" select="$consolidated-xsd[@name = $xsd-name][1]"/>
	     <xsl:apply-templates select="$msg" mode="src.link-attribute"/>
   </xsl:template>
   <xsl:template name="src.elem">
	     <xsl:param name="src.elem.start-slash"/>
	     <xsl:param name="src.elem.end-slash"/>

	     <xsl:value-of select="concat('&lt;', $src.elem.start-slash, name(.))"
                    disable-output-escaping="no"/>
	     <xsl:if test="not($src.elem.start-slash)">
         <xsl:apply-templates select="@*" mode="src"/>
         <xsl:apply-templates select="." mode="src.namespace"/>
      </xsl:if>
	     <xsl:value-of select="concat($src.elem.end-slash, '&gt;')" disable-output-escaping="no"/>
   </xsl:template>
   <xsl:template match="@*" mode="src">
	     <xsl:text> </xsl:text>
	     <span class="xml-att">
		       <xsl:value-of select="concat(name(), '=')"/>
		       <span class="xml-att-val">
			         <xsl:value-of select="concat('&#34;', ., '&#34;')" disable-output-escaping="yes"/>
		       </span>
	     </span>
   </xsl:template>
   <xsl:template match="*" mode="src.namespace">
	     <xsl:variable name="supports-namespace-axis" select="count(/*/namespace::*) &gt; 0"/>
	     <xsl:variable name="current" select="current()"/>

	     <xsl:choose>
		       <xsl:when test="count(/*/namespace::*) &gt; 0">
				<!--
					When the namespace axis is present (e.g. Internet Explorer), we can simulate
					the namespace declarations by comparing the namespaces in scope on this element
					with those in scope on the parent element.  Any difference must have been the
					result of a namespace declaration.  Note that this doesn't reflect the actual
					source - it will strip out redundant namespace declarations.
				-->
			<xsl:for-each select="namespace::*[. != 'http://www.w3.org/XML/1998/namespace']"> 
				           <xsl:if test="not($current/parent::*[namespace::*[. = current()]])">
					             <div class="xml-att">
						               <xsl:text> xmlns</xsl:text>
						               <xsl:if test="string-length(name())">:</xsl:if>
						               <xsl:value-of select="concat(name(), '=')"/>
						               <span class="xml-att-val">
							                 <xsl:value-of select="concat('&#34;', ., '&#34;')" disable-output-escaping="yes"/>
						               </span>
					             </div>
				           </xsl:if>
			         </xsl:for-each>
		       </xsl:when>
		       <xsl:otherwise>
			<!-- 
				When the namespace axis isn't supported (e.g. Mozilla), we can simulate
				appropriate declarations from namespace elements.
				This currently doesn't check for namespaces on attributes.
				In the general case we can't reliably detect the use of QNames in content, but
				in the case of schema, we know which content could contain a QName and look
				there too.  This mechanism is rather unpleasant though, since it records
				namespaces where they are used rather than showing where they are declared 
				(on some parent element) in the source.  Yukk!
			-->
			<xsl:if test="namespace-uri(.) != namespace-uri(parent::*) or not(parent::*)">
				           <span class="xml-att">
					             <xsl:text> xmlns</xsl:text>
					             <xsl:if test="substring-before(name(),':') != ''">:</xsl:if>
					             <xsl:value-of select="substring-before(name(),':')"/>
					             <xsl:text>=</xsl:text>
					             <span class="xml-att-val">
						               <xsl:value-of select="concat('&#34;', namespace-uri(.), '&#34;')" disable-output-escaping="yes"/>
					             </span>
				           </span>
			         </xsl:if>
		       </xsl:otherwise>
	     </xsl:choose>

   </xsl:template>
   <xsl:template match="text()" mode="src">
	     <span class="xml-text">
         <xsl:value-of select="." disable-output-escaping="no"/>
      </span>
   </xsl:template>
   <xsl:template match="comment()" mode="src">
      <div class="xml-comment">
	        <xsl:text disable-output-escaping="no">&lt;!-- </xsl:text>
	        <xsl:value-of select="." disable-output-escaping="no"/>
	        <xsl:text disable-output-escaping="no"> --&gt;
</xsl:text>
      </div>
   </xsl:template>
   <xsl:template match="processing-instruction()" mode="src">
      <div class="xml-proc">
	        <xsl:text disable-output-escaping="no">&lt;?</xsl:text>
	        <xsl:copy-of select="name(.)"/>
	        <xsl:value-of select="concat(' ', .)" disable-output-escaping="yes"/>
	        <xsl:text disable-output-escaping="no"> ?&gt;
</xsl:text>
      </div>
   </xsl:template>

   <!--
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    End of included transformation: wsdl-viewer-src.xsl
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
-->





<!--
==================================================================
	Starting point
==================================================================
-->

<xsl:template match="/">
	     <html>
		       <xsl:call-template name="head.render"/>
		       <xsl:call-template name="body.render"/>
	     </html>
   </xsl:template>



   <!--
==================================================================
	Rendering: HTML head
==================================================================
-->

<xsl:template name="head.render">
      <head>
	        <title>
            <xsl:value-of select="concat($html-title, ' - ', 'Generated by wsdl-viewer.xsl')"/>
         </title>
	        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	        <meta http-equiv="content-script-type" content="text/javascript"/>
	        <meta http-equiv="content-style-type" content="text/css"/>
	        <meta name="Generator" content="http://tomi.vanek.sk/xml/wsdl-viewer.xsl"/>

	        <meta http-equiv="imagetoolbar" content="false"/>
	        <meta name="MSSmartTagsPreventParsing" content="true"/>

	        <style type="text/css">
            <xsl:value-of select="$css" disable-output-escaping="yes"/>
         </style>
      </head>
   </xsl:template>



   <!--
==================================================================
	Rendering: HTML body
==================================================================
-->

<xsl:template name="body.render">
      <body id="operations">
         <div id="outer_box">
            <div id="inner_box" onload="pagingInit()">
	              <xsl:call-template name="title.render"/>


               <!-- TODO: pages with tabs for selecting some aspect of the WSDL
	<xsl:call-template name="navig.render"/>
-->

	<xsl:call-template name="content.render"/>
	              <xsl:call-template name="footer.render"/>
            </div>
         </div>
      </body>
   </xsl:template>



   <!--
==================================================================
	Rendering: heading
==================================================================
-->

<xsl:template name="title.render">
	     <div id="header">
		       <h1>
            <xsl:value-of select="$html-title"/>
         </h1>
	     </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: navigation
==================================================================
-->

<xsl:template name="navig.render">
      <div id="outer_nav">
	        <div id="nav" class="floatcontainer">
		          <ul>
			            <li id="nav-service">
                  <a href="#page.service">Service</a>
               </li>
			            <li id="nav-operations">
                  <a href="#page.operations">Operations</a>
               </li>
			            <li id="nav-wsdl">
                  <a href="#page.src">Source Code</a>
               </li>


               <!--			<li id="nav-client"><a href="#TODO-1">Client</a></li>
-->

			<li id="nav-about">
                  <a href="#page.about" class="current">About</a>
               </li>
		          </ul>
	        </div>
      </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: content
==================================================================
-->

<xsl:template name="content.render">
      <div id="content">
	        <xsl:if test="$ENABLE-SERVICE-PARAGRAPH">
		          <xsl:call-template name="service.render"/>
	        </xsl:if>
	        <xsl:if test="$ENABLE-OPERATIONS-PARAGRAPH">
		          <xsl:call-template name="operations.render"/>
	        </xsl:if>
	        <xsl:if test="$ENABLE-SRC-CODE-PARAGRAPH">
		          <xsl:call-template name="src.render"/>
	        </xsl:if>
	        <xsl:if test="$ENABLE-ABOUT-PARAGRAPH">
		          <xsl:call-template name="about.render">
			            <xsl:with-param name="version" select="$wsdl-viewer.version"/>
		          </xsl:call-template>
	        </xsl:if>
      </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: footer
==================================================================
-->

<xsl:template name="footer.render">
      <div id="footer">
	This page was generated by wsdl-viewer.xsl (<a href="http://tomi.vanek.sk">http://tomi.vanek.sk</a>)
</div>
   </xsl:template>



   <!--
==================================================================
	Rendering: WSDL service information
==================================================================
-->

<xsl:template name="service.render">
      <div class="page">
	        <a class="target" name="page.service">
		          <h2>
               <xsl:value-of select="$html-title"/>
            </h2>
	        </a>
	        <xsl:apply-templates select="$consolidated-wsdl/*[local-name(.) = 'documentation']"
                              mode="documentation.render"/>
	        <xsl:apply-templates select="$consolidated-wsdl/ws:service|$consolidated-wsdl/ws2:service"
                              mode="service-start"/>
	        <xsl:if test="not($consolidated-wsdl/*[local-name() = 'service']/@name)">
		

<!-- If the WS is without implementation, just with binding points = WS interface -->

		<xsl:apply-templates select="$consolidated-wsdl/ws:binding" mode="service-start"/>
		          <xsl:apply-templates select="$consolidated-wsdl/ws2:interface" mode="service"/>
	        </xsl:if>
      </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: WSDL operations - detail
==================================================================
-->

<xsl:template name="operations.render">
      <div class="page">
	        <a class="target" name="page.operations">
		          <h2>Operations</h2>
	        </a>
	        <ul>
		          <xsl:apply-templates select="$consolidated-wsdl/ws:portType" mode="operations">
			            <xsl:sort select="@name"/>
		          </xsl:apply-templates>

		          <xsl:choose>
			            <xsl:when test="$consolidated-wsdl/*[local-name() = 'service']/@name">
				              <xsl:variable name="iface-name">
					                <xsl:apply-templates select="$consolidated-wsdl/*[local-name() = 'service']/@interface"
                                          mode="qname.normalized"/>
				              </xsl:variable>
				              <xsl:apply-templates select="$consolidated-wsdl/ws2:interface[@name = $iface-name]"
                                       mode="operations">
					                <xsl:sort select="@name"/>
				              </xsl:apply-templates>
			            </xsl:when>
			            <xsl:when test="$consolidated-wsdl/ws2:interface/@name">
				

<!-- TODO: What to do if there are more interfaces? -->

				<xsl:apply-templates select="$consolidated-wsdl/ws2:interface[1]" mode="operations"/>
			            </xsl:when>
			            <xsl:otherwise>
				

<!-- TODO: Error message or handling somehow this unexpected situation -->

			</xsl:otherwise>
		          </xsl:choose>
	        </ul>
      </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: WSDL and XSD source code files
==================================================================
-->

<xsl:template name="src.render">
      <div class="page">
	        <a class="target" name="page.src">
		          <h2>WSDL source code</h2>
	        </a>
	        <div class="box">
		          <div class="xml-proc">
			            <xsl:text>&lt;?xml version="1.0"?&gt;</xsl:text>
		          </div>
		          <xsl:apply-templates select="/" mode="src"/>
	        </div>

	        <xsl:apply-templates select="/*/*[local-name() = 'import'][@location]/@location" mode="src.import"/>
	        <xsl:apply-templates select="$consolidated-wsdl/*[local-name() = 'types']//xsd:import[@schemaLocation]/@schemaLocation | $consolidated-wsdl/*[local-name() = 'types']//xsd:include[@schemaLocation]/@schemaLocation"
                              mode="src.import"/>
      </div>
   </xsl:template>



   <!--
==================================================================
	Rendering: About
==================================================================
-->

<xsl:template name="about.render">
      <xsl:param name="version"/>
      <div class="page">
	        <a class="target" name="page.about">
		          <h2>About <em>wsdl-viewer.xsl</em>
            </h2>
	        </a>
	        <div class="floatcontainer">
		          <div id="fix_column">
		             <div class="shadow">
                  <div class="box">
			                  <xsl:call-template name="processor-info.render"/>
		                </div>
               </div>
		          </div>
	
		          <div id="flexi_column">
			            <xsl:call-template name="about.detail">
				              <xsl:with-param name="version" select="$wsdl-viewer.version"/>
			            </xsl:call-template>
		          </div>
	        </div>
      </div>
   </xsl:template>


</xsl:stylesheet>