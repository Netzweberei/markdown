A super fast, highly extensible markdown parser for PHP
=======================================================

[![Latest Stable Version](https://poser.pugx.org/cebe/markdown/v/stable.png)](https://packagist.org/packages/cebe/markdown)
[![Total Downloads](https://poser.pugx.org/cebe/markdown/downloads.png)](https://packagist.org/packages/cebe/markdown)
[![Build Status](https://secure.travis-ci.org/cebe/markdown.png)](http://travis-ci.org/cebe/markdown)
[![Tested against HHVM](http://hhvm.h4cc.de/badge/cebe/markdown.png)](http://hhvm.h4cc.de/package/cebe/markdown)
[![Code Coverage](https://scrutinizer-ci.com/g/cebe/markdown/badges/coverage.png?s=db6af342d55bea649307ef311fbd536abb9bab76)](https://scrutinizer-ci.com/g/cebe/markdown/)
<!-- [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/cebe/markdown/badges/quality-score.png?s=17448ca4d140429fd687c58ff747baeb6568d528)](https://scrutinizer-ci.com/g/cebe/markdown/) -->

Markdown Grid Extension
-------------

### A new Flavor to cebe/markdown inspired by https://github.com/dreikanter/markdown-grid

This is [Markdown](http://daringfireball.net/projects/markdown/) extension
for grid building. It provides minimal and straightforward syntax to create
multicolumn text layouts. By default the extension generates [Twitter
Bootstrap](http://twitter.github.com/bootstrap/) compatible HTML code but
it is intended to be template-agnostic. It could be configured to use any other
HTML/CSS framework (e.g. [Skeleton](http://getskeleton.com)) or custom design.


### The Syntax

Markdown syntax extension is pretty simple. Page source example below defines
a three-column page fragment:

```markdown
-- row 5,2,5 --
First column contains couple of paragraphs. Lorem ipsum dolor sit amet,
consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore
et dolore magna aliqua.

Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
ut aliquip ex ea commodo consequat.
----
Some images in the middle column:
![One](image-1.png)
![Two](image-2.png)
----
And some **more** text in the _third_ column. Duis aute irure dolor in
reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

[Excepteur](http://excepteur.org ) sint occaecat cupidatat non proident, sunt
in culpa qui officia deserunt mollit anim id est laborum.
-- end --
```

Comma separated list of numbers after the `row` instruction is an optional
definition for columns width. This example uses 12-column Twitter Bootstrap
grid, so "5, 2, 5" corresponds to "41.5%, 17%, 41.5%" relatively to the total
page width.



Usage
-----

### In your PHP project

To parse your markdown you need only two lines of code. The first one is to choose the markdown flavor as
one of the following:

- Original Markdown: `$parser = new \cebe\markdown\Markdown();`
- Github Flavored Markdown: `$parser = new \cebe\markdown\GithubMarkdown();`
- Markdown Extra: `$parser = new \cebe\markdown\MarkdownExtra();`

The next step is to call the `parse()`-method for parsing the text using the full markdown language
or calling the `parseParagraph()`-method to parse only inline elements.

Here are some examples:

```php
// original markdown and parse full text
$parser = new \cebe\markdown\Markdown();
$parser->parse($markdown);

// use github markdown
$parser = new \cebe\markdown\GithubMarkdown();
$parser->parse($markdown);

// use markdown extra
$parser = new \cebe\markdown\MarkdownExtra();
$parser->parse($markdown);

// use markdown extra with bootstrap-grid
$parser = new \cebe\markdown\BootstrapMarkdown();
$parser->parse($markdown);

// parse only inline elements (useful for one-line descriptions)
$parser = new \cebe\markdown\GithubMarkdown();
$parser->parseParagraph($markdown);
```

You may optionally set one of the following options on the parser object:

For all Markdown Flavors:

- `$parser->html5 = true` to enable HTML5 output instead of HTML4.
- `$parser->keepListStartNumber = true` to enable keeping the numbers of ordered lists as specified in the markdown.
  The default behavior is to always start from 1 and increment by one regardless of the number in markdown.

For GithubMarkdown:

- `$parser->enableNewlines = true` to convert all newlines to `<br/>`-tags. By default only newlines with two preceding spaces are converted to `<br/>`-tags. 

### The command line script

You can use it to render this readme:

    bin/markdown README.md > README.html

Using github flavored markdown:

    bin/markdown --flavor=gfm README.md > README.html

or convert the original markdown description to html using the unix pipe:

    curl http://daringfireball.net/projects/markdown/syntax.text | bin/markdown > md.html

Here is the full Help output you will see when running `bin/markdown --help`:

    PHP Markdown to HTML converter
    ------------------------------
    
    by Carsten Brandt <mail@cebe.cc>
   
    Usage:
        bin/markdown [--flavor=<flavor>] [file.md]

        --flavor  specifies the markdown flavor to use. If omitted the original markdown by John Gruber [1] will be used.
                  Available flavors:

                  gfm   - Github flavored markdown [2]
                  extra - Markdown Extra [3]

        --help    shows this usage information.

        If no file is specified input will be read from STDIN.
    
    Examples:
    
        Render a file with original markdown:

            bin/markdown README.md > README.html
    
        Render a file using gihtub flavored markdown:
    
            bin/markdown --flavor=gfm README.md > README.html
    
        Convert the original markdown description to html using STDIN:
    
            curl http://daringfireball.net/projects/markdown/syntax.text | bin/markdown > md.html
    
    
    [1] http://daringfireball.net/projects/markdown/syntax
    [2] https://help.github.com/articles/github-flavored-markdown
    [3] http://michelf.ca/projects/php-markdown/extra/



Extending the language
----------------------

Markdown consists of two types of language elements, I'll call them block and inline elements simlar to what you have in
HTML with `<div>` and `<span>`. Block elements are normally spreads over several lines and are separated by blank lines.
The most basic block element is a paragraph (`<p>`).
Inline elements are elements that are added inside of block elements i.e. inside of text.

This markdown parser allows you to extend the markdown language by changing existing elements behavior and also adding
new block and inline elements. You do this by extending from the parser class and adding/overriding class methods and
properties. For the different element types there are different ways to extend them as you will see in the following sections.

### Adding block elements

The markdown is parsed line by line to identify each non-empty line as one of the block element types.
This job is performed by the `indentifyLine()` method which takes the array of lines and the number of the current line
to identify as an argument. This method returns the name of the identified block element which will then be used to parse it.
In the following example we will implement support for [fenced code blocks][] which are part of the github flavored markdown.

[fenced code blocks]: https://help.github.com/articles/github-flavored-markdown#fenced-code-blocks
                      "Fenced code block feature of github flavored markdown"

```php
<?php

class MyMarkdown extends \cebe\markdown\Markdown
{
	protected function identifyLine($lines, $current)
	{
		// if a line starts with at least 3 backticks it is identified as a fenced code block
		if (strncmp($lines[$current], '```', 3) === 0) {
			return 'fencedCode';
		}
		return parent::identifyLine($lines, $current);
	}

	// ...
}
```

Parsing of a block element is done in two steps:

1. "consuming" all the lines belonging to it. In most cases this is iterating over the lines starting from the identified
   line until a blank line occurs. This step is implemented by a method named `consume{blockName}()` where `{blockName}`
   will be replaced by the name we returned in the `identifyLine()`-method. The consume method also takes the lines array
   and the number of the current line. It will return two arguments: an array representing the block element and the line
   number to parse next. In our example we will implement it like this:

   ```php
	protected function consumeFencedCode($lines, $current)
	{
		// create block array
		$block = [
			'type' => 'fencedCode',
			'content' => [],
		];
		$line = rtrim($lines[$current]);

		// detect language and fence length (can be more than 3 backticks)
		$fence = substr($line, 0, $pos = strrpos($line, '`') + 1);
		$language = substr($line, $pos);
		if (!empty($language)) {
			$block['language'] = $language;
		}

		// consume all lines until ```
		for($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			if (rtrim($line = $lines[$i]) !== $fence) {
				$block['content'][] = $line;
			} else {
				// stop consuming when code block is over
				break;
			}
		}
		return [$block, $i];
	}
	```

2. "rendering" the element. After all blocks have been consumed, they are being rendered using the `render{blockName}()`
   method:

   ```php
	protected function renderFencedCode($block)
	{
		$class = isset($block['language']) ? ' class="language-' . $block['language'] . '"' : '';
		return "<pre><code$class>" . htmlspecialchars(implode("\n", $block['content']) . "\n", ENT_NOQUOTES, 'UTF-8') . '</code></pre>';
	}
   ```

   You may also add code highlighting here. In general it would also be possible to render ouput in a different language than
   HTML for example LaTeX.


### Adding inline elements

Adding inline elements is different from block elements as they are directly parsed in the text where they occur.
An inline element is identified by a marker that marks the beginning of an inline element (e.g. `[` will mark a possible
beginning of a link or `` ` `` will mark inline code).

Inline markers are declared in the `inlineMarkers()`-method which returns a map from marker to parser method. That method
will then be called when a marker is found in the text. As an argument it takes the text starting at the position of the marker.
The parser method will return an array containing the text to append to the parsed markup and an offset of text it has
parsed from the input markdown. All text up to this offset will be removed from the markdown before the next marker will be searched.

As an example, we will add support for the [strikethrough][] feature of github flavored markdown:

[strikethrough]: https://help.github.com/articles/github-flavored-markdown#strikethrough "Strikethrough feature of github flavored markdown"

```php
<?php

class MyMarkdown extends \cebe\markdown\Markdown
{
	protected function inlineMarkers()
	{
		$markers = [
			'~~'    => 'parseStrike',
		];
		// merge new markers with existing ones from parent class
		return array_merge(parent::inlineMarkers(), $markers);
	}

	protected function parseStrike($markdown)
	{
		// check whether the marker really represents a strikethrough (i.e. there is a closing ~~)
		if (preg_match('/^~~(.+?)~~/', $markdown, $matches)) {
			return [
			    // return the parsed tag with its content and call `parseInline()` to allow
			    // other inline markdown elements inside this tag
				'<del>' . $this->parseInline($matches[1]) . '</del>',
				// return the offset of the parsed text
				strlen($matches[0])
			];
		}
		// in case we did not find a closing ~~ we just return the marker and skip 2 characters
		return [$markdown[0] . $markdown[1], 2];
	}
}
```


### Am I free to use this?

This library is open source and licensed under the [MIT License][]. This means that you can do whatever you want
with it as long as you mention my name and include the [license file][license]. Check the [license][] for details.

[MIT License]: http://opensource.org/licenses/MIT
