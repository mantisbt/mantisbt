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
 * The original Graphviz package including documentation is available at:
 * 	- https://www.graphviz.org/
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

/**
 * Base class for graph creation and manipulation.
 *
 * Generates undirected graphs are generated.
 * For directed graphs, use {@see Digraph} class.
 */
class Graph {

	/**
	 * Constants defining the Graphviz tools' names.
	 *
	 * These are the names of the executables in the directory defined by
	 * {@see $g_graphviz_path}.
	 * On Windows, '.exe' extension will be appended.
	 */
	const TOOL_DOT = 'dot';
	const TOOL_NEATO = 'neato';
	const TOOL_CIRCO = 'circo';

	/**
	 * Constant(s) defining the output formats supported by dot and neato.
	 */
	const GRAPHVIZ_ATTRIBUTED_DOT = 0;
	const GRAPHVIZ_PS = 1;
	const GRAPHVIZ_HPGL = 2;
	const GRAPHVIZ_PCL = 3;
	const GRAPHVIZ_MIF = 4;
	const GRAPHVIZ_PLAIN = 6;
	const GRAPHVIZ_PLAIN_EXT = 7;
	const GRAPHVIZ_GIF = 11;
	const GRAPHVIZ_JPEG = 12;
	const GRAPHVIZ_PNG = 13;
	const GRAPHVIZ_WBMP = 14;
	const GRAPHVIZ_XBM = 15;
	const GRAPHVIZ_ISMAP = 16;
	const GRAPHVIZ_IMAP = 17;
	const GRAPHVIZ_CMAP = 18;
	const GRAPHVIZ_CMAPX = 19;
	const GRAPHVIZ_VRML = 20;
	const GRAPHVIZ_SVG = 25;
	const GRAPHVIZ_SVGZ = 26;
	const GRAPHVIZ_CANONICAL_DOT = 27;
	const GRAPHVIZ_PDF = 28;

	/**
	 * @var string Name
	 */
	protected $name = 'G';

	/**
	 * @var array Attributes
	 */
	protected $attributes = array();

	/**
	 * @var array Default node attributes
	 */
	protected $default_node = null;

	/**
	 * @var array Default edge attributes
	 */
	protected $default_edge = null;

	/**
	 * @var array Nodes
	 */
	protected $nodes = array();

	/**
	 * @var array Edges
	 */
	protected $edges = array();

	/**
	 * @var string Graphviz tool
	 */
	protected $graphviz_tool;

	/**
	 * Graphviz output formats
	 * @see https://graphviz.org/docs/outputs/
	 */
	protected $formats = array(
		'dot' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_ATTRIBUTED_DOT,
			'mime' => 'text/x-graphviz',
		),
		'ps' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_PS,
			'mime' => 'application/postscript',
		),
		'hpgl' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_HPGL,
			'mime' => 'application/vnd.hp-HPGL',
		),
		'pcl' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_PCL,
			'mime' => 'application/vnd.hp-PCL',
		),
		'mif' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_MIF,
			'mime' => 'application/vnd.mif',
		),
		'gif' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_GIF,
			'mime' => 'image/gif',
		),
		'jpg' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'jpeg' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'png' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_PNG,
			'mime' => 'image/png',
		),
		'wbmp' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_WBMP,
			'mime' => 'image/vnd.wap.wbmp',
		),
		'xbm' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_XBM,
			'mime' => 'image/x-xbitmap',
		),
		'ismap' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_ISMAP,
			'mime' => 'text/plain',
		),
		'imap' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_IMAP,
			'mime' => 'application/x-httpd-imap',
		),
		'cmap' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_CMAP,
			'mime' => 'text/html',
		),
		'cmapx' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_CMAPX,
			'mime' => 'application/xhtml+xml',
		),
		'vrml' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_VRML,
			'mime' => 'x-world/x-vrml',
		),
		'svg' => array(
			'binary' => false,
			'type' => self::GRAPHVIZ_SVG,
			'mime' => 'image/svg+xml',
		),
		'svgz' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_SVGZ,
			'mime' => 'image/svg+xml',
		),
		'pdf' => array(
			'binary' => true,
			'type' => self::GRAPHVIZ_PDF,
			'mime' => 'application/pdf',
		),
	);

	/**
	 * Constructor for Graph objects.
	 *
	 * @param string $p_name       Graph name
	 * @param array  $p_attributes Attributes
	 * @param string $p_tool       Graph generation tool (one of the TOOL_* constants)
	 */
	public function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = Graph::TOOL_NEATO ) {
		if( is_string( $p_name ) ) {
			$this->name = $p_name;
		}

		$this->set_attributes( $p_attributes );

		$this->graphviz_tool = $p_tool;

		if( is_windows_server() ) {
			$this->graphviz_tool .= '.exe';
		}
	}

	/**
	 * Sets graph attributes.
	 *
	 * @param array $p_attributes Attributes.
	 *
	 * @return void
	 */
	public function set_attributes( array $p_attributes ) {
		$this->attributes = $p_attributes;
	}

	/**
	 * Sets default attributes for all nodes of the graph.
	 *
	 * @param array $p_attributes Attributes.
	 *
	 * @return void
	 */
	public function set_default_node_attr( array $p_attributes ) {
		$this->default_node = $p_attributes;
	}

	/**
	 * Sets default attributes for all edges of the graph.
	 *
	 * @param array $p_attributes Attributes.
	 *
	 * @return void
	 */
	public function set_default_edge_attr( array $p_attributes ) {
		$this->default_edge = $p_attributes;
	}

	/**
	 * Adds a node to the graph.
	 *
	 * @param string $p_name       Node name.
	 * @param array  $p_attributes Attributes.
	 *
	 * @return void
	 */
	public function add_node( $p_name, array $p_attributes = array() ) {
		if( is_array( $p_attributes ) ) {
			$this->nodes[$p_name] = $p_attributes;
		}
	}

	/**
	 * Adds an edge to the graph.
	 *
	 * @param string $p_src        Source.
	 * @param string $p_dst        Destination.
	 * @param array  $p_attributes Attributes.
	 *
	 * @return void
	 */
	public function add_edge( $p_src, $p_dst, array $p_attributes = array() ) {
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
	 *
	 * @param string $p_src Source.
	 * @param string $p_dst Destination.
	 *
	 * @return boolean
	 */
	public function is_edge_present( $p_src, $p_dst ) {
		foreach( $this->edges as $t_edge ) {
			if( $t_edge['src'] == $p_src && $t_edge['dst'] == $p_dst ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generates an undirected graph representation (suitable for neato).
	 *
	 * To test the generated graph, use the
	 * {@see http://magjac.com/graphviz-visual-editor/ Graphviz Visual Editor}
	 *
	 * @return void
	 */
	public function generate() {
		echo 'graph ' . $this->name . ' {' . "\n";

		$this->print_graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->build_attribute_list( $t_attr );
			echo "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->build_attribute_list( $t_attr );
			echo "\t" . $t_src . ' -- ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		echo "}\n";
	}

	/**
	 * Outputs a graph image or map in the specified format.
	 *
	 * @param string  $p_format  Graphviz output format.
	 * @param boolean $p_headers Whether to sent http headers.
	 *
	 * @return void
	 */
	public function output( $p_format = 'dot', $p_headers = false ) {
		# Check if it is a recognized format.
		if( !isset( $this->formats[$p_format] ) ) {
			error_parameters( "Invalid Graph format '$p_format'." );
			trigger_error( ERROR_GENERIC, ERROR );
		}

		# Graphviz tool missing or not executable
		$t_tool_path = $this->tool_path();
		if( !is_executable( $t_tool_path ) ) {
			error_parameters( $t_tool_path );
			trigger_error( ERROR_GRAPH_TOOL_NOT_FOUND, ERROR );
		}

		# Retrieve the source dot document into a buffer
		ob_start();
		$this->generate();
		$t_dot_source = ob_get_clean();

		# Start dot process
		$t_command = escapeshellarg( $t_tool_path ) . ' -T' . $p_format;
		$t_stderr = tempnam( sys_get_temp_dir(), 'graphviz' );
		$t_descriptors = array(
			0 => array( 'pipe', 'r', ),
			1 => array( 'pipe', 'w', ),
			# Writing to file instead of pipe to avoid locking issues
			2 => array( 'file', $t_stderr, 'w', ),
		);

		$t_pipes = array();
		$t_process = proc_open( $t_command, $t_descriptors, $t_pipes,
			null, null, [ 'bypass_shell' => true ] );

		if( !is_resource( $t_process ) ) {
			# proc_open failed
			trigger_error( ERROR_GENERIC, ERROR );
		}

		# Check for output in stderr
		# Wait a bit to ensure the file has been written before attempting to read it
		usleep( 5000 );
		$t_error = file_get_contents( $t_stderr );
		unlink( $t_stderr );
		if( $t_error ) {
			error_parameters( $t_error );
			trigger_error( ERROR_GENERIC, ERROR );
		}

		# Filter the generated document through dot
		fwrite( $t_pipes[0], $t_dot_source );
		fclose( $t_pipes[0] );

		if( $p_headers ) {
			header( 'Content-Type: ' . $this->formats[$p_format]['mime'] );

			# Use an output buffer to retrieve the size for Content-Length
			ob_start();
		}

		# Send the output
		while( !feof( $t_pipes[1] ) ) {
			echo fgets( $t_pipes[1], 1024 );
		}

		if( $p_headers ) {
			header( 'Content-Length: ' . ob_get_length() );
			ob_end_flush();
		}

		fclose( $t_pipes[1] );
		proc_close( $t_process );
	}

	/**
	 * Build a node or edge attribute list.
	 *
	 * @param array $p_attributes Attributes.
	 *
	 * @return string
	 */
	protected function build_attribute_list( array $p_attributes ) {
		if( empty( $p_attributes ) ) {
			return '';
		}

		$t_result = array();

		foreach( $p_attributes as $t_name => $t_value ) {
			if( !preg_match( '/[a-zA-Z]+/', $t_name ) ) {
				continue;
			}

			if( is_string( $t_value ) ) {
				if( $t_name == 'label' && $t_value != strip_tags( $t_value ) ) {
					// It's an HTML-like label
					// @see https://graphviz.org/doc/info/shapes.html#html
					$t_value = '<' . $t_value. '>';
				} else {
					$t_value = '"' . addcslashes( $t_value, "\0..\37\"\\" ) . '"';
				}
			} else if( is_integer( $t_value ) or is_float( $t_value ) ) {
				$t_value = (string)$t_value;
			} else {
				continue;
			}

			$t_result[] = $t_name . '=' . $t_value;
		}

		return '[ ' . implode( ', ', $t_result ) . ' ]';
	}

	/**
	 * Print graph attributes and defaults.
	 *
	 * @return void
	 */
	protected function print_graph_defaults() {
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
			$t_attr = $this->build_attribute_list( $this->default_node );
			echo "\t" . 'node ' . $t_attr . ";\n";
		}

		if( null !== $this->default_edge ) {
			$t_attr = $this->build_attribute_list( $this->default_edge );
			echo "\t" . 'edge ' . $t_attr . ";\n";
		}
	}

	/**
	 * Gets the path to the Graphviz tools directory.
	 *
	 * @return string
	 */
	public static function graphviz_path() {
		return realpath( config_get_global( 'graphviz_path' ) ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Gets the path to the Graphviz tool.
	 *
	 * @return string
	 */
	protected function tool_path() {
		return self::graphviz_path() . $this->graphviz_tool;
	}
}


/**
 * Directed graph creation and manipulation.
 */
class Digraph extends Graph {

	/**
	 * Constructor for Digraph objects.
	 *
	 * @param string $p_name       Name of the graph.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graphviz tool.
	 */
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = 'dot' ) {
		parent::__construct( $p_name, $p_attributes, $p_tool );
	}

	/**
	 * Generates a directed graph representation (suitable for dot).
	 *
	 * To test the generated graph, use the
	 * {@see http://magjac.com/graphviz-visual-editor/ Graphviz Visual Editor}
	 *
	 * @return void
	 */
	public function generate() {
		echo 'digraph ' . $this->name . ' {' . "\n";

		$this->print_graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->build_attribute_list( $t_attr );
			echo "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->build_attribute_list( $t_attr );
			echo "\t" . $t_src . ' -> ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		echo "}\n";
	}
}
