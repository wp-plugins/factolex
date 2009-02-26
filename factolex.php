<?php
/*
Plugin Name: Factolex
Plugin URI: http://sebmos.at/factolex-wordpress-plugin/
Description: Shows Factolex facts, requires minimum development skills
Author: Sebastian Moser
Version: 0.2
Author URI: http://sebmos.at/
License: GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
*/ 

define('FACTOLEX_NOFOLLOW',		0);

class Factolex_Facts
{
	private $query;
	private $max_facts;
	private $language;
	private $terms		= array();
	
	function __construct() { }
	
	public function getByTerm($query, $language = 'en')
	{
		$this->query		= $query;
		$this->terms		= array();
		
		$url = 'http://api.factolex.com/v1/get'
			. '?term=' . urlencode(mb_strtolower($this->query))
			. '&lang=' . $language
			. '&format=php';
		
		$xml = $this->getResult($url);
		
		if (isset($xml['error']))
		{
			switch ($xml['error']['code'])
			{
				case 201:
					// no term found
					
					break;
					
				case 202:
					// multiple terms found, continue
					
					$this->addTerms($xml['error']['terms']);
					
					break;
			}
		}
		else
		{
			$this->addTerm($xml['term']);
		}
	}
	
	public function getById($id, $reset = true)
	{
		if ($reset)
			$this->terms		= array();
		
		$url = 'http://api.factolex.com/v1/get'
			. '?id='		. $id
			. '&format=php';
		
		$xml = $this->getResult($url);
		
		if (!isset($xml['term']['facts']))
			return;
		
		$this->addTerm($xml['term']);
	}
	
	private function getResult($url)
	{
		$handler = fopen($url, 'rb');
		$contents = '';
		while (!feof($handler))
		{
			$contents .= fread($handler, 8192);
			continue;
		}
		
		$xml = unserialize($contents);
		
		return $xml;
	}
	
	public function search($query, $language = 'en')
	{
		$this->query		= $query;
		$this->terms		= array();
		
		$url = 'http://api.factolex.com/v1/search'
			. '?query=' . urlencode(mb_strtolower($this->query))
			. '&lang=' . $language
			. '&format=php';
		
		$xml = $this->getResult($url);
		
		if (count($xml['result']) > 0)
		{
			$this->addTerms($xml['result']);
		}
	}
	
	private function addTerms($terms)
	{
		foreach ($terms as $term)
		{
			$this->getById($term['id'], false);
		}
	}
	
	private function addTerm($term)
	{
		$this->terms[] = $term;
	}
	
	public function getArray()
	{
		return $this->terms;
	}
	
	public function getHtml($max_facts)
	{
		// order terms by difference from search term
		$terms_sorted = array();
		foreach ($this->terms as $term)
		{
			$terms_sorted[levenshtein($this->query, $term['title'])] =
				$term;
		}
		ksort($terms_sorted);
		
		$facts_count = 0;
		$output = '';
		
		foreach ($terms_sorted as $term)
		{
			foreach ($term['facts'] as $fact)
			{
				if (!($facts_count < $max_facts))
					break 2;
				
				$output .= '<li>'
						. '<a href="' . $term['link'] . '"';
				
				if (FACTOLEX_NOFOLLOW)
					$output .= ' rel="nofollow"';
				
				$output .= '>' . $term['title'] . '</a>: '
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
}