<?php
/**
 * customized layout for articles
 *
 * The two very first articles are displayed side-by-side, with a thumbnail image if one is present.
 *
 * Subsequent articles are listed below an horizontal line, as per decorated layout.
 *
 * @author Bernard Paques
 * @author Thierry Pinelli (ThierryP)
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */
Class Layout_articles_as_hardboiled extends Layout_interface {

	/**
	 * the preferred number of items for this layout
	 *
	 * @return 9 - two last articles first, plus 7 other pages
	 *
	 * @see skins/layout.php
	 */
	function items_per_page() {
		return 9;
	}

	/**
	 * list articles as boxesandarrows do
	 *
	 * @param resource the SQL result
	 * @return string the rendered text
	 *
	 * @see skins/layout.php
	**/
	function &layout(&$result) {
		global $context, $local;

		// we return some text
		$text = '';

		// empty list
		if(!SQL::count($result))
			return $text;

		// flag articles updated recently
		$now = gmstrftime('%Y-%m-%d %H:%M:%S');
		if($context['site_revisit_after'] < 1)
			$context['site_revisit_after'] = 2;
		$dead_line = gmstrftime('%Y-%m-%d %H:%M:%S', mktime(0,0,0,date("m"),date("d")-$context['site_revisit_after'],date("Y")));

		// build a list of articles
		include_once $context['path_to_root'].'articles/article.php';
		include_once $context['path_to_root'].'comments/comments.php';
		include_once $context['path_to_root'].'files/files.php';
		include_once $context['path_to_root'].'categories/categories.php';
		include_once $context['path_to_root'].'overlays/overlay.php';
		$item_count = 0;
		$items = array();
		while($item =& SQL::fetch($result)) {

			// next item
			$item_count += 1;

			// get the related overlay, if any
			$overlay = Overlay::load($item);

			// get the anchor
			$anchor = Anchors::get($item['anchor']);

			// the url to view this item
			$url = Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);

			// reset the rendering engine between items
			Codes::initialize($url);

			// one box per article
			$prefix = $suffix = '';

			// build a box around two first articles
			if($item_count == 1)
				$text .= '<div id="home_south">'."\n";
			elseif($item_count == 3)
				$text .= '</div><br style="clear: left;" />'."\n";

			// layout newest articles
			if($item_count < 3) {

				// style to apply
				switch($item_count) {
				case 1:
					$text .= '<div id="home_west">';
					break;
				case 2:
					$text .= '<div id="home_east">';
					break;
				}

				// the icon to put aside
				$icon = '';
				if($item['thumbnail_url']) {
					$icon = $item['thumbnail_url'];
				} elseif(is_object($anchor)) {
					$icon = $anchor->get_thumbnail_url();
				}
				if($icon)
					$text .= '<a href="'.$context['url_to_root'].$url.'" title="'.i18n::s('Read the page').'"><img src="'.$icon.'" class="left_image" alt=""'.EOT.'</a>';

				$text .= $this->layout_newest($item, $anchor).'</div>'."\n";

			// layout recent articles
			} else {

				// use the title to label the link
				if(is_object($overlay) && is_callable(array($overlay, 'get_live_title')))
					$title = $overlay->get_live_title($item);
				else
					$title = Codes::beautify_title($item['title']);

				// flag sticky pages
				if($item['rank'] < 10000)
					$prefix .= STICKY_FLAG;

				// flag articles that are dead, or created or updated very recently
				if(($item['expiry_date'] > NULL_DATE) && ($item['expiry_date'] <= $now))
					$prefix .= EXPIRED_FLAG;
				elseif($item['create_date'] >= $dead_line)
					$suffix .= NEW_FLAG;
				elseif($item['edit_date'] >= $dead_line)
					$suffix .= UPDATED_FLAG;

				// signal articles to be published
				if(($item['publish_date'] <= NULL_DATE) || ($item['publish_date'] > gmstrftime('%Y-%m-%d %H:%M:%S')))
					$prefix .= DRAFT_FLAG;

				// signal restricted and private articles
				if($item['active'] == 'N')
					$prefix .= PRIVATE_FLAG;
				elseif($item['active'] == 'R')
					$prefix .= RESTRICTED_FLAG;

				// the introductory text
				if($item['introduction']) {
					$suffix .= ' -&nbsp;'.Codes::beautify($item['introduction'], $item['options']);

					// link to description, if any
					if($item['description'])
						$suffix .= ' '.Skin::build_link($url, MORE_IMG, 'more', i18n::s('Read more')).' ';

				// else use a teaser, if no overlay
				} elseif(!is_object($overlay)) {
					$article =& new Article();
					$article->load_by_content($item);
					if($teaser = $article->get_teaser('teaser'))
						$suffix .= ' -&nbsp;'.$teaser;
				}

				// insert overlay data, if any
				if(is_object($overlay))
					$suffix .= $overlay->get_text('list', $item);

				// next line, except if we already are at the beginning of a line
				if($suffix && !preg_match('/<br\s*\/>$/', rtrim($suffix)))
					$suffix .= BR;

				// append details to the suffix
				$suffix .= '<span class="details">';

				// details
				$details = array();

				// the author
				if(isset($context['with_author_information']) && ($context['with_author_information'] == 'Y')) {
					if($item['create_name'] != $item['edit_name'])
						$details[] = sprintf(i18n::s('by %s, %s'), $item['create_name'], $item['edit_name']);
					else
						$details[] = sprintf(i18n::s('by %s'), $item['create_name']);
				}

				// the last action
				$details[] = get_action_label($item['edit_action']).' '.Skin::build_date($item['edit_date']);

				// the number of hits
				if(Surfer::is_logged() && ($item['hits'] > 1))
					$details[] = sprintf(i18n::s('%d hits'), $item['hits']);

				// info on related files
				if($count = Files::count_for_anchor('article:'.$item['id'], TRUE))
					$details[] = sprintf(i18n::ns('1 file', '%d files', $count), $count);

				// info on related links
				if($count = Links::count_for_anchor('article:'.$item['id'], TRUE))
					$details[] = sprintf(i18n::ns('1 link', '%d links', $count), $count);

				// info on related comments
				if($count = Comments::count_for_anchor('article:'.$item['id'], TRUE))
					$details[] = sprintf(i18n::ns('1 comment', '%d comments', $count), $count);

				// rating
				if($item['rating_count'] && !(is_object($anchor) && $anchor->has_option('without_rating')))
					$details[] = Skin::build_link(Articles::get_url($item['id'], 'rate'), Skin::build_rating_img((int)round($item['rating_sum'] / $item['rating_count'])), 'basic');

				// signal locked articles
				if(isset($item['locked']) && ($item['locked'] == 'Y'))
					$details[] = LOCKED_FLAG;

				// combine in-line details
				if(count($details))
					$suffix .= ucfirst(trim(implode(', ', $details)));

				// unusual ranks are signaled to associates
				if(($item['rank'] != 10000) && Surfer::is_empowered())
					$suffix .= ' {'.$item['rank'].'} ';

				// list up to three categories by title, if any
				$anchors = array();
				if($members = Members::list_categories_by_title_for_member('article:'.$item['id'], 0, 3, 'raw')) {
					foreach($members as $id => $attributes) {
						if($this->layout_variant != 'category:'.$id)
							$anchors[] = Skin::build_link(Categories::get_url($attributes['id'], 'view', $attributes['title']), $attributes['title'], 'category');
					}
				}

				// list section and categories in the suffix
				if(@count($anchors))
					$suffix .= BR.sprintf(i18n::s('In %s'), implode(' | ', $anchors));

				// end of details
				$suffix .= '</span>';

				// strip empty details
				$suffix = str_replace(BR.'<span class="details"></span>', '', $suffix);
				$suffix = str_replace('<span class="details"></span>', '', $suffix);

				// insert a suffix separator
	//			if(trim($suffix))
	//				$suffix = ' -&nbsp;'.$suffix;

				// the icon to put in the left column
				if($item['thumbnail_url'])
					$icon = $item['thumbnail_url'];

				// or inherit from the anchor
				elseif(is_object($anchor))
					$icon = $anchor->get_bullet_url();

				// list all components for this item
				$items[$url] = array($prefix, $title, $suffix, 'article', $icon);

			}

		}

		// extend the #home_south in case of floats
		if(($item_count > 1) && ($item_count < 3))
			$text .= '<p style="clear: left;">&nbsp;</p></div>'."\n";

		// turn the list to a string
		if(count($items))
			$text .= Skin::build_list($items, 'decorated');

		// end of processing
		SQL::free($result);

		return $text;
	}

	/**
	 * layout one of the newest articles
	 *
	 * @param array the article
	 * @param object the anchor of this article, if any
	 * @return string the rendered text
	**/
	function layout_newest($item, $anchor) {
		global $context, $local;

		// get the related overlay, if any
		$overlay = Overlay::load($item);

		// the url to view this item
		$url = Articles::get_url($item['id'], 'view', $item['title'], $item['nick_name']);

		// use the title to label the link
		if(is_object($overlay) && is_callable(array($overlay, 'get_live_title')))
			$title = $overlay->get_live_title($item);
		else
			$title = Codes::beautify_title($item['title']);

		// initialize variables
		$prefix = $suffix = $text = '';

		// help to jump here
		$prefix .= '<a id="article_'.$item['id'].'"></a>';

		// signal restricted and private articles
		if($item['active'] == 'N')
			$prefix .= PRIVATE_FLAG.' ';
		elseif($item['active'] == 'R')
			$prefix .= RESTRICTED_FLAG.' ';

		// rating
		if($item['rating_count'])
			$suffix .= Skin::build_link(Articles::get_url($item['id'], 'rate'), Skin::build_rating_img((int)round($item['rating_sum'] / $item['rating_count'])), 'basic', i18n::s('Rate this page'));

		// use the title as a link to the page
		$text .= $prefix.'<b>'.Skin::build_link($url, $title, 'basic', i18n::s('Read this article')).'</b>'.$suffix;

		// details
		$details = array();

		// the creator and editor of this article
		if(isset($context['with_author_information']) && ($context['with_author_information'] == 'Y')) {
			if($item['edit_name'] != $item['create_name'])
				$label = sprintf(i18n::s('by %s, %s'), ucfirst($item['create_name']), ucfirst($item['edit_name']));
			else
				$label = sprintf(i18n::s('by %s'), ucfirst($item['create_name']));
			$details[] = $label;
		}

		// poster details
		if(count($details))
			$text .= BR.'<span class="details">'.ucfirst(implode(', ', $details))."</span>\n";

		// the introductory text
		$introduction = '';
		if($item['introduction'])
			$introduction .= Codes::beautify($item['introduction'], $item['options']);
		elseif(!is_object($overlay))
			$introduction .= Skin::cap(Codes::beautify($item['description'], $item['options']), 70);
		if($introduction)
		$text .= '<p>'.$introduction.'</p>'."\n";

		// insert overlay data, if any
		if(is_object($overlay))
			$text .= $overlay->get_text('list', $item);

		// read this article
		$text .= '<p class="details right">'.Skin::build_link($url, i18n::s('Read article'), 'basic');

		// info on related files
		if($count = Files::count_for_anchor('article:'.$item['id'], TRUE)) {
			if($context['with_friendly_urls'] == 'Y')
				$file = 'articles/view.php/'.$item['id'].'/files/1';
			else
				$file = 'articles/view.php?id='.urlencode($item['id']).'&amp;files=1';
			$text .= ' ('.Skin::build_link($file, sprintf(i18n::ns('1 file', '%d files', $count), $count), 'basic').')';
		}

// 		// link to the anchor page
// 		if(is_object($anchor))
// 			$text .= BR.Skin::build_link($anchor->get_url(), $anchor->get_title(), 'basic', i18n::s('More similar pages in this section'));

		// list up to three categories by title, if any
		if($items = Members::list_categories_by_title_for_member('article:'.$item['id'], 0, 3, 'raw')) {
			$text .= BR;
			$first_category = TRUE;
			foreach($items as $id => $attributes) {
				if(!$first_category)
					$text .= ',';
				$text .= ' '.Skin::build_link(Categories::get_url($attributes['id'], 'view', $attributes['title']), $attributes['title'], 'basic', i18n::s('More similar pages in this category'));
				$first_category = FALSE;
			}
		}

		$text .= '</p>';

		return $text;
	}

}

?>