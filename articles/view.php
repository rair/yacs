<?php
/**
 * view one article
 *
 * @todo add 'add tag' button http://www.socialtext.com/products/tour/categories
 * @todo add list of watchers aside
 * @todo add a switcher to other pages of the section (moi-meme)
 * @todo link overlaid pages to a micro-summary page, as per Firefox 2
 *
 * The main panel has following elements:
 * - The article itself, with details, introduction, and main text. This may be overloaded if required.
 * - The list of related files
 * - The list of comments, if the focus is on comments or if comments are threaded
 * - The list of related links
 *
 * Details about this page are displayed if:
 * - surfer is a site associate or a section editor
 * - surfer is a member and ( ( global parameter content_without_details != Y ) or ( section option with_details == Y ) )
 *
 * @see skins/configure.php
 *
 * There are several options to display author's information, depending of option set in section.
 * Poster's avatar is displayed if the layout is a forum and if we are not building the page for a mobile device.
 *
 * If the main description field of the article has been split into pages with the keyword [code]&#91;page][/code],
 * a navigation menu is added at the bottom of the page to move around.
 *
 * A bar of links to previous, next and index pages is inserted at several places if the layout is a manual.
 * The idea comes from the layout of the [Link=MySQL Reference Manual]http://dev.mysql.com/doc/mysql/en/index.html[/link].
 *
 * @link http://dev.mysql.com/doc/mysql/en/index.html MySQL Reference Manual
 *
 * The files section is displayed only if some file has already been attached to this page.
 * Else only a command to add a file is displayed in the main menu bar.
 *
 * The list of comments is based on a specific layout, depending on options set for the anchoring section.
 * For layouts 'daily', 'manual' and 'yabb', a first page of comments is put below the article, and remaining comments
 * are available on secondary pages (see comments/list.php). For other layouts, the first comment is visible at
 * secondary page, and a simple link to it is put here.
 *
 * The links section is displayed only if some link has already been attached to this page.
 * Else a command to add a link is displayed in the main menu bar, and a command
 * to trackback is added to the bottom menu bar.
 *
 * An extended menu is featured at page bottom, which content depends on
 * items attached to this page.
 *
 * The extra panel has following elements:
 * - Navigation links to previous and next pages in the same section, if any
 * - Contextual links to switch to sections in the neighbour
 * - Tools including icons and links to comment the page, send an image, etc.
 * - The list of twin pages (with the same nick name)
 * - The list of related categories, into a sidebar box
 * - The nearest locations, if any, into a sidebar box
 * - Means to reference this page, into a sidebar box
 * - The top popular referrals, if any
 *
 * Several HTTP headers, or &lt;meta&gt; attributes of the displayed page, are set dynamically here
 * to help advanced web usage. This includes:
 * - a link to the section page (e.g., '&lt;link rel="contents" href="http://127.0.0.1/yacs/sections/view.php/4038" title="Ma cat&eacute;gorie" type="text/html" /&gt;')
 * - a link to a RDF description of this page (e.g., '&lt;link rel="meta" href="http://127.0.0.1/yacs/articles/describe.php/4310" title="rdf" type="application/rdf+xml" /&gt;')
 * - a rdf section implementing the [link=trackback]http://www.movabletype.org/docs/mttrackback.html[/link] interface
 * - a [link=pingback]http://www.hixie.ch/specs/pingback/pingback[/link] link (e.g., '&lt;link rel="pingback" href="http://here/yacs/services/pingback.php" /&gt;')
 * - a link to the [link=Comment API]http://wellformedweb.org/CommentAPI/[/link] for this page
 * - a link to the next page, if neighbours have been defined, enabling pre-fetching
 *
 * @link http://www.mozilla.org/projects/netlib/Link_Prefetching_FAQ.html Link Prefetching FAQ
 *
 * Meta information also includes:
 * - page description, which is a copy of the introduction, if any, or the default general description parameter
 * - page author, who is the original creator
 * - page publisher, if any
 *
 * The displayed article is saved into the history of visited pages if the global parameter
 * [code]pages_without_history[/code] has not been set to 'Y'.
 *
 * @see skins/configure.php
 *
 * The caching strategy for article rendering is mainly aiming to save on database
 * requests. Since this script udates $context['page_details'], $context['text'],
 * and $context['extra'], each of these is cached separately.
 * The caching topic is the reference of this article (e.g;, 'article:345').
 * Cache entries are purged directly either when the page is modified, or when
 * some object attached to it triggers the Article::touch() function.
 *
 * The permission assessment is based upon following rules applied in this order:
 * - associates and editors are allowed to move forward
 * - creator is allowed to view the page
 * - permission is denied if the anchor is not viewable
 * - access is restricted ('active' field == 'R'), but the surfer is an authenticated member
 * - public access is allowed ('active' field == 'Y')
 * - permission denied is the default
 *
 * Accept following invocations:
 * - view.php/12 (view the first page of the article document)
 * - view.php/12/nick_name (add nick name to regular id, for URL rewriting)
 * - view.php?id=12 (view the first page of the article document)
 * - view.php?id=12&variant=mobile (mobile edition)
 * - view.php/12/pages/1 (view the page 1 of the main content)
 * - view.php?id=12&pages=1 (view the page 1 of the main content)
 * - view.php/12/categories/1 (view the page 1 of the list of related categories)
 * - view.php?id=12&categories=1 (view the page 1 of the list of related categories)
 * - view.php/12/comments/1 (view the page 1 of the list of related comments)
 * - view.php?id=12&comments=1 (view the page 1 of the list of related comments)
 * - view.php/12/files/2 (view the page 2 of the list of related files)
 * - view.php?id=12&files=2 (view the page 2 of the list of related files)
 * - view.php/12/links/1 (view the page 1 of the list of related links)
 * - view.php?id=12&links=1 (view the page 1 of the list of related links)
 *
 * @link http://www.movabletype.org/docs/mttrackback.html TrackBack Technical Specification
 * @link http://www.hixie.ch/specs/pingback/pingback Pingback specification
 *
 * If this article, or one of its anchor, specifies a specific skin (option keyword '[code]skin_xyz[/code]'),
 * or a specific variant (option keyword '[code]variant_xyz[/code]'), they are used instead default values.
 *
 * @author Bernard Paques
 * @author GnapZ
 * @tester Ghjmora
 * @tester Eoin
 * @tester Mark
 * @tester Moi-meme
 * @tester Macnana
 * @tester Guillaume Perez
 * @tester Cyril Blondin
 * @tester NickR
 * @tester ThierryP
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// common definitions and initial processing
include_once '../shared/global.php';
include_once '../behaviors/behaviors.php';
include_once '../categories/categories.php';	// tags and categories
include_once '../comments/comments.php';		// attached comments and notes
include_once '../files/files.php';				// attached files
include_once '../images/images.php';			// attached images
include_once '../links/links.php';				// related pages
include_once '../locations/locations.php';
include_once '../overlays/overlay.php';
include_once '../versions/versions.php';		// back in history

// look for the id
$id = NULL;
if(isset($_REQUEST['id']))
	$id = $_REQUEST['id'];
elseif(isset($context['arguments'][0]))
	$id = $context['arguments'][0];
$id = strip_tags($id);

// encode ISO-8859-1 argument, if any
if(isset($_SERVER['HTTP_ACCEPT_CHARSET']) && preg_match('/^iso-8859-1/i', $_SERVER['HTTP_ACCEPT_CHARSET']))
	$id = utf8_encode($id);

// page within a page
$page = 1;
if(isset($_REQUEST['pages']))
	$page = $_REQUEST['pages'];
$page = strip_tags($page);

// no follow-up page yet
$zoom_type = '';
$zoom_index = 1;

// view.php?id=12&categories=2
if(isset($_REQUEST['categories']) && ($zoom_index = $_REQUEST['categories']))
	$zoom_type = 'categories';

// view.php?id=12&comments=2
elseif(isset($_REQUEST['comments']) && ($zoom_index = $_REQUEST['comments']))
	$zoom_type = 'comments';

// view.php?id=12&files=2
elseif(isset($_REQUEST['files']) && ($zoom_index = $_REQUEST['files']))
	$zoom_type = 'files';

// view.php?id=12&links=2
elseif(isset($_REQUEST['links']) && ($zoom_index = $_REQUEST['links']))
	$zoom_type = 'links';

// view.php/12/pages/2
elseif(isset($context['arguments'][1]) && ($context['arguments'][1] == 'pages') && isset($context['arguments'][2]))
	$page = $context['arguments'][2];

// view.php/12/files/2
elseif(isset($context['arguments'][1]) && isset($context['arguments'][2])) {
	$zoom_type = $context['arguments'][1];
	$zoom_index = $context['arguments'][2];
}

// view.php/12/nick name induces no particular processing

// get the item from the database
$item =& Articles::get($id);

// get poster profile, if any
$poster = array();
if(isset($item['create_id']))
	$poster =& Users::get($item['create_id']);

// get the related overlay, if any
$overlay = NULL;
if(isset($item['overlay']))
	$overlay = Overlay::load($item);

// get the related anchor, if any
$anchor = NULL;
if(isset($item['anchor']))
	$anchor = Anchors::get($item['anchor']);

// also load the article as an object
$article = NULL;
if(isset($item['id'])) {
	include_once 'article.php';
	$article = new Article();
	$article->load_by_content($item, $anchor);
}

// get related behaviors, if any
$behaviors = NULL;
if(isset($item['id']))
	$behaviors =& new Behaviors($item, $anchor);

// editors can do what they want on items anchored here
if(Surfer::is_member() && is_object($anchor) && $anchor->is_assigned())
	Surfer::empower();
elseif(isset($item['id']) && Articles::is_assigned($item['id']) && Surfer::is_member())
	Surfer::empower();

// readers have additional rights
elseif(Surfer::is_logged() && is_object($anchor) && $anchor->is_assigned())
	Surfer::empower('S');
elseif(isset($item['id']) && Articles::is_assigned($item['id']) && Surfer::is_logged())
	Surfer::empower('S');

// anonymous edition is allowed here
elseif(is_object($anchor) && $anchor->has_option('anonymous_edit'))
	Surfer::empower();
elseif(isset($item['options']) && $item['options'] && preg_match('/\banonymous_edit\b/i', $item['options']))
	Surfer::empower();

// members edition is allowed here
elseif(Surfer::is_member() && is_object($anchor) && $anchor->has_option('members_edit'))
	Surfer::empower();
elseif(Surfer::is_member() && isset($item['options']) && $item['options'] && preg_match('/\bmembers_edit\b/i', $item['options']))
	Surfer::empower();

// maybe this anonymous surfer is allowed to handle this item
elseif(isset($item['handle']) && Surfer::may_handle($item['handle']))
	Surfer::empower();

//
// is this surfer allowed to browse the page?
//

// associates, editors and readers can read this page
if(Surfer::is_empowered('S'))
	$permitted = TRUE;

// change default behavior
elseif(isset($item['id']) && is_object($behaviors) && !$behaviors->allow('articles/view.php', 'article:'.$item['id']))
	$permitted = FALSE;

// poster can always view the page
elseif(isset($item['create_id']) && Surfer::is($item['create_id']))
	$permitted = TRUE;

// the anchor has to be viewable by this surfer
elseif(is_object($anchor) && !$anchor->is_viewable())
	$permitted = FALSE;

// access is restricted to authenticated surfer
elseif(isset($item['active']) && ($item['active'] == 'R') && Surfer::is_logged())
	$permitted = TRUE;

// public access is allowed
elseif(isset($item['active']) && ($item['active'] == 'Y'))
	$permitted = TRUE;

// the default is to disallow access
else
	$permitted = FALSE;

//
// is this surfer allowed to change the page?
//

// associates and editors can do what they want
if(Surfer::is_empowered())
	$editable = TRUE;

// this page cannot be modified anymore
elseif(isset($item['locked']) && ($item['locked'] == 'Y'))
	$editable = FALSE;

// new posts are not allowed here
elseif(!isset($item['id']) && (is_object($anchor) && $anchor->has_option('locked')))
	$editable = FALSE;

// surfer created the page and the page has not been published
elseif(Surfer::get_id() && isset($item['create_id']) && ($item['create_id'] == Surfer::get_id())
	&& (!isset($item['publish_date']) || ($item['publish_date'] <= NULL_DATE)) )
	$editable = TRUE;

// surfer has created the published page and revisions are allowed
elseif(Surfer::get_id() && isset($item['create_id']) && ($item['create_id'] == Surfer::get_id())
	&& isset($item['publish_date']) && ($item['publish_date'] > NULL_DATE) && (!isset($context['users_without_revision']) || ($context['users_without_revision'] != 'Y')))
	$editable = TRUE;

// the anchor has to be editable by this surfer
elseif(is_object($anchor) && $anchor->is_editable())
	$editable = TRUE;

// the default is to disallow modifications
else
	$editable = FALSE;

//
// can the surfer publish this page?
//
if(!$editable)
	$publishable = FALSE;
elseif(isset($context['users_with_auto_publish']) && ($context['users_with_auto_publish'] == 'Y'))
	$publishable = TRUE;
elseif(is_object($anchor) && $anchor->has_option('auto_publish'))
	$publishable = TRUE;
elseif(Surfer::is_empowered())
	$publishable = TRUE;
else
	$publishable = FALSE;

// is the article on user watch list?
$in_watch_list = FALSE;
if(isset($item['id']) && Surfer::get_id())
	$in_watch_list = Members::check('article:'.$item['id'], 'user:'.Surfer::get_id());

// has this page some versions?
$has_versions = FALSE;
if(isset($item['id']) && !$zoom_type && $editable && Versions::count_for_anchor('article:'.$item['id']))
	$has_versions = TRUE;

// use a specific script to render the page in replacement of the standard one --also protect from hackers
if(isset($item['options']) && preg_match('/\bview_as_[a-zA-Z0-9_\.]+?\b/i', $item['options'], $matches) && is_readable($matches[0].'.php')) {
	include $matches[0].'.php';
	return;
} elseif(is_object($anchor) && ($viewer = $anchor->has_option('view_as')) && is_readable('view_as_'.$viewer.'.php')) {
	$name = 'view_as_'.$viewer.'.php';
	include $name;
	return;
}

// load the skin, maybe with a variant
load_skin('articles', $anchor, isset($item['options']) ? $item['options'] : '');

// clear the tab we are in
if(is_object($anchor))
	$context['current_focus'] = $anchor->get_focus();

// path to this page
if(is_object($anchor) && $anchor->is_viewable())
	$context['path_bar'] = $anchor->get_path_bar();
else
	$context['path_bar'] = array( 'articles/' => i18n::s('All pages') );

// page title
if(is_object($overlay))
	$context['page_title'] = $overlay->get_text('title', $item);
elseif(isset($item['title']) && $item['title'])
	$context['page_title'] = $item['title'];
else
	$context['page_title'] = i18n::s('No title has been provided.');

// page language, if any
if(isset($item['language']) && $item['language'] && ($item['language'] != 'none'))
	$context['page_language'] = $item['language'];

// modify this page
if(isset($item['id']) && !$zoom_type && $editable) {
	Skin::define_img('EDIT_ARTICLE_IMG', 'icons/articles/edit.gif');
	if(!is_object($overlay) || (!$label = $overlay->get_label('edit_command')))
		$label = i18n::s('Edit this page');
	$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'edit') => array('', EDIT_ARTICLE_IMG.$label, '', 'basic', '', i18n::s('Update the content of this page')) ));
}

// access previous versions, if any
if(isset($item['id']) && !$zoom_type && $editable && $has_versions) {
	Skin::define_img('HISTORY_TOOL_IMG', 'icons/tools/history.gif');
	$context['page_menu'] = array_merge($context['page_menu'], array( Versions::get_url('article:'.$item['id'], 'list') => array('', HISTORY_TOOL_IMG.i18n::s('History'), '', 'basic', '', i18n::s('Previous versions of this page')) ));
}

// publish this page
if(isset($item['id']) && !$zoom_type && $publishable) {

	if(!isset($item['publish_date']) || ($item['publish_date'] <= NULL_DATE)) {
		Skin::define_img('PUBLISH_ARTICLE_IMG', 'icons/articles/publish.gif');
		$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'publish') => PUBLISH_ARTICLE_IMG.i18n::s('Publish') ));
	} else {
		Skin::define_img('DRAFT_ARTICLE_IMG', 'icons/articles/draft.gif');
		$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'unpublish') => DRAFT_ARTICLE_IMG.i18n::s('Draft') ));
	}
}

// lock command provided to associates and authenticated editors
if(isset($item['id']) && !$zoom_type && (Surfer::is_associate() || (Surfer::is_member() && is_object($anchor) && $anchor->is_editable()))) {

	if(!isset($item['locked']) || ($item['locked'] == 'N')) {
		Skin::define_img('LOCK_TOOL_IMG', 'icons/tools/lock.gif');
		$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'lock') => LOCK_TOOL_IMG.i18n::s('Lock') ));
	} else {
		Skin::define_img('UNLOCK_TOOL_IMG', 'icons/tools/unlock.gif');
		$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'lock') => UNLOCK_TOOL_IMG.i18n::s('Unlock') ));
	}
}

// assign command provided to associates
if(isset($item['id']) && !$zoom_type && Surfer::is_associate()) {
	Skin::define_img('ASSIGN_TOOL_IMG', 'icons/tools/assign.gif');
	$context['page_menu'] = array_merge($context['page_menu'], array( Users::get_url('article:'.$item['id'], 'select') => ASSIGN_TOOL_IMG.i18n::s('Assign') ));
}

// review command provided to associates and section editors
if(isset($item['id']) && !$zoom_type && $editable) {
	Skin::define_img('STAMP_ARTICLE_IMG', 'icons/articles/stamp.gif');
	$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'stamp') => STAMP_ARTICLE_IMG.i18n::s('Stamp') ));
}

// delete command provided to associates and section editors
if(isset($item['id']) && !$zoom_type && $editable) {
	Skin::define_img('DELETE_ARTICLE_IMG', 'icons/articles/delete.gif');
	$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'delete') => DELETE_ARTICLE_IMG.i18n::s('Delete') ));
}

// duplicate command provided to associates and section editors
if(isset($item['id']) && !$zoom_type && (Surfer::is_associate() || (is_object($anchor) && $anchor->is_editable()))) {
	Skin::define_img('DUPLICATE_ARTICLE_IMG', 'icons/articles/duplicate.gif');
	$context['page_menu'] = array_merge($context['page_menu'], array( Articles::get_url($item['id'], 'duplicate') => DUPLICATE_ARTICLE_IMG.i18n::s('Duplicate') ));
}

// not found -- help web crawlers
if(!isset($item['id'])) {
	Safe::header('Status: 404 Not Found', TRUE, 404);
	Skin::error(i18n::s('No item has the provided id.'));

// permission denied
} elseif(!$permitted) {

	// anonymous users are invited to log in or to register
	if(!Surfer::is_logged())
		Safe::redirect($context['url_to_home'].$context['url_to_root'].'users/login.php?url='.urlencode(Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name'])));

	// permission denied to authenticated user
	Safe::header('Status: 403 Forbidden', TRUE, 403);
	Skin::error(i18n::s('You are not allowed to perform this operation.'));

// stop crawlers on non-published pages
} elseif((!isset($item['publish_date']) || ($item['publish_date'] <= NULL_DATE)) && !Surfer::is_logged()) {
	Safe::header('Status: 403 Forbidden', TRUE, 403);
	Skin::error(i18n::s('You are not allowed to perform this operation.'));

// display the article
} else {

	// behaviors can change page menu
	if(is_object($behaviors))
		$context['page_menu'] =& $behaviors->add_commands('articles/view.php', 'article:'.$item['id'], $context['page_menu']);

	// remember surfer visit
	Surfer::click('article:'.$item['id'], $item['active']);

	// increment silently the hits counter if not associate, nor creator, nor at follow-up page -- editors are taken into account
	if(Surfer::is_associate())
		;
	elseif(Surfer::get_id() && isset($item['create_id']) && (Surfer::get_id() == $item['create_id']))
		;
	elseif(!$zoom_type) {
		$item['hits'] = isset($item['hits'])?($item['hits']+1):1;
		Articles::increment_hits($item['id']);
	}

	// initialize the rendering engine
	Codes::initialize(Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']));

	// neighbours information
	$neighbours = NULL;
	if(is_object($anchor) && !$anchor->has_option('no_neighbours') && ($context['skin_variant'] != 'mobile'))
		$neighbours = $anchor->get_neighbours('article', $item);

	//
	// set page image -- $context['page_image']
	//

	// the article or the anchor icon, if any
	if(isset($item['icon_url']) && $item['icon_url'])
		$context['page_image'] = $item['icon_url'];
	elseif(is_object($anchor))
		$context['page_image'] = $anchor->get_icon_url();

	//
	// set page meta-information -- $context['page_header'], etc.
	//

	// add meta information, if any
	if(isset($item['meta']) && $item['meta'])
		$context['page_header'] .= $item['meta'];

	// a meta link to prefetch the next page
	if(isset($neighbours[2]) && $neighbours[2])
		$context['page_header'] .= "\n".'<link rel="next" href="'.$context['url_to_root'].$neighbours[2].'" title="'.encode_field($neighbours[3]).'"'.EOT;

	// a meta link to the section front page
	if(is_object($anchor))
		$context['page_header'] .= "\n".'<link rel="contents" href="'.$context['url_to_root'].$anchor->get_url().'" title="'.encode_field($anchor->get_title()).'" type="text/html"'.EOT;

	// a meta link to a description page (actually, rdf)
	$context['page_header'] .= "\n".'<link rel="meta" href="'.$context['url_to_root'].Articles::get_url($item['id'], 'describe').'" title="Meta Information" type="application/rdf+xml"'.EOT;

	// implement the trackback interface
	$permanent_link = $context['url_to_home'].$context['url_to_root'].Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);
	if($context['with_friendly_urls'] == 'Y')
		$trackback_link = $context['url_to_home'].$context['url_to_root'].'links/trackback.php/article/'.$item['id'];
	else
		$trackback_link = $context['url_to_home'].$context['url_to_root'].'links/trackback.php?anchor=article:'.$item['id'];
	$context['page_header'] .= "\n".'<!--'
		."\n".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'
		."\n".' 		xmlns:dc="http://purl.org/dc/elements/1.1/"'
		."\n".' 		xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">'
		."\n".'<rdf:Description'
		."\n".' trackback:ping="'.$trackback_link.'"'
		."\n".' dc:identifier="'.$permanent_link.'"'
		."\n".' rdf:about="'.$permanent_link.'" />'
		."\n".'</rdf:RDF>'
		."\n".'-->';

	// implement the pingback interface
	$context['page_header'] .= "\n".'<link rel="pingback" href="'.$context['url_to_root'].'services/ping.php" title="Pingback Interface"'.EOT;

	// implement the Comment API interface
	$context['page_header'] .= "\n".'<link rel="service.comment" href="'.$context['url_to_root'].Comments::get_url('article:'.$item['id'], 'service.comment').'" title="Comment Interface" type="text/xml"'.EOT;

	// show the secret handle at an invisible place, and only to associates
	if(Surfer::is_associate() && $item['handle'])
		$context['page_header'] .= "\n".'<meta name="handle" content="'.$item['handle'].'"'.EOT;

	// set specific headers
	if(isset($item['introduction']) && $item['introduction'])
		$context['page_description'] = $item['introduction'];
	if(isset($item['create_name']) && $item['create_name'])
		$context['page_author'] = $item['create_name'];
	if(isset($item['publish_name']) && $item['publish_name'])
		$context['page_publisher'] = $item['publish_name'];

	//
	// set page details -- $context['page_details']
	//

	// do not mention details at follow-up pages
	if(!$zoom_type) {

		// tags, if any
		if(isset($item['tags']) && $item['tags'])
			$context['page_tags'] = $item['tags'];

		// cache this component
		$cache_id = 'articles/view.php?id='.$item['id'].'#page_details';
		if(!$text =& Cache::get($cache_id)) {

			// one detail per line
			$text .= '<p class="details">';
			$details = array();

			// article rating, if the anchor allows for it, and if no rating has already been registered
			if(is_object($anchor) && !$anchor->has_option('without_rating') && !$anchor->has_option('rate_as_digg')) {

				// report on current rating
				$label = '';
				if($item['rating_count'])
					$label .= Skin::build_rating_img((int)round($item['rating_sum'] / $item['rating_count'])).' '.sprintf(i18n::ns('%d rate', '%d rates', $item['rating_count']), $item['rating_count']).' ';
				if(!$label)
					$label .= i18n::s('Rate this page');

				// link to the rating page
				$label = Skin::build_link(Articles::get_url($item['id'], 'rate'), $label, 'span', i18n::s('Rate this page'));

				// feature page rating
				$details[] = $label;
			}

			// the source, if any
			if($item['source']) {
				if(preg_match('/(http|https|ftp):\/\/([^\s]+)/', $item['source'], $matches))
					$item['source'] = Skin::build_link($matches[0], $matches[0], 'external');
				elseif(strpos($item['source'], '[') === 0) {
					if($attributes = Links::transform_reference($item['source'])) {
						list($link, $title, $description) = $attributes;
						$item['source'] = Skin::build_link($link, $title);
					}
				}
				$details[] = sprintf(i18n::s('Source: %s'), $item['source']);
			}

			// restricted to logged members
			if($item['active'] == 'R')
				$details[] = RESTRICTED_FLAG.' '.i18n::s('Access is restricted to authenticated members');

			// restricted to associates
			elseif($item['active'] == 'N')
				$details[] = PRIVATE_FLAG.' '.i18n::s('Access is restricted to associates and editors');

			// home panel
			if(Surfer::is_associate()) {
				if(isset($item['home_panel']) && ($item['home_panel'] == 'none'))
					$details[] = i18n::s('This page is NOT displayed at the front page.');
			}

			// expired article
			$now = gmstrftime('%Y-%m-%d %H:%M:%S');
			if((Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable()))
					&& ($item['expiry_date'] > NULL_DATE) && ($item['expiry_date'] <= $now)) {
				$details[] = EXPIRED_FLAG.' '.sprintf(i18n::s('Article has expired %s'), Skin::build_date($item['expiry_date']));
			}

			// article editors, for associates and section editors
			if((Surfer::is_associate() || (is_object($anchor) && $anchor->is_editable())) && ($items = Members::list_users_by_posts_for_member('article:'.$item['id'], 0, USERS_LIST_SIZE, 'compact'))) {

				// list all assigned users
				$details[] = Skin::build_link(Users::get_url('article:'.$item['id'], 'select'), i18n::s('Editors:'), 'basic').' '.Skin::build_list($items, 'comma');

			}

			// no more details
			if(count($details))
				$text .= ucfirst(implode(BR."\n", $details)).BR."\n";

			// other details
			$details = array();

			// the creator of this article, if associate or if editor or if not prevented globally or if section option
			if($item['create_date']
				&& (Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable())
						|| ((!isset($context['content_without_details']) || ($context['content_without_details'] != 'Y')) || (is_object($anchor) && $anchor->has_option('with_details')) ) ) ) {

				if($item['create_name'])
					$details[] = sprintf(i18n::s('posted by %s %s'), Users::get_link($item['create_name'], $item['create_address'], $item['create_id']), Skin::build_date($item['create_date']));
				else
					$details[] = Skin::build_date($item['create_date']);

			}

			// the publisher of this article, if any
			if(($item['publish_date'] > NULL_DATE)
				&& !strpos($item['edit_action'], ':publish')
				&& (Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable()))) {

				if($item['publish_name'])
					$details[] = sprintf(i18n::s('published by %s %s'), Users::get_link($item['publish_name'], $item['publish_address'], $item['publish_id']), Skin::build_date($item['publish_date']));
				else
					$details[] = Skin::build_date($item['publish_date']);
			}

			// last modification by creator, and less than 24 hours between creation and last edition
			if(($item['create_date'] > NULL_DATE) && ($item['create_id'] == $item['edit_id'])
					&& (SQL::strtotime($item['create_date'])+24*60*60 >= SQL::strtotime($item['edit_date'])))
				;
			// publication is the last action
			elseif(strpos($item['edit_action'], ':publish'))
				;
			elseif(Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable())
				|| ((!isset($context['content_without_details']) || ($context['content_without_details'] != 'Y')) || (is_object($anchor) && $anchor->has_option('with_details')) ) ) {

				if($item['edit_action'])
					$action = get_action_label($item['edit_action']).' ';
				else
					$action = i18n::s('edited');

				if($item['edit_name'])
					$details[] = sprintf(i18n::s('%s by %s %s'), $action, Users::get_link($item['edit_name'], $item['edit_address'], $item['edit_id']), Skin::build_date($item['edit_date']));
				else
					$details[] = $action.' '.Skin::build_date($item['edit_date']);

			}

			// last revision, if any
			if(isset($item['review_date']) && ($item['review_date'] > NULL_DATE) && Surfer::is_associate())
				$details[] = sprintf(i18n::s('reviewed %s'), Skin::build_date($item['review_date'], 'no_hour'));

			// signal articles to be published
			if(($item['publish_date'] <= NULL_DATE)) {
				if($publishable)
					$label = Skin::build_link(Articles::get_url($item['id'], 'publish'), i18n::s('not published'));
				else
					$label = i18n::s('not published');
				$details[] = DRAFT_FLAG.' '.$label;
			}

			// the number of hits
			if(($item['hits'] > 1)
				&& (Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable())
						|| ((!isset($context['content_without_details']) || ($context['content_without_details'] != 'Y')) || (is_object($anchor) && $anchor->has_option('with_details')) ) ) ) {

				// flag popular pages
				$popular = '';
				if($item['hits'] > 100)
					$popular = POPULAR_FLAG;

				// actually show numbers only to associates and editors
				if(Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable()) )
					$details[] = $popular.sprintf(i18n::s('%d hits'), $item['hits']);

				// show first hits
				elseif($item['hits'] < 100)
					$details[] = $popular.sprintf(i18n::s('%d hits'), $item['hits']);

				// other surfers will benefit from a stable ETag
				elseif($popular)
					$details[] = $popular;
			}

			// rank for this article
			if((Surfer::is_associate() || Articles::is_assigned($item['id']) || (is_object($anchor) && $anchor->is_editable())) && (intval($item['rank']) != 10000))
				$details[] = '{'.$item['rank'].'}';

			// locked article
			if(Surfer::is_member() && isset($item['locked']) && ($item['locked'] == 'Y') )
				$details[] = LOCKED_FLAG.' '.i18n::s('page is locked.');

			// in-line details
			if(count($details))
				$text .= ucfirst(implode(', ', $details))."\n";

			// reference this item
			if(Surfer::is_member()) {
				$text .= BR.sprintf(i18n::s('Code to reference this page: %s'), '[article='.$item['id'].']');

				// the nick name
				if($item['nick_name'] && ($link = normalize_shortcut($item['nick_name'])))
					$text .= BR.sprintf(i18n::s('Shortcut: %s'), $link);
			}

			// no more details
			$text .= "</p>\n";

			// save in cache
			Cache::put($cache_id, $text, 'article:'.$item['id']);
		}

		// update page details
		$context['page_details'] .= $text;

	}

	//
	// compute main panel -- $context['text']
	//

	// cache varies on $zoom_type and $page
	if($zoom_type)
		$cache_id = 'articles/view.php?id='.$item['id'].'#text#'.$zoom_type.'#'.$page;
	else
		$cache_id = 'articles/view.php?id='.$item['id'].'#text#'.$page;
	if(!$text =& Cache::get($cache_id)) {

		// insert anchor prefix
		if(is_object($anchor))
			$text .= $anchor->get_prefix();

		// display very few things if we are on a follow-up page (comments, files, etc.)
		if($zoom_type) {

			if(isset($item['introduction']) && $item['introduction'])
				$text .= Codes::beautify($item['introduction'], $item['options']);
			else
				$text .= '<div class="description">'.Skin::cap(Codes::beautify($item['description'], $item['options']), 50)."</div>\n";

		// else expose full details
		} else {

			// buttons to display previous and next pages, if any
			if(is_object($anchor) && $anchor->has_layout('manual'))
				$text .= Skin::neighbours($neighbours, 'manual');

			// link to the front page if on a mobile device
			if($context['skin_variant'] == 'mobile') {

				// use the same link than AvantGo to our front page
				$text .= '<p>'.date('j/n/Y').' - <a href="'.$context['url_to_root'].'">'.i18n::s('Home').'</a></p>'."\n";
			}

			// the poster profile, if any, at the beginning of the first page
			if(($page == 1) && isset($poster['id']) && is_object($anchor) && is_callable(array($anchor, 'get_user_profile')))
				$text .= $anchor->get_user_profile($poster, 'prefix');

			// only at the first page
			if($page == 1) {

				// article rating, if the anchor allows for it, and if no rating has already been registered
				if(is_object($anchor) && !$anchor->has_option('without_rating') && $anchor->has_option('rate_as_digg')) {

					// rating
					if($item['rating_count'])
						$rating_label = sprintf(i18n::ns('%s vote', '%s votes', $item['rating_count']), '<span class="big">'.$item['rating_count'].'</span>'.BR);
					else
						$rating_label = i18n::s('No vote');

					// rendering
					$text .= '<div class="digg"><div class="votes">'.$rating_label.'</div>'
						.'<div class="rate">'.Skin::build_link(Articles::get_url($item['id'], 'rate'), i18n::s('Rate it'), 'basic').'</div>'
						.'</div>';

					// signal DIGG
					define('DIGG', TRUE);
				}

				// the introduction text, if any
				if(is_object($overlay))
					$text .= Skin::build_block($overlay->get_text('introduction', $item), 'introduction');
				elseif(isset($item['introduction']) && trim($item['introduction']))
					$text .= Skin::build_block($item['introduction'], 'introduction');

				// get text related to the overlay, if any
				if(is_object($overlay))
					$text .= $overlay->get_text('view', $item);

			}

			// the beautified description, which is the actual page body
			if(trim($item['description'])) {

				// use adequate label
				if(is_object($overlay) && ($label = $overlay->get_label('description')))
					$text .= Skin::build_block($label, 'title');

				// provide only the requested page
				$pages = preg_split('/\s*\[page\]\s*/is', $item['description']);
				if($page > count($pages))
					$page = count($pages);
				if($page < 1)
					$page = 1;
				$description = $pages[ $page-1 ];

				// if there are several pages, remove toc and toq codes
				if(count($pages) > 1)
					$description = preg_replace('/\s*\[(toc|toq)\]\s*/is', '', $description);

				// beautify the target page
				$text .= '<div class="description">'.Codes::beautify($description, $item['options'])."</div>\n";

				// if there are several pages, add navigation commands to browse them
				if(count($pages) > 1) {
					$page_menu = array( '_' => i18n::s('Pages') );
					$home = Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);
					$prefix = Articles::get_url($item['id'], 'navigate', 'pages');
					$page_menu = array_merge($page_menu, Skin::navigate($home, $prefix, count($pages), 1, $page));

					$text .= Skin::build_list($page_menu, 'menu_bar');
				}

			}

			// the poster profile, if any, at the end of the page
			if(isset($poster['id']) && is_object($anchor) && is_callable(array($anchor, 'get_user_profile')))
				$text .= $anchor->get_user_profile($poster, 'suffix');

		}

		//
		// files attached to this article
		//

		// the list of related files if not at another follow-up page
		if(!$zoom_type || ($zoom_type == 'files')) {

			// build a complete box
			$box = array('bar' => array(), 'text' => '');

			// count the number of files in this article
			if($count = Files::count_for_anchor('article:'.$item['id'])) {
				$box['bar'] = array_merge($box['bar'], array('_count' => sprintf(i18n::ns('1 file', '%d files', $count), $count)));

				// list files by date (default) or by title (option files_by_title)
				$offset = ($zoom_index - 1) * FILES_PER_PAGE;
				if(preg_match('/\bfiles_by_title\b/i', $item['options']))
					$items = Files::list_by_title_for_anchor('article:'.$item['id'], $offset, FILES_PER_PAGE, 'no_anchor');
				else
					$items = Files::list_by_date_for_anchor('article:'.$item['id'], $offset, FILES_PER_PAGE, 'no_anchor');

				// actually render the html
				if(is_array($items))
					$box['text'] .= Skin::build_list($items, 'decorated');
				elseif(is_string($items))
					$box['text'] .= $items;

				// navigation commands for files
				$home = Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);
				$prefix = Articles::get_url($item['id'], 'navigate', 'files');
				$box['bar'] = array_merge($box['bar'], Skin::navigate($home, $prefix, $count, FILES_PER_PAGE, $zoom_index));

			}

			// the command to post a new file
			if(Files::are_allowed($anchor, $item)) {
				$link = 'files/edit.php?anchor='.urlencode('article:'.$item['id']);
				$box['bar'] = array_merge($box['bar'], array( $link => FILE_TOOL_IMG.i18n::s('Upload a file') ));
			}

			// some files have been attached to this page
			if(($page == 1) && ($count > 1)) {

				// the command to download all files
				$link = 'files/fetch_all.php?anchor='.urlencode('article:'.$item['id']);
				if($count > 20)
					$label = i18n::s('Zip 20 first files');
				else
					$label = i18n::s('Zip all files');
				$box['bar'] = array_merge($box['bar'], array( $link => $label ));

			}

			// build a box
			if($box['text']) {

				// show commands
				if(count($box['bar']) && ($context['skin_variant'] != 'mobile')) {

					// append the menu bar at the end
					$box['text'] .= Skin::build_list($box['bar'], 'menu_bar');

				}

				// insert a full box
				$box['text'] =& Skin::build_box(i18n::s('Files'), $box['text'], 'header1', 'files');
			}

			// there is some box content
			if(trim($box['text']))
				$text .= $box['text'];

		}

		//
		// comments attached to this article
		//

		// the list of related comments, if not at another follow-up page
		if(!$zoom_type || ($zoom_type == 'comments')) {

			// title label
			if(is_object($anchor) && $anchor->is_viewable())
				$title_label = ucfirst($anchor->get_label('comments', 'count_many'));
			else
				$title_label = i18n::s('Your comments');

//			// label for one comment
//			if(is_object($anchor) && $anchor->is_viewable())
//				$comment_label = $anchor->get_label('comments', 'count_one');
//			else
//				$comment_label = i18n::s('comment');

//			// label for several comments
//			if(is_object($anchor) && $anchor->is_viewable())
//				$comments_label = $anchor->get_label('comments', 'count_many');
//			else
//				$comments_label = i18n::s('comments');

			// label to create a comment
			if(is_object($anchor) && $anchor->is_viewable())
				$add_label = $anchor->get_label('comments', 'new_command');
			else
				$add_label = i18n::s('Add a comment');

			// layout is defined in anchor
			if(is_object($anchor) && $anchor->has_layout('compact')) {
				include_once '../comments/layout_comments.php';
				$layout =& new Layout_comments();

			} elseif(is_object($anchor) && $anchor->has_layout('daily')) {
				include_once '../comments/layout_comments_as_daily.php';
				$layout =& new Layout_comments_as_daily();

			} elseif(is_object($anchor) && $anchor->has_layout('decorated')) {
				include_once '../comments/layout_comments.php';
				$layout =& new Layout_comments();

			} elseif(is_object($anchor) && $anchor->has_layout('jive')) {
				include_once '../comments/layout_comments_as_jive.php';
				$layout =& new Layout_comments_as_jive();

			} elseif(is_object($anchor) && $anchor->has_layout('manual')) {
				include_once '../comments/layout_comments_as_manual.php';
				$layout =& new Layout_comments_as_manual();

			} elseif(is_object($anchor) && $anchor->has_layout('yabb')) {
				include_once '../comments/layout_comments_as_yabb.php';
				$layout =& new Layout_comments_as_yabb();

			// regular case
			} else {
				include_once '../comments/layout_comments.php';
				$layout =& new Layout_comments();
			}

			// provide author information to layout
			if(is_object($layout) && $item['create_id'])
				$layout->set_variant('user:'.$item['create_id']);

			// the maximum number of comments per page
			if(is_object($layout))
				$items_per_page = $layout->items_per_page();
			else
				$items_per_page = COMMENTS_PER_PAGE;

			// the first comment to list
			$offset = ($zoom_index - 1) * $items_per_page;
			if(is_object($layout) && method_exists($layout, 'set_offset'))
				$layout->set_offset($offset);

			// build a complete box
			$box = array('bar' => array(), 'prefix_bar' => array(), 'text' => '');

			// a navigation bar for these comments
			if($count = Comments::count_for_anchor('article:'.$item['id'])) {
				$box['bar'] = array_merge($box['bar'], array('_count' => sprintf(i18n::ns('1 comment', '%d comments', $count), $count)));

				// list comments by date
				$items = Comments::list_by_date_for_anchor('article:'.$item['id'], $offset, $items_per_page, $layout);

				// actually render the html
				if(is_array($items))
					$box['text'] .= Skin::build_list($items, 'rows');
				elseif(is_string($items))
					$box['text'] .= $items;

				// navigation commands for comments
				$prefix = Comments::get_url('article:'.$item['id'], 'navigate');
				$box['bar'] = array_merge($box['bar'],
					Skin::navigate(NULL, $prefix, $count, $items_per_page, $zoom_index, FALSE, TRUE));

			}

			// new comments are allowed
			if(Comments::are_allowed($anchor, $item)) {
				$box['bar'] = array_merge($box['bar'], array( Comments::get_url('article:'.$item['id'], 'comment') => array('', COMMENT_TOOL_IMG.$add_label, '', 'basic', '', i18n::s('Add a comment'))));

				// also feature this command at the top
				if($count > $items_per_page)
					$box['prefix_bar'] = array_merge($box['prefix_bar'], array( Comments::get_url('article:'.$item['id'], 'comment') => array('', COMMENT_TOOL_IMG.$add_label, '', 'basic', '', i18n::s('Add a comment'))));

			}

			// show commands
			if(count($box['bar']) && ($context['skin_variant'] != 'mobile')) {

				// shortcut to last comment in page
				if(is_object($layout) && ($count > 7)) {
					$box['prefix_bar'] = array_merge($box['prefix_bar'], array('#last_comment' => i18n::s('Page bottom')));
					$box['text'] .= '<span id="last_comment" />';
				}

				// commands before the box
				$box['text'] = Skin::build_list($box['prefix_bar'], 'menu_bar').$box['text'];

				// append the menu bar at the end
				$box['text'] .= Skin::build_list($box['bar'], 'menu_bar');

			}

			// build a box
			if($box['text']) {

				// put a title if there are other titles or if more than 2048 chars
				$title = '';
				if(preg_match('/(<h1|<h2|<h3|<table|\[title|\[subtitle)/i', $context['text'].$text) || (strlen($context['text'].$text) > 2048))
					$title = $title_label;

				// insert a full box
				$box['text'] =& Skin::build_box($title, $box['text'], 'header1', 'comments');
			}

			// there is some box content
			if(trim($box['text']))
				$text .= $box['text'];

		}

		//
		// links attached to this article
		//

		// the list of related links if not at another follow-up page
		if(!$zoom_type || ($zoom_type == 'links')) {

			// build a complete box
			$box = array('bar' => array(), 'text' => '');

			// a navigation bar for these links
			if($count = Links::count_for_anchor('article:'.$item['id'])) {
				$box['bar'] = array_merge($box['bar'], array('_count' => sprintf(i18n::ns('1 link', '%d links', $count), $count)));

				// list links by date (default) or by title (option links_by_title)
				$offset = ($zoom_index - 1) * LINKS_PER_PAGE;
				if(preg_match('/\blinks_by_title\b/i', $item['options']))
					$items = Links::list_by_title_for_anchor('article:'.$item['id'], $offset, LINKS_PER_PAGE);
				else
					$items = Links::list_by_date_for_anchor('article:'.$item['id'], $offset, LINKS_PER_PAGE);

				// actually render the html
				if(is_array($items))
					$box['text'] .= Skin::build_list($items, 'decorated');
				elseif(is_string($items))
					$box['text'] .= $items;

				// navigation commands for links
				$home = Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);
				$prefix = Articles::get_url($item['id'], 'navigate', 'links');
				$box['bar'] = array_merge($box['bar'], Skin::navigate($home, $prefix, $count, LINKS_PER_PAGE, $zoom_index));
			}

			// new links are allowed
			if(Links::are_allowed($anchor, $item)) {

				// the command to post a new link
				if($box['text'] && Surfer::is_member()) {
					Skin::define_img('NEW_LINK_IMG', 'icons/links/new.gif');
					$link = 'links/edit.php?anchor='.urlencode('article:'.$item['id']);
					$box['bar'] = array_merge($box['bar'], array( $link => NEW_LINK_IMG.i18n::s('Add a link') ));
				}

			}

			// build a box
			if($box['text']) {

				// show commands
				if(count($box['bar']) && ($context['skin_variant'] != 'mobile')) {

					// append the menu bar at the end
					$box['text'] .= Skin::build_list($box['bar'], 'menu_bar');

				}

				// insert a full box
				$box['text'] =& Skin::build_box(i18n::s('Links'), $box['text'], 'header1', 'links');

			}

			// there is some box content
			if(trim($box['text']))
				$text .= $box['text'];

		}

		//
		// trailer information
		//

		// add trailer information from the overlay, if any
		if(is_object($overlay))
			$text .= $overlay->get_text('trailer', $item);

		// add trailer information from this item, if any
		if(isset($item['trailer']) && trim($item['trailer']))
			$text .= Codes::beautify($item['trailer']);

		// buttons to display previous and next pages, if any
		if(is_object($anchor) && $anchor->has_layout('manual'))
			$text .= Skin::neighbours($neighbours, 'manual');

		// insert anchor suffix
		if(is_object($anchor))
			$text .= $anchor->get_suffix();

		// special layout for digg
		if(defined('DIGG'))
			$text = '<div class="digg_content">'.$text.'</div>';

		// save in cache
		Cache::put($cache_id, $text, 'article:'.$item['id']);

	}

	// update the main content panel
	$context['text'] .= $text;

	//
	// extra panel -- most content is cached, except commands specific to current surfer
	//


	// the poster profile, if any, aside
	if(isset($poster['id']) && is_object($anchor) && is_callable(array($anchor, 'get_user_profile')))
		$context['extra_prefix'] .= $anchor->get_user_profile($poster, 'extra');

	// cache content
	$cache_id = 'articles/view.php?id='.$item['id'].'#extra#head';
	if(!$text =& Cache::get($cache_id)) {

		// add extra information from this item, if any
		if(isset($item['extra']) && $item['extra'])
			$text .= Codes::beautify_extra($item['extra']);

		// add extra information from the overlay, if any
		if(is_object($overlay))
			$text .= $overlay->get_text('extra', $item);

		// save in cache
		Cache::put($cache_id, $text, 'article:'.$item['id']);
	}

	// update the extra panel
	$context['extra'] .= $text;

	// page tools
	//
	if(!$zoom_type && $editable) {

		// modify this page
		Skin::define_img('EDIT_ARTICLE_IMG', 'icons/articles/edit.gif');
		if(!is_object($overlay) || (!$label = $overlay->get_label('edit_command')))
			$label = i18n::s('Edit this page');
		$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'edit'), EDIT_ARTICLE_IMG.$label, 'basic', i18n::s('Update the content of this page'));

		// post an image, if upload is allowed
		if(Images::are_allowed($anchor, $item)) {
			Skin::define_img('IMAGE_TOOL_IMG', 'icons/tools/image.gif');
			$context['page_tools'][] = Skin::build_link('images/edit.php?anchor='.urlencode('article:'.$item['id']), IMAGE_TOOL_IMG.i18n::s('Add an image'), 'basic', i18n::s('You can upload a camera shot, a drawing, or any image file, to illustrate this page.'));
		}

		// attach a file, if upload is allowed
		if(Files::are_allowed($anchor, $item))
			$context['page_tools'][] = Skin::build_link('files/edit.php?anchor='.urlencode('article:'.$item['id']), FILE_TOOL_IMG.i18n::s('Upload a file'), 'basic', i18n::s('Do not hesitate to attach files related to this page.'));

		// comment this page if anchor does not prevent it
		if(Comments::are_allowed($anchor, $item))
			$context['page_tools'][] = Skin::build_link(Comments::get_url('article:'.$item['id'], 'comment'), COMMENT_TOOL_IMG.i18n::s('Add a comment'), 'basic', i18n::s('Express yourself, and say what you think.'));

		// add a link
		if(Links::are_allowed($anchor, $item))
			$context['page_tools'][] = Skin::build_link('links/edit.php?anchor='.urlencode('article:'.$item['id']), LINK_TOOL_IMG.i18n::s('Add a link'), 'basic', i18n::s('Contribute to the web and link to relevant pages.'));
	}

	// 'Share' box
	//
	$lines = array();

	// mail this page
	if(!$zoom_type && $editable && Surfer::get_email_address() && isset($context['with_email']) && ($context['with_email'] == 'Y')) {
		Skin::define_img('MAIL_TOOL_IMG', 'icons/tools/mail.gif');
		$lines[] = Skin::build_link(Articles::get_url($item['id'], 'mail'), MAIL_TOOL_IMG.i18n::s('Invite people'), 'basic', i18n::s('Spread the word'));
	}

	// the command to track back
	if(Surfer::is_logged()) {
		Skin::define_img('TRACKBACK_IMG', 'icons/links/trackback.gif');
		$lines[] = Skin::build_link('links/trackback.php?anchor='.urlencode('article:'.$item['id']), TRACKBACK_IMG.i18n::s('Reference this page'), 'basic', i18n::s('Various means to link to this page'));
	}

	// more tools
	if(((isset($context['with_export_tools']) && ($context['with_export_tools'] == 'Y'))
		|| (is_object($anchor) && $anchor->has_option('with_export_tools')))) {

		// check tools visibility
		if(Surfer::is_member() || (isset($context['with_anonymous_export_tools']) && ($context['with_anonymous_export_tools'] == 'Y'))) {

			// get a PDF version
			Skin::define_img('PDF_TOOL_IMG', 'icons/tools/pdf.gif');
			$lines[] = Skin::build_link(Articles::get_url($id, 'fetch_as_pdf'), PDF_TOOL_IMG.i18n::s('Save as PDF'), 'basic', i18n::s('Download this page as a PDF file.'));

			// open in Word
			Skin::define_img('MSWORD_TOOL_IMG', 'icons/tools/word.gif');
			$lines[] = Skin::build_link(Articles::get_url($id, 'fetch_as_msword'), MSWORD_TOOL_IMG.i18n::s('Copy in MS-Word'), 'basic', i18n::s('Copy this page in Microsoft MS-Word.'));

			// get a palm version
			Skin::define_img('PALM_TOOL_IMG', 'icons/tools/palm.gif');
			$lines[] = Skin::build_link(Articles::get_url($id, 'fetch_for_palm'), PALM_TOOL_IMG.i18n::s('Save in Palm'), 'basic', i18n::s('Fetch this page as a Palm memo.'));

		}
	}

	// export to XML command provided to associates -- complex command
	if(!$zoom_type && Surfer::is_associate() && Surfer::has_all()) {
		Skin::define_img('EXPORT_TOOL_IMG', 'icons/tools/export.gif');
		$lines[] = Skin::build_link(Articles::get_url($item['id'], 'export'), EXPORT_TOOL_IMG.i18n::s('Export to XML'), 'basic');
	}

	// print this page
	if(Surfer::is_logged() || (isset($context['with_anonymous_export_tools']) && ($context['with_anonymous_export_tools'] == 'Y'))) {
		Skin::define_img('PRINT_TOOL_IMG', 'icons/tools/print.gif');
		$lines[] = Skin::build_link(Articles::get_url($id, 'print'), PRINT_TOOL_IMG.i18n::s('Print this page'), 'basic', i18n::s('Get a paper copy of this page.'));
	}

	// in a side box
	if(count($lines))
		$context['extra'] .= Skin::build_box(i18n::s('Share'), Skin::finalize_list($lines, 'tools'), 'extra', 'share');

	// 'More information' box
	//
	$lines = array();

	// watch command is provided to logged surfers
	if(Surfer::get_id() && !$zoom_type) {

		$link = Users::get_url('article:'.$item['id'], 'track');

		if($in_watch_list)
			$label = i18n::s('Forget');
		else
			$label = i18n::s('Watch');

		Skin::define_img('WATCH_TOOL_IMG', 'icons/tools/watch.gif');
		$lines[] = Skin::build_link($link, WATCH_TOOL_IMG.$label, 'basic', i18n::s('Manage your watch list'));
	}

	// get news from rss
	if(isset($item['id']) && (!isset($context['skins_general_without_feed']) || ($context['skins_general_without_feed'] != 'Y')) ) {

		// list of attached files
		$lines[] = Skin::build_link($context['url_to_home'].$context['url_to_root'].Files::get_url('article:'.$item['id'], 'feed'), i18n::s('Recent files'), 'xml');

		// comments are allowed
		if(Comments::are_allowed($anchor, $item)) {
			$lines[] = Skin::build_link($context['url_to_home'].$context['url_to_root'].Comments::get_url('article:'.$item['id'], 'feed'), i18n::s('Recent comments'), 'xml');

			// public aggregators
			if(!isset($context['without_internet_visibility']) || ($context['without_internet_visibility'] != 'Y'))
				$lines[] = join(BR, Skin::build_subscribers($context['url_to_home'].$context['url_to_root'].Comments::get_url('article:'.$item['id'], 'feed'), $item['title']));
		}
	}

	// in a side box
	if(count($lines))
		$context['extra'] .= Skin::build_box(i18n::s('More information'), join(BR, $lines), 'extra', 'feeds');

	// cache content
	$cache_id = 'articles/view.php?id='.$item['id'].'#extra#tail';
	if(!$text =& Cache::get($cache_id)) {

		// twin pages
		if(isset($item['nick_name']) && $item['nick_name']) {

			// build a complete box
			$box['text'] = '';

			// list pages with same name
			$items = Articles::list_for_name($item['nick_name'], $item['id'], 'compact');

			// actually render the html for the section
			if(is_array($items))
				$box['text'] .= Skin::build_list($items, 'compact');
			if($box['text'])
				$text .= Skin::build_box(i18n::s('Related'), $box['text'], 'extra', 'twins');

		}

		// links to previous and next pages in this section, if any
		if(is_object($anchor) && !$anchor->has_option('no_neighbours') && ($context['skin_variant'] != 'mobile')) {

			// build a nice sidebar box
			if(isset($neighbours) && ($content = Skin::neighbours($neighbours, 'sidebar')))
				$text .= Skin::build_box(i18n::s('Navigation'), $content, 'navigation', 'neighbours');

		}

		// the contextual menu, in a navigation box, if this has not been disabled
		if( (!is_object($anchor) || !$anchor->has_option('no_contextual_menu'))
			&& isset($context['current_focus']) && ($menu =& Skin::build_contextual_menu($context['current_focus']))) {

			// use title from topmost level
			if(count($context['current_focus']) && ($top_anchor = Anchors::get($context['current_focus'][0]))) {
				$box_title = $top_anchor->get_title();
				$box_url = $top_anchor->get_url();

			// generic title
			} else {
				$box_title = i18n::s('Navigation');
				$box_url = '';
			}

			// in a navigation box
			$box_popup = '';
			$text .= Skin::build_box($box_title, $menu, 'navigation', 'contextual_menu', $box_url, $box_popup);
		}

		// categories attached to this article, if not at another follow-up page
		if(!$zoom_type || ($zoom_type == 'categories')) {

			// build a complete box
			$box['bar'] = array();
			$box['text'] = '';

			// list categories by title
			$offset = ($zoom_index - 1) * CATEGORIES_PER_PAGE;
			$items = Members::list_categories_by_title_for_member('article:'.$item['id'], $offset, CATEGORIES_PER_PAGE, 'sidebar');

			// the command to change categories assignments
			if(Categories::are_allowed($anchor, $item))
				$items = array_merge($items, array( Categories::get_url('article:'.$item['id'], 'select') => i18n::s('Assign categories') ));

			// actually render the html for the section
			if(is_array($box['bar']))
				$box['text'] .= Skin::build_list($box['bar'], 'menu_bar');
			if(is_array($items))
				$box['text'] .= Skin::build_list($items, 'compact');
			if($box['text'])
				$text .= Skin::build_box(i18n::s('See also'), $box['text'], 'navigation', 'categories');

		}

		// nearby locations, if any
		if(!$zoom_type) {

			// locate up to 5 neighbours
			$items = Locations::list_by_distance_for_anchor('article:'.$item['id'], 0, COMPACT_LIST_SIZE);
			if(@count($items))
				$text .= Skin::build_box(i18n::s('Neighbours'), Skin::build_list($items, 'compact'), 'navigation', 'locations');

		}

		// referrals, if any
		if(!$zoom_type && (Surfer::is_associate() || (isset($context['with_referrals']) && ($context['with_referrals'] == 'Y')))) {

			// in a sidebar box
			include_once '../agents/referrals.php';
			if($content = Referrals::list_by_hits_for_url($context['url_to_root_parameter'].Articles::get_url($item['id'])))
				$text .= Skin::build_box(i18n::s('Referrals'), $content, 'navigation', 'referrals');

		}

		// save in cache
		Cache::put($cache_id, $text, 'article:'.$item['id']);

	}

	// update the extra panel
	$context['extra'] .= $text;

	//
	// the AJAX part
	//

	$context['page_footer'] .= '<script type="text/javascript">// <![CDATA['."\n"
		."\n"
		.'// reload this page on update'."\n"
		.'var PeriodicalCheck = {'."\n"
		."\n"
		.'	url: "'.$context['url_to_home'].$context['url_to_root'].Articles::get_url($item['id'], 'check').'",'."\n"
		.'	timestamp: '.SQL::strtotime($item['edit_date']).','."\n"
		."\n"
		.'	initialize: function() { },'."\n"
		."\n"
		.'	subscribe: function() {'."\n"
		.'		this.ajax = new Ajax.Request(PeriodicalCheck.url, {'."\n"
		.'			method: "get",'."\n"
		.'			requestHeaders: {Accept: "application/json"},'."\n"
		.'			onSuccess: PeriodicalCheck.updateOnSuccess,'."\n"
		.'			onFailure: PeriodicalCheck.updateOnFailure });'."\n"
		.'	},'."\n"
		."\n"
		.'	updateOnSuccess: function(transport) {'."\n"
		.'		var response = transport.responseText.evalJSON(true);'."\n"
		.'		// page has been updated'."\n"
		.'		if(PeriodicalCheck.timestamp && response["timestamp"] && (PeriodicalCheck.timestamp != response["timestamp"])) {'."\n"
		.'			// reflect updater name in window title'."\n"
		.'			if(typeof this.windowOriginalTitle != "string")'."\n"
		.'				this.windowOriginalTitle = document.title;'."\n"
		.'			document.title = "[" + response["name"] + "] " + this.windowOriginalTitle;'."\n"
		.'			// smart reload of the page'."\n"
		.'			new Ajax.Updater( { success: $$("body")[0] }, window.location, { method: "get" } );'."\n"
		.'		}'."\n"
		.'		// wait for more time'."\n"
		.'		setTimeout("PeriodicalCheck.subscribe()", 120000);'."\n"
		.'	},'."\n"
		."\n"
		.'	updateOnFailure: function(transport) {'."\n"
		.'		setTimeout("PeriodicalCheck.subscribe()", 600000);'."\n"
		.'	}'."\n"
		."\n"
		.'}'."\n"
		."\n"
		.'// look for some page update'."\n"
		.'setTimeout("PeriodicalCheck.subscribe()", 120000);'."\n"
		."\n"
		.'// ]]></script>'."\n";

	//
	// put this page in visited items
	//
	if(!isset($context['pages_without_history']) || ($context['pages_without_history'] != 'Y')) {

		// put at top of stack
		if(!isset($_SESSION['visited']))
			$_SESSION['visited'] = array();
		$_SESSION['visited'] = array_merge(array(Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']) => Codes::beautify($item['title'])), $_SESSION['visited']);

		// limit to 7 most recent pages
		if(count($_SESSION['visited']) > 7)
			array_pop($_SESSION['visited']);

	}

}

// stamp the page
if(isset($item['edit_date']) && $item['edit_date'] && !preg_match('/\[table=(.+?)\]/i', $item['description']))
	$last_modified = SQL::strtotime($item['edit_date']);
else
	$last_modified = time();

// at the minimum, consider the date of the last configuration change
if($last_configured = Safe::filemtime('../parameters/control.include.php'))
	$last_modified = max($last_modified, $last_configured);

// render the skin
render_skin($last_modified);

?>