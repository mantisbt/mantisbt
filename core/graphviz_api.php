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
		'dot'	=> 'text/x-graphviz',
		'ps'	=> 'application/postscript',
		'hpgl'	=> 'application/vnd.hp-HPGL',
		'pcl'	=> 'application/vnd.hp-PCL',
		'mif'	=> 'application/vnd.mif',
		'gif'	=> 'image/gif',
		'jpg'	=> 'image/jpeg',
		'jpeg'	=> 'image/jpeg',
		'png'	=> 'image/png',
		'wbmp'	=> 'image/vnd.wap.wbmp',
		'xbm'	=> 'image/x-xbitmap',
		'ismap'	=> 'text/plain',
		'imap'	=> 'application/x-httpd-imap',
		'cmap'	=> 'text/html',
		'cmapx'	=> 'application/xhtml+xml',
		'vrml'	=> 'x-world/x-vrml',
		'svg'	=> 'image/svg+xml',
		'svgz'	=> 'image/svg+xml',
		'pdf'	=> 'application/pdf',
		'webp'	=> 'image/webp',
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
	 * Outputs a script that adds an inline SVG image to a specified HTML element.
	 *
	 * @param string $p_id The ID of the HTML element to append to.
	 *
	 * @return void
	 */
	public function output_html( string $p_id ) {
		
		$this->attributes += [ 'layout' => $this->graphviz_tool ];
		
		# Retrieve the source document into a buffer
		ob_start();
		$this->generate();
		$t_source = ob_get_clean();

		# Include Viz.js
		if( config_get_global( 'cdn_enabled' ) == ON ) {
			html_javascript_cdn_link( 'https://cdn.jsdelivr.net/npm/@viz-js/viz@' . VIZJS_VERSION . '/dist/viz-global.min.js', VIZJS_HASH );
		} else {
			html_javascript_cdn_link( helper_mantis_url( 'js/viz-global.min.js' ), VIZJS_HASH );
		}

		# Include Viz.js proxy
		echo "\t", '<script src="', helper_mantis_url( 'js/viz-proxy.js' ),
			'" integrity="sha384-H/zty1fUwbry1pDZ9ug5UmMKBhwOu/FBMLUdVkGMv9Z+vCwLqcIrn3Tog/YUtwrL',
			'" data-id="', $p_id, '" data-source="', string_attribute( $t_source ), '" async></script>', PHP_EOL;
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

		# Retrieve the source document into a buffer
		ob_start();
		$this->generate();
		$t_source = ob_get_clean();

		# Start tool process
		$t_command = escapeshellarg( $t_tool_path ) . ' -T' . $p_format;
		$t_stderr = tempnam( sys_get_temp_dir(), 'graphviz' );
		$t_descriptors = array(
			0 => array( 'pipe', 'r', ),	# stdin
			1 => array( 'pipe', 'w', ),	# stdout
			# Writing to file instead of pipe to avoid locking issues
			2 => array( 'file', $t_stderr, 'w', ),	# stderr
		);

		$t_pipes = array();
		$t_process = @proc_open( $t_command, $t_descriptors, $t_pipes,
			null, null, [ 'bypass_shell' => true ] );

		if( !is_resource( $t_process ) ) {
			error_parameters( $t_tool_path );
			trigger_error( ERROR_GRAPH_TOOL_NOT_FOUND, ERROR );
		}

		# Filter the generated document through
		stream_set_blocking( $t_pipes[0], false );
		fwrite( $t_pipes[0], $t_source );
		fclose( $t_pipes[0] );

		if( $p_headers ) {
			# Use an output buffer to retrieve the size for Content-Length
			ob_start();
		}

		# Send the output
		while( !feof( $t_pipes[1] ) ) {
			echo fgets( $t_pipes[1], 1024 );
		}
		fclose( $t_pipes[1] );

		if( $p_headers ) {
			$t_length = ob_get_length();
			if( $t_length ) {
				header( 'Content-Type: ' . $this->formats[$p_format] );
				header( 'Content-Length: ' . $t_length );
				ob_end_flush();
			} else {
				# No output - error suspected
				ob_end_clean();
			}
		}

		# Check for output in stderr
		# Wait a bit to ensure the file has been written before attempting to read it
		usleep( 5000 );
		$t_error = file_get_contents( $t_stderr );
		unlink( $t_stderr );

		$t_exit = proc_close( $t_process );
		if( $t_error || $t_exit ) {
			error_parameters( $t_error ?: "Exit code: $t_exit" );
			trigger_error( ERROR_GENERIC, $t_exit ? ERROR : WARNING );
		}
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
		return self::graphviz_path() . $this->graphviz_tool . ( is_windows_server() ? '.exe' : '' );
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
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = Graph::TOOL_DOT ) {
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
