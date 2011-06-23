<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class sxml
{
    var $parser;
    var $error_code;
    var $error_string;
    var $current_line;
    var $current_column;
    var $data = array();
    var $datas = array();

    function parse($data)
    {
        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->parser, 'cdata');

        if (!xml_parse($this->parser, $data))
        {
            $this->data = array();
            $this->error_code = xml_get_error_code($this->parser);
            $this->error_string = xml_error_string($this->error_code);
            $this->current_line = xml_get_current_line_number($this->parser);
            $this->current_column = xml_get_current_column_number($this->parser);
        }
        else
        {
            $this->data = $this->data;
        }
        xml_parser_free($this->parser);

        return $this->data;
    }

    function tag_open($parser, $tag, $attribs)
    {
        $this->datas[] =& $this->data;

        if (isset($this->data[strtolower($tag)]))
        {
            if (!isset($this->data[strtolower($tag)][0]))
            {
                $this->data[strtolower($tag)] = array($this->data[strtolower($tag)]);
            }
            $this->data =& $this->data[strtolower($tag)][];
        }
        else
            $this->data =& $this->data[strtolower($tag)];
    }

    function cdata($parser, $cdata)
    {
        if (trim($cdata) != '')
        {
            @$this->data .= trim($cdata);
        }
    }

    function tag_close($parser, $tag)
    {
        $this->data =& $this->datas[count($this->datas)-1];
        array_pop($this->datas);
    }
}
?>