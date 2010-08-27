<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'TrailerAddict API Wrapper',
	'pi_version' =>'1.0',
	'pi_author' =>'Green Egg Media',
	'pi_author_url' => 'http://www.greeneggmedia.com/',
	'pi_description' => 'Allows easy access to TrailerAddict.com API functions',
	'pi_usage' => Traileraddict::usage()
);

/**
 * TrailerAddict API Wrapper
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Adam Fairholm, Green Egg Media
 * @copyright		Copyright (c) 2010, Green Egg Media
 * @link			http://www.greeneggmedia.com/code/traileraddict-ee2-api-wrapper-plugin
 */

class Traileraddict
{

	/**
	 * Defaults for config
	 */
	var $config = array(
		'width' 	=> 450,
		'countr'	=> 1
	);

	// -------------------------------------

	/**
	 * URL for making API calls
	 */
	var $api_url					= 'http://api.traileraddict.com/?';

	// -------------------------------------

  	function Traileraddict()
  	{
		$this->EE =& get_instance(); 
  	
  		// -------------------------------------
		// Get the params
  		// -------------------------------------

		if( $this->EE->TMPL->fetch_param('width') != '' ):

			$this->config['width'] 		= $this->EE->TMPL->fetch_param('width'); 

		endif;

  		// -------------------------------------

		if( $this->EE->TMPL->fetch_param('count') != '' ):

			$this->config['count'] 		= $this->EE->TMPL->fetch_param('count'); 

		endif;
  	}

	// ------------------------------------------------------------------------

	/**
	 * Get featured trailers
	 *
	 * @access	public
	 * @return	object
	 */
	function featured()
	{
		// Set feature config to "yes" to pull features
	
		$this->config['featured'] = 'yes';
  		
  		// -------------------------------------
		// Make API request and prep variables
  		// -------------------------------------
		
		$trailer_data = $this->_request();
		
		$variables = array();
		
		$count = 0;
		
		foreach( $trailer_data->trailer as $trailer )
		{
			$variables[$count]['title']			= $trailer->title;
			$variables[$count]['link']			= $trailer->link;
			$variables[$count]['trailer_id']	= $trailer->trailer_id;
			$variables[$count]['embed_code']	= $trailer->embed;
			$variables[$count]['pub_date']		= strtotime($trailer->pubDate);
			
			$count++;
		}
		
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get trailers for a film or actor/actress
	 *
	 * @access	public
	 * @return	object
	 */
	function trailers()
	{
		$this->EE->load->helper('url');

  		// -------------------------------------
		// Fetch and validate params
  		// -------------------------------------
	
		$mode 				= $this->EE->TMPL->fetch_param('mode');
		
		if( $mode != 'film' && $mode != 'actor' ):
			
			$mode = 'film';
		
		endif;
	
		// Use CI's url_title function to get name into TA format 
	
		$this->config[$mode] = url_title( $this->EE->TMPL->fetch_param( $mode ) );

  		// -------------------------------------
		// Make API request and prep variables
  		// -------------------------------------
		
		$trailer_data = $this->_request();
				
		$variables = array();
		
		$count = 0;
		
		foreach( $trailer_data->trailer as $trailer )
		{
			$variables[$count]['title']			= $trailer->title;
			$variables[$count]['link']			= $trailer->link;
			$variables[$count]['trailer_id']	= $trailer->trailer_id;
			$variables[$count]['embed_code']	= $trailer->embed;
			$variables[$count]['pub_date']		= strtotime($trailer->pubDate);
			
			$count++;
		}

  		// -------------------------------------
		
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Use TA's simple API to get data from a single film
	 *
	 * @access	public
	 * @return	object
	 */
	function simple()
	{
  		// -------------------------------------
		// Make API request and prep variables
  		// -------------------------------------

		$url = $this->EE->TMPL->fetch_param( 'url' );
	
		$request = "http://simpleapi.traileraddict.com/".$url;

		$response = @file_get_contents( $request );

		$trailer_data = simplexml_load_string( $response );
		
		print_r($trailer_data);
		
		$variables = array();
		
		foreach( $trailer_data->trailer as $trailer ):

			foreach( $trailer as $key => $value ):
			
				$variables[0][$key]			= $value;

			endforeach;
		
		endforeach;

  		// -------------------------------------
		
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Make the API request
	 *
	 * @access	private
	 * @param	[array]
	 * @return	object
	 */
	function _request()
	{
		$request = $this->api_url . $this->_build_string( $this->config );
		
		$response = @file_get_contents( $request );

		$xml = simplexml_load_string( $response );

		return $xml;	
	}

	// --------------------------------------------------------------------------

	/**
	 * Build request data array into a string
	 *
	 * @access	private
	 * @param	array data
	 * @return 	string
	 */
	function _build_string( $data )
	{
		$return = NULL;
		$i = 0;
		$t = count( $data );

		foreach( $data as $k => $v ) {

			$k = urlencode( $k );
			$v = urlencode( $v );

			$return .= "&{$k}={$v}";

		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Usage
	 *
	 * Describes plugin usage.
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
  	{
		ob_start(); 
 
?>

This plugin allows easy access to TrailerAddict.com API functionality.

http://ee-docs.greeneggmedia.com/trailer_addict.html

Example usage:

{exp:traileraddict:featured count="3" width="700"}

	<h2><a href="{link}">{title}</a></h2>

	<p>Trailer ID: {trailer_id}</p>

	{embed_code}

	{/exp:traileraddict:featured}

<?php
  
	  $buffer = ob_get_contents();
		
	  ob_end_clean(); 
	
	  return $buffer;
	}
  
  	// END

}
/* End of file pi.plugin_name.php */ 
/* Location: ./system/expressionengine/third_party/traileraddict/pi.traileraddict.php */