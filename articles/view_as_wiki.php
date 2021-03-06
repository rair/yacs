<?php
/**
 * structure a wiki page, with comments in a separate tab
 *
 * This script is included into [script]articles/view.php[/script], when the
 * option is set to 'view_as_wiki'.
 *
 * The basic structure is made of following panels:
 * - Information - with details, introduction, main text, files and links. This may be overloaded if required.
 * - Discussion - A thread of contributions, not in real-time
 *
 * @author Bernard Paques
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// loaded from articles/view.php
defined('YACS') or exit('Script must be included');

// links to previous and next pages, if any
if(isset($neighbours) && $neighbours)
	$context['text'] .= Skin::neighbours($neighbours, 'manual');

//
// panels
//
$panels = array();

//
// article panel
//

$article = '';

// put page title there
if($context['page_title']) {
	$article .= Skin::build_block($context['page_title'], 'page_title');
	define('without_page_title', TRUE);
}

// modify this page
if(Articles::allow_modification($item, $anchor)) {
	if(!is_object($overlay) || (!$label = $overlay->get_label('edit_command', 'articles')))
		$label = i18n::s('Edit this page');
	$menu = array( Articles::get_url($item['id'], 'edit') => fa::_("fa-edit").' '.$label);
	$article .= Skin::build_list($menu, 'menu_bar');
}


// insert anchor prefix
if(is_object($anchor))
	$article .= $anchor->get_prefix();

// article rating, if the anchor allows for it, and if no rating has already been registered
if(!Articles::has_option('without_rating', $anchor, $item) && Articles::has_option('rate_as_digg', $anchor, $item)) {

	// rating
	if($item['rating_count'])
		$rating_label = sprintf(i18n::ns('%s vote', '%s votes', $item['rating_count']), '<span class="big">'.$item['rating_count'].'</span>'.BR);
	else
		$rating_label = i18n::s('No vote');

	// a rating has already been registered
	$digg = '';
	if(isset($_COOKIE['rating_'.$item['id']]))
		Cache::poison();

	// where the surfer can rate this item
	else
		$digg = '<div class="rate">'.Skin::build_link(Articles::get_url($item['id'], 'like'), i18n::s('Rate it'), 'basic').'</div>';

	// rendering
	$article .= '<div class="digg"><div class="votes">'.$rating_label.'</div>'
		.$digg
		.'</div>';

	// signal DIGG
	define('DIGG', TRUE);
}

// special layout for digg
if(defined('DIGG'))
	$article .= '<div class="digg_content">';

// the owner profile, if any, at the beginning of the first page
if(isset($owner['id']) && is_object($anchor))
	$article .= $anchor->get_user_profile($owner, 'prefix', Skin::build_date($item['create_date']));

// only at the first page
if($page == 1) {

	// the introduction text, if any
	if(is_object($overlay))
		$article .= Skin::build_block($overlay->get_text('introduction', $item), 'introduction');
	elseif(isset($item['introduction']) && trim($item['introduction']))
		$article .= Skin::build_block($item['introduction'], 'introduction');

}

// get text related to the overlay, if any
if(is_object($overlay))
	$article .= $overlay->get_text('view', $item);

// description has been formatted in articles/view.php
if(isset($context['page_description']))
	$article .= $context['page_description'];

// special layout for digg
if(defined('DIGG'))
	$article .= '</div>';

// the owner profile, if any, at the end of the page
if(isset($owner['id']) && is_object($anchor))
	$article .= $anchor->get_user_profile($owner, 'suffix', Skin::build_date($item['create_date']));

// list files only to people able to change the page
if(Articles::allow_modification($item, $anchor))
	$embedded = NULL;
else
	$embedded = Codes::list_embedded($item['description']);

// build a complete box
$box = array('bar' => array(), 'text' => '');

// a navigation bar for these files
if($count = Files::count_for_anchor('article:'.$item['id'], FALSE, $embedded)) {
	if($count > 20)
		$box['bar'] += array('_count' => sprintf(i18n::ns('%d file', '%d files', $count), $count));

	// list files by date (default) or by title (option files_by_title)
	if(Articles::has_option('files_by', $anchor, $item) == 'title')
		$items = Files::list_by_title_for_anchor('article:'.$item['id'], 0, 300, 'article:'.$item['id'], $embedded);
	else
		$items = Files::list_by_date_for_anchor('article:'.$item['id'], 0, 300, 'article:'.$item['id'], $embedded);

	// actually render the html
	if(is_array($items))
		$box['text'] .= Skin::build_list($items, 'decorated');
	elseif(is_string($items))
		$box['text'] .= $items;

	// the command to post a new file
	if(Files::allow_creation($item, $anchor, 'article')) {
		$box['bar'] += array('files/edit.php?anchor='.urlencode('article:'.$item['id']) => fa::_("fa-upload").' '.i18n::s('Add a file'));
	}

}

// display files
if($box['text'])
	$article .= Skin::build_content('files', i18n::s('Files'), $box['text'], $box['bar']);

// build a complete box
$box = array('bar' => array(), 'text' => '');

// a navigation bar for these links
if($count = Links::count_for_anchor('article:'.$item['id'])) {
	if($count > 20)
		$box['bar'] += array('_count' => sprintf(i18n::ns('%d link', '%d links', $count), $count));

	// list links by date (default) or by title (option links_by_title)
	if(Articles::has_option('links_by_title', $anchor, $item))
		$items = Links::list_by_title_for_anchor('article:'.$item['id'], 0, 50, 'no_anchor');
	else
		$items = Links::list_by_date_for_anchor('article:'.$item['id'], 0, 50, 'no_anchor');

	// actually render the html
	if(is_array($items))
		$box['text'] .= Skin::build_list($items, 'rows');
	elseif(is_string($items))
		$box['text'] .= $items;

	// new links are allowed
	if(Links::allow_creation($item, $anchor)) {
		$box['bar'] += array('links/edit.php?anchor='.urlencode('article:'.$item['id']) => fa::_("fa-chain").' '.i18n::s('Add a link'));
	}

}

// display links
if($box['text'])
	$article .= Skin::build_content('links', i18n::s('Links'), $box['text'], $box['bar']);

// add trailer information from the overlay, if any
if(is_object($overlay))
	$article .= $overlay->get_text('trailer', $item);

// add trailer information from this item, if any
if(isset($item['trailer']) && trim($item['trailer']))
	$article .= Codes::beautify($item['trailer']);

// links to previous and next pages, if any
if(isset($neighbours) && $neighbours)
	$article .= Skin::neighbours($neighbours, 'manual');

// insert anchor suffix
if(is_object($anchor))
	$article .= $anchor->get_suffix();

// display in a separate panel
if($article)
	$panels[] = array('article', i18n::s('Article'), 'article_panel', $article);

//
// append tabs from the overlay, if any -- they have been captured in articles/view.php
//
if(isset($context['tabs']) && is_array($context['tabs']))
	$panels = array_merge($panels, $context['tabs']);

//
// discussion tab - a near real-time interaction area
//
$discussion = '';
$discussion_count = 0;

// conversation is over
if(isset($item['locked']) && ($item['locked'] == 'Y')) {

	// display a transcript of past comments
	include_once $context['path_to_root'].'comments/comments.php';
	$items = Comments::list_by_date_for_anchor('article:'.$item['id'], 0, 500, 'excerpt');
	if(is_array($items))
		$discussion .= Skin::build_list($items, 'rows');
	elseif(is_string($items))
		$discussion .= $items;

// on-going conversation
} else {

	// get a layout for these comments
	$layout = Comments::get_layout($anchor, $item);

	// provide author information to layout
	if(is_object($layout) && $item['create_id'])
		$layout->set_focus('user:'.$item['create_id']);

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
	$box = array('top' => array(), 'bottom' => array(), 'text' => '');

	// feed the wall
	if(Comments::allow_creation($item, $anchor))
		$box['text'] .= Comments::get_form('article:'.$item['id']);

	// a navigation bar for these comments
	if($count = Comments::count_for_anchor('article:'.$item['id'])) {
		$discussion_count = $count;
		$box['bottom'] += array('_count' => sprintf(i18n::ns('%d comment', '%d comments', $count), $count));

		// list comments by date
		$items = Comments::list_by_date_for_anchor('article:'.$item['id'], $offset, $items_per_page, $layout, TRUE);

		// actually render the html
		if(is_array($items))
			$box['text'] .= Skin::build_list($items, 'rows');
		elseif(is_string($items))
			$box['text'] .= $items;

		// navigation commands for comments
		$prefix = Comments::get_url('article:'.$item['id'], 'navigate');
		$box['bottom'] += Skin::navigate(NULL, $prefix, $count, $items_per_page, $zoom_index, FALSE, TRUE);

	}

	// build a box
	$discussion .= Skin::build_content('comments', '', $box['text'], $box['top'], $box['bottom']);

}

// display in a separate panel
if($discussion) {
	$label = i18n::s('Discussion');
	if($discussion_count)
		$label .= ' ('.$discussion_count.')';
	$panels[] = array('discussion', $label, 'discussion_panel', $discussion);
}

// let YACS do the hard job
$context['text'] .= Skin::build_tabs($panels);

//
// extra panel -- most content is cached, except commands specific to current surfer
//

// the owner profile, if any, aside
if(isset($owner['id']) && is_object($anchor))
	$context['components']['profile'] = $anchor->get_user_profile($owner, 'extra', Skin::build_date($item['create_date']));

// page tools
//

// modify this page
if(Articles::allow_modification($item, $anchor)) {
	if(!is_object($overlay) || (!$label = $overlay->get_label('edit_command', 'articles')))
		$label = i18n::s('Edit this page');
	$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'edit'), fa::_("fa-edit").' '.$label, 'basic', i18n::s('Press [e] to edit'), FALSE, 'e');
}

// post an image, if upload is allowed
if(Images::allow_creation($item, $anchor)) {
	$context['page_tools'][] = Skin::build_link('images/edit.php?anchor='.urlencode('article:'.$item['id']), fa::_("fa-image").' '.i18n::s('Add an image'), 'basic', i18n::s('You can upload a camera shot, a drawing, or another image file.'));
}

// add a file, if upload is allowed
if(Files::allow_creation($item, $anchor, 'article')) {
	$context['page_tools'][] = Skin::build_link('files/edit.php?anchor='.urlencode('article:'.$item['id']), fa::_("fa-upload").' '.i18n::s('Add a file'), 'basic', i18n::s('Attach related files.'));
}

// add a link
if(Links::allow_creation($item, $anchor)) {
	$context['page_tools'][] = Skin::build_link('links/edit.php?anchor='.urlencode('article:'.$item['id']), fa::_("fa-chain").' '.i18n::s('Add a link'), 'basic', i18n::s('Contribute to the web and link to relevant pages.'));
}

// comment this page if anchor does not prevent it
if(Comments::allow_creation($item, $anchor)) {
	$context['page_tools'][] = Skin::build_link(Comments::get_url('article:'.$item['id'], 'comment'), fa::_("fa-commenting-o").' '.i18n::s('Post a comment'), 'basic', i18n::s('Express yourself, and say what you think.'));
}



// access previous versions, if any
if($has_versions && Articles::is_owned($item, $anchor)) {
	$context['page_tools'][] = Skin::build_link(Versions::get_url('article:'.$item['id'], 'list'), fa::_("fa-history").' '.i18n::s('Versions'), 'basic', i18n::s('Restore a previous version if necessary'));
}

// publish this page
if((!isset($item['publish_date']) || ($item['publish_date'] <= NULL_DATE)) && Articles::allow_publication($item,$anchor)) {
	$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'publish'), fa::_("fa-calendar-check-o").' '.i18n::s('Publish'));
}

// review command provided to container owners
if(is_object($anchor) && $anchor->is_owned()) {
	$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'stamp'), fa::_("fa-clock").' '.i18n::s('Stamp'));
}

// lock command provided to associates and authenticated editors
if(Articles::is_owned($item, $anchor)) {

	if(!isset($item['locked']) || ($item['locked'] == 'N')) {
		$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'lock'), fa::_("fa-lock").' '.i18n::s('Lock'));
	} else {
		$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'lock'), fa::_("fa-unlock").' '.i18n::s('Unlock'));
	}
}

// delete command
if(Articles::allow_deletion($item, $anchor)) {
	$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'delete'), fa::_("fa-trash").' '.i18n::s('Delete this page'));
}

// duplicate command provided to container owners
if(Articles::is_owned(NULL, $anchor)) {
	$context['page_tools'][] = Skin::build_link(Articles::get_url($item['id'], 'duplicate'), fa::_("fa-copy").' '.i18n::s('Duplicate this page'));
}

// use date of last modification into etag computation
if(isset($item['edit_date']))
	$context['page_date'] = $item['edit_date'];

// render the skin
render_skin();

?>
