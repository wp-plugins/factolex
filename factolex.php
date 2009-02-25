<?php
/*
Plugin Name: Factolex
Plugin URI: http://sebmos.at/factolex-wordpress-plugin/
Description: Shows Factolex facts
Author: Sebastian Moser
Version: 0.1
Author URI: http://sebmos.at/
License: GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
*/ 

class Factolex_Facts
{
	private $term;
	private $max_facts;
	private $language;
	private $terms		= array();
	
	function __construct($term, $max_facts = 3, $language = 'en')
	{
		$this->term			= $term;
		$this->max_facts	= $max_facts;
		$this->language		= $language;
		
		$this->getByTerm();
	}
	
	private function getByTerm()
	{
		$this->search();return;
		
		$url = 'http://api.factolex.com/v1/get'
			. '?term=' . urlencode(mb_strtolower($this->term))
			. '&lang=' . $this->language
			. '&format=xml';
		
		$xml = $this->xml2array($url);
		
		if (isset($xml['error']))
		{
			switch ($xml['error']['code'])
			{
				case 201:
					// no term found, try to search for term
					
					$this->search();
					
					break;
					
				case 202:
					// multiple terms found, continue
					
					$this->addTerms($xml['error']['terms']['term']);
					
					break;
			}
		}
		else
		{
			$this->addTerm($xml['response']['term']);
		}
	}
	
	private function getById($id)
	{
		$url = 'http://api.factolex.com/v1/get'
			. '?id='		. $id
			. '&amp;lang='	. $this->language
			. '&format=xml';
		
		$xml = $this->xml2array($url);
		
		if (isset($xml['response']['term']['facts']['fact']))
			$this->addTerm($xml['response']['term']);
	}
	
	private function search()
	{
		$url = 'http://api.factolex.com/v1/search'
			. '?query=' . urlencode(mb_strtolower($this->term))
			. '&lang=' . $this->language
			. '&format=xml';
		
		$xml = $this->xml2array($url);
		
		if ($xml['response']['result']['term'])
		{
			$this->addTerms($xml['response']['result']['term']);
		}
	}
	
	private function addTerms($terms)
	{
		// if only one term is returned, it's not in an array; fix that
		$keys = array_keys($terms);
		if (!is_numeric($keys[0]))
			$terms = array($terms);
		
		foreach ($terms as $term)
		{
			$this->getById($term['id']);
		}
	}
	
	private function addTerm($term)
	{
		// if only one fact is returned, it's not in an array; fix that
		$keys = array_keys($term['facts']['fact']);
		if (!is_numeric($keys[0]))
			$term['facts']['fact'] = array($term['facts']['fact']);
		
		$this->terms[] = $term;
	}
	
	public function getHtml()
	{
		// order terms by difference from search term
		$terms_sorted = array();
		foreach ($this->terms as $term)
		{
			$terms_sorted[levenshtein($this->term, $term['title'])] =
				$term;
		}
		ksort($terms_sorted);
		
		$facts_count = 0;
		$output = '';
		
		foreach ($terms_sorted as $term)
		{
			foreach ($term['facts']['fact'] as $fact)
			{
				if (!($facts_count < $this->max_facts))
					break 2;
				
				$output .= '<li>'
						. '<a href="' . $term['link'] . '" rel="nofollow">'
						. $term['title'] . '</a>: '
						. $fact['title']
						. '</li>';
				
				$facts_count++;
			}
		}
		
		if ($output == '')
			return '';
		
		return
			  '<ul>' . $output . '</ul>';
	}
	
	private function xml2array($url, $get_attributes = 1, $priority = 'tag')
	{
	    $contents = "";
	    if (!function_exists('xml_parser_create'))
	    {
	        return array ();
	    }
	    $parser = xml_parser_create('');
	    if (!($fp = @ fopen($url, 'rb')))
	    {
	        return array ();
	    }
	    while (!feof($fp))
	    {
	        $contents .= fread($fp, 8192);
	    }
	    fclose($fp);
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	    if (!$xml_values)
	        return; //Hmm...
	    $xml_array = array ();
	    $parents = array ();
	    $opened_tags = array ();
	    $arr = array ();
	    $current = & $xml_array;
	    $repeated_tag_index = array ();
	    foreach ($xml_values as $data)
	    {
	        unset ($attributes, $value);
	        extract($data);
	        $result = array ();
	        $attributes_data = array ();
	        if (isset ($value))
	        {
	            if ($priority == 'tag')
	                $result = $value;
	            else
	                $result['value'] = $value;
	        }
	        if (isset ($attributes) and $get_attributes)
	        {
	            foreach ($attributes as $attr => $val)
	            {
	                if ($priority == 'tag')
	                    $attributes_data[$attr] = $val;
	                else
	                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
	            }
	        }
	        if ($type == "open")
	        {
	            $parent[$level -1] = & $current;
	            if (!is_array($current) or (!in_array($tag, array_keys($current))))
	            {
	                $current[$tag] = $result;
	                if ($attributes_data)
	                    $current[$tag . '_attr'] = $attributes_data;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                $current = & $current[$tag];
	            }
	            else
	            {
	                if (isset ($current[$tag][0]))
	                {
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }
	                else
	                {
	                    $current[$tag] = array (
	                        $current[$tag],
	                        $result
	                    );
	                    $repeated_tag_index[$tag . '_' . $level] = 2;
	                    if (isset ($current[$tag . '_attr']))
	                    {
	                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                        unset ($current[$tag . '_attr']);
	                    }
	                }
	                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
	                $current = & $current[$tag][$last_item_index];
	            }
	        }
	        elseif ($type == "complete")
	        {
	            if (!isset ($current[$tag]))
	            {
	                $current[$tag] = $result;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                if ($priority == 'tag' and $attributes_data)
	                    $current[$tag . '_attr'] = $attributes_data;
	            }
	            else
	            {
	                if (isset ($current[$tag][0]) and is_array($current[$tag]))
	                {
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    if ($priority == 'tag' and $get_attributes and $attributes_data)
	                    {
	                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                    }
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }
	                else
	                {
	                    $current[$tag] = array (
	                        $current[$tag],
	                        $result
	                    );
	                    $repeated_tag_index[$tag . '_' . $level] = 1;
	                    if ($priority == 'tag' and $get_attributes)
	                    {
	                        if (isset ($current[$tag . '_attr']))
	                        {
	                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                            unset ($current[$tag . '_attr']);
	                        }
	                        if ($attributes_data)
	                        {
	                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                        }
	                    }
	                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
	                }
	            }
	        }
	        elseif ($type == 'close')
	        {
	            $current = & $parent[$level -1];
	        }
	    }
	    return ($xml_array);
	}
}