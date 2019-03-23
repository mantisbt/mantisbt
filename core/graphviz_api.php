<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * GraphViz API
 *
 * Wrapper classes around GraphViz utilities (dot and neato) for
 * directed and undirected graph generation. These wrappers are enhanced
 * enough just to support relationship_graph_api.php. They don't
 * support subgraphs yet.
 *
 * The original Graphviz package is available at:
 * 	- http://www.graphviz.org/
 * Additional documentation can be found at:
 * 	- http://www.graphviz.org/Documentation.html
 *
 * @package CoreAPI
 * @subpackage GraphVizAPI
 * @author Juliano Ravasi Ferraz <jferraz at users sourceforge net>
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses utility_api.php
 */

require_api( 'constant_inc.php' );
require_api( 'utility_api.php' );

# constant(s) defining the output formats supported by dot and neato.
define( 'GRAPHVIZ_ATTRIBUTED_DOT', 0 );
define( 'GRAPHVIZ_PS', 1 );
define( 'GRAPHVIZ_HPGL', 2 );
define( 'GRAPHVIZ_PCL', 3 );
define( 'GRAPHVIZ_MIF', 4 );
define( 'GRAPHVIZ_PLAIN', 6 );
define( 'GRAPHVIZ_PLAIN_EXT', 7 );
define( 'GRAPHVIZ_GIF', 11 );
define( 'GRAPHVIZ_JPEG', 12 );
define( 'GRAPHVIZ_PNG', 13 );
define( 'GRAPHVIZ_WBMP', 14 );
define( 'GRAPHVIZ_XBM', 15 );
define( 'GRAPHVIZ_ISMAP', 16 );
define( 'GRAPHVIZ_IMAP', 17 );
define( 'GRAPHVIZ_CMAP', 18 );
define( 'GRAPHVIZ_CMAPX', 19 );
define( 'GRAPHVIZ_VRML', 20 );
define( 'GRAPHVIZ_SVG', 25 );
define( 'GRAPHVIZ_SVGZ', 26 );
define( 'GRAPHVIZ_CANONICAL_DOT', 27 );
define( 'GRAPHVIZ_PDF', 28 );

/**
 * Base class for graph creation and manipulation. By default,
 * undirected graphs are generated. For directed graphs, use Digraph
 * class.
 */
class Graph {
	/**
	 * Name
	 */
	public $name = 'G';

	/**
	 * Attributes
	 */
	public $attributes = array();

	/**
	 * Default node
	 */
	public $default_node = null;

	/**
	 * Default edge
	 */
	public $default_edge = null;

	/**
	 * Nodes
	 */
	public $nodes = array();

	/**
	 * Edges
	 */
	public $edges = array();

	/**
	 * Graphviz tool
	 */
	public $graphviz_tool;

	/**
	 * Formats
	 */
	public $formats = array(
		'dot' => array(
			'binary' => false,
			'type' => GRAPHVIZ_ATTRIBUTED_DOT,
			'mime' => 'text/x-graphviz',
		),
		'ps' => array(
			'binary' => false,
			'type' => GRAPHVIZ_PS,
			'mime' => 'application/postscript',
		),
		'hpgl' => array(
			'binary' => true,
			'type' => GRAPHVIZ_HPGL,
			'mime' => 'application/vnd.hp-HPGL',
		),
		'pcl' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PCL,
			'mime' => 'application/vnd.hp-PCL',
		),
		'mif' => array(
			'binary' => true,
			'type' => GRAPHVIZ_MIF,
			'mime' => 'application/vnd.mif',
		),
		'gif' => array(
			'binary' => true,
			'type' => GRAPHVIZ_GIF,
			'mime' => 'image/gif',
		),
		'jpg' => array(
			'binary' => false,
			'type' => GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'jpeg' => array(
			'binary' => true,
			'type' => GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'png' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PNG,
			'mime' => 'image/png',
		),
		'wbmp' => array(
			'binary' => true,
			'type' => GRAPHVIZ_WBMP,
			'mime' => 'image/vnd.wap.wbmp',
		),
		'xbm' => array(
			'binary' => false,
			'type' => GRAPHVIZ_XBM,
			'mime' => 'image/x-xbitmap',
		),
		'ismap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_ISMAP,
			'mime' => 'text/plain',
		),
		'imap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_IMAP,
			'mime' => 'application/x-httpd-imap',
		),
		'cmap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_CMAP,
			'mime' => 'text/html',
		),
		'cmapx' => array(
			'binary' => false,
			'type' => GRAPHVIZ_CMAPX,
			'mime' => 'application/xhtml+xml',
		),
		'vrml' => array(
			'binary' => true,
			'type' => GRAPHVIZ_VRML,
			'mime' => 'x-world/x-vrml',
		),
		'svg' => array(
			'binary' => false,
			'type' => GRAPHVIZ_SVG,
			'mime' => 'image/svg+xml',
		),
		'svgz' => array(
			'binary' => true,
			'type' => GRAPHVIZ_SVGZ,
			'mime' => 'image/svg+xml',
		),
		'pdf' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PDF,
			'mime' => 'application/pdf',
		),
	);

	/**
	 * Constructor for Graph objects.
	 * @param string $p_name       Graph name.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graph generation tool.
	 */
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = 'neato' ) {
		if( is_string( $p_name ) ) {
			$this->name = $p_name;
		}

		$this->set_attributes( $p_attributes );

		$this->graphviz_tool = $p_tool;
	}

	/**
	 * Sets graph attributes.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	function set_attributes( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->attributes = $p_attributes;
		}
	}

	/**
	 * Sets default attributes for all nodes of the graph.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	function set_default_node_attr( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->default_node = $p_attributes;
		}
	}

	/**
	 * Sets default attributes for all edges of the graph.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	 function set_default_edge_attr( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->default_edge = $p_attributes;
		}
	}

	/**
	 * Adds a node to the graph.
	 * @param string $p_name       Node name.
	 * @param array  $p_attributes Attributes.
	 * @return void
	 */
	 function add_node( $p_name, array $p_attributes = array() ) {
		if( is_array( $p_attributes ) ) {
			$this->nodes[$p_name] = $p_attributes;
		}
	}

	/**
	 * Adds an edge to the graph.
	 * @param string $p_src        Source.
	 * @param string $p_dst        Destination.
	 * @param array  $p_attributes Attributes.
	 * @return void
	 */
	 function add_edge( $p_src, $p_dst, array $p_attributes = array() ) {
		if( is_array( $p_attributes ) ) {
			$this->edges[] = array(
				'src' => $p_src,
				'dst' => $p_dst,
				'attributes' => $p_attributes,
			);
		}
	}

	/**
	 * Check if an edge is already present.
	 * @param string $p_src Source.
	 * @param string $p_dst Destination.
	 * @return boolean
	 */
	function is_edge_present( $p_src, $p_dst ) {
		foreach( $this->edges as $t_edge ) {
			if( $t_edge['src'] == $p_src && $t_edge['dst'] == $p_dst ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generates an undirected graph representation (suitable for neato).
	 * @return void
	 */
	function generate() {
		echo 'graph ' . $this->name . ' {' . "\n";

		$this->_print_graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_src . ' -- ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		echo "};\n";
	}

	/**
	 * Outputs a graph image or map in the specified format.
	 * @param string  $p_format  Graphviz output format.
	 * @param boolean $p_headers Whether to sent http headers.
	 * @return void
	 */
	function output( $p_format = 'dot', $p_headers = false ) {
		# Check if it is a recognized format.
		if( !isset( $this->formats[$p_format] ) ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		$t_binary = $this->formats[$p_format]['binary'];
		$t_type = $this->formats[$p_format]['type'];
		$t_mime = $this->formats[$p_format]['mime'];

		# Send Content-Type header, if requested.
		if( $p_headers ) {
			header( 'Content-Type: ' . $t_mime );
		}
		# Retrieve the source dot document into a buffer
		ob_start();
		$this->generate();
		$t_dot_source = ob_get_contents();
		ob_end_clean();

		# Start dot process

		$t_command = $this->graphviz_tool . ' -T' . $p_format;
		$t_descriptors = array(
			0 => array( 'pipe', 'r', ),
			1 => array( 'pipe', 'w', ),
			2 => array( 'file', 'php://stderr', 'w', ),
			);

		$t_pipes = array();
		$t_process = proc_open( $t_command, $t_descriptors, $t_pipes );

		if( is_resource( $t_process ) ) {
			# Filter generated output through dot
			fwrite( $t_pipes[0], $t_dot_source );
			fclose( $t_pipes[0] );

			if( $p_headers ) {
				# Headers were requested, use another output buffer to
				# retrieve the size for Content-Length.
				ob_start();
				while( !feof( $t_pipes[1] ) ) {
					echo fgets( $t_pipes[1], 1024 );
				}
				header( 'Content-Length: ' . ob_get_length() );
				ob_end_flush();
			} else {
				# No need for headers, send output directly.
				while( !feof( $t_pipes[1] ) ) {
					print( fgets( $t_pipes[1], 1024 ) );
				}
			}

			fclose( $t_pipes[1] );
			proc_close( $t_process );
		}
	}

	/**
	 * PROTECTED function to build a node or edge attribute list.
	 * @param array $p_attributes Attributes.
	 * @return string
	 */
	function _build_attribute_list( array $p_attributes ) {
		if( empty( $p_attributes ) ) {
			return '';
		}

		$t_result = array();

		foreach( $p_attributes as $t_name => $t_value ) {
			if( !preg_match( '/[a-zA-Z]+/', $t_name ) ) {
				continue;
			}

			if( is_string( $t_value ) ) {
				$t_value = '"' . addcslashes( $t_value, "\0..\37\"\\" ) . '"';
			} else if( is_integer( $t_value ) or is_float( $t_value ) ) {
				$t_value = (string)$t_value;
			} else {
				continue;
			}

			$t_result[] = $t_name . '=' . $t_value;
		}

		return '[ ' . join( ', ', $t_result ) . ' ]';
	}

	/**
	 * PROTECTED function to print graph attributes and defaults.
	 * @return void
	 */
	function _print_graph_defaults() {
		foreach( $this->attributes as $t_name => $t_value ) {
			if( !preg_match( '/[a-zA-Z]+/', $t_name ) ) {
				continue;
			}

			if( is_string( $t_value ) ) {
				$t_value = '"' . addcslashes( $t_value, "\0..\37\"\\" ) . '"';
			} else if( is_integer( $t_value ) or is_float( $t_value ) ) {
				$t_value = (string)$t_value;
			} else {
				continue;
			}

			echo "\t" . $t_name . '=' . $t_value . ";\n";
		}

		if( null !== $this->default_node ) {
			$t_attr = $this->_build_attribute_list( $this->default_node );
			echo "\t" . 'node ' . $t_attr . ";\n";
		}

		if( null !== $this->default_edge ) {
			$t_attr = $this->_build_attribute_list( $this->default_edge );
			echo "\t" . 'edge ' . $t_attr . ";\n";
		}
	}
}

/**
 * Directed graph creation and manipulation.
 */
class Digraph extends Graph {
	/**
	 * Constructor for Digraph objects.
	 * @param string $p_name       Name of the graph.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graphviz tool.
	 */
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = 'dot' ) {
		parent::__construct( $p_name, $p_attributes, $p_tool );
	}

	/**
	 * Generates a directed graph representation (suitable for dot).
	 * @return void
	 */
	function generate() {
		echo 'digraph ' . $this->name . ' {' . "\n";

		$this->_print_graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_src . ' -> ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		echo "};\n";
	}
}
