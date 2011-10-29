<?php

/**
 * Wordpress to Jekyll
 *
 * A small conversion script that will convert a Wordpress export
 * into individual post files that you can use for a Jekyll
 * powered website.
 *
 * @author David Winter <i@djw.me>
 */
class WordpressToJekyll {
	
	protected $_export_file;
	protected $_build_directory;
	protected $_layout_post = 'post';

	protected $_items;

	protected $_yaml;

	public function __construct($wordpress_xml_file, YamlDumperInterface $yaml, $build_directory)
	{
		$this->_export_file = $wordpress_xml_file;

		$this->_load_items();

		$this->_yaml = $yaml;

		$this->_build_directory = rtrim($build_directory, '/');
	}

	protected function _setup_build_directory()
	{
		if ( ! file_exists($this->_build_directory))
		{
			return mkdir($this->_build_directory);
		}

		return TRUE;
	}

	public function convert()
	{
		$this->_setup_build_directory();

		foreach ($this->_items as $item)
		{
			$post = $this->_post_array($item);
			$formatted_post = $this->_format_post($post);
			$this->_write_post($post, $formatted_post);
		}
	}

	protected function _load_items()
	{
		$xml = simplexml_load_file($this->_export_file, 'SimpleXMLElement', LIBXML_NOCDATA);
		$this->_items = $xml->channel->item;
	}

	protected function _post_array($item)
	{
		$post = array();

		$namespaces = $item->getNameSpaces(TRUE);

		$wp = $item->children($namespaces['wp']);

		$post['filename'] = sprintf('%s-%s.md',
			date('Y-m-d', strtotime($wp->post_date)),
			$wp->post_name
		);

		$post['meta']['layout'] = $this->_layout_post;

		$post['meta']['title'] = (string) $item->title;
		
		$tags = array();

		if ($item->category)
		{
			foreach ($item->category as $tag)
			{
				if ( (string) $tag['domain'] === 'post_tag')
				{
					$tags[] = (string) $tag['nicename'];
				}
			}
		}

		if ( ! empty($tags))
		{
			$post['meta']['tags'] = $tags;
		}

		$content = $item->children($namespaces['content']);

		$post['content'] = str_replace("<!--more-->\n\n", '', $content->encoded);

		return $post;
	}

	protected function _format_post($post)
	{
		$meta = $this->_yaml->dump($post['meta']);

		$post_content = <<<EOT
---
{$meta}
---

{$post['content']}

EOT;
		
		return $post_content;
	}

	protected function _write_post($post, $formatted)
	{
		return file_put_contents($this->_build_directory.'/'.$post['filename'], $formatted);
	}

}

interface YamlDumperInterface {
	
	public function dump($data);

}

$sfyaml = __DIR__.'/vendor/yaml/lib/sfYaml.php';

if ( ! file_exists($sfyaml))
{
	throw new Exception('Symfony yaml was not found. You may need to initialise the git submodules: git submodule update --init --recursive');
}

require($sfyaml);

class YamlDumper implements YamlDumperInterface {
	
	public function dump($data)
	{
		$yaml = new sfYaml();

		return $yaml->dump($data);
	}

}

$file = (isset($argv[1])) ? $argv[1] : getcwd().'/export.xml';
$build = (isset($argv[2])) ? $argv[2] : getcwd().'/posts';

$convert = new WordpressToJekyll($file, new YamlDumper, $build);
$convert->convert();
