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
 * Directed Graph API
 *
 * @package CoreAPI
 * @author Juliano Ravasi Ferraz <jferraz at users sourceforge net>
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 */

require_api( 'constant_inc.php' );

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
	 * Constructor for Graph objects.
	 * @param string $p_name       Graph name.
	 * @param array  $p_attributes Attributes.
	 */
	function Graph( $p_name = 'G', array $p_attributes = array() ) {
		if( is_string( $p_name ) ) {
			$this->name = $p_name;
		}

		$this->set_attributes( $p_attributes );
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
		#$this->_print_graph_defaults();
		
		echo '<svg id="svg-canvas" width=960 height=600></svg>';
		echo '<script id="js">';
		echo 'var g = new dagreD3.Digraph();' .
			 'var renderer = new dagreD3.Renderer();' .
			 'renderer.zoom(false);' .
			 'var oldDrawNodes = renderer.drawNodes();' .
			 'renderer.drawNodes(function(graph, root) {' .
			 'var svgNodes = oldDrawNodes(graph, root);' .
			 'svgNodes.each(function(u) { d3.select(this).classed(graph.node(u).nodeclass, true); });' .
			 'return svgNodes;' . 
			 '});';
			 
		echo 'var oldDrawEdgePaths = renderer.drawEdgePaths();
renderer.drawEdgePaths(function(graph, root) {
  var svgPaths = oldDrawEdgePaths(graph, root);
  svgPaths.each(function(u) { 
		if( graph.edge(u).dir == null )
			d3.select(this).select(\'path\').attr(\'marker-end\', \'\'); 
		if( graph.edge(u).style == \'dashed\' )
			d3.select(this).select(\'path\').attr(\'stroke-dasharray\',"5,5");
		if( graph.edge(u).color != null )
			d3.select(this).select(\'path\').classed(\'mo\',true);
		else
			d3.select(this).select(\'path\').classed(\'moo\',true);
	}	
  );
  return svgPaths;
});';

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo 'g.addNode(' . $t_name . ', ' . $t_attr . '   );' . "\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo 'g.addEdge(null, ' . $t_src . ', ' . $t_dst . ', ' . $t_attr . ' );' . "\n";
		}
		
		echo 'var svg = d3.select(\'svg\'),' .
			 'svgGroup = svg.append(\'g\');' .
			 'var layout = dagreD3.layout().nodeSep(20).rankDir("LR");' .
			 'renderer.layout(layout).run(g, d3.select(\'svg g\'));' .
			 '</script>';
	}

	/**
	 * PROTECTED function to build a node or edge attribute list.
	 * @param array $p_attributes Attributes.
	 * @return string
	 */
	function _build_attribute_list( array $p_attributes ) {
		if( empty( $p_attributes ) ) {
			return '{}';
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

			$t_result[] = $t_name . ':' . $t_value;
		}

		return '{ ' . join( ', ', $t_result ) . ' }';
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
	 */
	function Digraph( $p_name = 'G', array $p_attributes = array()) {
		parent::Graph( $p_name, $p_attributes );
	}
}
