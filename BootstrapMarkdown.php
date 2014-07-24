<?php
/**
 * BootstrapGrid for cebe/markdown
 * inspired by https://github.com/dreikanter/markdown-grid
 * @author Andreas Neumann, a.neumann@netzweberei.de
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */
namespace cebe\markdown;

class BootstrapMarkdown extends \cebe\markdown\MarkdownExtra
{
    protected function identifyLine($lines, $current)
    {
        $matches = array();
        preg_match('/-- row .*--/', $lines[$current], $matches);
//        print_r($matches);
        if(count($matches)){
            return 'bootstrapGrid';
        }
        return parent::identifyLine($lines, $current);
    }

    protected function consumeBootstrapGrid($lines, $current)
    {
        // create block array
        $block = [
            'type' => 'bootstrapGrid',
            'content' => []
        ];

        $block['content'][] = '<!--mdtb:row-->';

        $line = rtrim($lines[$current]);
        $test = array();
        preg_match('/-- row[ ]*(\d+(?:,[ ]?\d+)*)*[ ]*--/', $line, $test);
        if(count($test)>1) $cols = (explode(',',$test[1]));

        if(is_array($cols))
        {
            $block['content'][] = '<!--mdtb:col'.current($cols).'-->';
            next($cols);
        }

        // detect end of grid
        $endOfBlock = '-- end --';
        $endOfCol = '----';
        // consume all lines until $endOfBlock
        for($i = $current + 1, $count = count($lines); $i < $count; $i++) {
            if (rtrim($line = $lines[$i]) !== $endOfBlock)
            {
                if (rtrim($line = $lines[$i]) == $endOfCol && is_array($cols))
                {
                    $block['content'][] = '';
                    $block['content'][] = '<!--mdtb:endcol-->';
                    $block['content'][] = '<!--mdtb:col'.current($cols).'-->';
                    next($cols);
                }
                else
                {
                    $block['content'][] = $line;
                }
            }
            else
            {
                // stop consuming when code block is over
                break;
            }
        }

        if(is_array($cols))
        {
            $block['content'][] = '<!--mdtb:endcol-->';
        }

        $block['content'][] = '<!--mdtb:endrow-->';

        return [$block, count($block['content'])+1];
    }

    protected function renderBootstrapGrid($block)
    {
        return $this->parseBlocks($block['content']);
    }

    /**
     * Consume lines for an HTML block
     */
    protected function consumeHtml($lines, $current)
    {
        $block = [
            'type' => 'html',
            'content' => [],
        ];

        $matches = array();
        preg_match('/^<!--mdtb:([a-z]*)(\d+)?-->/', $lines[$current], $matches);
        if (count($matches)>1) // TwitterBootstrap-Comment
        {
            switch($matches[1])
            {
                case 'row':
                    $block['content'][] = '<div class="row-fluid">';
                    break;
                case 'col':
                    $span = $matches[2] ? $matches[2] : '';
                    $block['content'][] = '<div class="span'.$span.'">';
                    break;
                case 'endrow':
                case 'endcol':
                    $block['content'][] = '</div>';
                    break;
            }
            return [$block, $current];
        }

        return parent::consumeHtml($lines, $current);
    }
}