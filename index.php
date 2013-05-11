<?php
/**
 * welcome in the world of YACS
 *
 * Well, this script is the front page of a YACS server.
 *
 * If you are looking for general information about YACS, including design principles, please look at [script]help/index.php[/script].
 *
 * Usually the front page is the most often visited page of a web site.
 * Therefore we have crafted it as carefully as possible, to allow for maximum flexibility and efficiency.
 *
 * First of all, YACS offers powerful features to assign content to various areas of the front page.
 * These areas (e.g., cover page, gadget boxes, etc.) are introduced below.
 *
 * Second, YACS offers a large number of customization options,
 * and most parameters that impact the front page can be conveniently changed
 * at the related configuration panel.
 * Look at [script]configure.php[/script] for a full description of these parameters.
 * This configuration panel is accessible from the Control Panel.
 *
 * Also, several extension mechanisms have been added to this page, and we hope that gifted software programmers will take the most out of it.
 * Look at [script]control/scan.php[/script] for more information on YACS hooks and software extensions.
 *
 * And of course, most tags generated by YACS, if not all, make intensive usage of CSS. Therefore the visual rendering can be changed
 * directly by editing the style sheet used in the live skin for the site.
 * Look at [script]scripts/index.php[/script] for more information on skins and templates used by YACS.
 *
 * [title]General structure of a YACS page[/title]
 *
 * Generally speaking the overall YACS page factory handles three different kinds of areas on the screen:
 * - the main panel - This is where most of the text will be placed.
 * Every YACS script is aiming to put some content in the main area.
 * - the extra panel - Extra information, if any, to be displayed on page side.
 * YACS scripts make intensive use of this to further improve site navigation.
 * - the navigation panel - This is common information to be displayed on all pages.
 * Most content of the navigation panel is generated and managed only in the template file of the current skin.
 * However, some script, such as the one that generates the front page, may complement the navigation panel
 * with additional information specific to only one page.
 *
 * Please note that some skins do combine the extra and the navigation panels as a single visual column,
 * where other skins will feature three column, or two columns plus a floating container, etc.
 *
 *
 * [title]The main panel[/title]
 *
 * Several elements compose the main part of this page. From first to last:
 * - Top icons
 * - The cover article
 * - Text produced by hooks included on id 'index.php#prefix'
 * - A dynamic Flash object listing most recent articles
 * - Gadgets boxes
 * - A list of sections. Several layouts are available?
 * - Text produced by hooks included on id 'index.php'
 * - The list of most recent articles. Several layouts are available, as explained below.
 * - Bottom icons
 * - Text produced by hooks included on id 'index.php#suffix'
 *
 * The cover article is, actually, posted in the section dedicated to global
 * pages. This section is visible from the Site Map.
 * To change the cover page edit it, like you would do for any regular article.
 * You can add images, use various YACS code or any HTML tag to achieve your goal.
 * On completion, publish the article to make it visible at the front page.
 * Rendering of the cover page may be total or partial based on parameter [code]root_cover_at_home[/code].
 * Content of the cover article is formatted through a call to [code]Skin::layout_cover_article()[/code].
 * Though this function has been implemented into [script]skins/skin_skeleton.php[/script],
 * it can be overloaded into [code]skin.php[/code] of any skin if necessary.
 *
 * The prefix hook is used to invoke any software extension bound as follows:
 * - id: 'index.php#prefix'
 * - type: 'include'
 * - parameters: none
 * Use this hook to include any text right after the cover article.
 *
 * The Flash object is a convenient way to dynamically animate the front page, based on most recent articles published.
 * This has to be explicitly activated in the parameter [code]root_flash_at_home[/code].
 *
 * Gadget boxes are used either to display information that is not part of the
 * regular stream of new content, or to specifically focus on special pages.
 * Change parameter [code]root_gadget_boxes_at_home[/code] to disable the display of
 * gadget boxes. Up to 6 gadget boxes can be displayed at the front page.
 *
 * To create gadget boxes, just post articles in the section dedicated to gadget boxes.
 * Each article will be put in a separate box.
 *
 * Activate the display of the Site Map (actually, a downsize version of it) to introduce
 * top-level sections of your site.
 * The layout used by YACS is specified in parameter [code]sections_layout[/code].
 *
 * The main hook is used to invoke any software extension bound as follows:
 * - id: 'index.php'
 * - type: 'include'
 * - parameters: none
 * Use this hook to include any text just before the list of recent publications.
 *
 * By default, articles are formatted through a call to [code]Skin::build_list(... 'decorated')[/code].
 * This function can be overloaded into [code]skin.php[/code] of any skin.
 * The configuration panel for the front page (aka, [script]configure.php[/script]) may be used
 * to adopt another layout depending of your needs.
 * The parameter [code]root_articles_layout[/code] defines the layout to be used at the front page:
 * - [code]daily[/code] - Make titles out of publication dates.
 * This layout is suitable for weblogs. It is the default value. See [script]skins/layout_home_articles_as_daily.php[/script]
 * - [code]newspaper[/code] - Focus on the last published article, and list some articles published previously.
 * This layout is suitable for most sites. See [script]skins/layout_home_articles_as_newspaper.php[/script]
 * - [code]hardboiled[/code] - List the last ten most recent pages.
 * Previous articles may be accessed through sections, or through the index of articles.
 * This layout is suitable for sites providing several different kinds of information. See [script]skins/layout_home_articles_as_hardboiled.php[/script]
 * - [code]slashdot[/code] - List the last ten most recent pages.
 * Previous articles may be accessed through sections, or through the index of articles.
 * This layout is suitable for sites providing several different kinds of information. See [script]skins/layout_home_articles_as_slashdot.php[/script]
 * - [code]decorated[/code] - A compact list of the ten most recent articles.
 * This layout is suitable for sites with a lot of items (gadget boxes, etc.) at the front page. See [script]articles/layout_articles.php[/script]
 * - [code]compact[/code] - A simple list of titles.
 * - [code]alistapart[/code] - Display only the most recent published page.
 * Previous articles may be accessed through a menu.
 * This layout is suitable for small sites with a low activity, maybe with a single section of pages.
 * - [code]no_articles[/code] - Do not mention recent articles.
 * Use this option to fully customize the front page, for example through some hook.
 *
 * The number of articles displayed depends on the selected layout.
 * For example, the alistapart layout displays one single full-page, while slashdot summarizes several articles.
 * To override this number set the parameter [code]root_articles_count_at_home[/code] in the configuration panel for page factory.
 *
 * This front page is also able to display the content of one single section, if its id
 * or nick name is specified in the parameter ##root_sections_at_home##.
 * In this case, the list of recent pages may be affected by any overlay that has been activated for the target section.
 * For example, if a section has been overlaid with ##day##, and if its id has been set in ##root_sections_at_home##, then a pretty calendar of coming events will
 * be displayed at the front page.
 *
 * Bottom and trailing icons are clickable images linked to related articles.
 * For example, you would use this to show logos of your partners at the front page,
 * and create one page to introduce each partner.
 * To achieve this, add thumbnails to target pages, then configure the containing section
 * to display them at the front page.
 * Up to 12 images are displayed.
 *
 * The suffix hook is used to invoke any software extension bound as follows:
 * - id: 'index.php#suffix'
 * - type: 'include'
 * - parameters: none
 * Use this hook to include any text at the bottom of the main area, after everything else.
 *
 *
 * [title]The extra panel[/title]
 *
 * Following components are displayed as boxes in the extra panel:
 * - The list of featured articles, if any
 * - Articles featured as extra boxes (one box per article) -- see the '[code]extra_boxes[/code]' section
 *
 * Featured pages are those articles that have been assigned to the category dedicated to that purpose.
 * To feature one particular article, display it, then use side links to change assigned categories.
 * Select the category named Featured in the drop list and click on the button.
 *
 * [title]Customized templates[/title]
 *
 * You may create a specific template for this page to depart from regular
 * rendering if you wish.
 *
 * YACS looks for specific templates for this page into the skin directory,
 * and it loads:
 * - ##template_home.php## for the regular front page of the server,
 * - or ##template_slash.php## for the topmost page
 *
 * For example, when the server is installed in directory ##/yacs/## the template
 * ##template_home.php## is loaded on url ##/yacs/index.php##, where the template
 * ##template_slash.php## is loaded on url ##/index.php##.
 *
 * When YACS is directly installed at the top level of the server (e.g., when
 * the parameter 'url_to_root' is '/'), only the template ##template_home.php##
 * is used.
 *
 * [title]Meta information[/title]
 *
 * A feeding link has been included, in order to let robots browse this resource when necessary.
 * See [script]feeds/rss.php[/script] for more information.
 *
 * Simlarly, a meta-link to our blogging API is added, to allow for easy
 * auto-discovery of server capability.
 * See [script]services/describe.php[/script] for more information.
 *
 * If geographical information has been set in [script]skins/configure.php[/script], it is included
 * in meta data.
 * See either [link=GeoTags Search Engine]http://geotags.com/[/link]
 * and [link=Free Geocoding Service for 22 Countries]http://www.travelgis.com/geocode/Default.aspx[/link]
 * for more information.
 *
 * @link http://geotags.com/ GeoTags Search Engine
 * @link http://www.travelgis.com/geocode/Default.aspx Free Geocoding Service for 22 Countries
 *
 * Note that, contrary to other regular pages, this one does not trigger the 'tick' hook.
 *
 * @author Bernard Paques
 * @author Christophe Battarel [email]christophe.battarel@altairis.fr[/email]
 * @tester Christian Loubechine
 * @tester Pat
 * @tester Olivier
 * @tester Agnes
 * @tester Guillaume Perez
 * @tester Viviane Zaniroli
 * @tester Anatoly
 * @tester Timster
 * @tester Mordread Wallas
 * @tester Thierry Pinelli (ThierryP)
 * @tester Alain Lesage (Lasares)
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// load global definitions
if($home = getenv('YACS_HOME'))
	include_once str_replace('//', '/', $home.'/').'shared/global.php';
elseif(is_readable('yacs.home') && is_callable('file_get_contents') && ($content = trim(file_get_contents('yacs.home'), " \t\n\r\0\x0B\\/.")) && is_readable($content.'/shared/global.php'))
	include_once $content.'/shared/global.php';
elseif(is_readable('shared/global.php'))
	include_once 'shared/global.php';
elseif(is_readable('yacs/shared/global.php'))
	include_once 'yacs/shared/global.php';
else
	exit('The file shared/global.php has not been found. Please reinstall or mention home directory in file yacs.home or configure the YACS_HOME environment variable.');

// load libraries used in this script
include_once $context['path_to_root'].'feeds/feeds.php'; // some links to newsfeeds
include_once $context['path_to_root'].'links/links.php';

// load localized strings
i18n::bind('root');

// load the skin, and flag topmost page against regular front page
if(($context['script_url'] == '/index.php') && ($context['url_to_root'] != '/'))
	load_skin('slash');
else
	load_skin('home');

// the menu bar may be made of sections
if(isset($context['root_sections_at_home']) && ($context['root_sections_at_home'] != 'none') && isset($context['root_sections_layout']) && ($context['root_sections_layout'] == 'menu')) {

	// default number of sections to list
	if(!isset($context['root_sections_count_at_home']) || ($context['root_sections_count_at_home'] < 1))
		$context['root_sections_count_at_home'] = 5;

	if($items = Sections::list_by_title_for_anchor(NULL, 0, $context['root_sections_count_at_home'], 'menu'))
		$context['page_menu'] = $items;
}

// load the cover page
if((!isset($context['root_cover_at_home']) || ($context['root_cover_at_home'] != 'none'))) {

	// look for a named page
	if($cover_page = Articles::get('cover'))
		;

	// else take newest page from section of covers
	elseif($anchor = Sections::lookup('covers'))
		$cover_page =& Articles::get_newest_for_anchor($anchor);

	// compute page title -- $context['page_title']
	if(isset($cover_page['title']) && (!isset($context['root_cover_at_home']) || ($context['root_cover_at_home'] == 'full')))
		$context['page_title'] = $cover_page['title'];

	// layout content of cover page -- may be changed in skin.php if necessary
	if(isset($cover_page['id']))
		$context['text'] .= Skin::layout_cover_article($cover_page);

}

// the prefix hook
if(is_callable(array('Hooks', 'include_scripts')))
	$context['text'] .= Hooks::include_scripts('index.php#prefix');

// most recent articles listed in flash, if ming module is available
if(isset($context['root_flash_at_home']) && ($context['root_flash_at_home'] == 'Y'))
	$context['text'] .= Codes::beautify('[news=flash]');

// gadget boxes
if((!isset($context['root_gadget_boxes_at_home']) || ($context['root_gadget_boxes_at_home'] == 'Y'))) {

	// build an array of boxes
	$gadget_boxes = array();

	// articles to be displayed as gadget boxes
	if($anchor = Sections::lookup('gadget_boxes')) {

		// up to 6 articles to be displayed as gadget boxes
		if($items =& Articles::list_for_anchor_by('publication', $anchor, 0, 6, 'boxes')) {
			foreach($items as $title => $attributes)
				$gadget_boxes[] = array($title, $attributes['content'], $attributes['id']);
		}

	}

	// we do have some boxes to display
	if(count($gadget_boxes)) {

		// limit the number of boxes displayed
		@array_splice($gadget_boxes, 6);

		// gadget box rendering
		$context['text'] .= "\n".'<p id="gadgets_prefix"> </p>'."\n";
		foreach($gadget_boxes as $gadget_box)
			$context['text'] .= Skin::build_box($gadget_box[0], $gadget_box[1], 'gadget', isset($gadget_box[2])?$gadget_box[2]:NULL);
		$context['text'] .= '<p id="gadgets_suffix"> </p>'."\n";
	}

}

// no section should be featured at the home page
if(!isset($context['root_sections_at_home']) || ($context['root_sections_at_home'] == 'none'))
	;

// look at only one section
elseif(($target_section = Sections::get($context['root_sections_at_home'])) && isset($target_section['id'])) {

	// re-use the existing script to render this specific section
	$context['arguments'][0] = $target_section['id'];
	include_once $context['path_to_root'].'sections/view.php';

// section(s) at the front page
} else {

	// load the layout to use
	switch($context['root_sections_layout']) {
	case 'decorated':
		include_once $context['path_to_root'].'sections/layout_sections.php';
		$layout = new Layout_sections();
		break;
	case 'map':
		include_once $context['path_to_root'].'sections/layout_sections_as_yahoo.php';
		$layout = new Layout_sections_as_yahoo();
		$layout->set_variant(20); // show more elements at the front page
		break;
	default:

		// load layout, if one exists
		if(is_readable($context['path_to_root'].'sections/layout_sections_as_'.$context['root_sections_layout'].'.php')) {
			$name = 'layout_sections_as_'.$context['root_sections_layout'];
			include_once $context['path_to_root'].'sections/'.$name.'.php';
			$layout = new $name;

		// no layout to use
		} else {

			// useful warning for associates
			if(Surfer::is_associate())
				Logger::error(sprintf(i18n::s('Warning: No script exists for the customized layout %s'), $context['root_sections_layout']));

			// load default layout
			include_once $context['path_to_root'].'sections/layout_sections_as_yahoo.php';
			$layout = new Layout_sections_as_yahoo();
		}
	}

	// the maximum number of sections
	if(is_object($layout))
		$items_per_page = $layout->items_per_page();
	else
		$items_per_page = SECTIONS_PER_PAGE;

	// query the database and layout that stuff
	$items = '';
	if($context['root_sections_layout'] != 'menu')
		$items = Sections::list_by_title_for_anchor(NULL, 0, $items_per_page, $layout);

	// we have an array to format
	if(is_array($items)) {

		// two columns
		if($context['root_sections_layout'] == 'map')
			$items =& Skin::build_list($items, '2-columns');

		// decorated
		else
			$items =& Skin::build_list($items, 'decorated');
	}

	// make a box
	if($items)
		$context['text'] .= $items;

}

// the main hook
if(is_callable(array('Hooks', 'include_scripts')))
	$context['text'] .= Hooks::include_scripts('index.php');

// the list of most recent articles
switch($context['root_articles_layout']) {
case 'compact':
	include_once $context['path_to_root'].'articles/layout_articles_as_compact.php';
	$layout = new Layout_articles_as_compact();
	break;
case 'decorated':
	include_once $context['path_to_root'].'articles/layout_articles.php';
	$layout = new Layout_articles();
	break;
case 'no_articles':
	$layout = NULL;
	break;
default:

	// load layout, if one exists, for the home page
	if(is_readable($context['path_to_root'].'skins/layout_home_articles_as_'.$context['root_articles_layout'].'.php')) {
		$name = 'layout_home_articles_as_'.$context['root_articles_layout'];
		include_once $context['path_to_root'].'skins/'.$name.'.php';
		$layout = new $name;

	// no layout to use
	} else {

		// useful warning for associates
		if(Surfer::is_associate())
			Logger::error(sprintf(i18n::s('Warning: No script exists for the customized layout %s'), $context['root_articles_layout']));

		// load default layout
		include_once $context['path_to_root'].'skins/layout_home_articles_as_daily.php';
		$layout = new Layout_home_articles_as_daily();
	}
}

// the maximum number of articles
if(isset($context['root_articles_count_at_home']) && ($context['root_articles_count_at_home'] > 0))
	$items_per_page = $context['root_articles_count_at_home'];
elseif(is_object($layout))
	$items_per_page = $layout->items_per_page();
else
	$items_per_page = ARTICLES_PER_PAGE;

// no layout
if($layout === NULL)
	$items = '';

// look for recent articles across all sections
elseif(!$items =& Articles::list_(0, $items_per_page, $layout)) {

	// no article yet
	$items = '<p>'.i18n::s('No page to display.');
	if(Surfer::is_associate())
		$items .= ' '.sprintf(i18n::s('Use the %s to populate this server.'), Skin::build_link('help/populate.php', i18n::s('Content Assistant'), 'shortcut'));
	$items .= '</p>';

}

// we have an array to format
if(is_array($items)) {

	// add a link to articles index
	$items['articles/'] = array('', i18n::s('All pages'), '', 'shortcut');

	// make a string out of the array
	if(isset($context['root_articles_layout']) && ($context['root_articles_layout'] == 'compact'))
		$items =& Skin::build_list($items, 'compact');
	else
		$items =& Skin::build_list($items, 'decorated');

	// add a title in case of complex page
	$title = '';
	if(preg_match('/<h2>|<h3>/', $context['text'].$items)) {
		$title = i18n::s('Recent Pages');
	}

	// make a box
	if($items)
		$items =& Skin::build_box($title, $items, 'header1', 'recent_articles');

}
$context['text'] .= $items;

// the suffix hook
if(is_callable(array('Hooks', 'include_scripts')))
	$context['text'] .= Hooks::include_scripts('index.php#suffix');

// the trail of the cover article
if((!isset($context['root_cover_at_home']) || ($context['root_cover_at_home'] != 'none'))) {

	// may be changed in skin.php if necessary
	if(isset($cover_page['trailer']))
		$context['text'] .= Codes::beautify($cover_page['trailer']);

}

//
// compute extra information -- $context['extra']
//

// page tools
//
if(Surfer::is_associate()) {
	$context['page_tools'][] = Skin::build_link('configure.php', i18n::s('Configure'));
	if(isset($cover_page['id']))
		$context['page_tools'][] = Skin::build_link(Articles::get_permalink($cover_page), i18n::s('Cover page'), 'basic');
	if(($section = Sections::get('gadget_boxes')) && isset($section['id']))
		$context['page_tools'][] = Skin::build_link(Sections::get_permalink($section), i18n::s('Gadget boxes'), 'basic');
	if(($section = Sections::get('extra_boxes')) && isset($section['id']))
		$context['page_tools'][] = Skin::build_link(Sections::get_permalink($section), i18n::s('Extra boxes'), 'basic');
	if(($section = Sections::get('navigation_boxes')) && isset($section['id']))
		$context['page_tools'][] = Skin::build_link(Sections::get_permalink($section), i18n::s('Navigation boxes'), 'basic');
}

// save some database requests
$cache_id = 'index.php#extra_news';
if(!$text = Cache::get($cache_id)) {

	// show featured articles -- set in configure.php
	if(isset($context['root_featured_layout']) && ($context['root_featured_layout'] != 'none')) {

		// set in configure.php
		if(!isset($context['root_featured_count']) || ($context['root_featured_count'] < 1))
			$context['root_featured_count'] = 7;

		// the category used to assign featured pages
		$anchor = Categories::get(i18n::c('featured'));
		if($anchor['id'] && ($items =& Members::list_articles_by_date_for_anchor('category:'.$anchor['id'], 0, ($context['root_featured_count']+1), 'news'))) {

			// link to the category page from the box title
			$title =& Skin::build_box_title($anchor['title'], Categories::get_permalink($anchor), i18n::s('Featured pages'));

			// limit to seven links only
			if(@count($items) > $context['root_featured_count']) {
				@array_splice($items, $context['root_featured_count']);

				// link to the category page
				$url = Categories::get_permalink($anchor);
				$items[$url] = i18n::s('Featured pages').MORE_IMG;

			}

			// render html
			if(is_array($items))
				$items =& Skin::build_list($items, 'news');

			// we do have something to display
			if($items) {

				// animate the text if required to do so
				if($context['root_featured_layout'] == 'scroll') {
					$items = Skin::scroll($items);
					$box_id = 'scrolling_featured';
				} elseif($context['root_featured_layout'] == 'rotate') {
					$items = Skin::rotate($items);
					$box_id = 'rotating_featured';
				} else
					$box_id = 'featured';

				// make an extra box -- the css id is either #featured, #scrolling_featured or #rotating_featured
				$text .= Skin::build_box($title, $items, 'news', $box_id);
			}
		}
	}

	// save in cache, whatever change, for 5 minutes
	Cache::put($cache_id, $text, 'stable', 300);
}

// news
$context['components']['news'] = $text;

// list extra boxes
$text = '';
if($anchor = Sections::lookup('extra_boxes')) {

	// the maximum number of boxes is a global parameter
	if(!isset($context['site_extra_maximum']) || !$context['site_extra_maximum'])
		$context['site_extra_maximum'] = 7;

	// articles to be displayed as extra boxes
	if($items =& Articles::list_for_anchor_by('publication', $anchor, 0, $context['site_extra_maximum'], 'boxes')) {
		foreach($items as $title => $attributes)
			$text .= Skin::build_box($title, $attributes['content'], 'boxes', $attributes['id'])."\n";
	}

}

// boxes
$context['components']['boxes'] = $text;

// referrals, if any
if(Surfer::is_associate() || (isset($context['with_referrals']) && ($context['with_referrals'] == 'Y')))
	$context['components']['referrals'] =& Skin::build_referrals('index.php');

//
// compute navigation information -- $context['navigation']
//

// a meta link to a feeding page
$context['page_header'] .= "\n".'<link rel="alternate" href="'.$context['url_to_root'].Feeds::get_url('rss').'" title="RSS" type="application/rss+xml" />';

// a meta link to our blogging interface
$context['page_header'] .= "\n".'<link rel="EditURI" href="'.$context['url_to_home'].$context['url_to_root'].'services/describe.php" title="RSD" type="application/rsd+xml" />';

// render the skin
render_skin();

?>
